<?php
/**
 * GENERIC abstract functionality for plugin in the module sources
 *
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
 * @copyright GPL 2
 * @see {@link http://stackoverflow.com/questions/4584895/favicon-to-png-in-php/4718629#4718629}
 */
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\sources_generic_plugin"))
{
    /**
     * GENERIC abstract class for plugin in the module sources
     *
     * @author Edward de Leau
     */
    abstract class sources_generic_plugin extends Plugin
    {
    	/**
    	 * This Method is called by default by the master Plugin object
    	 * @see plugins/EdlLinkFaviconPlugin::AddFilter()
	     * @since 0.4.0
     	 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
    	 */
		function AddFilter()
    	{
    		/* parent class arranges that this is not called when the bool is off */
    		add_filter(Config::GetPluginSlug() . 'search', array($this,'DoFilter'), 6, 1);
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
			if (!$favicon->FoundStatus()) {
				$this->Init($favicon);
				Http::GetFavicon($favicon);
				if ($favicon->FoundStatus())
				{
					$favicon->SetDefault(false);
					if ($favicon->getSource() == '') {
						$favicon->SetSource($this->_plugin);
					}
					$favicon->SetFaviconUri($favicon->LastUriAddedToUriArr());
					$this->ExecuteFilter($favicon);
				}
			}
			return $favicon;
		}

    }
}