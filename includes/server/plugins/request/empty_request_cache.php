<?php
/**
 * Empties the request cache
 * @package WP-Favicons
 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
 * @since 0.5.0
 */
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\request_empty_request_cache"))
{
	/**
	 * empties the request cache
	 * This plugin offers functionality to empty the requestcache
 	 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
 	 * @since 0.5.0
	 */
	class request_empty_request_cache extends Plugin
	{

		function ExecuteAdminAction()
		{
			$options_array = Config::GetOptionsAsArray();
        	if (1 == Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'requestempty_cache'))
        	{
        		$temp_line =
					__('Empty the request cache. After emptying the value will jump back ' .
					'to no and the time will be recorded. This option cleans the  request database' .
					' cache. You are advised not to clean this unless a lot of connection issues occurred. This cache ensures that even if you delete the regular cache that you will not make a lot of new requests again. ','wp-favicons');

				Database::EmptyRequestCache();
				$options_array[Config::GetPluginSlug() . 'requestempty_cache'] = 0;
				$options_array[Config::GetPluginSlug() . 'requestcache_emptied_date'] = current_time("mysql");
				Config::UpdateOptions($options_array);

				Config::SetModulePlugin($this->_module,$this->_plugin,'header',
					$temp_line . 'Last time Emptied on: ' .
					Config::GetOptionsArrayValue(Config::GetPluginSlug() .'requestcache_emptied_date'));
        	}
        }
	}
}