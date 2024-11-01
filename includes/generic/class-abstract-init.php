<?php
/**
 * GENERIC Init
 * Contains generic Init class - with some specific loaders
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://edward.de.leau.net}
 * @copyright GPL 2
 */
namespace leau\co\wp_generic;

if (!class_exists("\\leau\\co\\wp_generic\\AbstractInit"))
{
	/**
	 * Init
	 * Generic Init class that is reusable over multiple plugins contains NO specific
	 * entries for this plugin
	 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
	 * @since 0.4.0
	 */
	abstract class AbstractInit
	{
		public $pluginObjects = array();

		/**
		 * To make sure we use the child namespace. following 2 methods
		 */
		function getNameSpace()
		{
			$currentClass = get_class($this);
			$refl = new \ReflectionClass($currentClass);
			$namespace = $refl->getNamespaceName();
			return '\\' .$namespace . '\\';
		}		
		
		function callConfig($method,$par='')
		{
			if ('' == $par)
			{
				return call_user_func(array(self::getNameSpace() . 'Config',
					$method));
			}
			else
			{
				return call_user_func_array(array(self::getNameSpace() . 'Config',
					$method),$par);
			}
		}
		
		/**
		 * constructor
		 * Initializes the plugin by registering the settings (1 array),
		 * loads the administration modules (which load the plugins) and
		 * adds the admin pages, only called on admin area
		 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
	 	 * @since 0.4.0
		 * @uses add_action {@link http://codex.wordpress.org/Function_Reference/add_action}
		 * @uses admin_init {@link http://codex.wordpress.org/Plugin_API/Action_Reference/admin_init}
		 * @uses admin_menu {@link http://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu}
		 */
		public function __construct()
		{
			/*
			 * instantiate all registered plugins dynamically
			 * and add the admin panels
			 */
			
			// get data from static Config object
			$config_all_plugins = self::callConfig('GetRegisteredPlugins');
			$config_plugin_slug = self::callConfig('GetPluginSlug');
			
			$n=0;
			foreach ($config_all_plugins as $module_key => $module_value)
			{
				foreach ($module_value as $plugin_key => $plugin_value)
				{
					$class_name = self::getNameSpace() .
						$module_key . "_" . $plugin_key;					
					
					$this->pluginObjects[$n] = new $class_name;
					$this->pluginObjects[$n]->Activate($module_key,$plugin_key);
					
					// @see http://codex.wordpress.org/Function_Reference/add_action
					// hooks into the do action defined in class-module method AddSubMenu()
					// in other words: the plugin adds here its admin menu to the module
					// page in the admin area
					add_action(self::callConfig('GetPluginSlug') . $module_key .
						'_plugins', array($this->pluginObjects[$n],
						'AddAdminOption'), 1, 10);
					$n++;
										
				}
			}

			/*
			 * after first time activation write a finalizer
			 * to prevent multiple lookups for the existence of option keys
			 * thereafter, when a new plugin gets added this boolean should
			 * be cleared to run it again.
			 */
			if (false == self::callConfig('GetOptionsArrayValue',array('FINAL')))
			{
				// @todo a bit dirty
				if (strpos(self::getNameSpace(),'wp_favicons_client') === false)
				{				
					call_user_func(array(self::getNameSpace() . 'Database',
							'Install'));
				}		
				
				// get data from static Config object
				self::callConfig('UpdateOptions',array(array('FINAL' => true)));		
			}

			/*
			 * for admin panel:
			 * - register the settings
			 * - load all modules (subpages)
			 * - and then let plugins fill the module's content
			 */

			if (is_admin())
			{
				add_action('admin_init', array($this,'RegisterSettings'), 1, 1 );
				$modules = self::getNameSpace() . 'Modules';
				new $modules;
				add_action('admin_menu', array( $this, 'AddAdminPages' ), 1, 1 );
			}
		}

		/**
		 * Registers the settings
		 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
	 	 * @since 0.4.0
		 * @uses register_settings {@link http://codex.wordpress.org/Function_Reference/register_setting}
		 */
		function RegisterSettings()
		{
			// get data from static Config object
			$config_settinsgroupname = self::callConfig('GetSettingsGroupName');
			$config_optionsname  = self::callConfig('GetOptionsName');
											
			register_setting($config_settinsgroupname,$config_optionsname,
				array( &$this, 'MergeOptions' ));
		}

		/**
		 * Merges the option arrays (Validation function)
		 * We want 1 options array managed on multiple pages so we need to merge it after save
		 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
	 	 * @since 0.4.0
	 	 * @uses add_settings_error {@link http://codex.wordpress.org/Function_Reference/add_settings_error}
		 * @param array $page_options the array filled within the specific admin form
		 * @return array $validated_options the merged array containing all the plugins options
		 */
		function MergeOptions( $page_options ) {
					
			/* start with an empty array to prevent evil arrays */
			$validated_options = array();

			/*
			 * plugins will validate their fields via the validatefields filter:
			 * - they check wether the page_options are valid for them
			 * - and if so add the validated fields to the validated options
			 * - if an error is encountered they will set a settings error
			 * So: without validation NO values of the plugin will be saved!
			 */
			// get data from static Config object
			$config_pluginslug = self::callConfig('GetPluginSlug');
			$plugin_validated_options =
				apply_filters( $config_pluginslug . 'validatefields',
					$validated_options, $page_options );

			if (is_array($plugin_validated_options))
			{
				$validated_options = $plugin_validated_options;
			}

			/*
			 * While the plugins now have validated their fields and
			 * added it to the validated array, it could well so that we
			 * have additional options that are stored outide of plugins
			 * so we need to merge these into the final array
			 */
			$merged_options = array();
			// get data from static Config object
			$config_optionsname = self::callConfig('GetOptionsName');
			$options = get_option($config_optionsname);
			self::callConfig('SetOptionsFromArray',array($options));
        	
			$options = self::callConfig('GetOptionsAsArray');
			
        	if ( !is_array($options) ||
        		 !is_array( $validated_options ))
        	{
        		$pagetitle = self::callConfig('GetPageTitle');
        		
        		add_settings_error(
					$pagetitle,
					'settings_not_merged',
					__('Could not merge existing options with page options'),
					'error'
				);
        	}
        	else
        	{
        		$options = self::callConfig('GetOptionsAsArray');
        		
        		$merged_options = array_merge( $options,
        			$validated_options);
        	}

        	/* return the merged validated array */
        	return $merged_options;
    	}

		/**
		 * Adds the main entry admin root and via action the admin pages added by plugins
		 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
	 	 * @since 0.4.0
		 * @uses add_menu_page {@link http://codex.wordpress.org/Function_Reference/add_menu_page}
		 * @uses do_action {@link http://codex.wordpress.org/Function_Reference/do_action}
		 */
		function AddAdminPages()
		{
			if ( function_exists( 'add_menu_page' ) )
				
				$config_pagetitle = self::callConfig('GetPageTitle');
				$config_menutitle = self::callConfig('GetMenuTitle');
				$config_capability = self::callConfig('GetCapability');
				$config_menuslug = self::callConfig('GetMenuSlug');
				$config_menuicon = self::callConfig('GetMenuIcon');
				$config_pluginslug = self::callConfig('GetPluginSlug');
								
				add_menu_page( $config_pagetitle,
					$config_menutitle,
					$config_capability,
					$config_menuslug,
					array( $this, 'MainPage' ),
					$config_menuicon
				);
			/*
			 * modules === subpages can be added via plugins via the following action
			 * meaning: if you want to add a new module just add an action to this
			 * to create it (and make sure you add the new module to the config class)
			 */
			do_action($config_pluginslug . 'modules');
		}

		/**
		 * Splash page Info
		 */
		function MainPage() {
			$config_mainpage = self::callConfig('GetMainPage');
			echo $config_mainpage;
		}

	}
}
