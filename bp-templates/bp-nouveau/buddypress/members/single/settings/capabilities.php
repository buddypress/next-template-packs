<?php
/**
 * BuddyPress - Members Settings ( Capabilities )
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

bp_nouveau_member_hook( 'before', 'settings_template' ); ?>

<h2 class="screen-heading member-capabilities-screen">
	<?php _e('Members Capabilities', 'bp-nouveau'); ?>
</h2>

<form action="<?php echo bp_displayed_user_domain() . bp_get_settings_slug() . '/capabilities/'; ?>" name="account-capabilities-form" id="account-capabilities-form" class="standard-form" method="post">

	<label for="user-spammer">
		<input type="checkbox" name="user-spammer" id="user-spammer" value="1" <?php checked( bp_is_user_spammer( bp_displayed_user_id() ) ); ?> />
		 <?php _e( 'This user is a spammer.', 'bp-nouveau' ); ?>
	</label>

	<?php bp_nouveau_submit_button( 'member-capabilities' ); ?>

</form>

<?php bp_nouveau_member_hook( 'after', 'settings_template' );
