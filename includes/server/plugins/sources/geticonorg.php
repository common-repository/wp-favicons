<?php
/**
 * Filter get a favicon from geticon.org
 *
 * This file contains the wp_favicons_search filter implementation
 * to get a favicon.ico from geticon.org
 *
 * Note: feel free to add your own filters!
 *
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
 * @copyright GPL 2
 * @see {@link http://stackoverflow.com/questions/4584895/favicon-to-png-in-php/4718629#4718629}
 */

namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\sources_geticonorg"))
{
	/**
 	 * Filter get a favicon from geticon.org
     * @since 0.4.0
     * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
     * @param $favicon_array
     * @param $url
    */
	class sources_geticonorg extends sources_generic_plugin
	{
		/**
		 * @var string $_default1 holds an MD5 string contain the default returned image
		 * note that this can change over time @todo make this a user setting
		 */
		private $_default1 = '06d630cf0af0548c6abbe750141acbbe';

		/**
		 * @var string $_default2 holds an MD5 string contain the default returned image
		 * note that this can change over time @todo make this a user setting
		 */
		private $_default2 = 'b8a0bf372c762e966cc99ede8682bc71';

	    /**
    	 * Sets the url where to grab the icon from
         * @since 0.4.0
         * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
		 * @param string $url the url to grab the data from
		 * @returns string $url the url to grab the data from
		 * @todo make the google base string a user setting in case it changes
    	 */
    	function Init($favicon)
    	{
    		Log::F($favicon,'[Check Geticon.org]-----------------');
    		$favicon->SetFaviconUri('http://geticon.org/of/'
    		. parse_url($favicon->GetUri(), PHP_URL_HOST));
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
			/* it returns a text string with the site name if nothing found */
			$pos = strpos($favicon->GetData(), parse_url($favicon->GetUri(), PHP_URL_HOST));
			if ($pos === false)
			{
				/* or returns the following default icon in MD5 */
				if (md5($favicon->GetData()) == $this->_default1 ||
				 	md5($favicon->GetData()) == $this->_default2)
				{
					Log::F($favicon,'Was Geticon Org Default Icon');
					$favicon->BlockFoundIcon();
				}
			}
			else
			{
				Log::F($favicon,'Was Geticon Org Default Icon');
				$favicon->BlockFoundIcon();
			}
		}
	}
}
