<?php
/**
 * GENERIC abstract function for plugin in the module context
 *
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
 * @copyright GPL 2
 * @see {@link http://stackoverflow.com/questions/4584895/favicon-to-png-in-php/4718629#4718629}
 */

namespace leau\co\wp_favicons_client;

if (!class_exists("\\leau\\co\\wp_favicons_client\\context_generic_plugin"))
{
    /**
     * Adds the filter to add favicons in front of urls for posts and pages
     *
     * @author Edward de Leau
     */
    abstract class context_generic_plugin extends Plugin
    {
    	/**
    	 * (non-PHPdoc)
    	 * @see plugins/EdlLinkFaviconPlugin::AddFilter()
    	 */
     	function AddFilter()
        {
        	// @todo : the following should schedule a call for a replacement
        	// the old metadata component could be helpful for determining the hooks
            $wp_favicon_the_content = new XMLRPCCall(
            	$this->_module, $this->_plugin);
            
            
            add_action('wp_head', array($this,'AddLineToStyleSheet'));
        }

        /**
         * @todo queue the stylesheet written to disk: need to collect them from all plugins
         * combine and them write them :: probably make 1 tag styling and combine all of
         * them in one styling form.
         */
        function AddLineToStyleSheet()
        {
        	echo "\n<style type=\"text/css\">";
			echo '.'. 'wp_favicons_' . Config::GetModulePluginVar($this->_module,
				$this->_plugin, 'filter_context_name') . '{';
			echo Config::GetFieldValue($this->_module, $this->_plugin, 1, 'name');

			echo '}';
			echo "</style>\n";
        }
    }
}