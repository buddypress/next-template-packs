<?php
/**
 * BP Nouveau temptate notices template.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>
<div class="<?php bp_nouveau_template_message_classes(); ?>">

	<?php bp_nouveau_template_message(); ?>

	<?php if ( bp_nouveau_has_dismiss_button() ) : ?>

		<a href="#" title="close" data-bp-close="<?php bp_nouveau_dismiss_button_type(); ?>"><span class="dashicons dashicons-dismiss" aria-hidden="true"></span></a>

	<?php endif ; ?>
</div>
