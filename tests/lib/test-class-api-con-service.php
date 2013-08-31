<?php

class API_Con_ServiceTest extends WP_UnitTestCase{

	protected $obj;
	protected $service;

	function setUp(){
		$this->obj = API_Con_Manager::get_service( 'dropbox' );
		$this->service = API_Con_Manager::get_service( 'facebook' );
		$res = $this->service->set_options( array( 'key'=>'foo', 'secret'=>'bar' ) );
	}

	function test_get_authorize_url(){
		$auth_url = "https://www.dropbox.com/1/oauth2/authorize?response_type=code&redirect_uri=http%3A%2F%2Fexample.org%2Fwp-admin%2Fadmin-ajax.php%3Faction%3Dapi-con-manager%26api-con-action%3Drequest_token";
		$this->assertEquals( $auth_url, $this->obj->get_authorize_url() );
		$this->obj->auth_url = null;
		$this->assertInstanceOf( 'API_Con_Error', $this->obj->get_authorize_url() );
	}

	function test_get_login_link(){

		$link = $this->obj->get_login_link( array('FooClass','foo_method') );
	}

	function test_get_login_url(){
		$this->obj->name = 'dropbox';
		$test = admin_url('admin-ajax.php') . '?action=api-con-manager&api-con-action=service_login&service=' . $this->obj->name;
		$res = $this->obj->get_login_url();
		$this->assertEquals( $test, $res );
	}

	function test_get_options(){
		$this->assertEquals( array('key'=>"foo",'secret'=>"bar"), $this->service->get_options() );
	}

	function test_get_redirect_url(){
		$test = $this->obj->get_redirect_url();
		$redirect_url = admin_url( 'admin-ajax.php' ) . '?action=api-con-manager&api-con-action=request_token';
			$this->assertEquals( $test, $redirect_url );
	}

	function test_request(){

		$res = $this->obj->request('http://example-foo.com/bar?x=z');
		$this->assertInstanceOf('API_Con_Error', $res);

		//service not connected, login url returned
		$this->assertEquals( 
			'<a href="http://example.org/wp-admin/admin-ajax.php?action=api-con-manager&api-con-action=service_login&service=Dropbox" target="_new">Login to Dropbox</a>',
			$res->get_error_message()
		);
	}
}