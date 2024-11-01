<?php
/**
 * Main Settings
 *
 * This file sets the main settings
 *
 * Note: feel free to add your own actions to it
 *
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
 * @copyright GPL 2
 */

namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\information_directories"))
{
	/**
	 *
	 * Enter description here ...
	 * @author cogmios
	 *
	 */
	class information_directories extends Plugin
	{
		/**
    	 * Executes admin functions
    	 */
     	function ExecuteAdminAction()
        {
        	/* first get the options array */
        	$options_array = Config::GetOptionsAsArray();

        	/* if the user wants to clean the default */
        	if (1 == Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'reset_dirs'))
        	{

				$upload_dir = wp_upload_dir();
				$rootUpload_dir = 'wp-favicons';
				$rootUpload_dirFull =  $upload_dir['basedir'] . '/' . $rootUpload_dir . '/';
				$rootUpload_urlFull =  $upload_dir['baseurl'] . '/' . $rootUpload_dir . '/';
				$cssUpload_dir = 'style/';
				$cssUpload_file = 'style.css';
				$cacheUpload_dir = 'cache/';
				$n=0;

				$options_array[Config::GetPluginSlug() .'upload_dir']
        		= $rootUpload_dirFull;
				$options_array[Config::GetPluginSlug() .'upload_dir_style']
        		= $rootUpload_dirFull . $cssUpload_dir;
				$options_array[Config::GetPluginSlug() .'upload_dir_cache']
        		= $rootUpload_dirFull . $cacheUpload_dir;
				$options_array[Config::GetPluginSlug() .'upload_dir_style_file']
        		= $rootUpload_dirFull . $cssUpload_dir . $cssUpload_file;
				$options_array[Config::GetPluginSlug() .'upload_url']
        		= $rootUpload_urlFull;
				$options_array[Config::GetPluginSlug() .'upload_url_style']
        		= $rootUpload_urlFull . $cssUpload_dir;
				$options_array[Config::GetPluginSlug() .'upload_url_style_file']
        		= $rootUpload_urlFull . $cssUpload_dir . $cssUpload_file;
        		$options_array[Config::GetPluginSlug() .'upload_url_cache']
        		= $rootUpload_urlFull . $cacheUpload_dir;

				/* now set the  option back to 0 again */
				$options_array[Config::GetPluginSlug() . 'reset_dirs'] = 0;
        		Config::UpdateOptions($options_array);
        	}
        }
	}
}

		
