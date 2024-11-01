<?php
/**
 * GENERIC Config
 * Contains the generic configuration class filled by load-configuration
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://edward.de.leau.net}
 * @copyright GPL 2
 *
 */
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\Config"))
{
	/**
	 * Holds the configuration of the plugin
	 * Contains only simple vars and helpers no stuff that influences the action
 	 * @package WP-Favicons
 	 * @since 0.4.0
 	 * @author Edward de Leau <e@leau.net>, {@link http://edward.de.leau.net}
  	 * @copyright GPL 2
	 */
	class Config
	{
		private $_options_name;
		private $_options_array = array();
		private $_settings_group;
		private $_page_title;
		private $_menu_title;
		private $_capability;
		private $_menu_slug;
		private $_plugin_slug;
		private $_modules = array();
		private $_mainpage;
		private $_module_plugin = array();
		private $_module_plugin_register = array();
		private $_menu_icon;
		private $_plugin_version;
		private $_nice_plugin_slug;

		// singleton instance
		private static $instance;

		// private constructor function
		// to prevent external instantiation
		final private function __construct()
		{

		}
		private function __clone()
		{
		}

		// getInstance method
		public static function getInstance()
		{
			if(!self::$instance)
			{
				self::$instance = new self();
			}
			return self::$instance;
		}

		/* ------------ options name ------------------ */

		public static function GetOptionsName()
		{
			$instance = self::getInstance();
			return $instance->_options_name;
		}

		public static function SetOptionsName($optionsName)
		{
			$instance = self::getInstance();
			$instance->_options_name = $optionsName;
		}

		/* ------------ options array ------------------ */

		/**
		 *
		 * Enter description here ...
		 */
		public static function GetOptionsAsArray()
		{
			$instance = self::getInstance();
			return $instance->_options_array;
		}

		public static function GetOptionsArrayValue($key)
		{
			$instance = self::getInstance();
			if (is_array($instance->_options_array))
			{
				if (array_key_exists($key, $instance->_options_array)) 
				{
					return $instance->_options_array[$key];
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}	
		}

		/**
		 * @todo 0.5.0 check this one
		 * Enter description here ...
		 * @param unknown_type $optionsArray
		 */
		public static function SetOptionsFromArray($optionsArray)
		{
			$instance = self::getInstance();
			$instance->_options_array = $optionsArray;
		}

		/* ------------ Settings Group ------------------ */

		/**
		 *
		 * Enter description here ...
		 */
		public static function GetSettingsGroupName()
		{
			$instance = self::getInstance();
			return $instance->_settings_group;
		}

		/**
		 *
		 * Enter description here ...
		 * @param unknown_type $settingsGroup
		 */
		public static function SetSettingsGroupName($settingsGroup)
		{
			$instance = self::getInstance();
			$instance->_settings_group = $settingsGroup;
		}

		/* ------------ Plugin Version ------------------ */

		public static function GetPluginVersion()
		{
			$instance = self::getInstance();
			return $instance->_plugin_version;
		}

		public static function SetPluginVersion($version)
		{
			$instance = self::getInstance();
			$instance->_plugin_version = $version;
		}

		/* ------------ Page Title ------------------ */

		public static function GetPageTitle()
		{
			$instance = self::getInstance();
			return $instance->_page_title;
		}

		public static function SetPageTitle($pageTitle)
		{
			$instance = self::getInstance();
			$instance->_page_title = $pageTitle;
		}

		/* ------------ Menu Title ------------------ */

		public static function GetMenuTitle()
		{
			$instance = self::getInstance();
			return $instance->_menu_title;
		}

		public static function SetMenuTitle($menuTitle)
		{
			$instance = self::getInstance();
			$instance->_menu_title = $menuTitle;
		}

		/* ------------ Capability ------------------ */

		public static function GetCapability()
		{
			$instance = self::getInstance();
			return $instance->_capability;
		}

		public static function SetCapability($capability)
		{
			$instance = self::getInstance();
			$instance->_capability = $capability;
		}

		/* ------------ Menu Slug ------------------ */

		public static function GetMenuSlug()
		{
			$instance = self::getInstance();
			return $instance->_menu_slug;
		}

		public static function SetMenuSlug($menuSlug)
		{
			$instance = self::getInstance();
			$instance->_menu_slug = $menuSlug;
		}

		/* ------------ Plugin Slug ------------------ */

		public static function GetPluginSlug()
		{
			$instance = self::getInstance();
			return $instance->_plugin_slug;
		}

		public static function SetPluginSlug($pluginSlug)
		{
			$instance = self::getInstance();
			$instance->_plugin_slug = $pluginSlug;
		}

		public static function SetNicePluginSlug($nicePluginSlug)
		{
			$instance = self::getInstance();
			$instance->_nice_plugin_slug = $nicePluginSlug;
		}
		
		public static function GetNicePluginSlug()
		{
			$instance = self::getInstance();
			return $instance->_nice_plugin_slug;
		}
		
		
		/* ------------ Modules ------------------ */

		/**
		 * Get one specific module
		 * Get a specific module by number
		 * @param unknown_type $n
		 */
		public static function GetModuleByOrder($n)
		{
			$instance = self::getInstance();
			return $instance->_modules[$n];
		}

		/**
		 * Get a page title of a menu based on the id
		 * @param $n
		 */
		public static function GetModulePageTitle($n)
		{
			$instance = self::getInstance();
			return $instance->_modules[$n]['page_title'];
		}

		/**
		 * Get a menu title of a menu based on the id
		 * @param $n
		 */
		public static function GetModuleMenuTitle($n)
		{
			$instance = self::getInstance();
			return $instance->_modules[$n]['menu_title'];
		}

		/**
		 * Get a menu slug of a menu based on the id
		 * @param $n
		 */
		public static function GetModuleMenuSlug($n)
		{
			$instance = self::getInstance();
			return $instance->_modules[$n]['menu_slug'];
		}

		/**
		 * Get a section header title of a menu based on the id
		 * @param $n
		 */
		public static function GetModuleSectionHeaderTitle($n)
		{
			$instance = self::getInstance();
			return $instance->_modules[$n]['section_header_title'];
		}

		/**
		 * Get a section header text of a menu based on the id
		 * @param $n
		 */
		public static function GetModuleSectionHeaderText($n)
		{
			$instance = self::getInstance();
			return $instance->_modules[$n]['section_header_text'];
		}

		/**
		 * Get section help text of a menu based on the id
		 * @param $n
		 */
		public static function GetModuleHelpText($n)
		{
			$instance = self::getInstance();
			return $instance->_modules[$n]['help_text'];
		}

		/**
		 * Gets all modules in a hierarchical array
		 * Enter description here ...
		 */
		public static function GetAllModules()
		{
			$instance = self::getInstance();
			return $instance->_modules;
		}

		/**
		 * Add a new module
		 * Adds a new module by passing the module configuration data in array
		 * @param array $module
		 */
		public static function AddModule($module)
		{
			$instance = self::getInstance();
			if (is_array($module))
			{
				$instance->_modules[] = $module;
			}
			else
			{
				throw new Exception('Invalid Parameter: should be array');
			}
		}

		public static function AddMenuIcon($icon)
		{
			$instance = self::getInstance();
			$instance->_menu_icon = $icon;
		}

		public static function GetMenuIcon()
		{
			$instance = self::getInstance();
			return $instance->_menu_icon;
		}

		/* ------------ Main page ------------------ */

		/**
		 *
		 * Enter description here ...
		 * @param unknown_type $mainPage
		 */
		public static function SetMainPage($mainPage)
		{
			$instance = self::getInstance();
			$instance->_mainpage = $mainPage;
		}

		/**
		 *
		 * Enter description here ...
		 * @param unknown_type $mainPage
		 */
		public static function GetMainPage()
		{
			$instance = self::getInstance();
			return $instance->_mainpage;
		}

		/* ------------ Module Plugins ------------------ */

		/**
		 *
		 * Enter description here ...
		 * @param unknown_type $moduleID
		 * @param unknown_type $pluginID
		 * @param unknown_type $pluginVar
		 * @param unknown_type $pluginValue
		 */
		public static function SetModulePlugin($moduleID,$pluginID,$pluginVar,$pluginValue)
		{
			$instance = self::getInstance();
			$instance->_module_plugin[$moduleID][$pluginID][$pluginVar] = $pluginValue;
		}

		public static function GetModulePluginPageName($moduleID,$pluginID)
		{
			$instance = self::getInstance();
			return $instance->GetPluginSlug() . $moduleID . '_page';
		}

		public static function GetModulePluginSectionName($moduleID,$pluginID)
		{
			$instance = self::getInstance();
			return $instance->GetPluginSlug() . $moduleID . '_' . $pluginID . '_section';
		}

		public static function GetModulePluginVar($moduleID,$pluginID,$pluginVar)
		{
			$instance = self::getInstance();
			return $instance->_module_plugin[$moduleID][$pluginID][$pluginVar];
		}

		public static function GetModulePluginTitle($moduleID,$pluginID)
		{
			$instance = self::getInstance();
			if (array_key_exists('title', $instance->_module_plugin[$moduleID][$pluginID])) {
				return $instance->_module_plugin[$moduleID][$pluginID]['title'];
			}	
		}

		public static function GetModulePluginHeader($moduleID,$pluginID)
		{
			$instance = self::getInstance();
			if (array_key_exists('header', $instance->_module_plugin[$moduleID][$pluginID])) {
				return $instance->_module_plugin[$moduleID][$pluginID]['header'];
			}	
		}



		/* ------------ Module Plugin Fields ------------------ */

		/**
		 * @param string @moduleID
		 * @param string @pluginID
		 * @param int @pluginField id of pluginField
		 * @param array @pluginValue an array of the values of the field
		 */
		public static function SetModulePluginField($moduleID,$pluginID,$pluginField,$pluginValue)
		{
			$instance = self::getInstance();
			$instance->_module_plugin[$moduleID][$pluginID]['fields'][$pluginField] = $pluginValue;
		}

		/*
		 * 0.5.6: added: some plugins have no fields
		 */
		public static function GetModulePluginFields($moduleID,$pluginID)
		{
			$instance = self::getInstance();
			if (array_key_exists('fields', $instance->_module_plugin[$moduleID][$pluginID])) {
				return $instance->_module_plugin[$moduleID][$pluginID]['fields'];
			}	
		}

		public static function CheckExistenceOfFields($moduleID, $pluginID)
		{
			$instance = self::getInstance();
			$current_fields = $instance->GetModulePluginFields($moduleID,$pluginID);
			$fields_to_add = array();
			if ($current_fields) 
			{
				foreach ($current_fields as $field)
				{
					$fields_to_add[$field['name']] = $field['default'];

				}
			}	
			$instance->AddOptions($fields_to_add);
		}

		public static function HasFields($moduleID, $pluginID)
		{
			$instance = self::getInstance();
			$current_fields = $instance->GetModulePluginFields($moduleID,$pluginID);
			if (is_array($current_fields)) {return true;} else {return false;}
		}

		public static function GetFieldValue($moduleID, $pluginID, $fieldID, $fieldKey)
		{
			$instance = self::getInstance();
			return $instance->GetOptionsArrayValue($instance->_module_plugin[$moduleID][$pluginID]['fields'][$fieldID][$fieldKey]);
		}

		/**
		 * @todo probably this should be done in a better way it means that
		 * the first boolean of a plugin should be true, so that always the
		 * first option MUST be if it should be turned on or off.
		 * Enter description here ...
		 * @param unknown_type $moduleID
		 * @param unknown_type $pluginID
		 */
		public static function IsPluginActive($moduleID, $pluginID)
		{
			$instance = self::getInstance();
			if ($instance->HasFields($moduleID, $pluginID))
			{
				if ($instance->GetFieldValue($moduleID, $pluginID, 0, 'name') == '1')
            	{
            		return true;
            	}
            	else
            	{
            		return false;
            	}
			}
		}

		/* ------------ Module Plugin Register ------------------ */

		public static function RegisterModulePlugin($moduleID,$pluginID)
		{
			$instance = self::getInstance();
			$instance->_module_plugin_register[$moduleID][$pluginID] = true;
		}

		public static function UnRegisterModulePlugin($moduleID,$pluginID)
		{
			$instance = self::getInstance();
			$instance->_module_plugin_register[$moduleID][$pluginID] = false;
		}

		/**
		 * Returns all or one specific module's plugins
		 * @param string $module the module requested or empty
		 * @return array the list of plugins
		 */
		public static function GetRegisteredPlugins($module='')
		{
			$instance = self::getInstance();
			if ($module=='')
			{
				return $instance->_module_plugin_register;
			}
			else
			{
				return $instance->_module_plugin_register[$module];
			}
		}

		/**
		 * Update Options
		 * Helper function to update all the options for the plugin note that
		 * add_option almost always does nothing because we have an array
		 * not a single field
		 * @uses update_option {@link http://codex.wordpress.org/Function_Reference/update_option}
		 */
		public static function AddOptions($new_values)
		{
			$instance = self::getInstance();
			$values_to_add = array();
			if (!empty($new_values))
			{
				foreach ($new_values as $key => $value)
				{
					if (!is_array($instance->_options_array) || !array_key_exists($key,$instance->_options_array))
					{
						$values_to_add[$key] = $value;
					}
				}
			}
			$instance->UpdateOptions($values_to_add);
		}

		/**
		 * Updates the options array
		 * @param $new_values
		 */
		public static function UpdateOptions($new_values)
		{
			$instance = self::getInstance();
			if (!empty($new_values))
			{
				foreach ($new_values as $key => $value)
				{
					$instance->_options_array[$key] = $value;
				}
				update_option($instance->_options_name, $instance->_options_array);
			}
		}


	}
}




