<?php
/**
 * BP Nouveau Group's delete group template.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>

<?php bp_nouveau_user_feedback( 'group-delete-warning' ); ?>

<label for="delete-group-understand" class="bp-label-text warn">
	<input type="checkbox" name="delete-group-understand" id="delete-group-understand" value="1" onclick="if(this.checked) { document.getElementById('delete-group-button').disabled = ''; } else { document.getElementById('delete-group-button').disabled = 'disabled'; }" />
	<?php _e( 'I understand the consequences of deleting this group.', 'bp-nouveau' ); ?>
</label>
