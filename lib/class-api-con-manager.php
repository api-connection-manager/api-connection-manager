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
	 * Factory method to get service object
	 * @param  string $name The service name
	 * @return API_Con_Service       Will return API_Con_Error when no service found
	 */
	public static function get_service( $name ){

		//load file
		if( !file_exists( 'modules/class-' . $name . '.php' ) )
			return new API_Con_Error( 'Can\'t find module for ' . $name );
		else
			require_once( 'modules/class-' . $name . '.php' );

		//construct service
		$class = 'API_Con_Module_' . ucfirst($name);
		$service = new $class();

		return $service;
	}
}