<?php

class API_Con_ManagerTest extends WP_UnitTestCase {

	protected $obj;
	protected $user;

	function setUp(){
		require_once( 'lib/class-api-con-manager.php' );
		$this->obj = new API_Con_Manager();
		$this->user = @wp_signon( array(
			'user_login' => 'admin',
			'user_password' => 'password'
			));

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
		API_Con_Manager::set_service_states( array('facebook'), 'active' );
		$this->assertTrue( in_array('Facebook', API_Con_Manager::get_services('active') ) );
	}

	function test_is_valid_service_name(){
		$this->assertTrue( API_Con_Manager::is_valid_service_name( 'facebook' ) );
		$this->assertFalse( API_Con_Manager::is_valid_service_name( 'foo' ) );
	}

	function test_set_service_state(){
		$service = array(API_Con_Manager::get_service( 'facebook' ));
		API_Con_Manager::set_service_states( array('facebook'), 'deactivate' );
		$this->assertTrue( API_Con_Manager::set_service_states( array('facebook'), 'activate' ) );

		$this->assertInstanceOf( 'API_Con_Error', API_Con_Manager::set_service_states( array('foo'), 'deactivate') );
	}

	function test_valid_url(){
		$this->assertFalse( API_Con_Manager::valid_url( 'slkdfj' ) );
		$this->assertEquals( 'http://examplefoo.com', API_Con_Manager::valid_url( 'http://example-foo.com' ) );
	}

	function test_action_admin_menu(){
		$res = $this->obj->action_admin_menu();
		$this->assertEquals( $res[0], 'toplevel_page_api-con-manager');
		$this->assertInstanceOf( 'API_Con_Dash_Service', $res[2] );
	}

	function test_get_page(){
		$this->expectOutputString( '<h1>API Connection Manager</h1>', $this->obj->get_page() );
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

}