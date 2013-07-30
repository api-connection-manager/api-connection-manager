<?php
/**
 * The main file for the API Connection Manager Plugin
 * @author Daithi Coombes <webeire@gmail.com>
 */

/**
 * The main class for the API Con Model
 * @package  api-connection-manager
 * @author Daithi Coobmes <webeire@gmail.com>
 */
class API_Con_Model{

	/**
	 * Get a value from the db
	 * @param  string  $key       The key used to store value
	 * @param  boolean $delete 	Default false. Whether or not db value is deleted
	 * @return mixed             The unserialized value
	 */
	public static function get( $key, $delete=false ){
		
		$val = get_option( $key );

		if( $delete )
			delete_option( $key );

		return $val;
	}

	/**
	 * Store a value in the db
	 * @param string  $key       This is usually the name of the api con class calling this func
	 * @param mixed  $val       Anything that $wpdb methods can handle
	 */
	public static function set( $key, $val ){
		
		update_option( $key, $val );
	}
}