<?php
/**
 * File for the API_Con_Service class
 */

/**
 * Class for handling all view actions, such as login forms etc.
 * @package  api-connection-manager
 * @author Daithi Coobmes <webeire@gmail.com>
 */
abstract class API_Con_Service{
	
	/** @var string Default custom. Either: oauth1, oauth2, custom */
	public $auth_type = 'custom';
	/** @var string The authorize url. @see API_Con_Service::get_authorize_url() */
	public $auth_url;
	/** @var string The filename for login image. Image must be in modules folders */
	public $button;
	/** @var string The client key/id */
	public $key;
	/** @var string The name of this service. Declared in factory method API_Con_Manager::get_service() */
	public $name;
	/** @var string The client secret */
	public $secret;
	/** @var OAuthConsumer @see API_Con_Consumer */
	protected $consumer;
	/** @var string The endpoint url */
	protected $endpoint;
	/** @var array An associative array of options for this service */
	protected $options;
	/** @var string The redirect URI for this blog. @see API_Con_Service::__construct() */
	protected $redirect_url;
	/** @var string The URI for requesting an access token */
	protected $token_url;

	/**
	 * When extending this class you must specify params
	 * @param array $args Parameters
	 * @param  string $class The child class name
	 */
	function __construct( array $args, $class ){

		if (  $args['options'] )
			$this->register_options( $args['options'] );
		unset( $args['options'] );

		$this->name = str_replace( 'API_Con_Module_', '', $class );
		$this->load_options();

		//set config
		foreach ( $args as $field => $val )
			@$this->$field = $val;

		//set protected/private fields
		$this->redirect_url = API_Con_Manager::get_redirect_url();
	}

	/**
	 * Returns the service user id.
	 * If data is passed as a parameter then this will be parsed for a user id,
	 * if not then a request to the service for a user id is made. Default is to
	 * make a request from the service.
	 * @param mixed $data Default false. A response body from the service to
	 * parse. If not passed then request to service will be made.
	 * @return string 
	 */
	abstract public function get_uid( $data=false );

	/**
	 * Returns the authorize url. 
	 * @uses string API_Con_Service::auth_url
	 * @todo  see about using the OAuth2 state parameter. This would mean 
	 * storing state values in the db
	 * @todo  oauth1 and custom types
	 * @return mixed Will return API_Con_Error if invalid or missing authorize url.
	 */
	public function get_authorize_url(){
		if ( ! API_Con_Manager::valid_url( $this->auth_url ) )
			return new API_Con_Error( 'Invalid authorize url' );

		$consumer = API_Con_Manager::get_consumer( $this );

		switch ( $this->auth_type ){
			case 'oauth2':
					
					$params = array(
							'client_id' => $consumer->key,
							'response_type' => 'code',
							'redirect_uri' => $this->redirect_url,
						);
					if ( $this->options['scope'] )
						$params['scope'] = $this->options['scope'];

					$url = $this->auth_url . '?' . http_build_query( $params );
					
				break;
			
			default:
				# code...
				break;
		}

		return $url;
	}

	/**
	 * Return the login link.
	 * All logins should open in a new tab. Callbacks are stored as transient
	 * records. The record id is required to be sent in the url request and 
	 * matched in _response_listener to get the callback. This is to ensure
	 * callback, class names etc are not displayed in the login link - only the
	 * transient record id will.
	 *
	 * Each call to get_login_link() will require its own transient. These
	 * recoreds must be deleted after x amount of time and have unique key.
	 * @see  API_Con_Model::set_transient()
	 * @see  API_Con_Manager::_response_listener()
	 * @param mixed $callback The callback function, or array(class, method)
	 * @param string $text Default false. Text for login link, will default to
	 * image, if no image will display service name.
	 * @param integer $transient_time Default 3600. Transient timeout in seconds
	 * @return string The html anchor link
	 */
	public function get_login_link( $callback, $text=false, $transient_time=3600 ){

		//use button
		if ( $this->button && !$text )
			$text = '<img src="' . API_Con_Manager::get_module_url() . '/' . $this->button . '"/>';

		if ( !$text )
			$text = $this->name;

		//return htm link | API_Con_Error
		if( !is_wp_error( $trans_id ) )
			return '<a href="' 
				. $this->get_login_url( $callback )
				. '" target="_new">'
				. $text
				. '</a>';
		else
			return $trans_id;
	}

