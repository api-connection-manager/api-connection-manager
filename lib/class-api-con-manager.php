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
	 * @param  WP_User         $user    Default null. If not set then will use
	 * the currently logged in user.
	 * @return array
	 */
	public static function connect_user( API_Con_Service $service, $user=null ){

		//use current user?
		if ( !$user )
			$user = wp_get_current_user();

		if ( $user->ID==0 )
			return new API_Con_Error( 'Cannot connect user. No user logged in' );

		//check for access token
		if ( get_class($service->token)!='OAuthToken' )
			return new API_Con_Error( 'No access token set for this service' );

		//build connections array()
		$connections = get_user_meta(
			$user->ID, 
			API_Con_Model::$meta_keys['user_connections'],
			true
		);
		if ( !is_array($connections) )
			$connections = array();
		$connections[$service->name] = $service->token;
		
		//set and return
		update_user_meta(
			$user->ID,
			API_Con_Model::$meta_keys['user_connections'],
			$connections
		);
		return $connections;
	}

	/**
	 * Do a callback.
	 * @param  mixed $callback The class/method/function data
	 * @param  API_Con_DTO $dto      The data transport object
	 * @param  API_CON_Service $service  The service to pass to callback
	 * @return mixed           Returns the callback call.
	 */
	public static function do_callback( $callback, $dto, $service ){
		if ( is_array($callback) ){	
			$class_name = $callback[0];
			$method = $callback[1];
			$class = new $class_name();
			return $class->$method( $service, $dto );
		}
		else
			return $callback( $service, $dto );
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
	 * @param  enum $type Default all. 'active'|'inactive'|'all'
	 * @todo  try implement WP_Filesystem for scanning the modules directory
	 * @return array       An array of service objects.
	 */
	public static function get_services( $type = null ){
		$services = array();
		
		switch ( $type ) {
			case 'inactive':
				$db_services = API_Con_Model::get( API_Con_Model::$meta_keys['services'] );
				if ( !$db_services )
					return array();

					foreach( $db_services['inactive'] as $service)
						$services[] = API_Con_Manager::get_service( $service );
				break;
			

			case 'active':
					$db_services = API_Con_Model::get( API_Con_Model::$meta_keys['services'] );
					if ( !$db_services )
						return array();

					foreach( $db_services['active'] as $service )
						$services[] = API_Con_Manager::get_service( $service );
				break;

			//returns all
			case 'all':
				$handle = opendir( self::get_module_dir() );

				while ( false !== ( $file = readdir( $handle ) ) ) {
					if ( $file == '.' || $file == '..' )
						continue;
					preg_match( '/[^class-](.+)[^\.php]/i', $file, $matches );
					$services[] = API_Con_Manager::get_service( ucfirst( $matches[0] ) );
				}
			break;

			//default return API_Con_Error
			default:
				return new API_Con_Error('Invalid param $type: ' . $type);
				break;
		}

		return $services;
	}

	/**
	 * Get user connections from usermeta.
	 * @see  API_Con_Manager::connect_user()
	 * @return array
	 */
	public static function get_user_connections(){

		$user = wp_get_current_user();
		return get_user_meta(
			$user->ID, 
			API_Con_Model::$meta_keys['user_connections'], 
			array()
		);
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
	 * @param array  $services An array of service objects
	 * @param enum $action   activate|deactivate
	 * @return  boolean
	 */
	public static function set_service_states( array $services, $action ){

		$db_services = (array) API_Con_Model::get( API_Con_Model::$meta_keys['services'] );
    	if ( $action == 'activate' ){
    		$update = 'active';
    		$delete = 'inactive';
    	}elseif ( $action == 'deactivate' ){
    		$update = 'inactive';
    		$delete = 'active';
    	}else
    		return new API_Con_Error( 'invalid action for API_Con_Manager::set_service_states' );

    	//rebuild services[]
		foreach ( $services as $service ) {
			if ( !API_Con_Manager::is_valid_service_name( $service->name ) )
				return new API_Con_Error( 'Invalid service name ' . $service->name );

			if ( false !== ( $key = @array_search( $service->name, $db_services[$delete] ) ) )
				unset( $db_services[$delete][$key] );
			if ( @in_array( $service->name, $db_services[$update] ) )
				continue;
			$db_services[ $update ][] = $service->name;
		}
		$db_services['inactive'] = array_values($db_services['inactive']);	//reset array keys
		$db_services['active'] = array_values($db_services['active']);

		//set new services[]
		$db_services[$update] = array_unique( $db_services[$update] );
		return API_Con_Model::set(
			API_Con_Model::$meta_keys['services'],
			$db_services
		);
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
	 * Register the dashboard menus.
	 * @return  array Returns admin slug, settings slug, options slug
	 */
	public function action_admin_menu(){
		$dash = new API_Con_Dash_Service();

		//dashboard
		$menu = add_menu_page(
			'API Connection Manager', 
			'API Manager',
			'manage_options',
			'api-con-manager',
			array(&$dash, 'get_page_services')
		);

		//services
		$services = add_submenu_page(
			'api-con-manager',
			'API Con Services',
			'Services',
			'manage_options',
			'api-con-services',
			array(&$dash, 'get_page_services')
			);

		//options
		$options = add_submenu_page(
			'api-con-manager',
			'API Con Options',
			'Options',
			'manage_options',
			'api-con-options',
			array(&$dash, 'get_page_options')
			);

		return array( $menu, $services, $options );
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
		if ( $dto && (!get_class( $dto ) == 'API_Con_DTO') )
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
		
		//get service and callback
		if ( @$_SESSION['api-con-manager-callback']['service'] )
			$service = API_Con_Manager::get_service( $_SESSION['api-con-manager-callback']['service'] );
		else
			$service = API_Con_Manager::get_service( $dto->data['service'] );
		$callback = @$_SESSION['api-con-manager-callback']['callback'];
		unset( $_SESSION['api-con-manager-callback'] );

		//do callbacks?
		if ( $callback )
			API_Con_Manager::do_callback( $callback, $dto, $service );
		
		//do action
		$method = $dto->data['api-con-action'];
		$dto = $this->$method( $dto, $service );

		//error report | return
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
	 * If user is logged in will connect that user to this service.
	 * @see  API_Con_Manager::response_listener()
	 * @param  API_Con_DTO $dto The dto, should contain the code.
	 * @param  API_Con_Service $service The service the dto is related to.
	 * @return API_Con_DTO           Returns the DTO with token set or
	 * API_Con_Error if issue.
	 */
	private function request_token( API_Con_DTO $dto, API_Con_Service $service ){
			
		//get token
		$token = $service->request_token( $dto );
		
		if ( is_wp_error($token) )
			return $token;

		//try connect user
		if ( is_user_logged_in() ){
			$user = wp_get_current_user();
			API_Con_Manager::connect_user( $service, $user );
		}

		return $service;
	}

	/**
	 * Redirects to remote authorization server. Callback transient record id is 
	 * taken from $dto->data['transid'].
	 * @uses  $_SESSION['api-con-manager-callback'] Stores service and callback.
	 * @uses  API_Con_Model::get_transient_by_id() To get callback value.
	 * @uses  API_Con_Service::get_authorize_url() The url to redirect to
	 * @param  API_Con_DTO $dto The data transport object
	 * @param API_Con_Service $service The service object
	 */
	private function service_login( API_Con_DTO $dto, API_Con_Service $service ){
		global $wpdb;

		//vars
		$_SESSION['api-con-manager-callback'] = array(
			'service' => $service->name,
			'callback' => API_Con_Model::get_transient_by_id( $dto->data['transid'] )
		);

		$service = API_Con_Manager::get_service( $dto->data['service'] );
		$url = $service->get_authorize_url();

		//redirect & die
		wp_redirect( $url );
		die();
	}
}