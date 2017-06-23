<?php
/**
 * Messages template tags
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Fire specific hooks into the private messages template.
 *
 * @since 1.0.0
 * @since 1.0.0 (BuddyPress) for the 'composent_content' (after), 'thread_header_actions', 'meta',
 *                           'content', 'thread_content', 'thread_list', 'reply_box' suffixes
 * @since 1.1.0 (BuddyPress) for the 'compose_content' (before), 'thread_reply' suffixes
 *
 * @param string $when   Either 'before' or 'after'.
 * @param string $suffix Use it to add terms at the end of the hook name.
 */
function bp_nouveau_messages_hook( $when = '', $suffix = '' ) {
	$hook = array( 'bp' );

	if ( $when ) {
		$hook[] = $when;
	}

	// It's a message hook
	$hook[] = 'message';

	if ( $suffix ) {
		if ( 'compose_content' === $suffix ) {
			$hook[2] = 'messages';
		}

		$hook[] = $suffix;
	}

	bp_nouveau_hook( $hook );
}

/**
 * Load the new Messages User Interface
 *
 * @since 1.0.0
 */
function bp_nouveau_messages_member_interface() {
	/**
	 * Fires before the member messages content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_before_member_messages_content' );

	// Load the Private messages UI
	bp_get_template_part( '_accessoires/messages/index' );

	/**
	 * Fires after the member messages content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_after_member_messages_content' );
}
