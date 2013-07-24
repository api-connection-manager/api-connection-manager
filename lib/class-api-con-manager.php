<?php
/**
 * The main file for the API Connection Manager Plugin
 * @author Daithi Coombes <webeire@gmail.com>
 */

/**
 * The main class for the API Connection Manager plugin
 * @package  api-connection-manager
 * @author Daithi Coobmes <webeire@gmail.com>
 */
class API_Con_Manager{

	/**
	 * Pass array('bootstrap'=>true) if bootstrapping the API Connection Manager. This
	 * needs to be done only once 
	 * @see index.php
	 * @param array $config An array of key value pairs.
	 */
	function __construct( $config=null ){

		//if bootstrapping
		if( @$config['bootstrap'] )
			$this->bootstrap();
	}

	/**
	 * Factory method to get API Connection Manager object
	 * @param  array $config Deprecated, for now.
	 * @todo see if $config param is needed
	 * @return API_Con_Manager
	 */
	public static function factory( $config=null ){
		return new API_Con_Manager( $config );
	}

	/**
	 * Factory method. Builds a new API_Con_Consumer
	 * @param  API_Con_Service $service The service to build the consumer around.
	 * @return OAuthConsumer
	 */
	public static function get_consumer( API_Con_Service $service ){
		//validate params
		if( !$service->key || !$service->secret )
			return new API_Con_Error( 'Service missing client key or client secret' );

		return new OAuthConsumer( $service->key, $service->secret, $service->get_redirect_url() );
	}

	/**
	 * Factory method to get service object
	 * @param  string $name The service name
	 * @return API_Con_Service       Will return API_Con_Error when no service found
	 */
	public static function get_service( $name ){

		//load file
		$service_path = dirname( __FILE__ ) . '/../modules/class-' . $name . '.php';
		if( !file_exists( $service_path ) )
			return new API_Con_Error( 'Can\'t find module for ' . $name );
		else
			require_once( $service_path );

		//construct service
		$class = 'API_Con_Module_' . ucfirst($name);
		$service = new $class();
		$service->name = $name;

		return $service;
	}

	/**
	 * Check if a service is connected.
	 * @todo  Design system for checking if services are connected or not.
	 * @param  API_Con_Service $service The service module.
	 * @return boolean Default false;
	 */
	public static function is_connected( API_Con_Service $service ){
		return false;
	}

	/**
	 * Check if a url is valid.
	 * Fix for php 5.2 bug with FILTER_VALIDATE_URL '-' are replaced in urls
	 * before check is done.
	 * @param  string $url The url to test
	 * @return mixed      Returns string( $url ) if valid or false if not
	 */
	public static function valid_url( $url ){

		$url = str_replace("-", "", $url);
		return filter_var($url, FILTER_VALIDATE_URL);
	}

	/**
	 * Handles callbacks such as ajax requests.
	 * Can be used outside of ajax by passing object type API_Con_DTO with
	 * necessary data.
	 * @param  mixed $dto Default null. If used outside ajax pass a valid API_Con_DTO here
	 * @return mixed Returns API_Con_DTO if all ok, or API_Con_Error if error
	 */
	public function response_listener( $dto=null ){

		//if used outside wp_ajax, make sure API_Con_DTO is passed
		if( $dto && !is_string($dto) )
			if( !get_class( $dto ) == 'API_Con_DTO' )
				return new API_Con_Error( 'API_Con_Manager::response_listener() takes API_Con_DTO as a parameter' );

		//construct DTO
		if( !$dto )
			$dto = new API_Con_DTO( $_REQUEST );

		/**
		 * Security.
		 * Check valid api-con-action
		 */
		$valid_actions = array(
			'request_token',
			'service_login'
		);
		if( !in_array( @$dto->data['api-con-action'], $valid_actions ) )
			return new API_Con_Error( 'Invalid request' );
		//end Security
		
		//run method
		$method = $dto->data['api-con-action'];
		$dto = $this->$method( $dto );

		return $dto;
	}

	/**
	 * Bootstraps the API Connection Manager. This should only be called once
	 * and currently is only called from index.php by passing 'bootstrap' => true
	 * to the __construct $config param
	 * @return void
	 */
	private function bootstrap(){

		add_action('wp_ajax_api-con-manager', array( &$this, 'response_listener' ) );
		add_action('wp_ajax_nopriv_api-con-manager', array( &$this, 'response_listener' ) );
	}

	/**
	 * Oauth 1/2 callback to request token.
	 * @see  API_Con_Manager::response_listener()
	 * @param  API_Con_DTO $dto The dto.
	 * @return API_Con_DTO           Returns the DTO
	 */
	private function request_token( API_Con_DTO $dto ){

		$key = __CLASS__ . '::service_login';
		$service = API_Con_Manager::get_service( API_Con_Model::get( $key, true ) );
		die();
	}

	/**
	 * Redirects to remote authorization server. Service name is taken from $dto->data['service']
	 * and stored in db.
	 * @uses  API_Con_Service::get_authorize_url() The url to redirect to
	 * @param  API_Con_DTO $dto The data transport object
	 */
	private function service_login( API_Con_DTO $dto ){
		
		//vars
		$service = API_Con_Manager::get_service( $dto->data['service'] );
		$url = $service->get_authorize_url();

		//store service in db
		$key = __CLASS__ . '::service_login';
		API_Con_Model::set( 
			$key, 
			$service->name
		);

		//redirect & die
		wp_redirect( $url );
		die();
	}
}