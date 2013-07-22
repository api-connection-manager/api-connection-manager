<?php

class API_Con_ServiceTest extends WP_UnitTestCase{

	protected $obj;

	function setUp(){
		$this->obj = API_Con_Manager::get_service('dropbox');
	}

	function test_login_url(){
		$this->obj->name = 'dropbox';
		$test = admin_url('admin-ajax.php') . '?action=api-con-login&service=' . $this->obj->name;
		$res = $this->obj->get_login_url();
		$this->assertEquals( $test, $res );
	}

	function test_request(){

		$this->assertInstanceOf( 'WP_Error', $this->obj->request() );
		$this->assertInstanceOf( 'API_Con_Consumer', $this->obj->request( 'http://example-foo.com/bar?x=z' ) );
	}
}