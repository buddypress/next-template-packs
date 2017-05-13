<?php
/**
 * Blogs Ajax functions
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Registers friends AJAX actions.
 */
bp_nouveau_register_ajax_actions( array(
	array( 'blogs_filter' => array( 'function' => 'bp_nouveau_ajax_object_template_loader', 'nopriv' => true ) ),
) );
