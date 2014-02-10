<?php
/**
 * File for the Google module
 */
if( !class_exists( 'API_Con_Module_Google' ) ):

	/**
	 * Class for defining the Google module parameters
	 * @package  api-connection-manager
	 * @subpackage module
	 * @author Daithi Coobmes <webeire@gmail.com>
	 */
	class API_Con_Module_Google extends API_Con_Service{
		
		/**
		 * Construct by passing module details to the parent class
		 * @see  API_Con_Service
		 */
		function __construct(){

			//construct parent
			parent::__construct( array(
				'auth_type'	=> 'oauth2',
				'auth_url'	=> '',
				'button'	=> 'google-29x29.png',
				'endpoint'	=> '',
				'options'	=> array( 'key', 'secret' )
			), __CLASS__ );
		}

		function get_uid( $data=false ){
			;
		}
	}
endif;