<?php
/*
 * 
 *  dependencies: none
 * 
 */
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\LoadConfiguration"))
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
			Config::SetOptionsName('wp_favicons_options');
			Config::SetOptionsFromArray(get_option(Config::GetOptionsName()));
			Config::SetSettingsGroupName('wp_favicons_settings_group');
			Config::SetPageTitle('WP Favicons Settings');
			Config::SetMenuTitle('Icon Server');
			Config::SetCapability('manage_options');
			Config::SetMenuSlug('WP-Favicons-Menu');
			Config::SetPluginSlug('wp_favicons_');
			Config::SetNicePluginSlug('wp-favicons_');
			Config::AddMenuIcon(plugins_url('/img/wpfavicons.png', __FILE__));

			
/* -------------------------------------------------------------*/
/*          MAIN			                                    */
/* -------------------------------------------------------------*/
			$temp_array = get_option('wp_favicons_options');
			$cache_status = 0;			
			if (is_array( $temp_array))
			{
				if (array_key_exists('wp_favicons_use_cache', $temp_array)) 
				{
					$cache_status = $temp_array['wp_favicons_use_cache'];
				}
			}	
			$main_addition="";
			if ( $cache_status == 0) {
				$main_addition=
				"<h3>Cach off</h3><p>Your cache is OFF - remove the plugin and reinstall</p> ";
			}
				

			Config::SetMainPage(
			__('<h2>Welcome to the WordPress Favicons Server</h2>'
			. '<p>'
			. 'This <img src="'. $gurl . 'wordpress.org"> WordPress Plugin responds to XMLRPC Requests '
			. 'from clients and serves favicons to clients, in addition it logs all requests for some nice '
			. 'stats'
			. '</p>'
			. '<p>'
			. 'The plugin has a directory /includes with more or less generic stuff. It also has a directory '
			. 'plugins which adds all functionality in modular bits, which means you can extend the server '
			. 'with more plugins / the plugins are examples included with this main plugin ' 
			. '</p>'
			
			
			
			. "<p>this is still a beta version please contribute in the <a href=\"http://wordpress.org/tags/wp-favicons?forum_id=10\">forum</a> "
			. "(questions, defects, RFCS)</p>"
			. "<p>Version 0.6.0 is what you are looking at</p>"
			. $main_addition

			));
			
/* -------------------------------------------------------------*/
/*          MODULE EXCEPTIONS                                   */
/* -------------------------------------------------------------*/
			$moduleID = 'exceptions';
/* main module settings */
			Config::AddModule(array(
							'page_title' => 'Exceptions',
							'menu_title' => 'Exceptions',
							'menu_slug' => $moduleID,
							'section_header_title' => '<h2>Exceptions</h2>',
							'section_header_text' =>
								"Objects which we will not process ",
							'help_text' =>
								"Exceptions"
			));
/* plugin: exclude filetypes */
			$pluginID = 'excluded_filetypes';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'Filetypes that are excluded');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Excluded Filetypes');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'exclude_filetypes',
			    					'default' => '.zip .pdf .arj',
			    					'label' => 'Exclude the following',
			    					'type' => 'text'));
			
/* -------------------------------------------------------------*/
/*          FAVICON / MODULE FAVICON                            */
/* -------------------------------------------------------------*/
			$moduleID = 'metadata_favicon';
/* main module settings */
			Config::AddModule(array(
							'page_title' => 'Favicons Main',
							'menu_title' => 'Favicons Main',
							'menu_slug' => $moduleID,
							'section_header_title' => '<h2>Favicons</h2>',
							'section_header_text' =>
								"<ul>".
								"<li>Show a favicon next to links</li>".
								"</ul>"
			,
							'help_text' =>
								'<h3>Favicons</h3>' .
								'<h3>Specific information per Wp-Favicons Content plugin:</h3>'
			));
/* plugin: main */
			$pluginID = 'main';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'If you choose yes favicons will be shown next to links, this is enabled by default');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Show a favicon');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'show_favicon',
			    					'default' => 1,
			    					'label' => 'Show favicon',
			    					'type' => 'radio_bool'));
/* plugin: disk and uri cache settings */
			$pluginID = 'cache';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Use the disk and metadata cache');
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'To make this simple: Use the Cache or use Google');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'use_cache',
			    					'default' => 1,
									'label' => 'Use the disk and metadata cache',
			    					'type' => 'radio_bool'));
