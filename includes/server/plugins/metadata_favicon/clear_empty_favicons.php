<?php
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\metadata_favicon_clear_empty_favicons"))
{
	/**
	 *
	 * Enter description here ...
	 * @author cogmios
	 *
	 */
	class metadata_favicon_clear_empty_favicons extends Plugin
	{

	/**
    	 * Executes admin functions
    	 */
     	function ExecuteAdminAction()
        {
        	/* first get the options array */
        	$options_array = Config::GetOptionsAsArray();

        	/* if the user wants to clean the cache */
        	if (1 == Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'clear_empty_favicons'))
        	{
            	$this->delete_empties();
        		
            	/* now set the empty cache option back to 0 again */
				$options_array[Config::GetPluginSlug() . 'clear_empty_favicons'] = 0;
				Config::UpdateOptions($options_array);

        	}
        }
        
        function delete_empties()
        {
        	global $wpdb;
			$table_name = Database::TableName1();
			
			$row_uri = $wpdb->get_results(	"SELECT id " .
				"FROM $table_name WHERE favicon_uri = 'EMPTY';",
				ARRAY_A);
				
			if ($row_uri)
			{
				$id = $row_uri[0]['id'];
				$wpdb->query("DELETE FROM $table_name WHERE favicon_parent = $id;");				
			}	
        }
        
        
		
	}
}