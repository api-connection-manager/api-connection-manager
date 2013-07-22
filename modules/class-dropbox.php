<?php

if( !class_exists( 'API_Con_Module_Dropbox' ) ):

	class API_Con_Module_Dropbox extends API_Con_Service{
		
		function __construct(){

			//construct parent
			parent::__construct( array(
				'auth_type' => 'oauth2',
				'auth_url' => 'https://www.dropbox.com/1/oauth2/authorize',
			) );
		}
	}
endif;