/* plugin: empty cache */
			$pluginID = 'empty_metadata_cache';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Empty the metadata cache');
			$temp_line =
			__('Empty the metadata db cache. After emptying the value will jump back ' .
							'to no and the time will be recorded.','wp-favicons');
			
			if (Config::GetOptionsArrayValue(Config::GetPluginSlug() .'cache_emptied_date'))
			{
				Config::SetModulePlugin($moduleID,$pluginID,'header',
				$temp_line . 'Last time Emptied on: ' .
				Config::GetOptionsArrayValue(Config::GetPluginSlug() .'cache_emptied_date'));
			}
			else
			{
				Config::SetModulePlugin($moduleID,$pluginID,'header',
				$temp_line);
			}
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'empty_cache',
			    					'default' => 0,
			    					'label' => 'Empty the metadata cache',
			    					'type' => 'radio_bool'));
			
/* plugin: placement */
			$pluginID = 'placement';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'You can place the icon in several places and even more at once (though that would be silly)');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Select where to place the favicon');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'favicon_spot1',
			    					'default' => 0,
			    					'label' => 'Before the link tag',
			    					'type' => 'radio_bool'));
			Config::SetModulePluginField($moduleID,$pluginID,1,
			array(	'name' => Config::GetPluginSlug() . 'favicon_spot2',
			    					'default' => 0,
			    					'label' => 'After the link tag',
			    					'type' => 'radio_bool'));
			Config::SetModulePluginField($moduleID,$pluginID,2,
			array(	'name' => Config::GetPluginSlug() . 'favicon_spot3',
			    					'default' => 1,
			    					'label' => 'Inside the link tag, before the link, read <a href="http://stackoverflow.com/questions/271688/wrap-an-image-in-an-a-tag-but-only-have-underline-on-text">this</a> if you want to not underline the icon.',    					
			    					'type' => 'radio_bool'));
			Config::SetModulePluginField($moduleID,$pluginID,3,
			array(	'name' => Config::GetPluginSlug() . 'favicon_spot4',
			    					'default' => 0,
			    					'label' => 'Inside the link tag, after the link',
			    					'type' => 'radio_bool'));
			
/* clear all EMPTY database items */
			$pluginID = 'clear_empty_favicons';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Clear EMPTY database');
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'Clear all non-found favicon entries to retry them');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'clear_empty_favicons',
			    					'default' => 0,
									'label' => 'Clear empty entries',
			    					'type' => 'radio_bool'));		
				
			
			
/* -------------------------------------------------------------*/
/*          FAVICON  SOURCES                                    */
/* -------------------------------------------------------------*/
			$moduleID = 'sources';
/* main module settings */
			Config::AddModule(array(
							'page_title' => 'Favicon sources',
							'menu_title' => 'Favicon Sources',
							'menu_slug' => $moduleID,
							'section_header_title' => '<h2>Favicons Sources</h2>',
							'section_header_text' =>
								"<ul>".
								"<li>There are multiple sources to retrieve the favicon from.</li>".
								"<li>Select the ones you would like to use. Please note that with each addition, ".
								"depending on the provider, your page load will increase.</li><li> However, IF you have the ".
								"WP-Favicons Cache on this will happen only the first time it needs to look for an ".
								"icon.</li><li> The benefit of more providers is that you will increase your chance of finding ".
								"the icon. So... I strongly advice to turn them all on.</li>".
								"</ul>"
			
			,
							'help_text' =>
								'<h3>The WordPress Favicons Plugin - Context Screen</h3>' .
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
								'<h3>Specific information per Wp-Favicons Content plugin:</h3>'
			));
/* plugin: page */
			$pluginID = 'page';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'Checks the hosts rel tags or favicon.ico in the root. Handles redirection.');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Get favicon directly from URI');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'search_page',
			    					'default' => 1,
			    					'label' => 'Search in Page',
			    					'type' => 'radio_bool'));
/* plugin: Google S2 */
			$pluginID = 'google_s2';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'Uses Google to find favicons in .png format, this is very fast');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Backup: Search Google');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'search_google',
			    					'default' => 1,
			    					'label' => 'Search Google',
			    					'type' => 'radio_bool'));
