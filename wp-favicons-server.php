<?php
/*
Plugin Name: wp-favicons-server
Plugin URI: http://wordpress.org/extend/plugins/wp-favicons/
Description: Favicons Server - activate this one ONLY on the single WP instance that will be serving the favicons
Author: Edward de Leau
Author URI: http://edward.de.leau.net
Version: 0.6.6
License: GPLv2, Copyright 2008,2009,2010,2011,2012  edward de Leau  (email : deleau@gmail.com)
*/
define("WP_FAVICON_REQUEST_TIMEOUT", 30);
define("WP_FAVICON_TRANSIENT_TIMEOUT_SERVER", 360);
//define("WP_FAVICON_REQUEST_TIMEOUT", 1);

/*
 * fetches an xml request for an icon (uri)
 * returns bit of html code for the uri
 * 
 */

/**
* To run when this plugin is activated
* {@link http://codex.wordpress.org/Function_Reference/register_activation_hook}
*/
register_activation_hook( __FILE__ , 'wp_favicons_server_install');
function wp_favicons_server_install() {
	global $wp_version;
	if (version_compare($wp_version, "3.1", "<")) {
		deactivate_plugins(basename( __FILE__ ));
		wp_die("This plugin requires WordPress version 3.1 or higher");
	}
	if (version_compare(phpversion(), "5.3.0", "<")) {
		deactivate_plugins(basename( __FILE__ ));
		wp_die("This plugin requires at least PHP version 5.3.0, your version: " . PHP_VERSION . "\n".
			"Please ask your hosting company to bring your PHP version up to date.");
	}
	$wp_favicons = get_option('wp_favicons_options');
	$wp_favicons['FINAL'] = false;
	update_option('wp_favicons_options', $wp_favicons);
}

/**
* To run when this plugin is deactivated
* {@link http://codex.wordpress.org/Function_Reference/register_deactivation_hook}
*/
register_deactivation_hook( __FILE__ ,'wp_favicons_server_uninstall');
function wp_favicons_server_uninstall() {
	//
}

/**
* To run when the plugin is deinstalled
*/
if ( function_exists('register_uninstall_hook') )
register_uninstall_hook(__FILE__, 'wp_favicons_server_uninstall_hook');
function wp_favicons_server_uninstall_hook()
{
	delete_option('wp_favicons_options');
	global $wpdb;
	$wpdb->query("DROP TABLE IF_EXISTS " . $wpdb->prefix . "wpfavicons_uri;");
	$wpdb->query("DROP TABLE IF_EXISTS " . $wpdb->prefix . "wpfavicons_icon;");
	$wpdb->query("DROP TABLE IF_EXISTS " . $wpdb->prefix . "wpfavicons_1;");
	$wpdb->query("DROP TABLE IF_EXISTS " . $wpdb->prefix . "wpfavicons_2;");
	$wpdb->query("DROP TABLE IF_EXISTS " . $wpdb->prefix . "wp_http_request_cache;");
}

/**
* load specific configuration and run with extra php check to not fail on
* older PHP versions @todo check also for xmlrpc extension existence
*/
if (version_compare(phpversion(), "5.3.0", ">=")) {
	add_action('plugins_loaded','RunWpFaviconsServer',5);
	function RunWpFaviconsServer() {
		require_once dirname( __FILE__  ) . '/includes/server/start.php';

	}
}




