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
 * Fire specific hooks into the private messages template
 *
 * @since 1.0.0
 *
 * @param string $when    'before' or 'after'
 * @param string $suffix  Use it to add terms at the end of the hook name
 */
function bp_nouveau_messages_hook( $when = '', $suffix = '' ) {
	$hook = array( 'bp' );

	if ( ! empty( $when ) ) {
		$hook[] = $when;
	}

	// It's a message hook
	$hook[] = 'message';

	if ( ! empty( $suffix ) ) {
		if ( 'compose_content' === $suffix ) {
			$hook[2] = 'messages';
		}

		$hook[] = $suffix;
	}

	/**
	 * @since 1.0.0 (BuddyPress) for the 'composent_content' (after), 'thread_header_actions', 'meta',
	 *                           'content', 'thread_content', 'thread_list', 'reply_box' suffixes
	 * @since 1.1.0 (BuddyPress) for the 'compose_content' (before), 'thread_reply' suffixes
	 */
	return bp_nouveau_hook( $hook );
}

/**
 * Output the sitewide notices into the member's header
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_sitewide_notices() {
	// Do not show notices if user is not logged in.
	if ( ! is_user_logged_in() || ! bp_is_my_profile() ) {
		return;
	}

	$notice = BP_Messages_Notice::get_active();

	if ( empty( $notice ) ) {
		return false;
	}

	$user_id = bp_loggedin_user_id();

	$closed_notices = bp_get_user_meta( $user_id, 'closed_notices', true );

	if ( empty( $closed_notices ) ) {
		$closed_notices = array();
	}

	if ( is_array( $closed_notices ) ) {
		if ( ! in_array( $notice->id, $closed_notices ) && $notice->id ) {
			?>
			<aside class="bp-sitewide-notice info" rel="n-<?php echo esc_attr( $notice->id ); ?>">
				<strong class="subject"><?php echo stripslashes( wp_filter_kses( $notice->subject ) ) ?></strong>
				<?php echo stripslashes( wpautop( wp_filter_kses( $notice->message ) ) ) ?>
				 <button type="button" class="close-notice bp-tooltip" data-bp-tooltip="<?php esc_attr_e( 'Dismiss this notice', 'buddypress' ) ?>"><span class="bp-screen-reader-text"><?php _e( 'Dismiss this notice', 'buddypress' ) ?>"></span> <span aria-hidden="true">&Chi;</span></button>
			</aside>
			<?php

			// Add the notice to closed ones
			$closed_notices[] = (int) $notice->id;
			bp_update_user_meta( $user_id, 'closed_notices', $closed_notices );
		}
	}
}

/**
 * Load the new Messages User Interface
 *
 * @since  1.0.0
 *
 * @return string HTML Output
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
