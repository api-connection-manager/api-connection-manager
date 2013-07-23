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
	/** @var string The endpoint url */
	protected $endpoint;
	/** @var string The redirect URI for this blog. @see API_Con_Service::__construct() */
	protected $redirect_url;

	/**
	 * When extending this class you must specify params
	 * @param array $args Parameters
	 */
	function __construct( array $args ){

		//set config
		foreach( $args as $field => $val )
			@$this->$field = $val;

		//set protected/private fields
		$this->redirect_url = admin_url( 'admin-ajax.php' ) . '?action=api-con-manager&api-con-action=request';
	}

	/**
	 * Returns the authorize url. 
	 * @uses string API_Con_Service::auth_url
	 * @return mixed Will return API_Con_Error if invalid or missing authorize url.
	 */
	public function get_authorize_url(){
		if( ! API_Con_Manager::valid_url( $this->auth_url ) )
			return new API_Con_Error( 'Invalid authorize url' );
		return $this->auth_url;
	}

	/**
	 * Return the login url
	 * @return string the full `URI` to login this service
	 */
	public function get_login_url(){
		return admin_url('admin-ajax.php') . '?action=api-con-login&service=' . $this->name;
	}

	/**
	 * Get redirect url for this service
	 * @return string
	 */
	public function get_redirect_url(){
		return $this->redirect_url;
	}

	/**
	 * Make a request to the remote api
	 * @param  string $url    endpoint
	 * @param  array  $params parameters to be sent
	 * @param  string $method Default GET
	 * @return API_Con_Consumer Returns API_Con_Error on error.
	 */
	public function request( $url=null, $params=array(), $method='GET' ){

		//get full target url or return API_Con_Error
		$url = $this->get_endpoint_http_url( $target );
		if( is_wp_error( $url ) )
			return $url;

		//setup consumer
		$consumer = API_Con_Manager::get_consumer( $this );

		return $consumer;
	}
	
	protected function get_endpoint_http_url( $target=null ){

		//build full url
		if( @$this->endpoint )
			$url = $this->endpoint . $target;
		else
			$url = $target;

		if( !API_Con_Manager::valid_url( $url ) ) 
			return new API_Con_Error('Please provide a valid url');
	}
}