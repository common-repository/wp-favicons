<?php
/**
 * Filter to get a favicon from Google's Favicon provider
 *
 * This file contains the wp_favicons_search filter implementation
 * to get a favicon.ico from Google S2 favicon service
 * {@link http://stackoverflow.com/questions/4584895/favicon-to-png-in-php/4718629#4718629}
 *
 * Note: feel free to add your own filters!
 *
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
 * @copyright GPL 2
 */
namespace leau\co\wp_favicons_server;
if (!class_exists("\\leau\\co\\wp_favicons_server\\sources_google_s2"))
{
	/**
 	 * Filter to get a favicon from Google's Favicon provider
 	 * @package WP-Favicons
     * @since 0.4.0
     * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
     * @copyright GPL 2
 	 */
	class sources_google_s2 extends sources_generic_plugin
	{
		/**
		 * @var string $_default holds an MD5 string contain the default returned image
		 * note that this can change over time @todo make this a user setting
		 */
		private $_default = 'b8a0bf372c762e966cc99ede8682bc71';


	    /**
    	 * Sets the url where to grab the icon from
         * @since 0.4.0
         * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 * @param string $url the url to grab the data from
		 * @returns string $url the url to grab the data from
    	 */
    	function Init($favicon)
    	{
    		Log::M('[Check GOOGLE]-----------------');
    		$favicon->SetFaviconUri('http://www.google.com/s2/favicons?domain=' .
				parse_url($favicon->GetUri(), PHP_URL_HOST));
			return;
    	}

    	/**
    	 * Executes the functionality of the filter
    	 * @since 0.4.0
         * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
    	 * @param array $favicon_array main favicon array passed all through the chain
    	 * @param string $url URI of website to get icon for
    	 * @param string @data the data of the image
    	 * @return array $favicon_array main favicon array passed all through the chain
    	 */
    	function ExecuteFilter($favicon)
		{
			/*
			 * if it is the default icon skip it maybe other providers can
			 * deliver a better image, note: this relies on the google default
			 * icon staying the same... or else fail miserable (!)
			 */
			if (md5($favicon->GetData()) == $this->_default)
			{
				Log::F($favicon,'Was Google Default Icon');
				$favicon->BlockFoundIcon();
			}
		}
	}
}