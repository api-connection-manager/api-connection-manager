<?php

/**
 * @group api-connection-manager
 */
class API_Con_DTOTest extends WP_UnitTestCase{

	protected $obj;

	function setUp(){
		$this->obj = new API_Con_DTO();
	}

	function test_foo(){
		$this->assertTrue(true);
	}
}