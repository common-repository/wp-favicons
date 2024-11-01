<?php
/**
 * Adds filter to replace content in comment text
 *
 * @package WP-Favicons
 * @since 0.4.0
 * @author Edward de Leau <e@leau.net>, {@link http://wp.leau.co}
 * @copyright GPL 2
 */

namespace leau\co\wp_favicons_client;

if (!class_exists("\\leau\\co\\wp_favicons_client\\context_comment_text"))
{
    /**
     * Adds the filter to add favicons in front of urls for comment text
     * @author Edward de Leau
     * @since 0.4.0
     */
    class context_comment_text extends context_generic_plugin {}
}
