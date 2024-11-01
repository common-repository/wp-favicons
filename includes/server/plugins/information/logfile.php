<?php
/**
 * Debug logfile
 * Write a DEBUG.LOG in the cache dir
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
 * @copyright GPL 2
 */

namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\information_logfile"))
{
	/**
	 *
	 * Enter description here ...
	 * @author cogmios
	 *
	 */
	class information_logfile extends Plugin
	{

		function ExecuteAdminAction()
        {
        	/* first get the options array */
        	$options_array = Config::GetOptionsAsArray();

        	/* if the user wants to clean the cache */
        	if (1 == Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'deletedebuglog'))
        	{
        		$turndebuglogon_afterwards = false;
        		if (true == Config::GetOptionsArrayValue('debuglog')) {
        			$turn_cache_back_on_afterwards = true;
        			$options_array[Config::GetPluginSlug() . 'debuglog'] = false;
        			Config::UpdateOptions($options_array);
        		}

				$dir=Config::GetOptionsArrayValue(Config::GetPluginSlug() .
						'upload_dir_cache');
				$file=$dir.'DEBUG.LOG';
				try {
					unlink($file);
				} catch (exception $e) {
					EdlUtils::favicon_page_msg('logfile',
						' [' . $this->_module. '_' . $this->_plugin . '] '  .
						 $e->getMessage());
				}

            	/* now set the empty cache option back to 0 again */
				$options_array[Config::GetPluginSlug() . 'deletedebuglog'] = 0;
				Config::UpdateOptions($options_array);

				/* 6. if needed activate the log again */
				if ($turndebuglogon_afterwards)
				{
					$options_array[Config::GetPluginSlug() . 'debuglog'] = true;
        			Config::UpdateOptions($options_array);
				}
        	}
        }

	}
}