<?php
/**
 * BuddyPress - Members Settings ( Delete Account )
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

bp_nouveau_member_hook( 'before', 'settings_template' ); ?>

<h2 class="screen-heading delete-account-screen warn">
	<?php _e( 'Delete Account', 'bp-nouveau' ); ?>
</h2>

<?php bp_nouveau_user_feedback( 'member-delete-account' ); ?>

<form action="<?php echo esc_url( bp_displayed_user_domain() . bp_get_settings_slug() . '/delete-account' ); ?>" name="account-delete-form" id="account-delete-form" class="standard-form" method="post">

	<label class="warn" for="delete-account-understand">
		<input type="checkbox" name="delete-account-understand" id="delete-account-understand" value="1" onclick="if(this.checked) { document.getElementById( 'delete-account-button' ).disabled = ''; } else { document.getElementById( 'delete-account-button' ).disabled = 'disabled'; }" />
		 <?php _e( 'I understand the consequences.', 'bp-nouveau' ); ?>
	</label>

	<?php bp_nouveau_submit_button( 'member-delete-account' ); ?>

</form>

<?php bp_nouveau_member_hook( 'after', 'settings_template' );
