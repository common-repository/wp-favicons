<?php
/**
 * GENERIC Module Configurator
 * Defines the Module / Subpage settings GENERIC - - so probably no need to look in here
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
 * @copyright GPL 2
 */
namespace leau\co\wp_generic;

if (!class_exists("\\leau\\co\\wp_generic\\AbstractModule"))
{
	/**
	 * Holds and defines 1 specific module
	 * One module corresponds to a submenu section and contains multiple plugins
	 * @author Edward de Leau <e@leau.net>, http://edward.de.leau.net
	 * @since 0.4.0
	 */
	abstract class AbstractModule
	{
		private $_module_id;

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
		 * Constructor
		 * Configures a new module / subpage
		 * @param int $moduleId the module id
		 */
		function __construct($moduleId)
		{
			$this->_module_id = $moduleId;
		}

		/**
		 * Adds a submenu / module / page / screen
		 * This is called by the modules class after instantiation of each module
		 * @uses add_submenu_page {@link http://codex.wordpress.org/Function_Reference/add_submenu_page}
		 * @uses add_settings_section {@link http://codex.wordpress.org/Function_Reference/add_settings_section}
		 */
		function AddSubMenu()
		{

			if (function_exists('add_submenu_page'))
			{
			
				
				/* Add a submenu page representing the module */
				add_submenu_page(
					self::callConfig('GetMenuSlug'),
					self::callConfig('GetModulePageTitle',array($this->_module_id)),
					self::callConfig('GetModuleMenuTitle',array($this->_module_id)),
					self::callConfig('GetCapability'),
					self::callConfig('GetPluginSlug').
					self::callConfig('GetModuleMenuSlug',array($this->_module_id))  . '_page',
				array( $this, 'AddForm' )
				);
			}

			if (function_exists('add_settings_section'))
			{
				/* Add the main settings section to the submenu page / module */
				/* $id, $title, $callback, $page */
				add_settings_section(
				self::callConfig('GetPluginSlug'). self::callConfig('GetModuleMenuSlug',array($this->_module_id)) . '_menu_section_main'						
				, self::callConfig('GetModuleSectionHeaderTitle', array($this->_module_id))						
				, array($this,'SectionHeader')
				, self::callConfig('GetPluginSlug') . self::callConfig('GetModuleMenuSlug',array($this->_module_id)) . '_page'
				);
			}

			/* Allow plugins to add their sections 
			 * This is e.g. called from the init class to add the AddAdminOption method of the 
			 * abstract plugin to the module
			 * @uses do_action {@link http://codex.wordpress.org/Function_Reference/do_action}
			 */
			do_action(self::callConfig('GetPluginSlug') .
				self::callConfig('GetModuleMenuSlug',array($this->_module_id)) . '_plugins');			
			
			/* Add Help and allow plugins to add their own help screens */
			add_action('contextual_help', array($this,'contextual_help'),1,1);
			
		}

		/**
		 * Add contextual help for this screen / subpage / module
		 * @todo screen api changed see http://wpdevel.wordpress.com/2011/12/06/help-and-screen-api-changes-in-3-3/
		 * i now need to add all help texts at once
		 * @param $text
		 */
		function contextual_help($text)
		{
			$current_screen = get_current_screen();
			/* yes this is a weird id */
			$screen_id 	= strtolower(self::callConfig('GetMenuTitle')) . "_page_" . 
			self::callConfig('GetPluginSlug') .
			self::callConfig('GetModuleMenuSlug',array($this->_module_id)) . "_page";
			if ($current_screen->id == $screen_id)
			{
				$text = self::callConfig('GetModuleHelpText',array($this->_module_id));
			}
			
			$current_screen->add_help_tab( array (
				'id'		=> $screen_id,
				'title'		=>	self::callConfig('GetModuleMenuSlug',array($this->_module_id)),
				'content' 	=> self::callConfig('GetModuleHelpText',array($this->_module_id))
			));
			
			return $text;
		}

		/**
		 * Creates a section header for the module / subpage
		 */
		function SectionHeader()
		{
			//$current_screen = get_current_screen();
			//echo $current_screen ;
			//screen_icon(screen_icon($screen = ''));
			echo self::callConfig('GetModuleSectionHeaderText',array($this->_module_id));
		}

		/**
		 * Generic WordPress Form which uses the settings API
		 * @uses settings_fields
		 * @uses do_settings_sections {@link http://codex.wordpress.org/Function_Reference/do_settings_sections}
		 *  	 (defined in \wp-admin\includes\template.php)
		 * @uses get_current_screen
		 * @uses submit_button
		 *       (defined in \wp-admin\includes\template.php)
		 * @uses settings_errors
		 */
		function AddForm()
		{
			echo "<div class=\"adminform\">";
			settings_errors();
			echo "<form method=\"post\" action=\"options.php\" enctype=\"multipart/form-data\" id=\"plugin_options\">";
			settings_fields(self::callConfig('GetSettingsGroupName'));
			do_settings_sections(self::callConfig('GetPluginSlug') .
			self::callConfig('GetModuleMenuSlug',array($this->_module_id)) . '_page');
			echo submit_button( 'Save Changes', 'primary', 'submit', true, NULL );
			echo "<hr />";
			echo "<i>Developers can add more plugins to this page | ";
			echo "Click Help for more information |";
			echo " <a href=\"http://wordpress.org/extend/plugins/wp-favicons/\">homepage</a> | ";
			echo " <a href=\"http://wordpress.org/tags/wp-favicons?forum_id=10\">forum</a> ";
			echo "</i>";
			echo "</form>";
			echo "</div>";
		}

	}
}
