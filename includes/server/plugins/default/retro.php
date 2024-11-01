<?php
/**
 * Filter to get an retro icon as a default icon for a url
 *
 * This file get an retro icon from the Gravatar service as default url
 *
 * @package WP-Favicons
 * @since 0.5.0
 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
 * @copyright GPL 2
 */

namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\default_retro"))
{
	/**
 	 * Gets an Retro Icon
 	 * @package WP-Favicons
     * @since 0.5.0
     * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
     * @copyright GPL 2
 	 */
	class default_retro extends default_generic_plugin
	{
	}
}

