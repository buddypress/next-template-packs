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
			<div class="clear"></div>
			<div class="bp-feedback info" rel="n-<?php echo esc_attr( $notice->id ); ?>">
				<strong><?php echo stripslashes( wp_filter_kses( $notice->subject ) ) ?></strong><br />
				<?php echo stripslashes( wp_filter_kses( $notice->message) ) ?>
			</div>
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
