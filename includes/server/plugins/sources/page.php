<?php
/**
 * Filter to Extract a favicon from a webpage
 *
 * This file contains the wp_favicons_search filter implementation
 * to get a webpage from the and extract the icon from there.
 *
 * Note: feel free to add your own filters!
 *
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://edward.de.leau.net}
 * @copyright GPL 2
 */

namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\sources_page"))
{
	/**
	 * Filter to Extract a favicon from a webpage
	 * @package WP-Favicons
	 * @since 0.4.0
	 */
	class sources_page extends sources_generic_plugin
	{
		function Init($favicon)
    	{
    		Log::F($favicon,'[Check PAGE]-----------------');
    		$favicon->SetFaviconUri($favicon->GetUri());
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
			$favicon->VerifyImage();
		}
	}
}
