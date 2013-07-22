<?php

class API_Con_ConsumerTest extends WP_UnitTestCase{

	protected $service;
	protected $obj;

	function setUp(){
		$this->service = API_Con_Manager::get_service( 'dropbox' );
		$this->obj = API_Con_Consumer::get_consumer( $this->service );
	}

	function test_get_consumer(){
		$this->assertInstanceOf( 'API_Con_Consumer', API_Con_Consumer::get_consumer( $this->service ) );
	}
}