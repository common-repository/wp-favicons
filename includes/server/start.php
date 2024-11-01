<?php
/*
 * we load this bit in a seperate file because otherwise it will cause an error message
 * for php versions < 5.3 
 * 
 * @package WP-Favicons
 * @since 0.030
 * @author Edward de Leau <e@leau.net, http://edward.de.leau.net>
 * @copyright gpl 2
 */
namespace leau\co\wp_favicons_server;

/* helpers */
$namespace = "\\leau\\co\\wp_favicons_server\\";
$current_dir = dirname( __FILE__ ) . '/';

/**************************************************************
 * WordPress Plugin Framework Classes | Generic
 **************************************************************/
$generic_root = $current_dir  . '/../generic/';
require_once $generic_root . 'class-abstract-utils.php';
require_once $generic_root . 'class-abstract-module.php';
require_once $generic_root . 'class-abstract-modules.php';
require_once $generic_root . 'class-abstract-plugin.php';
require_once $generic_root . 'class-abstract-init.php';

if (!class_exists($namespace. "EdlUtils")) {
	class EdlUtils extends \leau\co\wp_generic\AbstractUtils { }
}
if (!class_exists($namespace. "Module")) {
	class Module extends \leau\co\wp_generic\AbstractModule {}
}
if (!class_exists($namespace. "Modules")) {
	class Modules extends \leau\co\wp_generic\AbstractModules { }
}
if (!class_exists($namespace. "Plugin")) {
	class Plugin extends \leau\co\wp_generic\AbstractPlugin { }
}
if (!class_exists($namespace. "Init")) {
	class Init extends \leau\co\wp_generic\AbstractInit { }
}


/**************************************************************
 * Specific classes for THIS WordPress Plugin
**************************************************************/
$plugin_root = $current_dir  . '/plugins/';
require_once $current_dir . 'class-xmlrpc.php';
require_once $current_dir . 'class-log.php';
require_once $current_dir . 'class-uri-xmlrpc.php';
require_once $current_dir . 'class-database.php';
require_once $current_dir . 'class-http.php';
require_once $current_dir . 'class-metadata.php';

/* Specific includes for server plugin favicon */
require_once $plugin_root . '/metadata_favicon/inc/class-favicon.php';
require_once $plugin_root . '/metadata_favicon/inc/class-favicon-factory.php';
require_once $plugin_root . '/metadata_favicon/inc/class-EdlCache.php';
require_once $plugin_root . '/sources/inc/class-module-sources-plugin.php';
require_once $plugin_root . '/default/inc/class-module-default-plugin.php';
require_once $plugin_root . '/filters/inc/class.ico.php';

/* Specific includes needed for server plugin: http requests */
require_once $plugin_root . '/request/inc/class-statuscode.php';
require_once $plugin_root . '/request/inc/class-statuscode-factory.php';

/**************************************************************
 * Configuration of this WordPress Plugin | Generic
**************************************************************/
$config_root = $current_dir  . '/config/';
require_once $config_root . 'class-config.php';
require_once $config_root . 'class-load-configuration.php';
do_action( Config::GetPluginSlug() . 'config');
new LoadConfiguration;


/**************************************************************
 * Init the WP Plugin Framework | Generic
**************************************************************/
$all_plugins = Config::GetRegisteredPlugins();
foreach ($all_plugins as $module_key => $module_value)
{
	foreach ($module_value as $plugin_key => $plugin_value)
	{
		require_once $plugin_root . $module_key . '/' . $plugin_key . '.php';
	}
}
new Init();

/**************************************************************
 * Start the plugin
**************************************************************/
new uri_xmlrpc('wpserver');


