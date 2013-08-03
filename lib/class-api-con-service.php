<?php
/**
 * File for the API_Con_Service class
 */

/**
 * Class for handling all view actions, such as login forms etc.
 * @package  api-connection-manager
 * @author Daithi Coobmes <webeire@gmail.com>
 */
class API_Con_Service{
	
	/** @var string Default custom. Either: oauth1, oauth2, custom */
	public $auth_type = 'custom';
	/** @var string The authorize url. @see API_Con_Service::get_authorize_url() */
	public $auth_url;
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

		if( $args['options'] )
			$this->register_options( $args['options'] );
		unset( $args['options'] );

		$this->name = str_replace("API_Con_Module_", "", $class);
		$this->load_options();

		//set config
		foreach( $args as $field => $val )
			@$this->$field = $val;

		//set protected/private fields
		$this->redirect_url = admin_url( 'admin-ajax.php' ) . '?action=api-con-manager&api-con-action=request_token';
	}

	/**
	 * Returns the authorize url. 
	 * @uses string API_Con_Service::auth_url
	 * @todo  see about using the OAuth2 state parameter. This would mean storing state values in the db
	 * @return mixed Will return API_Con_Error if invalid or missing authorize url.
	 */
	public function get_authorize_url(){
		if( ! API_Con_Manager::valid_url( $this->auth_url ) )
			return new API_Con_Error( 'Invalid authorize url' );

		$consumer = API_Con_Manager::get_consumer( $this );

		switch ($this->auth_type) {
			case 'oauth2':
				
				$url = $this->auth_url . '?' . http_build_query(array(
					'client_id' => $consumer->key,
					'response_type' => 'code',
					'redirect_uri' => $this->get_redirect_url()
				));

				break;
			
			default:
				# code...
				break;
		}

		return $url;
	}

	/**
	 * Return the login url
	 * @return string the full `URI` to login this service
	 */
	public function get_login_url(){
		return admin_url('admin-ajax.php') . '?action=api-con-manager&api-con-action=service_login&service=' . $this->name;
	}

	/**
	 * Returns the optiions for this service
	 * @return array
	 */
	public function get_options(){
		return $this->options;
	}

	/**
	 * Get redirect url for this service
	 * @return string
	 */
	public function get_redirect_url(){
		return $this->redirect_url;
	}

	/**
	 * Get an access token
	 * @param  API_Con_DTO $dto The data transport object containing the code value
	 * @todo  write unit tests for this method
	 * @return OAuthToken Returns API_Con_Error if error
	 */
	public function get_token( API_Con_DTO $dto ){

		$code = $dto->data['code'];

		$res = $this->request( $this->token_url, array(
			'client_id' => $this->key,
			'client_secret' => $this->secret,
			'redirect_uri' => $this->get_redirect_url(),
			'code' => $code
			), 'GET', false, false );
		if( is_wp_error( $res ) )
			return $res;

		parse_str( $res['body'], $body );
		var_dump($body);
		return new OAuthToken( $body[ 'access_token' ], null );
	}

	/**
	 * Register the option names
	 * @param  array  $keys The option keys
	 */
	protected function register_options( array $keys ){

		$this->options = array();
		foreach( $keys as $key )
			$this->options[ $key ] = '';

		$this->load_options();
	}

	/**
	 * Make a request to the remote api.
	 * If not connected will die() with login link, or print login url.
	 * @param  string $url    endpoint
	 * @param  array  $params parameters to be sent
	 * @param  string $method Default GET
	 * @param boolean $die Default true. Wether to die with html login link or return login url, if not connected.
	 * @param boolean $check_connect Default true. Whether to test if connected or not.
	 * @return stdClass Returns API_Con_Error on error.
	 */
	public function request( $url=null, $params=array(), $method='GET', $die=true, $check_connect=true ){

		//get full target url or return API_Con_Error
		$url = $this->get_endpoint_http_url( $url );
		if( is_wp_error( $url ) )
			return $url;
		
		//check if connected
		if( 
			!API_Con_Manager::is_connected( $this ) &&
			$check_connect
		){
			$link = '<a href="' . $this->get_login_url() . '" target="_new">Login to ' . $this->name . '</a>';
			if( $die )
				die( $link );
			else print $link;
		}

		if( strtolower( $method )==='get' )
			$res = wp_remote_get( $url, $params );
		else
			$res = wp_remote_post( $url, array( 'body' => $params ) );

		return $res;
	}

	/**
	 * Store options
	 * @param array $options Array of key value pairs.
	 */
	public function set_options( array $options ){

		$service_options = API_Con_Model::get("service_options");
		if( !$service_options )
			$service_options = array();

		foreach($options as $key=>$val)
			if( isset($this->options[ $key ]) )
				$service_options[ $this->name ][ $key ] = $val;
		
		API_Con_Model::set("service_options", $service_options);
		$this->options = $service_options[ $this->name ];
	}

	/**
	 * Builds the endpoint url
	 * @param  string $target Default null. The target to append to $this->endpoint
	 * @return mixed         Returns (string) $url or API_Con_Error if error
	 */
	protected function get_endpoint_http_url( $target=null ){

		if( preg_match( '/^http/', $target ) )
			return $target;

		//build full url
		if( @$this->endpoint )
			$url = $this->endpoint . $target;
		else
			$url = $target;

		if( !API_Con_Manager::valid_url( $url ) ) 
			return new API_Con_Error( 'Please provide a valid url' );

		return $url;
	}

	/**
	 * Load options from the db
	 * @return array Also returns the options
	 */
	public function load_options(){

		$service_options = API_Con_Model::get( "service_options" );
		$options = $service_options[ $this->name ];

		if( count( $options ) )
			foreach( $options as $key=>$val )
				$this->options[$key] = $val;

		return $this->options;
	}
}