<?php
/**
 * File for the API_Con_View class
 */

/**
 * Class for handling all view actions, such as login forms etc.
 * @package  api-connection-manager
 * @todo  integrate API_Con_View with bootstrap
 * @author Daithi Coobmes <webeire@gmail.com>
 */
class API_Con_Consumer{
	
	/**
	 * Construct.
	 * @param API_Con_Service $service The service to build the consumer around.
	 */
	function __construct( API_Con_Service $service ){
		require_once( dirname( __FILE__ ) . '/../vendor/OAuth.php' );

		switch( $service->auth_type ){

			case 'oauth1':
				break;

			case 'oauth2':

				//validate params
				if( !$service->key || !$service->secret )
					return new API_Con_Error( 'Service missing client key or client secret' );

				//build consumer
				$consumer = new OAuthConsumer(
					$service->key,
					$service->secret,
					$service->get_redirect_url()
				);
				break;

			default:
				die('oops');
				break;
		}
	}

	/**
	 * Factory method. Builds a new API_Con_Consumer
	 * @param  API_Con_Service $service The service to build the consumer around.
	 * @return API_Con_Consumer
	 */
	public static function get_consumer( API_Con_Service $service ){
		return new API_Con_Consumer( $service );
	}

}