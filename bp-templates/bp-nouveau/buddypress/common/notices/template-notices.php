<?php
/**
 * BP Nouveau temptate notices template.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
?>
<div class="bp-feedback bp-template-notice <?php bp_nouveau_template_message_type(); ?>">
	<?php bp_nouveau_template_message(); ?>
	<a href="#" title="close" data-bp-close="clear"><span class="dashicons dashicons-dismiss"></span></a>
</div>
