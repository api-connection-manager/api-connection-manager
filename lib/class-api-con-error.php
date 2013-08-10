<?php
/**
 * File for the API_Con_Error class
 */

/**
 * Class for handling ALL errors produced in the running of API Connection Manager. Extends WP_Error
 * @uses  WP_Error Extends WP_Error, so native wp functions such as is_wp_error() will still work.
 * @package  api-connection-manager
 * @author Daithi Coobmes <webeire@gmail.com>
 */
class API_Con_Error extends WP_Error{

	/** @var string The WP_Error::code string. Default 'API Con Manager Error' */
	protected $code = 'API Con Manager Error';

	/**
	 * Pass the error string.
	 * @param string $msg The error produced
	 */
	function __construct( $msg = '' ){
		$this->add( $this->code, $msg );
	}
}