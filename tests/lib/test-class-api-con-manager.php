<?php

/**
 * @group api-connection-manager
 */
class API_Con_ManagerTest extends WP_UnitTestCase {

	protected $obj;
	protected $service;
	protected $user;

	function setUp(){
		
		//set test case params
		$this->obj = new API_Con_Manager();
		$this->service = API_Con_Manager::get_service( 'facebook' );
		$this->service->set_options( array( 'key'=>'foo', 'secret'=>'bar' ) );
		$this->services = array( 'Dropbox', 'Facebook', 'Github');
		$this->user = @wp_signon( array(
			'user_login' => 'admin',
			'user_password' => 'password'
			));
		wp_set_current_user($this->user->ID);

		//set db params
		API_Con_Model::set(
			API_Con_Model::$meta_keys['services'],
			array(
				'active' => array( $this->services[0], $this->services[1] ),
				'inactive' => array( $this->services[2] )
			)
		);
	}

	function test_do_callback(){

		$callback = array(__CLASS__, "do_callback_foo");
		$dto = new API_Con_DTO();
		$res = API_Con_Manager::do_callback( $callback, $dto, $this->service );

		$this->assertEquals($res[0], $this->service);
		$this->assertEquals($res[1], $dto);
	}

	function test_factory(){
		$api = API_Con_Manager::factory();

		$this->assertInstanceOf(
			'API_Con_Manager',
			$api
		);
	}

	function test_get_consumer(){
		$this->assertInstanceOf( 'OAuthConsumer', API_Con_Manager::get_consumer( $this->service ) );
		
		$this->service->set_options( array( 'key'=>null ) );
		$this->assertInstanceOf( 'API_Con_Error', API_Con_Manager::get_consumer( $this->service ) );
	}

	function test_get_module_dir(){
		$this->assertEquals(
			dirname(dirname(dirname( __FILE__ ))) . "/lib/../modules",
			API_Con_Manager::get_module_dir()
		);
	}

	function test_get_service() {
		$this->assertInstanceOf(
			'API_Con_Service',
			API_Con_Manager::get_service('facebook')
		);

		$this->assertInstanceOf(
			'API_Con_Error',
			API_Con_Manager::get_service('foo')
		);
	}

	/**
	 * @group services
	 */
	function test_get_services(){

		//bootstrap test
		$installed = $active = $inactive = array();
		foreach ( API_Con_Manager::get_services('all') as $service )
			$installed[] = $service->name;
		foreach ( API_Con_Manager::get_services('active') as $service )
			$active[] = $service->name;
		foreach ( API_Con_Manager::get_services('inactive') as $service )
			$inactive[] = $service->name;
		$db = API_Con_Model::get(API_Con_Model::$meta_keys['services']);
		$db['all'] = array_merge($active, $inactive);
		sort( $installed );
		sort( $active );
		sort( $inactive );
		asort($db);

		//run tests
		$this->assertEquals( $db['active'], $active, "API_Con_Manager::get_services['active'] failed");
		$this->assertEquals( $db['inactive'], $inactive, "API_Con_Manager::get_services['inactive'] failed");
		$this->assertEquals( $db['all'], $installed, "API_Con_Manager::get_services['installed'] failed");
	}

	function test_is_valid_service_name(){
		$this->assertTrue( API_Con_Manager::is_valid_service_name( 'facebook' ) );
		$this->assertFalse( API_Con_Manager::is_valid_service_name( 'foo' ) );
	}

	/**
	 * @group services
	 */
	function test_set_service_state(){
		$active = array(
			API_Con_Manager::get_service('Github')
		);
		$inactive = array(
			API_Con_Manager::get_service('Dropbox'),
			API_Con_Manager::get_service('Facebook')
		);
		API_Con_Manager::set_service_states( $active, 'activate' );
		API_Con_Manager::set_service_states( $inactive, 'deactivate' );
		$all = API_Con_Model::get( API_Con_Model::$meta_keys['services'] );

		$this->assertEquals( array('Github'), $all['active'] );
		$this->assertEquals( array('Dropbox','Facebook'), $all['inactive']);
		$this->assertInstanceOf( 'API_Con_Error', API_Con_Manager::set_service_states( array('foo'), 'deactivate') );
	}

	function test_valid_url(){
		$this->assertFalse( API_Con_Manager::valid_url( 'slkdfj' ) );
		$this->assertEquals( 'http://examplefoo.com', API_Con_Manager::valid_url( 'http://example-foo.com' ) );
	}

	/**
	 * @group wp-actions
	 */
	function test_action_admin_menu(){
		$res = $this->obj->action_admin_menu();
		$this->assertEquals( $res, array(
			'toplevel_page_api-con-manager',
			'api-manager_page_api-con-services',
			'api-manager_page_api-con-options'
		) );
	}

	function test_response_listener(){
		;
	}

	function do_callback_foo( $service, $dto ){
		return func_get_args();
	}

}