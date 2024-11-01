<?php
/**
 * Filter to get an identicon as a default icon for a url
 *
 * This file get an identicon from the Gravatar service as default url
 *
 * Note: feel free to add your own filters!
 *
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>
 * @copyright Edward de Leau, http://edward.de.leau.net
 */

namespace leau\co\wp_favicons_server;

/**
 * Gets an Wavatar
 * @param $favicon_array
 * @param $url
 */
if (!class_exists("\\leau\\co\\wp_favicons_server\\default_wavatar"))
{
	class default_wavatar extends default_generic_plugin
	{
	}
}

