<?php

class API_Con_Error extends WP_Error{

	protected $code = 'API Con Manager Error';

	function __construct( $msg='' ){
		$this->add( $this->code, $msg );
	}
}