/* plugin: geticonorg */
			$pluginID = 'geticonorg';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'Uses geticon.org to find favicons in .png format, nice backup for Google');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Backup: Search Favicon.org');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'search_geticonorg',
			    					'default' => 1,
			    					'label' => 'Search Geticon.org',
			    					'type' => 'radio_bool'));
			
			
			
			
			
/* -------------------------------------------------------------*/
/*          FAVICON / MODULE FILTERS                            */
/* -------------------------------------------------------------*/
			
			$moduleID = 'filters';
/* main module settings */
			Config::AddModule(array(
							'page_title' => 'Favicon filters',
							'menu_title' => 'Favicon Filters',
							'menu_slug' => $moduleID,
							'section_header_title' => '<h2>Filters</h2>',
							'section_header_text' =>
								"<ul>".
								"<li>Filters allow image manipulation after retrieval of the icon.<li>" .
								"<li>You need to have the cache on to enable these filters.</i>".
								"<li>If you changed filters and want to apply them to existing cached icons then empty the cache.</i>".
								"</ul>",
							'help_text' => "Filters Help"
			));
/* plugin: convert to png */
			$pluginID = 'convert_to_png';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'Converts all received icons to <a href="http://en.wikipedia.org/wiki/Portable_Network_Graphics" target="_blank">png</a>');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Convert to PNG (I dont advise this unless you REALLY want all icons to be in PNG format)');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'convert_to_pngs',
			    					'default' => 0,
			    					'label' => 'Convert to PNG',
			    					'type' => 'radio_bool'));
			
/* -------------------------------------------------------------*/
/*          FAVICON / MODULE DEFAULT					        */
/* -------------------------------------------------------------*/
			
			$moduleID = 'default';
/* main module settings */
			Config::AddModule(array(
							'page_title' => 'Default icons',
							'menu_title' => 'Favicon Default',
							'menu_slug' => $moduleID,
							'section_header_title' => '<h2>Default icon</h2>',
							'section_header_text' =>
						 		"<ul>".
								"<li>If no image is found by the plugins you activated under 'sources' a default icon will be used.</li>".
								"<li>If you select multiple defaults only one will be used (the top one).</li>".
								"<li>If all plugins hereunder are disabled then nothing will be shown if no icon is found.</li>".
								"<li>If you dont cache the gravatar generated defaults then it will save you a lot of space but it will generate a lot of requests to gravatar.com, up to you.</li>".
								"<li>If you dont choose to use the cache option no after filter procesing is possible since we do that in the cache</li>".
								"<li>If you change to another default/caching and want already cached default icons to be resetted then clear the cache.<li>",
							'help_text' => "Default Icon Help"
			));
/* plugin: identicon */
			$pluginID = 'identicon';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'An Identicon is a visual representation of a hash value of the IP address of the website.');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Shows an <a href="http://en.wikipedia.org/wiki/Identicon" target="_blank">identicon</a> when no favicon is found');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'default_identicon',
			    					'default' => 1,
			    					'label' => 'Show identicon',
			    					'type' => 'radio_bool'));
			Config::SetModulePluginField($moduleID,$pluginID,1,
			array(	'name' => Config::GetPluginSlug() . 'cache_default_identicon',
			    					'default' => 0,
			    					'label' => 'Cache identicon',
			    					'type' => 'radio_bool'));
/* plugin: wavatar */
			$pluginID = 'wavatar';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'A Wavatar is a visual representation of a hash value of the IP address of the website displaying a face');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Shows a <a href="http://www.shamusyoung.com/twentysidedtale/?p=1462" target="_blank">wavatar</a> when no favicon is found');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'default_wavatar',
			    					'default' => 0,
			    					'label' => 'Show wavatar',
			    					'type' => 'radio_bool'));
			Config::SetModulePluginField($moduleID,$pluginID,1,
			array(	'name' => Config::GetPluginSlug() . 'cache_default_wavatar',
			    					'default' => 0,
			    					'label' => 'Cache wavatar',
			    					'type' => 'radio_bool'));
/* plugin: retro */
			$pluginID = 'retro';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'A Retro icon is a visual representation of a hash value of the IP address of the website displaying a retro thingie');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Shows a retro icon when no favicon is found');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'default_retro',
			    					'default' => 0,
			    					'label' => 'Show retro icon',
			    					'type' => 'radio_bool'));
			Config::SetModulePluginField($moduleID,$pluginID,1,
			array(	'name' => Config::GetPluginSlug() . 'cache_default_retro',
			    					'default' => 0,
			    					'label' => 'Cache retro icon',
			    					'type' => 'radio_bool'));
			
