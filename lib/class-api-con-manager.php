<?php

class API_Con_Manager{

	public static function get_service( $name ){

		if( !file_exists( 'modules/class-' . $name . '.php' ) )
			return new API_Con_Error( 'Can\'t find module for ' . $name );
		else
			require_once( 'modules/class-' . $name . '.php' );

		$class = 'API_Con_Module_' . ucfirst($name);
		$service = new $class();

		return $service;
	}
}