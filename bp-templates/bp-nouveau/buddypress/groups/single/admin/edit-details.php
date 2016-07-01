<?php
/**
 * BP Nouveau Group's edit details template.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>

<label for="group-name"><?php _e( 'Group Name (required)', 'bp-nouveau' ); ?></label>
<input type="text" name="group-name" id="group-name" value="<?php bp_is_group_create() ? bp_new_group_name() : bp_group_name(); ?>" aria-required="true" />

<label for="group-desc"><?php _e( 'Group Description (required)', 'bp-nouveau' ); ?></label>
<textarea name="group-desc" id="group-desc" aria-required="true"><?php bp_is_group_create() ? bp_new_group_description() : bp_group_description_editable(); ?></textarea>

<?php if ( ! bp_is_group_create() ) : ?>
	<p>
		<label for="group-notify-members">
			<input type="checkbox" name="group-notify-members" id="group-notify-members" value="1" /> <?php _e( 'Notify group members of these changes via email', 'bp-nouveau' ); ?>
		</label>
	</p>
<?php endif ; ?>
