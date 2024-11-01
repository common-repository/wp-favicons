<?php
/**
 * GENERIC
 * Enter description here ...
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
 * @copyright GPL 2
 *
 */

namespace leau\co\wp_generic;

if (!class_exists("\\leau\\co\\wp_generic\\AbstractPlugin"))
{
	/**
	 * Abstract generic plugin framework
	 * All plugins are based on this abstract class, makes it easier to create them
	 * @author cogmios
	 *
	 */
	abstract class AbstractPlugin
	{
  		/* specific admin screen page and section and fields */
    	public $_module;
		public $_plugin;

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
		function callUtils($method,$par='')
		{
			if ('' == $par)
			{
				return call_user_func(array(self::getNameSpace() . 'EdlUtils',
						$method));
			}
			else
			{
				return call_user_func_array(array(self::getNameSpace() . 'EdlUtils',
						$method),$par);
			}
		}
		
		
		function __construct(){}

		function Activate($module,$plugin)
		{
			$this->_module=$module;
			$this->_plugin=$plugin;
			
			
			/* if needed: set values specific for the plugin
			 * however, preferably do this in the config object not in the plugin itself
			 * */
			if (method_exists($this,'SetFields') && is_admin())
			{
				$this->SetFields();
			}

			/* add option values specific for the plugin
			 * please note: when you add a new plugin then set final to False
			 * it will rerun and read your fields whereafter it will be set
			 * to final again.
			 */
			
			$config_optionsarrayvalue = 
				self::callConfig('GetOptionsArrayValue',array('FINAL'));
			
			if (!$config_optionsarrayvalue) {				
				self::callConfig('CheckExistenceOfFields',array($this->_module, $this->_plugin));
			}
			
			/* execute non admin functionality
			 * note that the first field should be a boolean to determine
			 * if the functionality should be executed (as set in the user admin
			 * pages)
			 * 
			 * @ todo : could be created somewhat smarter than looking at the first boolean
			 * 
			 */
			if (!is_admin())
			{
				$config_isactive = 
					self::callConfig('IsPluginActive',array($this->_module, $this->_plugin));
				
				if ($config_isactive)
				{
					if (method_exists($this,'AddFilter'))
					{
						$this->AddFilter();
            		}
				}
			}

			/*
			 * execute specific admin functionality
			 */
			if (is_admin())
			{
				$config_pluginslug = self::callConfig('GetPluginSlug');
				
				// validation of field data entered in admin pages
				add_filter($config_pluginslug . 'validatefields',
					array($this,'ValidateFields'), 6, 2);

				if (method_exists($this,'ExecuteAdminAction'))
				{
					$this->ExecuteAdminAction();
				}

			}
		}

		/**
		 * Validates fields after saving in admin screens
		 * Master method to validate contents of subpages, plugins can add their
		 * own validations and hook on their own to the validation
		 * @param $validated_options
		 * @param $page_options
		 */
	    function ValidateFields($validated_options, $page_options)
    	{
    		
    		$fields = self::callConfig('GetModulePluginFields', array($this->_module, $this->_plugin));    		
    		if ($page_options && $fields) {
    		foreach($fields as $field)
    		{
    			if (array_key_exists($field['name'], $page_options))
    			{
    				/* simple booleans */
    				if ($field['type'] == 'radio_bool')
    				{
    					if ($page_options[$field['name']] == '0' ||
    						$page_options[$field['name']] == '1')
    					{
    						$validated_options[$field['name']]
    							= $page_options[$field['name']];
    					}
    					else
    					{
    						add_settings_error(
								$page_options[$field['name']],
								'bool_error_hack',
								__('Should be 1 or 0: form hacking not allowed'),
								'error'
								);
    					}
    				}
    				if ($field['type'] == 'textarea' )
    				{
    					$validated_options[$field['name']]
    							= wp_kses_data($page_options[$field['name']]);
    				}
    				if ($field['type'] == 'text' )
    				{
    					$validated_options[$field['name']]
    							= wp_kses_data($page_options[$field['name']]);
    				}
    				/* add other types */
    			}
    			else
    			{
    				// it is not part of the current page so do nothing
    				// merging takes place in the plugin class
    			}
    		}
    		}
    		return $validated_options;
    	}


	   /**
         * Adds an admin section and section for fields
         * Generic
         * @uses add_settings_section {@link http://codex.wordpress.org/Function_Reference/add_settings_section}
         * @uses add_settings_field {@link http://codex.wordpress.org/Function_Reference/add_settings_field}
         */
        function AddAdminOption()
        {
        	
        	        	
        	if (is_admin())
        	{
        		
        		$config_pluginsectioname	=
        			self::callConfig('GetModulePluginSectionName', array($this->_module, $this->_plugin));
        		$config_plugintitle			=
        			self::callConfig('GetModulePluginTitle', array($this->_module, $this->_plugin));
        		$config_pluginpagename		=
        			self::callConfig('GetModulePluginPageName', array($this->_module, $this->_plugin));         	
        		
				add_settings_section($config_pluginsectioname
					, $config_plugintitle 
					, array($this,'SectionHeader')
					, $config_pluginpagename);

				/* Version 0.5.6: some plugins do not have fields but add their own
				 * functionality without fields
				 */	
				$fields = self::callConfig('GetModulePluginFields', array($this->_module, $this->_plugin));
				if ($fields)
				{
					foreach($fields as $field)
					{
						add_settings_field($field['name']
							,$field['label']
							,array($this,'AddSettingsField')
							,$config_pluginpagename
							,$config_pluginsectioname
							,$field);
					}
				}
        	}
        }

	   /**
         * adds a section header
         * Generic
         */
        function SectionHeader()
        {
        	echo self::callConfig('GetModulePluginHeader', array($this->_module, $this->_plugin));;
        }

        /**
         * Adds a setting field
         * @param unknown_type $field
         */
        function AddSettingsField( /*array*/ $field)
        {
        	/* get the latest field value */
			$options_array = self::callConfig('GetOptionsAsArray');					
			$fieldname = self::callConfig('GetOptionsName') . '['. $field['name'] . ']';
			if (array_key_exists($field['name'],$options_array))
			{
        		$fieldvalue = $options_array[$field['name']];
        		
        		self::callUtils('AdminForm',array($field,$fieldname,$fieldvalue));
			}
			/* if a plugin added new fields without calling init */
			else
			{
				echo "WARNING: new functions were installed but not visible yet. Please deactivate and reactive this plugin";
			}

        }
	}
}