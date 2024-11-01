<?php
// see: http://core.trac.wordpress.org/attachment/ticket/15386/my-list-table-class.php
namespace leau\co\wp_favicons_server;

class My_List_Table extends \WP_List_Table {
	
	function My_List_Table() {
		parent::WP_List_Table( array(
			'items',
		) );
	}
	
	function prepare_items() {
		
		
		$this->items = $this->get_items();
		
		//print_r($this->items);

	}

	function check_permissions() {
		if ( !current_user_can('manage_options') )
			wp_die(__('Cheatin&#8217; uh?'));
	}

	/**
	 * (non-PHPdoc)
	 * @see includes/WP_List_Table::get_columns()
	 * must be overridden, sets the column content
	 */
	function get_columns() {
		return array(
			'cb'    => '<input type="checkbox" />',
			'uri'  => __( 'Link', 'wp-favicons' ),
			'request_response_code' => __( 'Code', 'wp-favicons' ),
			'request_response_message' => __( 'Message', 'wp-favicons' ),
		);
	}
	
	function get_sortable_columns() {
		return array(
			'uri' => 'uri',
			'request_response_code' => 'request_response_code',
			'request_response_message' => 'request_response_message'
		);
	}

	function column_cb( $item ) {
		return '<input type="checkbox" name="checked[]" value="' . $item->id . '">';
	}
	
	function column_uri( $item ) {
		$out = '';
		$out .= '<a href="' . $item->uri . '" target="_blank">' . 
			$item->uri . '</a>';
		$out .= '<br />';
		$actions = array();
		$actions['view'] = '<a href="?page=wp_favicons_statuscodes_page&action=view&amp;id=' . $item->id . '">' . __( 'View' ) . '</a>';
		$actions['hide'] = '<a href="?page=wp_favicons_statuscodes_page&action=hide&amp;id=' . $item->id . '">' . __( 'Hide' ) . '</a>';
		$actions['remove'] = '<a href="?page=wp_favicons_statuscodes_page&action=remove&amp;id=' . $item->id . '">' . __( 'Remove' ) . '</a>';
	
		$out .= $this->row_actions( $actions );

		return $out;
			
	}

	function column_request_response_code( $item ) {
		return $item->request_response_code;
	}
	
	function column_request_response_message( $item) 
	{
		return $item->request_response_message;
	}
	
	function column_default( $item, $column_name ) {
		return apply_filters( 'manage_items_custom_column', '', $column_uri, $item->id );
	}

	function display_tablenav( $which ) {
		global $status;
	
		parent::display_tablenav( $which );
	}
	
	function extra_tablenav( $which ) {		
		return;
		global $wpdb;
		$sql = "select request_response_code,request_response_message " .
				"from wp_http_request_cache group by request_response_code,request_response_message ";		
		$items = $wpdb->get_results($sql,'ARRAY_A');
		
		echo '<div class="alignleft actions">filter on: ';
		foreach($items as $item)
		{
			$line = $item['request_response_code'] . ' ' . $item['request_response_message'];
			submit_button( __( $line ), 'secondary', $line, false );
		}		
		echo '</div>';		
	}
	
	
	/**
	 * Returns the list of items to be shown by SQL on the Db
	 */
	function get_items() {
		global $wpdb;
		$items = array();		
		$format = 'OBJECT';
		
		/* pagination */
		$page = $this->get_pagenum();
		$per_page = 30;
		$bottom_range = ($page-1) * $per_page;
	
		/* sorting */	
		$orderby = 'uri';	
		$order = 'ASC';
		
		if ( isset( $_REQUEST['orderby'] ))
		{
			$orderby = $_REQUEST['orderby'];
		}
			
		if ( isset( $_REQUEST['order'] ))
		{	
			if ('desc' == $_REQUEST['order'])
			{
				$order = 'DESC';		
			}
		}

		/* pagination stuff in the gui */	
		$sql = "select count(*) from `wp_http_request_cache` where 1;";	
		$total_items = $wpdb->get_var($sql);
		$this->set_pagination_args( array (
			'total_items' => $total_items,
			'per_page'    => $per_page ));
		
		/* the set to be returned */		
		$sql = "select id, uri, request_response_code, request_response_message " .
				"from wp_http_request_cache order by $orderby $order limit $bottom_range,$per_page ";		
		$items = $wpdb->get_results($sql,$format);
				
		return $items;
	}
}
 