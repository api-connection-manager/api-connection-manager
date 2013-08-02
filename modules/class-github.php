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
				'options' => array('key','secret'),
				'token_url' => 'https://github.com/login/oauth/access_token'
			), __CLASS__ );
		}
	}
endif;