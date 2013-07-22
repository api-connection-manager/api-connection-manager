<?php

class API_Con_ManagerTest extends WP_UnitTestCase {

	protected $obj;

	function setUp(){
		require_once( 'lib/class-api-con-manager.php' );
		$this->obj = new API_Con_Manager();
	}

	function test_factory(){
		$api = API_Con_Manager::factory();

		$this->assertInstanceOf(
			'API_Con_Manager',
			$api
		);
	}

	function test_get_service() {
		$service = API_Con_Manager::get_service( 'dropbox' );
		
		$this->assertInstanceOf(
			'API_Con_Service',
			$service
		);
	}

}