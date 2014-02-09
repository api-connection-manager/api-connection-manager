<?php

/**
 * @group api-connection-manager
 */
class API_Con_ModelTest extends WP_UnitTestCase{

	protected $obj;

	function test_get(){
		update_option('test', array('foo'));
		$this->assertEquals( 
			array('foo'), 
			API_Con_Model::get( 'test', true ) 
		);
		$this->assertFalse(API_Con_Model::get('test'));
	}

	function test_get_transient(){
		set_transient('foo',array('bar'));
		$res = API_Con_Model::get_transient('foo');

		$this->assertEquals($res, array('bar'));
	}

	function test_get_transient_by_id(){
		global $wpdb;
		set_transient('foo',array('bar'));
		$id = $wpdb->insert_id;
		$res = API_Con_Model::get_transient_by_id($id);

		$this->assertEquals(array('bar'), $res);
	}

	function test_set(){
		API_Con_Model::set( 'test', array('foo') );
		$this->assertEquals(get_option('test'), array('foo'));
	}

	function test_set_transient(){
		global $wpdb;

		$id = API_Con_Model::set_transient('bar', array('foo'));
		$res = $wpdb->get_row("SELECT * FROM {$wpdb->options} WHERE option_id=$id");
		$name = str_replace("_transient_", "", $res->option_name);
		$value = maybe_unserialize($res->option_value);

		$this->assertEquals('bar', $name);
		$this->assertEquals(array('foo'),$value);
	}
}