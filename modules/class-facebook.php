<?php
/**
 * File for the Facebook module
 */
if( !class_exists( 'API_Con_Module_Facebook' ) ):

	/**
	 * Class for defining the Facebook module parameters
	 * @package  api-connection-manager
	 * @subpackage module
	 * @author Daithi Coobmes <webeire@gmail.com>
	 */
	class API_Con_Module_Facebook extends API_Con_Service{
		
		/**
		 * Construct by passing module details to the parent class
		 * @see  API_Con_Service
		 */
		function __construct(){

			//construct parent
			parent::__construct( array(
				'auth_type' => 'oauth2',
				'auth_url' => 'https://www.Facebook.com/1/oauth2/authorize',
				'endpoint' => 'https://api.dropbox.com/1',
				'key' => '379711095469820',
				'secret' => '3a9u8a8nf1vm3x2'
			) );
		}
	}
endif;