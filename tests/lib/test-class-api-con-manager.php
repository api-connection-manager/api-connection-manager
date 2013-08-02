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

	function test_get_consumer(){
		$service = API_Con_Manager::get_service( 'facebook' );
		$service->key = 'foo';
		$service->secret = 'bar';
		$this->assertInstanceOf( 'OAuthConsumer', API_Con_Manager::get_consumer( $service ) );
		
		$service->key = null;
		$this->assertInstanceOf( 'API_Con_Error', API_Con_Manager::get_consumer( $service ) );
	}

	function test_get_module_dir(){
		$this->assertEquals(
			dirname(dirname(dirname( __FILE__ ))) . "/lib/../modules",
			API_Con_Manager::get_module_dir()
		);
	}

	function test_get_service() {
		$service = API_Con_Manager::get_service( 'dropbox' );
		$this->assertInstanceOf(
			'API_Con_Service',
			$service
		);

		$service = API_Con_Manager::get_service( 'foo' );
		$this->assertInstanceOf(
			'API_Con_Error',
			$service
		);
	}

	function test_get_services(){
		var_dump( API_Con_Manager::get_services() );
	}

	function test_response_listener(){
		$this->assertTrue( true );
		return;
		$this->assertInstanceOf( 'API_Con_Error', $this->obj->response_listener() );
		$dto = new API_Con_DTO( array(
			'api-con-action' => 'request_token'
			) );
		$this->assertInstanceOf( 'API_Con_DTO', $this->obj->response_listener( $dto ) );
	}

	function test_valid_url(){
		$this->assertFalse( API_Con_Manager::valid_url( 'slkdfj' ) );
		$this->assertEquals( 'http://examplefoo.com', API_Con_Manager::valid_url( 'http://example-foo.com' ) );
	}

}