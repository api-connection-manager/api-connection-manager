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
	function __construct( $config = null ){

		//if bootstrapping
		if ( @$config['bootstrap'] )
			$this->bootstrap();
	}

	/**
	 * Set a connection between user and service
	 * @param  API_Con_Service $service The service to set
	 * @param  WP_User         $user    The user to connect with
	 * @param  array           $data    Tokens and other data needed by
	 * API_Con_Service::request()
	 * @return array
	 */
	public static function connect_user( API_Con_Service $service, WP_User $user, array $data ){

		//build connections array()
		$connections = get_user_meta(
			$user->ID, 
			API_Con_Model::$meta_keys['user_connections'], 
			array()
		);
		$connections[$service->name] = $data;

		//set and return
		return update_user_meta(
			$user->ID,
			API_Con_Model::$meta_keys['user_connections'],
			$connections
		);
	}

	/**
	 * Factory method to get API Connection Manager object
	 * @param  array $config Deprecated, for now.
	 * @todo see if $config param is needed
	 * @return API_Con_Manager
	 */
	public static function factory( $config = null ){
		return new API_Con_Manager( $config );
	}

	/**
	 * Factory method. Builds a new API_Con_Consumer
	 * @param  API_Con_Service $service The service to build the consumer around.
	 * @return OAuthConsumer
	 */
	public static function get_consumer( API_Con_Service $service ){
		//validate params
		$options = $service->get_options();
		
		if ( !$options['key'] || !$options['secret'] )
			return new API_Con_Error( 'Service missing client key or client secret' );

		return new OAuthConsumer( $options['key'], $options['secret'], $service->get_redirect_url() );
	}

	/**
	 * Get the serivce module directory
	 * @return string
	 */
	public static function get_module_dir(){
		return dirname( __FILE__ ) . '/../modules';
	}

	/**
	 * Factory method to get service object
	 * @param  string $name The service name
	 * @return API_Con_Service       Will return API_Con_Error when no service found
	 */
	public static function get_service( $name ){

		if ( empty($name) )
			return new API_Con_Error( 'No service name specified' );

		//load file
		$service_path = dirname( __FILE__ ) . '/../modules/class-' . strtolower( $name ) . '.php';
		if ( !file_exists( $service_path ) )
			return new API_Con_Error( 'Can\'t find module for ' . $name );
		else
			require_once( $service_path );

		//construct service
		$class = 'API_Con_Module_' . ucfirst( $name );
		$service = new $class();

		return $service;
	}

	/**
	 * Get range of service modules, depending on $type
	 * @param  enum $type Default all. 'active'|'inactive'
	 * @todo  try implement WP_Filesystem for scanning the modules directory
	 * @todo  write unit tests
	 * @return array       An array of services, if all then returns array['active'] and array['inactive']
	 */
	public static function get_services( $type = null ){
		$services = array();
		
		switch ( $type ) {
			case 'inactive':
				;
				break;
			

			case 'active':
					$db_services = API_Con_Model::get( 'services' );
					if ( !$db_services )
						return array();

					foreach( $db_services['active'] as $service )
						$services[] = API_Con_Manager::get_service( $service );
				break;

			case 'installed':
				$handle = opendir( self::get_module_dir() );

				while ( false !== ( $file = readdir( $handle ) ) ) {
					if ( $file == '.' || $file == '..' )
						continue;

					preg_match( '/[^class-](.+)[^\.php]/i', $file, $matches );
					$services[] = ucfirst( $matches[0] );
				}
			break;

			//default return all services
			default:
				# code...
				break;
		}

		return $services;
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
	 * Tests if valid service name.
	 * Checks for service module file in modules/ does not try to load
	 * or construct service module.
	 * @param  string  $service The service name to check
	 * @return boolean
	 */
	public static function is_valid_service_name( $service ){

		$service_path = dirname( __FILE__ ) . '/../modules/class-' . strtolower( $service ) . '.php';
		if ( file_exists( $service_path ) )
			return true;

		return false;
	}

	/**
	 * Activate / Deactivate services
	 * @param array  $services An array of service names
	 * @param enum $action   activate|deactivate
	 * @return  boolean
	 */
	public static function set_service_states( array $services, $action ){

		$db_services = (array) API_Con_Model::get( 'services' );

    	if ( $action == 'activate' ){
    		$update = 'active';
    		$delete = 'inactive';
    	}elseif ( $action == 'deactivate' ){
    		$update = 'inactive';
    		$delete = 'active';
    	}else
    		return false;

    	//rebuild services[]
		foreach ( $services as $service ) {
			if ( !API_Con_Manager::is_valid_service_name( $service ) )
				return new API_Con_Error( 'Invalid service name ' . $service );

			if ( false !== ( $key = @array_search( $service->name, $db_services[$delete] ) ) )
				unset( $db_services[$delete][$key] );
			if ( @in_array( $service->name, $db_services[$update] ) )
				continue;
			$db_services[ $update ][] = $service->name;
		}

		$db_services[$update] = array_unique( $db_services[$update] );
		return API_Con_Model::set( 'services', $db_services );
	}

	/**
	 * Check if a url is valid.
	 * Fix for php 5.2 bug with FILTER_VALIDATE_URL '-' are replaced in urls
	 * before check is done.
	 * @param  string $url The url to test
	 * @return mixed      Returns string( $url ) if valid or false if not
	 */
	public static function valid_url( $url ){

		$url = str_replace( '-', '', $url );
		return filter_var( $url, FILTER_VALIDATE_URL );
	}

	/**
	 * Register the dashboard menus
	 * @return  array Returns admin slug, sub menu slug, API_Con_Dash_Service
	 */
	public function action_admin_menu(){
		//dashboard
		$menu = add_menu_page(
			'API Connection Manager', 
			'API Manager',
			'manage_options',
			'api-con-manager',
			array(&$this, 'get_page')
		);

		//services
		$services = new API_Con_Dash_Service();
		$submenu = add_submenu_page(
			'api-con-manager',
			'API Con Services',
			'Services',
			'manage_options',
			'api-con-services',
			array(&$services, 'get_page')
			);

		return array( $menu, $submenu, $services );
	}

	/**
	 * Print the main API Con dashboard page
	 */
	public function get_page(){
		print '<h1>API Connection Manager</h1>';
	}

	/**
	 * Handles callbacks such as ajax requests.
	 * Can be used outside of ajax by passing object type API_Con_DTO with
	 * necessary data.
	 * @param  mixed $dto Default null. If used outside ajax pass a valid API_Con_DTO here
	 * @todo  write unit tests
	 * @return mixed Returns API_Con_DTO if all ok, or API_Con_Error if error
	 */
	public function response_listener( $dto = null ){

		//if used outside wp_ajax, make sure API_Con_DTO is passed
		if ( $dto && !is_string( $dto ) )
			if ( !get_class( $dto ) == 'API_Con_DTO' )
				return new API_Con_Error( 'API_Con_Manager::response_listener() takes API_Con_DTO as a parameter' );

		//construct DTO
		if ( !$dto )
			$dto = new API_Con_DTO( $_REQUEST );

		/**
		 * Security.
		 * Check valid api-con-action
		 */
		$valid_actions = array(
			'request_token',
			'service_login',
		);
		if ( !in_array( @$dto->data['api-con-action'], $valid_actions ) )
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

		//ajax
		add_action( 'wp_ajax_api-con-manager', array( &$this, 'response_listener' ) );
		add_action( 'wp_ajax_nopriv_api-con-manager', array( &$this, 'response_listener' ) );
		add_action( 'admin_menu', array( &$this, 'action_admin_menu' ) );

	}

	/**
	 * Oauth 1/2 callback to request token.
	 * @see  API_Con_Manager::response_listener()
	 * @param  API_Con_DTO $dto The dto.
	 * @return API_Con_DTO           Returns the DTO
	 */
	private function request_token( API_Con_DTO $dto ){
		
		$service = API_Con_Manager::get_service( $_SESSION['api-con-manager-callback']['service'] );
		$callback = $_SESSION['api-con-manager-callback']['callback'];
		unset($_SESSION['api-con-manager-callback']);

		if ( is_wp_error( $service ) )
			die( $service->get_error_message() );

		$token = (array) $service->get_token( $dto );
		$user = wp_get_current_user();
		API_Con_Manager::connect_user( $service, $user, $token );

		//do callback
		if ( is_array($callback) ){
			$class_name = $callback[0];
			$method = $callback[1];
			$class = new $class_name();
			$class->$method( $dto );
		}
		elseif( $callback )
			$callback( $dto );

		die('process finished');
	}

	/**
	 * Redirects to remote authorization server. Service name is taken from 
	 * $dto->data['service'] and callback transient record is taken from 
	 * $dto->data['transid']. Both are stored in a session.
	 * @uses  $_SESSION['api-con-manager-callback'] Stores service and callback.
	 * @uses  API_Con_Model::get_transient_by_id() To get callback value.
	 * @uses  API_Con_Service::get_authorize_url() The url to redirect to
	 * @param  API_Con_DTO $dto The data transport object
	 */
	private function service_login( API_Con_DTO $dto ){
		global $wpdb;

		//vars
		$_SESSION['api-con-manager-callback'] = array(
			'service' => $dto->data['service'],
			'callback' => API_Con_Model::get_transient_by_id( $dto->data['transid'] )
		);

		$service = API_Con_Manager::get_service( $dto->data['service'] );
		$url = $service->get_authorize_url();

		//redirect & die
		wp_redirect( $url );
		die();
	}
}