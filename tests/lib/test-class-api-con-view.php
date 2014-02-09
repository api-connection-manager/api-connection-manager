<?php

/**
 * @group api-connection-manager
 */
class API_Con_ViewTest extends WP_UnitTestCase{
	
	protected $obj;

	function setUp(){
		$this->obj = new API_Con_View();
	}

	function test_foo(){
		$this->assertTrue(true);
	}
}