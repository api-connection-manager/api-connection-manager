<?php

class API_Con_ModelTest extends WP_UnitTestCase{

	protected $obj;

	function test_get(){
		API_Con_Model::set( 'test', array(foo) );
		$this->assertEquals( array(foo), API_Con_Model::get( 'test' ) );
	}
}