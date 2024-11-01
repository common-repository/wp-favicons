<?php
/*
 * 
 *  dependencies: none
 * 
 */
namespace leau\co\wp_favicons_client;

if (!class_exists("\\leau\\co\\wp_favicons_client\\LoadConfiguration"))
{
	/**
	 * Sets Specific Configuration settings for WP-Favicons
	 * uses the generic config class
	 * @since 0.4.0
	 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
	 *
	 */
	class LoadConfiguration
	{
		function __construct()
		{
			// handy vars
			$page_title = 'page_title';
			$menu_title = 'menu_title';
			$menu_slug = 'menu_slug';
			$section_header_title = 'section_header_title';
			$section_header_text = 'section_header_text';
			$help_text = 'help_text';
			$gurl='http://www.google.com/s2/favicons?domain=';

			// set the specific configuration for the favicon plugin:
			Config::SetPluginVersion('0.6.0');
			Config::SetOptionsName('wp_favicons_client_options');
			Config::SetOptionsFromArray(get_option(Config::GetOptionsName()));
			Config::SetSettingsGroupName('wp_favicons_client_settings_group');
			Config::SetPageTitle('WP Favicons Client Settings');
			Config::SetMenuTitle('Icon Client');
			Config::SetCapability('manage_options');
			Config::SetMenuSlug('WP-Favicons-Client-Menu');
			Config::SetPluginSlug('wp_favicons_client_');
			Config::SetNicePluginSlug('wp-favicons_client_');
			Config::AddMenuIcon(plugins_url('/img/wpfavicons.png', __FILE__));

/* -------------------------------------------------------------*/
/*          MAIN			                                    */
/* -------------------------------------------------------------*/
			Config::SetMainPage(
			__('<h2>Welcome to the WordPress Favicons Client</h2>'
			. '<p>'
			. 'This <img src="'. $gurl . 'wordpress.org"> WordPress Plugin does XMLRPC Requests '
			. 'to the server and gets favicons '
			. '</p>'
			. "<p>This is still a beta version please contribute in the <a href=\"http://wordpress.org/tags/wp-favicons?forum_id=10\">forum</a> "
			. "(questions, defects, RFCS)</p>"
			. "<p>Version 0.6.0 is what you are looking at</p>"));

				
/* -------------------------------------------------------------*/
/*          MODULE CONTEXT                                      */
/* -------------------------------------------------------------*/
			$moduleID = 'context';
/* main module settings */
					Config::AddModule(array(
							$page_title 			=> 'Favicon Replacement Context',
							$menu_title 			=> 'Context',
							$menu_slug 				=> $moduleID,
							$section_header_title 	=> '<h2>Metadata Context</h2>',
							$section_header_text   =>
							__('A wordPress theme consists out of many elements. Each element has its own demands. On '.
									'this page you select where you want to have metadata such as icons or statuscodes added.'),
							$help_text				=>
							__('<h3>The WordPress Favicons Plugin - Context Screen</h3>' .
									'<p>A WordPress theme consists of many components. Each component ' .
									'can be treated differently and styled differently.</p> <p> On a functional ' .
									'level you might want big icons in your post content and small icons ' .
									'in a sidebar widget. Or... you might even want different styled icons (or no) icons per ' .
									'widget. This can be set on this page.</p>' .
									'<p>On a technical level widgets and other content provide different \'filters\': ' .
									'each of the filters can be called and used differently.</p>' .
									'<p>If you are a developer you can add plugins to this page to add more ' .
									'content replacers. You might e.g. write a plugin for replacing the content ' .
									'of a complete sidebar using ob_. The architecture is open so it is easy to ' .
									'add this plugin so they show up here.</p>' .
									'<h3>Specific information per Wp-Favicons Content plugin:</h3>')
					));
/* plugin: the_content */
					$pluginID = 'the_content';
					Config::RegisterModulePlugin($moduleID,$pluginID);
					Config::SetModulePlugin($moduleID,$pluginID,'mode',
							'href');
					Config::SetModulePlugin($moduleID,$pluginID,'header',
							__('In the post and page content','wp-favicons'));
					Config::SetModulePlugin($moduleID,$pluginID,'title',
							__('Posts and Pages','wp-favicons'));
					Config::SetModulePlugin($moduleID,$pluginID,'filter_context_name',
							'the_content');
					Config::SetModulePluginField($moduleID,$pluginID,0,
							array(	'name' => Config::GetPluginSlug() . 'show_in_posts',
									'default' => 1,
									'label' => __('Replace in posts','wp-favicons'),
									'type' => 'radio_bool'));
					Config::SetModulePluginField($moduleID,$pluginID,1,
							array(	'name' => Config::GetPluginSlug() . 'show_in_posts_css',
									'default' => 'width:10px!important;height:10px!important;margin-left:0px!important;margin-right:0px!important;padding-left:3px!important;padding-right:3px!important;padding-bottom: 0px!important;padding-top:0px!important;',
									'label' => __('Styling','wp-favicons'),
									'type' => 'textarea'));
/* plugin: widget_text */
					$pluginID = 'widget_text';
					Config::RegisterModulePlugin($moduleID,$pluginID);
					Config::SetModulePlugin($moduleID,$pluginID,'mode',
							'href');
					Config::SetModulePlugin($moduleID,$pluginID,'header',
							'In text areas of widgets');
					Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Text areas of widgets');
					Config::SetModulePlugin($moduleID,$pluginID,'filter_context_name',
							'widget_text');
					Config::SetModulePluginField($moduleID,$pluginID,0,
							array(	'name' => Config::GetPluginSlug() . 'show_widget_text',
									'default' => 1,
									'label' => 'Replace Widget Text',
									'type' => 'radio_bool'));
					Config::SetModulePluginField($moduleID,$pluginID,1,
							array(	'name' => Config::GetPluginSlug() . 'show_in_widget_text_css',
									'default' => 'width:10px!important;height:10px!important;margin-left:0px!important;margin-right:0px!important;padding-left:3px!important;padding-right:3px!important;padding-bottom: 0px!important;padding-top:0px!important;',
									'label' => 'Styling',
									'type' => 'textarea'));
/* plugin: bookmark_list */
					$pluginID = 'bookmark_list';
					Config::RegisterModulePlugin($moduleID,$pluginID);
					Config::SetModulePlugin($moduleID,$pluginID,'mode',
							'href');
					Config::SetModulePlugin($moduleID,$pluginID,'header',
							'In bookmark list widgets');
					Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Bookmark List widgets');
					Config::SetModulePlugin($moduleID,$pluginID,'filter_context_name',
							'wp_list_bookmarks');
					Config::SetModulePluginField($moduleID,$pluginID,0,
							array(	'name' => Config::GetPluginSlug() . 'show_in_list_bookmarks',
									'default' => 1,
									'label' => 'Replace in Bookmark Lists',
									'type' => 'radio_bool'));
					Config::SetModulePluginField($moduleID,$pluginID,1,
							array(	'name' => Config::GetPluginSlug() . 'show_in_list_bookmarks_css',
									'default' => 'width:10px!important;height:10px!important;margin-left:0px!important;margin-right:0px!important;padding-left:3px!important;padding-right:3px!important;padding-bottom: 0px!important;padding-top:0px!important;',
									'label' => 'Styling',
									'type' => 'textarea'));
/* plugin: comment text */
					$pluginID = 'comment_text';
					Config::RegisterModulePlugin($moduleID,$pluginID);
					Config::SetModulePlugin($moduleID,$pluginID,'mode',
							'href');
					Config::SetModulePlugin($moduleID,$pluginID,'header',
							'In comment text');
					Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Comment Text');
					Config::SetModulePlugin($moduleID,$pluginID,'filter_context_name',
							'comment_text');
					Config::SetModulePluginField($moduleID,$pluginID,0,
							array(	'name' => Config::GetPluginSlug() . 'show_in_comment_text',
									'default' => 1,
									'label' => 'Replace in Comment Text',
									'type' => 'radio_bool'));
					Config::SetModulePluginField($moduleID,$pluginID,1,
							array(	'name' => Config::GetPluginSlug() . 'show_in_comment_text_css',
									'default' => 'width:10px!important;height:10px!important;margin-left:0px!important;margin-right:0px!important;padding-left:3px!important;padding-right:3px!important;padding-bottom: 0px!important;padding-top:0px!important;',
									'label' => 'Styling',
									'type' => 'textarea'));
/* plugin: comment author link */
					$pluginID = 'get_comment_author_link';
					Config::RegisterModulePlugin($moduleID,$pluginID);
					Config::SetModulePlugin($moduleID,$pluginID,'mode',
							'href');
					Config::SetModulePlugin($moduleID,$pluginID,'header',
							'Before comment author url');
					Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Comment Author Link');
					Config::SetModulePlugin($moduleID,$pluginID,'filter_context_name',
							'get_comment_author_link');
					Config::SetModulePluginField($moduleID,$pluginID,0,
							array(	'name' => Config::GetPluginSlug() . 'show_in_comment_author_link',
									'default' => 1,
									'label' => 'Replace in Comment Author Url',
									'type' => 'radio_bool'));
					Config::SetModulePluginField($moduleID,$pluginID,1,
							array(	'name' => Config::GetPluginSlug() . 'show_in_comment_author_link_css',
									'default' => 'width:10px!important;height:10px!important;margin-left:0px!important;margin-right:0px!important;padding-left:3px!important;padding-right:3px!important;padding-bottom: 0px!important;padding-top:0px!important;',
									'label' => 'Styling',
									'type' => 'textarea'));

/* -------------------------------------------------------------*/
/*          MODULE XMLRPC SERVER SETTINGS						*/
/* -------------------------------------------------------------*/
$moduleID = 'xmlrpcserver';

/* 
 * main module settings 
 * 
 */
Config::AddModule(array(
	$page_title 			=> 'Server Settings',
	$menu_title 			=> 'Server Settings',
	$menu_slug 				=> $moduleID,
	$section_header_title 	=> '<h2>Server Settings</h2>',
	$section_header_text   =>
		__('Set the favicon server settings'),
	$help_text				=>
		__('<h3>The WordPress Favicons Plugin - Server Settings</h3><p>enter the server settings</p>')
));
						
/* 
 * plugin: server settings 
 * 
 */
$pluginID = 'server_settings';
Config::RegisterModulePlugin($moduleID,$pluginID);
Config::SetModulePlugin($moduleID,$pluginID,'title',
	'Server URI');
Config::SetModulePlugin($moduleID,$pluginID,'header',
	'Enter the  XMLRPC server information - if you have only 1 wp installation running both client and server then simply enter your own server information.');
					
// @todo remove all getpluginslug prefixes
Config::SetModulePluginField($moduleID,$pluginID,0,
	array(	'name' 		=> Config::GetPluginSlug() . 'server_uri',
			'default' 	=> site_url() . '/xmlrpc.php',
			'label' 	=> 'Server Uri',
			'type' 		=> 'text'));
Config::SetModulePluginField($moduleID,$pluginID,1,
	array(	'name' 		=> Config::GetPluginSlug() . 'blog_id',
		'default' 	=> '0',
		'label' 	=> 'Blog Id',
		'type' 		=> 'text'));
Config::SetModulePluginField($moduleID,$pluginID,2,
		array(	'name' 		=> Config::GetPluginSlug() . 'server_usr',
				'default' 	=> 'admin',
				'label' 	=> 'username',
				'type' 		=> 'text'));
Config::SetModulePluginField($moduleID,$pluginID,3,
	array(	'name' 		=> Config::GetPluginSlug() . 'server_pwd',
			'default' 	=> 'password',
			'label' 	=> 'Password',
			'type' 		=> 'text'));					
					
		}
	}		
}


