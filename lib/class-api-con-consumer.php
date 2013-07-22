<?php

class API_Con_Consumer{

	function __construct( API_Con_Service $service ){

		switch( $service->auth_type ){

			case 'oauth1':
				require_once( dirname( __FILE__ ) . '/../vendor/OAuth.php' );
				break;

			default:
				die('oops');
				break;
		}
	}

	public static function get_consumer( API_Con_Service $service ){
		return new API_Con_Consumer( $service );
	}
}