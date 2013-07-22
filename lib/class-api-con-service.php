<?php

class API_Con_Service{
	
	/** @var string Default custom. Either: oauth1, oauth2, custom */
	public $auth_type = 'custom';
	/** @var string The name of this service. Declared in factory method API_Con_Manager::get_service() */
	public $name;

	/**
	 * When extending this class you must specify params
	 * @param array $args Parameters
	 */
	function __construct( array $args ){

		//set config
		foreach( $args as $field => $val )
			@$this->$field = $val;
	}


	/**
	 * Return the login url
	 * @return string the full `URI` to login this service
	 */
	public function get_login_url(){
		return admin_url('admin-ajax.php') . '?action=api-con-login&service=' . $this->name;
	}

	/**
	 * Make a request to the remote api
	 * @param  string $url    endpoint
	 * @param  array  $params parameters to be sent
	 * @param  string $method Default GET
	 * @return API_Con_Consumer Returns API_Con_Error on error.
	 */
	public function request( $url=null, $params=array(), $method='GET' ){

		//test params
		if(!$url) 
			return new API_Con_Error('Please provide a url');

		//setup consumer
		$consumer = API_Con_Consumer::get_consumer( $this );
	}
}