<?php
/**
 * BuddyPress - Members Settings Notifications
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

bp_nouveau_member_hook( 'before', 'settings_template' ); ?>

<form action="<?php echo bp_displayed_user_domain() . bp_get_settings_slug() . '/notifications'; ?>" method="post" class="standard-form" id="settings-form">
	<p><?php _e( 'Send an email notice when:', 'bp-nouveau' ); ?></p>

	<?php

	/**
	 * Fires at the top of the member template notification settings form.
	 *
	 * @since 1.0.0
	 */
	do_action( 'bp_notification_settings' ); ?>

	<?php

	/**
	 * Fires before the display of the submit button for user notification saving.
	 *
	 * @since 1.5.0
	 */
	do_action( 'bp_members_notification_settings_before_submit' ); ?>

	<div class="submit">
		<input type="submit" name="submit" value="<?php esc_attr_e( 'Save Changes', 'bp-nouveau' ); ?>" id="submit" class="auto" />
	</div>

	<?php

	/**
	 * Fires after the display of the submit button for user notification saving.
	 *
	 * @since 1.5.0
	 */
	do_action( 'bp_members_notification_settings_after_submit' ); ?>

	<?php wp_nonce_field('bp_settings_notifications' ); ?>

</form>

<?php bp_nouveau_member_hook( 'after', 'settings_template' );
