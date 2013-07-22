<?php
/**
 * @package API_Con_Manager
 * @author Daithi Coombes <webeire@gmail.com>
 */

/**
 * The main class for the API Connection Manager plugin
 */
class API_Con_Manager{

	/**
	 * Factory method to get API Connection Manager object
	 * @param  array $config Deprecated, for now.
	 * @todo see if $config param is needed
	 * @return API_Con_Manager
	 */
	public static function factory( $config=null ){

		return new API_Con_Manager();
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
}