/* plugin: monsterID */
			$pluginID = 'monsterID';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'A MonsterID is a visual representation of a hash value of the IP address of the website displaying a monster');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Shows a <a href="http://www.splitbrain.org/projects/monsterid">monsterID</a> icon when no favicon is found');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'default_monsterID',
			    					'default' => 0,
			    					'label' => 'Show monsterID icon',
			    					'type' => 'radio_bool'));
			Config::SetModulePluginField($moduleID,$pluginID,1,
			array(	'name' => Config::GetPluginSlug() . 'cache_default_monsterID',
			    					'default' => 0,
			    					'label' => 'Cache monsterID icon',
			    					'type' => 'radio_bool'));
			
			
			
/* -------------------------------------------------------------*/
/*          MODULE INFORMATION                                  */
/* -------------------------------------------------------------*/
			
			$moduleID = 'information';
/* main module settings */
			Config::AddModule(array(
							'page_title' => 'Information',
							'menu_title' => 'Information',
							'menu_slug' => $moduleID,
							'section_header_title' => '<h2>Information</h2>',
							'section_header_text' =>
								"Show you technical information",
							'help_text' =>
								"Show you technical information"
			));
/* plugin: directories */
			$pluginID = 'directories';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
							'(read only)');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Directories and files');
			$upload_dir = wp_upload_dir();
			$rootUpload_dir = 'wp-favicons';
			$rootUpload_dirFull =  $upload_dir['basedir'] . '/' . $rootUpload_dir . '/';
			$rootUpload_urlFull =  $upload_dir['baseurl'] . '/' . $rootUpload_dir . '/';
			$cssUpload_dir = 'style/';
			$cssUpload_file = 'style.css';
			$cacheUpload_dir = 'cache/';
			$n=0;
			Config::SetModulePluginField($moduleID,$pluginID,$n++,
			array(  'name' 		=> Config::GetPluginSlug() . 'upload_dir',
			'default' 	=> $rootUpload_dirFull,
			'label' 	=> 'Physical Upload Directory',
			    					'type' 		=> 'text_no_change'));
						Config::SetModulePluginField($moduleID,$pluginID,$n++,
			array(	'name' 		=> Config::GetPluginSlug() . 'upload_dir_style',
									'default' 	=> $rootUpload_dirFull . $cssUpload_dir,
			'label' 	=> 'Physical Stylesheet Directory',
			    					'type' 		=> 'text_no_change'));
			Config::SetModulePluginField($moduleID,$pluginID,$n++,
			array(	'name' 		=> Config::GetPluginSlug() . 'upload_dir_cache',
			'default' 	=> $rootUpload_dirFull . $cacheUpload_dir,
			'label' 	=> 'Physical Cache Directory',
			'type' 		=> 'text_no_change'));
			Config::SetModulePluginField($moduleID,$pluginID,$n++,
			array(	'name' 		=> Config::GetPluginSlug() . 'upload_dir_style_file',
			'default' 	=> $rootUpload_dirFull . $cssUpload_dir . $cssUpload_file,
			'label' 	=> 'Physical Stylesheet Location',
			'type' 		=> 'text_no_change'));
						Config::SetModulePluginField($moduleID,$pluginID,$n++,
							array(	'name' 		=> Config::GetPluginSlug() . 'upload_url',
			'default' 	=> $rootUpload_urlFull,
			'label' 	=> 'Relative root',
			'type' 		=> 'text_no_change'));
			Config::SetModulePluginField($moduleID,$pluginID,$n++,
			array(	'name' 		=> Config::GetPluginSlug() . 'upload_url_style',
			'default' 	=> $rootUpload_urlFull . $cssUpload_dir,
			'label' 	=> 'Relative CSS Location',
			    					'type' 		=> 'text_no_change'));
			Config::SetModulePluginField($moduleID,$pluginID,$n++,
			array(	'name' 		=> Config::GetPluginSlug() . 'upload_url_style_file',
			'default' 	=> $rootUpload_urlFull . $cssUpload_dir . $cssUpload_file,
			'label' 	=> 'Relative CSS file location',
			    					'type' 		=> 'text_no_change'));
			Config::SetModulePluginField($moduleID,$pluginID,$n++,
							array(	'name' 		=> Config::GetPluginSlug() . 'upload_url_cache',
			'default' 	=> $rootUpload_urlFull . $cacheUpload_dir,
			'label' 	=> 'Relative Cache location',
			    					'type' 		=> 'text_no_change'));
			Config::SetModulePluginField($moduleID,$pluginID,$n++,
							array(	'name' 		=> Config::GetPluginSlug() . 'reset_dirs',
									'default' 	=> 0,
			'label' 	=> 'Reset the dirs e.g. when moving sites',
			    					'type' 		=> 'radio_bool'));
			
			
			
