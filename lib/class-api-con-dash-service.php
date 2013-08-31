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
		parent::__construct(
			array(
				'singular' => 'api_con_dash_service',
				'plural' => 'api_con_dash_services',
			)
		);

	}

    /**
     * The default column html
     * @param  stdClass $item        The row item
     * @param  string $column_name Current column name
     * @return string              The item value for this column
     */
    function column_default( $item, $column_name ){
    	return $item->$column_name;
    }

    /**
     * The html for the 'name' column
     * @param  stdClass $item The current item
     * @return string       The quick link html
     */
    function column_name( $item ){
        
        //Build row actions
        $actions = array(
            'activate'      => sprintf( '<a href="?page=%s&action=%s&api_con_dash_service=%s">Activate</a>',$_REQUEST['page'],'activate',$item->name ),
            'deactivate'    => sprintf( '<a href="?page=%s&action=%s&api_con_dash_service=%s">Deactivate</a>',$_REQUEST['page'],'deactivate',$item->name ),
            'edit'			=> sprintf( '<a href="#" class="api-con-dash-inline" id="inline-%s">Edit</a>',$item->name )
        );
        
        //Return the title contents
        $ret = sprintf(
        	'%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item->name,
            /*$2%s*/ $item->name,
            /*$3%s*/ $this->row_actions( $actions )
        );

        $inline = $this->inline_edit( $item );

        return $ret . $inline;
    }

    function _echo( $str ){
    	echo sanitize_text_field( $str );
    }

    /**
     * Register bulk actions
     * @return array An array of $slug=>$action_name pairs
     */
    function get_bulk_actions() {
        $actions = array(
            'activate'    => __( 'Activate' ),
            'deactivate' => __( 'Deactivate' ),
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
			'state' => 'state',
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
		
		foreach ( $services as $service ) {
			if ( in_array( $service, $services_active ) )
				$state = 'active';
			else
				$state = 'inactive';
			$items[] = (object) array( 'cb' => $service, 'name' => $service->name, 'state' => $state );
		}

		$this->items = $items;
	}

	/**
	 * Process bulk actions
	 * @todo  setup nonces for all bulk actions
	 * @return  void
	 */
    function process_bulk_action() {

    	//vars
    	$action = $_GET['action'];
		$db_services = API_Con_Model::get( 'services' );
    	$services = (array) $_GET['api_con_dash_service'];

    	//check nonce
    	if ( $action ){
	    	//if( !wp_verify_nonce( '963a1db024', 'api-con-dash' ) );
	    		//die('invalid nonce');
	    }
    	
    	//save inline-edit form
    	if ( 'inline-edit' == $action ){
    		$service = API_Con_Manager::get_service( $_GET['api_con_dash_service'] );
    		$options = $service->get_options();

    		foreach ( $options as $key => $val )
    			if ( @$_GET[ $key ] )
    				$options[ $key ] = $_GET[ $key ];

    		$service->set_options( $options );
    		
    		$service = API_Con_Manager::get_service( $_GET['api_con_dash_service'] );
    		$options = $service->get_options();
    	}

    	$service_objs = array();
    	foreach ( $services as $service )
    		$service_objs[] = API_Con_Manager::get_service( $service );
    	return API_Con_Manager::set_service_states( $service_objs, $action );
    }

    /**
     * Echo out single row html
     * @param  stdClass $item The current item
     */
    function single_row_columns( $item ){
		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class='$column_name column-$column_name'";

			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';

			$attributes = "$class$style";

			if ( 'cb' == $column_name ) {
				echo '<th scope="row" class="check-column">';
		        echo '<input type="checkbox" name="' . $this->_args['singular'] . '[]" value="' . $item->name . '" />';
				echo '</th>';
			}
			elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo '<td ' . esc_attr( $attributes ) . '>';
				echo wp_kses_post( call_user_func( array( &$this, 'column_' . $column_name ), $item ) );
				echo '</td>';
			}
			else {
				echo '<td ' . esc_attr( $attributes ) . '>';
				echo wp_kses_post( $this->column_default( $item, $column_name ) );
				echo '</td>';
			}
		}

		//inline edit
    }

	/**
	 * Prints the dashboard services page for API Connection Manager.
	 * This page allows the activating/deactivating of services
	 * @see  API_Con_Manager::action_admin_menu()
	 */
	public function get_page(){

		//$dash_services = new API_Con_Dash_Service();
		$this->prepare_items();
		$inline_nonce = wp_create_nonce( 'api-con-dash' );

		?>
        <form id="api-con-dash-services" method="get">
        	<input type="hidden" name="page" value="<?php echo sanitize_text_field( $_GET['page'] ); ?>"/>
            <?php $this->display() ?>
        </form>

		<script type="text/javascript">

			jQuery('.api-con-dash-hidden').hide();

			//submit inline edit
			jQuery('.api-con-dash-inline-btn').click(function(){
				var id = jQuery(this).attr('id').substr(7);
				var inputs = jQuery('input[type="text"]','#api-con-dash-inline-'+id);
				var url = document.URL.replace('#','');

				inputs.each(function(){
					if(this.value)
						url += "&" + this.name + "=" + this.value;
				})
				
				window.location.href = url + '&api_con_dash_service='+id+'&action=inline-edit&wpnonce=<?php echo sanitize_text_field( $inline_nonce ); ?>';
			});

			//show inline edit form
			jQuery('.api-con-dash-inline').click(function(){
				var id = jQuery(this).attr('id').substr(7);

				jQuery('.api-con-dash-hidden').hide();
				jQuery('#api-con-dash-inline-'+id).show()
					.children('input').each(function(){
						jQuery(this).removeAttr('disabled');
					});
			});
		</script>
		<?php

	}

	/**
	 * Build hmtl for inline edit form
	 * @param  stdclass $item current item
	 * @return string       the html
	 */
	public function inline_edit( $item = null ){
		if ( !$item )
			return;
		
		$service = API_Con_Manager::get_service( $item->name );
		$options = $service->get_options();
		if ( !count( $options ) )
			return;
		
		$ret = '<div class="api-con-dash-hidden" id="api-con-dash-inline-' . $item->name . '">
			<p>Redirect URL:<br/> <b>' . $service->get_redirect_url() . '</b></p>';

		foreach ( $options as $key => $val ){
			$ret .= '<label for="' . $key . '">' . $key . '</label>';
			$ret .= '<input type="text" name="' . $key . '" id="' . $key . '" value="' . $val . '" disabled/>';
		}
		
		$ret .= '<input type="button" class="api-con-dash-inline-btn" id="inline-' . $item->name . '" value="Save" disabled/>
		</div>';

		return $ret;
	}
}