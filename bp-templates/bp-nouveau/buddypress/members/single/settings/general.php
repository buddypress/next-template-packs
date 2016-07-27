<?php
/**
 * BuddyPress - Members Single Profile
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

bp_nouveau_member_hook( 'before', 'settings_template' ); ?>

<form action="<?php echo bp_displayed_user_domain() . bp_get_settings_slug() . '/general'; ?>" method="post" class="standard-form" id="settings-form">

	<?php if ( !is_super_admin() ) : ?>

		<label for="pwd"><?php _e( 'Current Password <span>(required to update email or change current password)</span>', 'bp-nouveau' ); ?></label>
		<input type="password" name="pwd" id="pwd" size="16" value="" class="settings-input small" <?php bp_form_field_attributes( 'password' ); ?>/> &nbsp;<a href="<?php echo wp_lostpassword_url(); ?>" title="<?php esc_attr_e( 'Password Lost and Found', 'bp-nouveau' ); ?>"><?php _e( 'Lost your password?', 'bp-nouveau' ); ?></a>

	<?php endif; ?>

	<label for="email"><?php _e( 'Account Email', 'bp-nouveau' ); ?></label>
	<input type="email" name="email" id="email" value="<?php echo bp_get_displayed_user_email(); ?>" class="settings-input" <?php bp_form_field_attributes( 'email' ); ?>/>

	<p class="bp-help-text">
		<?php _e( 'Change Password <span>(leave blank for no change)</span>', 'bp-nouveau' ); ?>
	</p>

	<label for="pass1"><?php _e('Add Your New Password', 'bp-nouveau'); ?></label>
	<input type="password" name="pass1" id="pass1" size="16" value="" class="settings-input small password-entry" <?php bp_form_field_attributes( 'password' ); ?>/>


	<label for="pass2" class="repeated-pwd"><?php _e( 'Repeat Your New Password', 'bp-nouveau' ); ?></label>
	<input type="password" name="pass2" id="pass2" size="16" value="" class="settings-input small password-entry-confirm" <?php bp_form_field_attributes( 'password' ); ?>/>
<div id="pass-strength-result"></div>
	<?php bp_nouveau_submit_button( 'members-general-settings' ); ?>

</form>

<?php bp_nouveau_member_hook( 'after', 'settings_template' );
