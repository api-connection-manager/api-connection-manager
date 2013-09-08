<?php
/**
 * Class file for API_Con_DTO
 */

/**
 * Data Transport Object for handling responses from remote services.
 * This class can be mocked on client side and used as if it came from
 * a remote service.
 * @package  api-connection-manager
 * @author Daithi Coobmes <webeire@gmail.com>
 */
class API_Con_DTO{
	
	/** @var array The data passed to the server */
	public $data;
	/** @var OAuthToken An access token object */
	public $token;

	/**
	 * Construct
	 * @param array $data An array of key value pairs passed from the remote server
	 */
	function __construct( array $data = null ){

			$this->data = $data;
	}
}