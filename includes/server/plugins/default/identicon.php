<?php
/**
 * Filter to get an identicon as a default icon for a url
 *
 * This file get an identicon from the Gravatar service as default url
 *
 *
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
 * @copyright GPL 2
 */

namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\default_identicon"))
{
	/**
 	 * Gets an Identicon
 	 * @package WP-Favicons
     * @since 0.4.0
     * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
     * @copyright GPL 2
 	 */
	class default_identicon extends default_generic_plugin
	{
	}
}

