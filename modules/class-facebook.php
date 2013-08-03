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
				'auth_url' => 'https://www.facebook.com/dialog/oauth',
				'endpoint' => 'https://api.dropbox.com/1',
				'options' => array( 'key', 'secret' ),
				'token_url' => 'https://graph.facebook.com/oauth/access_token',
			), __CLASS__ );

			//$this->set_redirect_url( 'http://api-connection-manager.loc' );
		}
	}
endif;