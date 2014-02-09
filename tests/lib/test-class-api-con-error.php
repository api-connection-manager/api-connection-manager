<?php

/**
 * @group api-connection-manager
 */
class API_Con_ErrorTest extends WP_UnitTestCase{

	protected $obj;

	function setUp(){
		$this->obj = new API_Con_Error();
	}

	function test_foo(){
		$this->assertTrue( $this->obj instanceof WP_Error );
	}
}