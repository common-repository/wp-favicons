<?php
/**
 * Excluded FileTypes
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, http://wp.leau.co
 * @copyright GPL 2 */
namespace leau\co\wp_favicons_server;

if (!class_exists("\\leau\\co\\wp_favicons_server\\exceptions_excluded_filetypes"))
{
	class exceptions_excluded_filetypes extends Plugin
	{
		public $_module = 'filetypes';
		public $_plugin = 'excluded_filetypes';
	}
}