	/**
	 * Return the login url
	 * @param array $extra_params Optional. Any extra query params.
	 * @param integer $trans_id Default false. Pass if a transient recored has
	 * already been setup. Leave empty otherwise.
	 * @return string the full `URI` to login this service
	 */
	public function get_login_url( $callback, $trans_id=false ){

		//generate unique key for callback transient
		if ( !$trans_id ){
			$key = API_Con_Model::$meta_keys['transient'][0];
			$x=0;
			if( API_Con_Model::get_transient( $key . '-' . $x ) )
				while( API_Con_Model::get_transient( $key . '-' . $x ) )
					$x++;
			$key .= '-' . $x;

			//force callback classname, instead of object reference
			if ( is_array($callback) )
				if ( is_object($callback[0]) )
					$callback[0] = get_class($callback[0]);

			//set transient
			$trans_id = API_Con_Model::set_transient(
				$key, 
				$callback,
				$transient_time
			);
		}

		$ret = admin_url( 'admin-ajax.php' ) 
			. '?action=api-con-manager&amp;api-con-action=service_login&amp;'
			. 'service=' 		. $this->name
			. '&amp;transid='	. $trans_id;

		return $ret;
	}

	/**
	 * Returns the optiions for this service
	 * @return array
	 */
	public function get_options(){

		return $this->options;
	}

	/**
	 * Request an access token.
	 * @param  API_Con_DTO $dto The data transport object containing the code value
	 * @param  array $params Aditional params to send
	 * @param string $method Default GET. Method to use for this service.
	 * @todo  write unit tests for this method
	 * @return OAuthToken Returns API_Con_Error if error
	 */
	public function request_token( API_Con_DTO $dto, $params = array(), $method='GET' ){

		//vars
   		$code = $dto->data['code'];
   		$consumer = API_Con_Manager::get_consumer( $this );
   		$params = array(
			'client_id' => $consumer->key,
			'client_secret' => $consumer->secret,
			'redirect_uri' => $this->redirect_url,
			'code' => $code,
		);

   		//request an access token
		if ( strtolower( $method ) === 'get' )
			$res = wp_remote_get( $this->token_url, array( 'body' => $params ) );
		else
			$res = wp_remote_post( $this->token_url, array( 'body' => $params ) );
		
		$error = $this->check_error( $res );
		if ( $error ){
			$this->token = $error;
			return $error;
		}

		//set and return
		parse_str( $res['body'], $body );
		$this->token = new OAuthToken( $body[ 'access_token' ], null );

		return $this->token;
	}

	/**
	 * Load options from the db
	 * @return array Also returns the options
	 */
	public function load_options(){

		$service_options = API_Con_Model::get( API_Con_Model::$meta_keys['service_options'] );
		$options = $service_options[ $this->name ];

		if ( count( $options ) )
			foreach ( $options as $key => $val )
				$this->options[$key] = $val;

		return $this->options;
	}

	/**
	 * Parse response from service
	 * @param  array $res The http(s) response from wp_remote_(*)
	 * @return mixed      Return value depends on the content-type of the $res
	 * param.
	 */
	public function parse_response( $res ){

		$type = $res['headers']['content-type'];

		//json
		if ( preg_match('/json/', $type) )
			$res = json_decode( $res['body'] );

		return (object) $res;
	}

