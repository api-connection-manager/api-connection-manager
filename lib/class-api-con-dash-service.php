<?php
/**
 * File for API_Con_Dash_Service class
 * @author Daithi Coombes <webeire@gmail.com>
 */

/**
 * This class handles displaying the dashboard page for activating/installing
 * service modules.
 * @see  API_Con_Manager::action_admin_menu()
 * @package api-connection-manager
 * @author Daithi Coombes <webeire@gmail.com>
 */
class API_Con_Dash_Service extends WP_List_Table{

	/**
	 * Call parent __construct()
	 * @see WP_List_Table::__construct()
	 */
	function __construct(){
		parent::__construct( array(
			'singular' => 'api_con_dash_service',
			'plural' => 'api_con_dash_services',
		) );
	}

	/**
	 * Prints the checkbox column
	 * @param  stdclass $item The row item
	 * @return string       checkbox html
	 */
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("service")
            /*$2%s*/ $item->name                //The value of the checkbox should be the record's id
        );
    }

    /**
     * The default column html
     * @param  stdClass $item        The row item
     * @param  string $column_name Current column name
     * @return string              The item value for this column
     */
    function column_default($item, $column_name){
    	return $item->$column_name;
    }

    /**
     * The html for the 'name' column
     * @param  stdClass $item The current item
     * @return string       The quick link html
     */
    function column_name($item){
        
        //Build row actions
        $actions = array(
            'activate'      => sprintf('<a href="?page=%s&action=%s&api_con_dash_service=%s">Activate</a>',$_REQUEST['page'],'activate',$item->name),
            'deactivate'    => sprintf('<a href="?page=%s&action=%s&api_con_dash_service=%s">Deactivate</a>',$_REQUEST['page'],'deactivate',$item->name),
        );
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item->name,
            /*$2%s*/ $item->name,
            /*$3%s*/ $this->row_actions($actions)
        );
    }

    /**
     * Register bulk actions
     * @return array An array of $slug=>$action_name pairs
     */
    function get_bulk_actions() {
        $actions = array(
            'activate'    => __( 'Activate' ),
            'deactivate' => __( 'Deactivate' )
        );
        return $actions;
    }

    /**
     * Register the columns
     * @return array An array of column $slug=>$name pairs
     */
	function get_columns(){
		return $columns = array(
			'cb' => '<input type="checkbox">',
			'name' => __( 'Name' ),
			'state' => __( 'State' ),
			);
	}

	/**
	 * Return sortable columns
	 * @return array an array of columns
	 */
	function get_sortable_columns(){
		return $sortable = array(
			'name' => 'name',
			'state' => 'state'
		);
	}

	/**
	 * Build the items for the table
	 * @return stdClass The items
	 */
	function prepare_items(){

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$screen = get_current_screen();
		$services = API_Con_Manager::get_services( 'installed' );
		$services_active = API_Con_Manager::get_services( 'active' );
		$items = array();

		foreach( $services as $service ){
			if( in_array( $service, $services_active ) )
				$state = 'active';
			else
				$state = 'inactive';
			$items[] = (object) array( 'cb' => $service, 'name' => $service, 'state' => $state );
		}

		$this->items = $items;
	}

	/**
	 * Process bulk actions
	 * @return  void
	 */
    function process_bulk_action() {

    	//vars
    	$action = $_GET['action'];
		$db_services = API_Con_Model::get('services');
    	$services = (array) $_GET['api_con_dash_service'];
    	
    	if( $action=='activate' ){
    		$update='active';
    		$delete='inactive';
    	}else{
    		$update='inactive';
    		$delete='active';
    	}

    	if( !@$action )
    		return;

    	//update/delete active/inactive table entries
		foreach($services as $service ){
			if( false!==($key=array_search($service, $db_services[$delete])))
				unset( $db_services[$delete][$key] );
			if(in_array($service, $db_services[$update]))
				continue;
			$db_services[ $update ][] = $service;
		}
		$db_services[$update] = array_unique($db_services[$update]);
		API_Con_Model::set('services', $db_services);
		return;
    }

	/**
	 * Prints the dashboard services page for API Connection Manager.
	 * This page allows the activating/deactivating of services
	 * @see  API_Con_Manager::action_admin_menu()
	 */
	public static function get_page(){

		$dash_services = new API_Con_Dash_Service();
		$dash_services->prepare_items();

		?>
        <form id="api-con-dash-services" method="get">
        	<input type="hidden" name="page" value="<?php echo $_GET['page']?>"/>
            <?php $dash_services->display() ?>
        </form>
		<?php
	}
}