<?php
/**
 * BuddyPress - Group Invites Settings
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */
?>

<h2 class="screen-heading group-invites-screen"><?php _e('Group Invites', 'bp-nouveau'); ?></h2>

<?php bp_nouveau_user_feedback( 'member-group-invites' ); ?>

<p class="bp-help-text group-invites-info"><?php _e( 'Set your email notification preferences.', 'bp-nouveau' ); ?></p>

<form action="<?php echo bp_displayed_user_domain() . bp_get_settings_slug() . '/invites/'; ?>" name="account-group-invites-form" id="account-group-invites-form" class="standard-form" method="post">

	<label for="account-group-invites-preferences">
		<input type="checkbox" name="account-group-invites-preferences" id="account-group-invites-preferences" value="1" <?php checked( 1, bp_nouveau_groups_get_group_invites_setting() ); ?>/>
		 <?php esc_html_e( 'I want to restrict Group invites to my friends only.', 'bp-nouveau' ); ?>
	</label>

	<?php bp_nouveau_submit_button( 'member-group-invites' ); ?>

</form>
