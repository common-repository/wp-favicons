<?php
/**
 * Filter to get an monsterID icon as a default icon for a url
 *
 * This file get an monsterID icon from the Gravatar service as default url
 *
 * @package WP-Favicons
 * @since 0.5.0
 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
 * @copyright GPL 2
 */

namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\default_monsterID"))
{
	/**
 	 * Gets an monsterID Icon
 	 * @package WP-Favicons
     * @since 0.5.0
     * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
     * @copyright GPL 2
 	 */
	class default_monsterID extends default_generic_plugin
	{
	}
}

