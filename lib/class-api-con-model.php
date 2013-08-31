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

	public static $meta_keys = array(
		'transient' => array( 'API_Con_Service-callback' )
	);

	/**
	 * Get a value from the db
	 * @param  string  $key       The key used to store value
	 * @param  boolean $delete 	Default false. Whether or not db value is deleted
	 * @return mixed             The unserialized value
	 */
	public static function get( $key, $delete = false ){
		
		$val = get_option( $key );

		if ( $delete )
			delete_option( $key );

		return $val;
	}

	public static function get_transient( $key ){
		return get_transient( $key );
	}

	/**
	 * Store a value in the db
	 * @param string  $key       This is usually the name of the api con class calling this func
	 * @param mixed  $val       Anything that $wpdb methods can handle
	 */
	public static function set( $key, $val ){
		
		return update_option( $key, $val );
	}

	/**
	 * Set a transient record.
	 * Method must return the record id from the database.
	 * @see  API_Con_Service::get_login_link()
	 * @param string  $key    The transient name.
	 * @param mixed  $val    The transient value.
	 * @param integer $expire Default 1hr. The expire time in seconds.
	 */
	public static function set_transient( $key, $val, $expire=3600 ){
		global $wpdb;
		set_transient( $key, $val, $expire );
		
		//return option_id | WP_Error
		if( $wpdb->insert_id )
			return $wpdb->insert_id;
		else
			return new API_Con_Error( 'API_Con_Model::set_transient Error: ' . $wpdb->last_error );
	}
}