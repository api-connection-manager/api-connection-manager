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
				'endpoint' => 'https://graph.facebook.com',
				'options' => array( 'key', 'secret' ),
				'token_url' => 'https://graph.facebook.com/oauth/access_token',
			), __CLASS__ );
		}

		function get_token( API_Con_DTO $dto ){
			return parent::get_token( $dto, null, 'post' );
		}

		/**
		 * Append the `redirect_uri` to the token url and make request.
		 * 
		 * API_Con_Service::get_token() will urlencode the `redirect_uri` param
		 * causing Facebook to return an error.
		 * @see  API_Con_Service::get_token()
		 * @param  API_Con_DTO $dto [description]
		 * @return [type]           [description]
		 * @deprecated
		 *
		function get_token( API_Con_DTO $dto ){

	   		$code = $dto->data['code'];
	   		$consumer = API_Con_Manager::get_consumer( $this );
	   		$params = array(
				'client_id' => $consumer->key,
				'client_secret' => $consumer->secret,
				'redirect_uri' => $this->get_redirect_url(),
				'code' => $code
			);
			$this->token_url = "{$this->token_url}?" . http_build_query($params);

			$res = $this->request( $this->token_url, null, null, false );
			if( is_wp_error( $res ) )
				return $res;

			parse_str( $res['body'], $body );
			return new OAuthToken( $body[ 'access_token' ], null );
		}
		*/

	}
endif;