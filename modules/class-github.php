<?php
/**
 * File for the GitHub module
 */
if( !class_exists( 'API_Con_Module_Github' ) ):

	/**
	 * Class for defining the dropbox module parameters
	 * @package  api-connection-manager
	 * @subpackage module
	 * @author Daithi Coobmes <webeire@gmail.com>
	 */
	class API_Con_Module_Github extends API_Con_Service{
		
		/**
		 * Construct by passing module details to the parent class
		 * @see  API_Con_Service
		 */
		function __construct(){

			//construct parent
			parent::__construct( array(
				'auth_type' => 'oauth2',
				'auth_url' => 'https://github.com/login/oauth/authorize',
				'endpoint' => 'https://api.dropbox.com/1',
				'key' => '8908cc4735de08bd2a4a',
				'secret' => 'd8ba51ba9a9c1ef55f9890bb2430ff6a947c8c32'
			) );
		}
	}
endif;