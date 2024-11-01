<?php
/**
 * GENERIC
 * Modules are the main blocks, each module contains of multiple plugins
 * This is a generic class -- so no need to be in here
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
 * @copyright GPL 2
 */

namespace leau\co\wp_generic;

if (!class_exists("\\leau\\co\\wp_generic\\AbstractModules"))
{
	/**
	 * Holds all instantiated module objects
	 * Modules are the main blocks, each module contains of multiple plugins
	 * @since 0.4.0
 	 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
	 */
	abstract class AbstractModules
	{
		private $_modules = array();
		private $_module_counter = 0;
		
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

		function __construct()
		{
			if (is_admin())
			{
				foreach(self::callConfig('GetAllModules') as $moduleSettings)
				{
					/* load the module */
					$module = self::getNameSpace() . 'Module';
					$this->_modules[$this->_module_counter] 
						= new $module($this->_module_counter);
					/* adds action to the init class to add the module during init */
					
					add_action( self::callConfig('GetPluginSlug') .
						'modules',
						array($this->_modules[$this->_module_counter],'AddSubMenu'), 5, 0 );
					/* increase the counter */
					$this->_module_counter = $this->_module_counter + 1;
				}
			}
		}
	}
}