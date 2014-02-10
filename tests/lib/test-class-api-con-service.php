<?php

/**
 * @group api-connection-manager
 */
class API_Con_ServiceTest extends WP_UnitTestCase{

	protected $obj;
	protected $service;

	function setUp(){
		$this->obj = API_Con_Manager::get_service( 'dropbox' );
		$this->service = API_Con_Manager::get_service( 'facebook' );

		//reset options
		update_option( API_Con_Model::$meta_keys['service_options'], array(ucfirst($this->obj->name) => array()));
	}

	function test_get_authorize_url(){

		$consumer = API_Con_Manager::get_consumer( $this->obj );
		$url = $this->obj->auth_url . '?' . http_build_query(
			array(
				'client_id' => $consumer->key,
				'response_type' => 'code',
				'redirect_uri' => API_Con_Manager::get_redirect_url(),
			)
		);
		
		$this->assertEquals( $url, $this->obj->get_authorize_url() );
		$this->obj->auth_url = null;
		$this->assertInstanceOf( 'API_Con_Error', $this->obj->get_authorize_url() );
	}

	function test_get_login_link(){
		global $wpdb;

		$link = $this->obj->get_login_link( array('FooClass','callback'), $this->obj->name );
		$id = $wpdb->insert_id;

		//check callback was set
		$res = $wpdb->get_row("SELECT * FROM {$wpdb->options} WHERE option_id={$id}");
		$name = API_Con_Model::$meta_keys['service']."-callback-(\d)";
		preg_match("/{$name}/",$res->option_name, $matches);
		$value = maybe_unserialize($res->option_value);

		$this->assertNotEmpty($matches);
		$this->assertEquals(array('FooClass','callback'), $value);

		//check link return
		$link_foo = 			'<a href="' 
				. $this->obj->get_login_url( null, $id )
				. '" target="_new">'
				. $this->obj->name
				. '</a>';

		$this->assertEquals($link_foo, $link);
	}

	function test_get_login_url(){
		global $wpdb;

		$this->obj->name = 'dropbox';
		//http://example.org/wp-admin/admin-ajax.php?action=api-con-manager&amp;api-con-action=service_login&amp;service=dropbox&amp;foo=bar
		$actual = $this->obj->get_login_url(array('foo'=>'bar'));
		$expected = admin_url('admin-ajax.php') . '?action=api-con-manager&amp;api-con-action=service_login&amp;service=' . $this->obj->name . '&amp;transid=' . $wpdb->insert_id;
		$this->assertEquals( $expected, $actual );
	}

	function test_get_options(){

		//set test option (code taken directly from API_Con_Service::set_option)
		$test = array('key'=>'foo', 'secret'=>'bar', 'foo'=>'bar');
		$options = get_option( API_Con_Model::$meta_keys['service_options'] );
		$options[ucfirst($this->obj->name)] = $test;
		$service_options = array_merge($options, $test);
		update_option( API_Con_Model::$meta_keys['service_options'], $service_options );
		$res = get_option( API_Con_Model::$meta_keys['service_options']);

		$this->assertEquals(
			$service_options[$this->obj->name],
			$res[$this->obj->name]
		);
	}

	function test_load_options(){

		$service_options = API_Con_Model::get( API_Con_Model::$meta_keys['service_options'] );
		$options = $service_options[ $this->obj->name ];
	}
}