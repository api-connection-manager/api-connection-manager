<?php

class API_Con_ServiceTest extends WP_UnitTestCase{

	protected $obj;

	function setUp(){
		$this->obj = $this->getMock('API_Con_Service');
	}

	function test_foo(){
		$this->assertTrue( $this->obj instanceof API_Con_Service );
	}
}