<?php
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\statuscodes_request_codes"))
{
	/**
	 * Holds and defines 1 specific module
	 * One module corresponds to a submenu section and contains multiple plugins
	 * 
	 * @see http://core.trac.wordpress.org/attachment/ticket/15386/my-list-table-plugin.php
	 * 
	 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
	 * @since 0.4.0
	 */
	class statuscodes_request_codes extends Plugin
	{
		private $list_table;
		
		/**
		 * This method acts as constructor for admin plugins
		 */
		function ExecuteAdminAction()
		{
			/* add a new section for the table */
			add_action(Config::GetPluginSlug() . $this->_module . '_plugins'
			, array($this,'AddSettingsTableSection'));
			
			/* add filter to load the specific table class  
			 * we need to add a filter because at this stage WP_List_Table
			 * is not yet loaded
			 */
			add_filter( 'get_list_table_My_List_Table', 
				array($this, 'RegisterListTable'));
		}
		 
		/**
		 * Adds Settings section for the table
		 * Enter description here ...
		 */
		function AddSettingsTableSection()
		{
			// 1. The list table class has to be instantiated before screen_meta() is called  
			add_action('load-' . Config::GetNicePluginSlug() . 'page_' . Config::GetPluginSlug() . 
				$this->_module . '_page',
				array($this, 'InitListTable'));		
			
			// Add a settings section
			add_settings_section(Config::GetPluginSlug() . $this->_module . '_menu_section_main'
			, 'Verify your outgoing Links'
			, array($this,'StatusCodeTable')
			, Config::GetPluginSlug() . $this->_module . '_page'
			);
		}
		
		/**
		 * 
		 * Enter description here ...
		 */
		function InitListTable() {
			require_once dirname( __FILE__ ) . '/class-list-table-status.php';
			//$this->RegisterListTable('My_List_Table');
			if (! isset($this->list_table))
			{
				$this->list_table = new My_List_Table();
			}					
		}
		
		/**
		 * 
		 * Enter description here ...
		 */
		function StatusCodeTable()
		{
			global $wpdb;
			
			echo "<div class='wrap'>\n";
			//screen_icon();
			//echo "<h2>Test</h2>\n";
			
			if ( isset($_REQUEST['action']) && $_REQUEST['action'] != -1 ) {
				$theaction = $_REQUEST['action'];
			} else {
				$theaction = 'none';
	  		}
	  		
	  		if ($theaction == 'view')
	  		{
	  			if ( isset($_REQUEST['id']))
	  			{
	  				$id = $_REQUEST['id'];
	  			}
	  			
	  			$sql = "select * " .
				"from wp_http_request_cache where id = " . $id;		
				$items = $wpdb->get_results($sql,'ARRAY_A');
				
				echo '<table style="border: 1px solid #666666;">';
				
				/* uri */
				echo '<tr><td style="border: 1px solid #666666;">uri</td><td style="border: 1px solid #666666;">' . $items[0]['uri'] . '</td></tr>';
				
				/* headers */
				echo '<tr><td style="border: 1px solid #666666;">headers</td><td style="border: 1px solid #666666;">'; 
				$headers = unserialize($items[0]['request_headers']); 
				foreach ($headers as $header1 => $header2)
				{
					echo $header1 . '-' . $header2 . '<br />';
				}
				
				echo '<tr><td style="border: 1px solid #666666;">response code</td>'
				. '<td style="border: 1px solid #666666;">' 
				. $items[0]['request_response_code'] . '</td></tr>';
				
				echo '<tr><td style="border: 1px solid #666666;">response message</td>'
				. '<td style="border: 1px solid #666666;">' 
				. $items[0]['request_response_message'] . '</td></tr>';
				
				
				
				
				echo '</td></tr>';
				
				
				echo '</table>';
				
	  			
	  		}
	  		else 
	  		{			
				/* prepare and display the grid */
	  			if ($this->list_table)
	  			{
					$this->list_table->prepare_items();
					$this->list_table->display();
	  			}	
	  		}	
		}

	}
}
