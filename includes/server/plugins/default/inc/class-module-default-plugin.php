<?php
/**
 * GENERIC abstract functionality for plugin in the module default
 *
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
 * @copyright GPL 2
 */
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\default_generic_plugin"))
{
    /**
     * GENERIC abstract class for plugin in the module sources
     *
     * @author Edward de Leau
     */
    abstract class default_generic_plugin extends Plugin
    {
	 	/**
		 * @var string $_data_url holds the url of the image
		 */
		public $_data_url = '';

   		/**
		 * Should we cache it?
		 * @since 0.5.0
         * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 */
    	function Cached()
    	{
    		return Config::GetOptionsArrayValue(Config::GetPluginSlug() .'cache_default_'
    			.trim($this->_plugin));
    	}

    	/**
    	 * This Method is called by default by the master Plugin object
    	 *
    	 * Since we now support both cached and uncached defaults and still want
    	 * to use the same object the check takes place here
    	 *
    	 * @see plugins/EdlLinkFaviconPlugin::AddFilter()
	     * @since 0.4.0
     	 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
    	 */
		function AddFilter()
    	{
    		if ($this->Cached()) {
    			add_filter(Config::GetPluginSlug() . 'default', array($this,'DoFilter'), 6, 1);
    			Log::M('Cached Filter Added');
    		}
    		else
    		{
    			add_filter(Config::GetPluginSlug() . 'default_non_cached',
    				array($this,'DoFilterNonCached'), 6, 1);
    			Log::M('Non Cached Filter Added');
    		}
    	}

		/**
		 *
		 * Enter description here ...
		 * @param $favicon_array
		 * @param $url
		 */
		function DoFilterNonCached($favicon)
		{
			if (!$favicon->FoundStatus())
			{
				Log::F($favicon,'!');
				$favicon->SetType		('png');
				$favicon->SetFilters	('NOCACHE');
				$favicon->SetSource		($this->_module. '_' . $this->_plugin);
				$favicon->SetDefault	(true);
				$favicon->SetData		('NOCACHE');
				$favicon->SetFaviconUri ($this->DataUrl($favicon->GetUri()));
				$favicon->Found();
			}
			return $favicon;
		}

    	 /**
    	 * Sets the url where to grab the icon from
         * @since 0.4.0
         * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 * @param string $url the url to grab the data from
		 * @returns string $url the url to grab the data from
    	 */
    	function DataUrl($favicon)
    	{
    		if (!is_object($favicon))
    		{
    			return $favicon;
    		}
    		Log::F($favicon,'[Check '.trim($this->_plugin).']-----------------');
    		$url_host = parse_url($favicon->GetUri(), PHP_URL_HOST);
    		$favicon->SetFaviconUri('http://0.gravatar.com/avatar/' . md5($url_host) .
    			'?default='.strtolower($this->_plugin).'&s=32');
			return $favicon;
    	}
		

        /**
		 * Grabs the data (icon)
         * @since 0.4.0
         * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 * @param array $favicon_array
		 * @param string $url
		 * @return array $favicon_array
		 * @uses Http::faviconFilterInit
		 */
		function DoFilter($favicon)
		{
			if (!$favicon->FoundStatus())
			{
				$this->DataUrl($favicon);
				Http::GetFavicon($favicon);
				if ($favicon->FoundStatus())
				{
					$favicon->SetDefault(true);
					$favicon->SetSource($this->_plugin);
					$favicon->SetFaviconUri($favicon->LastUriAddedToUriArr());
				}
			}
			return $favicon;
		}

    }
}
