<?php
/**
 * BP Nouveau Group's delete group template.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>

<div id="message" class="info">
	<p><?php _e( 'WARNING: Deleting this group will completely remove ALL content associated with it. There is no way back, please be careful with this option.', 'bp-nouveau' ); ?></p>
</div>

<label for="delete-group-understand"><input type="checkbox" name="delete-group-understand" id="delete-group-understand" value="1" onclick="if(this.checked) { document.getElementById('delete-group-button').disabled = ''; } else { document.getElementById('delete-group-button').disabled = 'disabled'; }" /> <?php _e( 'I understand the consequences of deleting this group.', 'bp-nouveau' ); ?></label>
