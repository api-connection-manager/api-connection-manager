<?php
/**
 * @package api-connection-manager
 */
/*
  Plugin Name: API Connection Manager
  Plugin URI: https://github.com/api-connection-manager/api-connection-manager
  Description: WordPress Core API for connecting to 3rd party services
  Version: 1.0
  Author: Daithi Coombes
  Author URI: http://david-coombes.com
 */

function api_con_autoload( $class ){
  
	$file = strtolower( 'class-' . str_replace( '_', '-', $class ) . '.php' );
	$path = dirname( __FILE__ ) . '/lib/' . $file;

	if( file_exists( $path ) )
		require_once( $path );
}
spl_autoload_register( 'api_con_autoload' );

/**
 * Bootstrap API_Con_Manager
 */
$API_Con_Manager = new API_Con_Manager();
//end Bootstrap API_Con_Manager