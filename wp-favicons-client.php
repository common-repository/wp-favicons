<?php
/*
Plugin Name: wp-favicons-client
Plugin URI: http://wordpress.org/extend/plugins/wp-favicons/
Description: Favicons Client - activate this one on all your clients
Author: Edward de Leau
Author URI: http://edward.de.leau.net
Version: 0.6.6
License: GPLv2, Copyright 2008,2009,2010,2011,2012  edward de Leau  (email : deleau@gmail.com)
*/

//ini_set('display_errors',1);
//error_reporting(E_ALL & ~E_STRICT);
define("WP_FAVICON_TRANSIENT_TIMEOUT", 3600);
//define("WP_FAVICON_TRANSIENT_TIMEOUT", 1);

/**
 * To run when this plugin is activated
 * {@link http://codex.wordpress.org/Function_Reference/register_activation_hook}
 */
register_activation_hook( __FILE__ , 'wp_favicons_client_install');
function wp_favicons_client_install() {
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
	$wp_favicons = get_option('wp_favicons_client_options');
	$wp_favicons['FINAL'] = false;
	update_option('wp_favicons_client_options', $wp_favicons);
}

/**
 * To run when this plugin is deactivated
 * {@link http://codex.wordpress.org/Function_Reference/register_deactivation_hook}
 */
register_deactivation_hook( __FILE__ ,'wp_favicons_client_uninstall');
function wp_favicons_client_uninstall() {
	//
}

/**
 * To run when the plugin is deinstalled: no database stuff on client side
 */
if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, 'wp_favicons_client_uninstall_hook');
function wp_favicons_client_uninstall_hook()
{
	delete_option('wp_favicons_client_options');
}

/**
 * load specific configuration and run with extra php check to not fail on
 * older PHP versions
 */
if (version_compare(phpversion(), "5.3.0", ">=")) {
	add_action('plugins_loaded','RunWpFaviconsClient',5);
	function RunWpFaviconsClient() {
		require_once dirname( __FILE__  ) . '/includes/client/start.php';

	}
}




