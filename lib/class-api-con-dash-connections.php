<?php
/**
 * File for API_Con_Dash_Service class
 * @author Daithi Coombes <webeire@gmail.com>
 */

/**
 * This class handles the connections dash page for letting user's manage their
 * connections.
 * @see  API_Con_Manager::action_admin_menu()
 * @package api-connection-manager
 * @author Daithi Coombes <webeire@gmail.com>
 */
class API_Con_Dash_Connections{

	public function get_page(){

		global $API_Con_Manager;

		$services = $API_Con_Manager->get_services('active');
		$user = wp_get_current_user();
		$connections = $API_Con_Manager->get_user_connections();

		foreach( $services as $service ){

			$_GET['service'] = $service->name;
			$link_params = http_build_query($_GET);
			$link = admin_url('admin.php') . '?' . $link_params;

			$html .= $link_params;
		}

		print $html;
	}
}