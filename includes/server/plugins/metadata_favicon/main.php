<?php

namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\metadata_favicon_main"))
{
	/**
	 *
	 * Enter description here ...
	 * @author cogmios
	 *
	 */
	class metadata_favicon_main extends Plugin
	{
		private $_html_string = '';

		/**
    	 * This Method is called by default by the master Plugin object
    	 * @see plugins/EdlLinkFaviconPlugin::AddFilter()
	     * @since 0.4.0
     	 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
    	 */
		function AddFilter()
    	{
    		/* parent class arranges that this is not called when the bool is off */

    		if (1 == Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'favicon_spot1'))
			{
				if ($this->_html_string == '')
				{
    				add_filter(Config::GetPluginSlug() . 'metadata_before_href'
    					, array($this,'DoFilter'), 6, 3);
				}
				else
				{
					add_filter(Config::GetPluginSlug() . 'metadata_before_href'
    					, array($this,'DoEasyFilter'), 16, 3);
				}
			}
			if (1 == Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'favicon_spot2'))
			{
				if ($this->_html_string == '')
				{
					add_filter(Config::GetPluginSlug() . 'metadata_after_href'
    					, array($this,'DoFilter'), 6, 3);
				}
				else
				{
					add_filter(Config::GetPluginSlug() . 'metadata_after_href'
    					, array($this,'DoEasyFilter'), 16, 3);
				}
			}
    		if (1 == Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'favicon_spot3'))
			{
				if ($this->_html_string == '')
				{
					add_filter(Config::GetPluginSlug() . 'metadata_inside_before_href'
    					, array($this,'DoFilter'), 6, 3);
				}
				else
				{
					add_filter(Config::GetPluginSlug() . 'metadata_inside_before_href'
    					, array($this,'DoEasyFilter'), 16, 3);
				}
			}
    		if (1 == Config::GetOptionsArrayValue(Config::GetPluginSlug() . 'favicon_spot4'))
			{
				if ($this->_html_string == '')
				{
					add_filter(Config::GetPluginSlug() . 'metadata_inside_after_href'
    					, array($this,'DoFilter'), 6, 3);
				}
				else
				{
					add_filter(Config::GetPluginSlug() . 'metadata_inside_after_href'
    					, array($this,'DoEasyFilter'), 16, 3);
				}
			}
    	}

    	function DoFilter($html_string, $uri, $filter)
    	{
    		$provider = new FaviconFactory(new Favicon($uri), $filter);
			$this->_html_string = $provider->GetAsHtml();
			return $html_string . $this->_html_string;
    	}

    	function DoEasyFilter($html_string, $uri, $filter)
    	{
    		return $html_string . $this->_html_string;
    	}

	}
}