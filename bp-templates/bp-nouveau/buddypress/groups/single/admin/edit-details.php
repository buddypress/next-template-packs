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
<input type="text" name="group-name" id="group-name" value="<?php bp_group_name(); ?>" aria-required="true" />

<label for="group-desc"><?php _e( 'Group Description (required)', 'bp-nouveau' ); ?></label>
<textarea name="group-desc" id="group-desc" aria-required="true"><?php bp_group_description_editable(); ?></textarea>

<?php

/**
 * Fires after the group description admin details.
 *
 * @since 1.0.0
 */
do_action( 'groups_custom_group_fields_editable' ); ?>

<p>
	<label for="group-notify-members">
		<input type="checkbox" name="group-notify-members" id="group-notify-members" value="1" /> <?php _e( 'Notify group members of these changes via email', 'bp-nouveau' ); ?>
	</label>
</p>
