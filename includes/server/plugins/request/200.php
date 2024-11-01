<?php
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\request_200"))
{
	class request_200 extends Plugin
	{
		function AddFilter()
        {
        	$wp_favicon_the_content = new MetaData(
            	$this->_module, $this->_plugin);
            add_action('wp_head', array($this,'AddLineToStyleSheet'));
        }

		 /**
         * @todo queue the stylesheet written to disk: need to collect them from all plugins
         * combine and them write them
         */
        function AddLineToStyleSheet()
        {
        	echo "\n<style type=\"text/css\">";
			echo '.'. Config::GetPluginSlug() . Config::GetModulePluginVar($this->_module,
				$this->_plugin, '_style') . '{';
			echo Config::GetFieldValue($this->_module, $this->_plugin, 1, 'name');

			echo '}';
			echo "</style>\n";
        }

	}
}