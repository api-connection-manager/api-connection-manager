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

	function __construct(){

		//if request to login
		if ( $_GET['action'] == 'disconnect' )
			$this->disconnect( $_GET['service'] );
	}

	/**
	 * Callback to hanlde connecting user to a service
	 * @param  API_Con_Service $service The service object
	 * @param  API_Con_DTO     $dto     The data transport object
	 */
	public function connect_user( API_Con_Service $service, API_Con_DTO $dto ){

		global $API_Con_Manager;

		$API_Con_Manager::connect_user( $service );

		?>
		<script type="text/javascript">
			window.opener.location.reload();
			window.close();
		</script>
		<?php
	}

	/**
	 * Disconnect from a service
	 * @param  string $service_name The service name
	 */
	public function disconnect( $service_name ){

		global $API_Con_Manager;
		$service = $API_Con_Manager->get_service( $service_name );
		$user = wp_get_current_user();

		$API_Con_Manager::disconnect( $service, $user );

		$params = $_GET;
		unset($params['service']);
		unset($params['action']);

		wp_redirect( admin_url('admin.php') . '?' . http_build_query($params) );
		die();
	}

	/**
	 * Prints the html
	 */
	public function get_page(){

		global $API_Con_Manager;

		$html  = '<ul>';
		$services = $API_Con_Manager->get_services('active');
		$user = wp_get_current_user();
		$connections = $API_Con_Manager->get_user_connections();

		foreach( $services as $service ){

			//vars
			$params = $_GET;
			$params['service'] = $service->name;
			$link_params = http_build_query($params);
			$link_text = 'Connect to ' . $service->name;
			$link = $service->get_login_link( 
				array(&$this, 'connect_user',),
				$link_text
			);
			$disconnect = admin_url('admin.php') 
				. '?' . http_build_query($_GET) 
				. '&amp;service=' . $service->name
				. '&amp;action=disconnect';

			( $connections[$service->name] ) ? $connected = true : $connected = false;

			//icon
			( $connected ) ?
				$icon = plugins_url() . '/api-connection-manager/assets/images/status_icon_green_12x12.png' :
				$icon = plugins_url() . '/api-connection-manager/assets/images/status_icon_red_12x12.png';

			$html .= '<li>
				<p>
					<img src="' . $icon . '"/>'
					. $service->name
					. '<br/>';

			( $connected ) ?
				$html .= '<button onclick="window.location.href=\'' . $disconnect . '\'">Disconnect</button>' :
				$html .=  $link;

			$html .= '</p>
				</li>';
		}

		print $html . '</ul>';
	}
}