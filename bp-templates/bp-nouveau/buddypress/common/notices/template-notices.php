<?php
/**
 * BP Nouveau temptate notices template.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>
<aside class="<?php bp_nouveau_template_message_classes(); ?>">
	<span class="bp-icon" aria-hidden="true"></span>
	<?php bp_nouveau_template_message(); ?>

	<?php if ( bp_nouveau_has_dismiss_button() ) : ?>

		<button type="button" class="bp-tooltip" data-bp-tooltip="<?php esc_attr_e( 'Close', 'bp-nouveau' ); ?>" aria-label="<?php esc_attr_e( 'Close this notice', 'bp-nouveau' ); ?>" data-bp-close="<?php bp_nouveau_dismiss_button_type(); ?>"><span class="dashicons dashicons-dismiss" aria-hidden="true"></span></button>

	<?php endif ; ?>
</aside>
