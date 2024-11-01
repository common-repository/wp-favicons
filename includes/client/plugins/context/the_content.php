<?php
/**
 * Adds filter to replace content in posts and pages
 * Adds Filter and more to replace favicons in the_content
 *
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
 * @copyright GPL 2
 */

namespace leau\co\wp_favicons_client;

if (!class_exists("\\leau\\co\\wp_favicons_client\\context_the_content"))
{
    /**
     * Adds the filter to add favicons in front of urls for the_content
     * @author Edward de Leau
     * @since 0.4.0
     */
    class context_the_content extends context_generic_plugin {}
}