	/**
	 * Make a request to the remote api.
	 * If not connected will return API_Con_Error with the html anchor for the 
	 * login link as message.
	 * @todo  write unit tests
	 * @param  string $url    endpoint
	 * @param  array  $params parameters to be sent
	 * @param  string $method Default GET
	 * @param boolean $check_connect Default true. Whether to test if connected or not.
	 * @return stdClass Returns API_Con_Error on error.
	 */
	public function request( $url = null, $params = array(), $method = 'GET', $check_connect = true ){
		
		//get full target url or return API_Con_Error
		$url = $this->get_endpoint_http_url( $url );
		if ( is_wp_error( $url ) )
			return $url;

		//if not connected return error with login link
		//its up to the plugin dev to work out if they want to print it etc
		if ( 
			!API_Con_Manager::is_connected( $this ) &&
			$check_connect
		)
			return new API_Con_Error( 'API Con: Can\'t make request, not connected to ' . $this->name );

		//auth_type params
		switch ($this->auth_type) {
			case 'oauth2':
				if ( strtolower( $method ) == 'get' || $method == null )
					$url .= '?access_token=' . $this->token->key;
				else
					$params['access_token'] = $this->token->key;
				break;
			
			default:
				break;
		}

		//make request
		if ( strtolower( $method ) == 'get' || $method == null )
			$res = wp_remote_get( $url, array( 'body' => $params ) );
		else
			$res = wp_remote_post( $url, array( 'body' => $params ) );

		//if reported as connected above, but request throws error, return it
		$error = $this->check_error( $res );
		if ( is_wp_error( $error ) )
			return $error;

		return $this->parse_response( $res );
	}

	public function set_token( OAuthToken $token ){

	}

	/**
	 * Store options
	 * @param array $options Array of key value pairs.
	 */
	public function set_options( array $options ){
		
		$service_options = API_Con_Model::get( API_Con_Model::$meta_keys['service_options'] );
		if ( !$service_options )
			$service_options = array();
		if ( !$service_options[ $this->name ] )
			$service_options[ $this->name ] = array();

		$service_options[ $this->name ] = array_merge(
			$service_options[ $this->name ],
			$options
		);
		
		API_Con_Model::set( API_Con_Model::$meta_keys['service_options'], $service_options );
		$this->options = $service_options[ $this->name ];
	}

	public function set_redirect_url( $url ){
		
		$this->redirect_url = $url;
	}

	/**
	 * Overwrite this to set a services custom error checks
	 * @param  mixed  $res The full response returned from WP_HTTP
	 * @return mixed      If error found returns API_Con_Error, false otherwise
	 */
	protected function check_error( $res ){
		if ( is_wp_error( $res ) )
			return new API_Con_Error( $res->get_error_message() );

		//get body
		if ( preg_match( '/text\/plain/', $res['headers']['content-type'] ) ){
			parse_str( $res['body'], $body );
			$body = (object) $body;
		}
		else
			$body = json_decode( $res['body'] );

		switch ( $this->auth_type ) {
			case 'oauth1':
				# code...
				break;
			
			case 'oauth2':
				if ( @$body->error ){
					( is_object( $body->error ) ) ?
						$error = $body->error->message :
						$error = $body->error;
					return new API_Con_Error( $error );
				}

				break;

			default:
				# code...
				break;
		}

		return false;
	}

	/**
	 * Builds the endpoint url
	 * @param  string $target Default null. The target to append to $this->endpoint
	 * @return mixed         Returns (string) $url or API_Con_Error if error
	 */
	protected function get_endpoint_http_url( $target = null ){

		if ( preg_match( '/^http/', $target ) )
			return $target;

		//build full url
		if ( @$this->endpoint )
			$url = $this->endpoint . $target;
		else
			$url = $target;

		if ( !API_Con_Manager::valid_url( $url ) ) 
			return new API_Con_Error( 'Please provide a valid url' );

		return $url;
	}

	/**
	 * Register the option names
	 * @param  array  $keys The option keys
	 */
	protected function register_options( array $keys ){

		$this->options = array();
		foreach ( $keys as $key )
			$this->options[ $key ] = '';

		$this->load_options();
	}
}