/* plugin: logfile */
						$pluginID = 'logfile';
						Config::RegisterModulePlugin($moduleID,$pluginID);
			$dir=Config::GetOptionsArrayValue(Config::GetPluginSlug() .
			'upload_dir_cache');
			$file=$dir.'DEBUG.LOG';
			Config::SetModulePlugin($moduleID,$pluginID,'header',
			'Writes a debug log file in the cache directory "DEBUG.LOG". Only activate this when testing because it can get big.'
			. ' On your system it is located here: '. $file .
			', so read it online <a href="' .
							Config::GetOptionsArrayValue(Config::GetPluginSlug() .
									'upload_url_cache') . 'DEBUG.LOG" target="_blank">here</a>.'
						);
			Config::SetModulePlugin($moduleID,$pluginID,'title',
			'Debug Log');
			Config::SetModulePluginField($moduleID,$pluginID,0,
							array(	'name' => Config::GetPluginSlug() . 'debuglog',
			'default' => 0,
			'label' => 'Write debuglog',
			'type' => 'radio_bool'));
			Config::SetModulePluginField($moduleID,$pluginID,1,
			array(	'name' => Config::GetPluginSlug() . 'deletedebuglog',
			'default' => 0,
			'label' => 'Delete debuglog',
			    					'type' => 'radio_bool'));
			/* plugin: statistics on sources */
			$pluginID = 'sources';
			Config::RegisterModulePlugin($moduleID,$pluginID);
			Config::SetModulePlugin($moduleID,$pluginID,'header',
			'');
			Config::SetModulePlugin($moduleID,$pluginID,'title',
							'Where did we get your favicons from');
			// @todo calculate only on admin page
			$ss='<table border=1>';
			$results = Database::SourcesStats();
			//if ($results)
			if (false)
			{
							foreach($results as $result)
			{
			$ss.="<tr>";
			if (''==$result['favicon_source'])
								{
									$ss.="<td>reference to already stored icon</td>";
			}
			else
			{
									$ss.="<td>" . $result['favicon_source'] . "</td>";
			}
			$ss.="<td>" . $result['NumOccurrences'] . "</td>";
				$ss.="</tr>";
			}
			}
				$ss.="</table>";
					$options_array = Config::GetOptionsAsArray();
						$options_array[Config::GetPluginSlug() . 'sourcesstats'] = $ss;
					Config::UpdateOptions($options_array);
					Config::SetModulePluginField($moduleID,$pluginID,0,
					array(	'name' => Config::GetPluginSlug() . 'sourcesstats',
			    					'default' => $ss,
					'label' => 'stats on sources',
					'type' => 'freetext'));
			
/* -------------------------------------------------------------*/
/*          MODULE HTTP REQUESTS                                */
/* -------------------------------------------------------------*/
					$moduleID = 'request';
