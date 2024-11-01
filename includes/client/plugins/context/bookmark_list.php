<?php
/**
 * Adds Filter and more to replace favicons in the_content
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
 * @copyright GPL 2
 */

namespace leau\co\wp_favicons_client;

if (!class_exists("\\leau\\co\\wp_favicons_client\\context_bookmark_list")) 
{
	class context_bookmark_list extends context_generic_plugin
	{
	}
}
