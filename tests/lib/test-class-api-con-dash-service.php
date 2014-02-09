<?php

/**
 * @group api-connection-manager
 */
class API_Con_Dash_ServiceTest extends WP_UnitTestCase{

	protected $obj;

	function setUp(){
		$this->obj = new API_Con_Dash_Service();
	}

	function test_foo(){
		$this->assertTrue(true);
	}
}