/* main module settings */
					Config::AddModule(array(
							'page_title' => 'HTTP Requests',
					'menu_title' => 'HTTP Requests',
					'menu_slug' => $moduleID,
					'section_header_title' => '<h2>HTTP Requests and Status Codes</h2>',
					'section_header_text' =>
					"HTTP Requests",
					'help_text' =>
					"HTTP Requests"
					));
					/* plugin: request cache settings */
					$pluginID = 'request_cache';
					Config::RegisterModulePlugin($moduleID,$pluginID);
						Config::SetModulePlugin($moduleID,$pluginID,'title',
					'Activation and placement');
						Config::SetModulePlugin($moduleID,$pluginID,'header',
							'Choose if you want to activate the request cache and where you want to place '
					. 'status codes. We advise to have the request cache on: it will also cache '
					. 'requests for favicons and other metadata (even if you dont want to show http '
					. 'status icons). If you don\'t show metadata such as favicons the request cache will '
					. 'not be filled and so no http status icons can be shown. ');
						Config::SetModulePluginField($moduleID,$pluginID,0,
							array(	'name' => Config::GetPluginSlug() . 'use_request_cache',
			    					'default' => 1,
									'label' => 'Activate request cache',
			    					'type' => 'radio_bool'));
						// location
						Config::SetModulePluginField($moduleID,$pluginID,1,
							array(	'name' => Config::GetPluginSlug() . $pluginID . '_show_status',
					'default' => 0,
					'label' => 'Show Status Icons',
			    					'type' => 'radio_bool'));
			
					/* plugin: empty request cache */
						$pluginID = 'empty_request_cache';
						Config::RegisterModulePlugin($moduleID,$pluginID);
					$temp_line =
					__('Empty the request cache. After emptying the value will jump back '
					. 'to no and the time will be recorded. This option cleans the  request database'
					. ' cache. You are advised not to clean this unless a lot of connection issues occurred. '
					. 'This cache ensures that even if you delete the regular cache that you will not make a lot of new requests again. '
					. ', also all http requests status icons will have to be rebuild.','wp-favicons');
			
					if (Config::GetOptionsArrayValue(Config::GetPluginSlug() .'requestcache_emptied_date'))
						{
							Config::SetModulePlugin($moduleID,$pluginID,'header',
								 $temp_line . 'Last time Emptied on: ' .
									Config::GetOptionsArrayValue(Config::GetPluginSlug() .'requestcache_emptied_date'));
						}
						else
						{
							Config::SetModulePlugin($moduleID,$pluginID,'header',
								$temp_line);
			}
			Config::SetModulePlugin($moduleID,$pluginID,'title',
			'Empty the request cache');
			Config::SetModulePluginField($moduleID,$pluginID,0,
			array(	'name' => Config::GetPluginSlug() . 'requestempty_cache',
			'default' => 0,
			'label' => 'Empty the request cache',
			    					'type' => 'radio_bool'));
			
/* -------------------------------------------------------------*/
/*          MODULE STATUS CODES                                 */
/* -------------------------------------------------------------*/
			$moduleID = 'statuscodes';
/* main module settings */
						Config::AddModule(array(
			'page_title' => 'HTTP Statuscodes',
			'menu_title' => 'HTTP Statuscodes',
							'menu_slug' => $moduleID,
			'section_header_title' => '<h2>HTTP Status Codes</h2>',
			'section_header_text' =>
			"HTTP Status Codes",
			'help_text' =>
			"HTTP Status Codes"
			));
/* plugin request codes */
			$pluginID = 'request_codes';
			Config::RegisterModulePlugin($moduleID,$pluginID);
						Config::SetModulePlugin($moduleID,$pluginID,'title',
			'Check Outgoing Links');
			//Config::SetModulePlugin($moduleID,$pluginID,'header',
						//	'Check your outgoing links');
			
			
/* plugin: 200 */
			/*
						$pluginID = '200';
						Config::RegisterModulePlugin($moduleID,$pluginID);
						Config::SetModulePlugin($moduleID,$pluginID,'header',
			'Shows with each link 200 - OK for links that are OK'
						);
			Config::SetModulePlugin($moduleID,$pluginID,'title',
			'HTTP 200');
			Config::SetModulePluginField($moduleID,$pluginID,0,
							array(	'name' => Config::GetPluginSlug() . $pluginID,
			'default' => 0,
			'label' => 'Activate HTTP 200 plugin',
			'type' => 'radio_bool'));
			 $n=1;
			foreach (Config::GetRegisteredPlugins('context') as $contextpluginkey => $contextpluginvalue )
			{
			if ($contextpluginvalue==1)
			{
			$slug=Config::GetModulePluginTitle('context',$contextpluginkey);
			Config::SetModulePluginField($moduleID, $pluginID, $n++,
			array( 'name' => Config::GetPluginSlug() . $pluginID . $contextpluginkey,
			'default' => 0,
			'label' => $slug,
			'type' => 'radio_bool'));
			}
			}
			*/
			
				
			
			
		}
	}
}

