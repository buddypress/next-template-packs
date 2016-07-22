<?php
/**
 * BuddyPress - Home
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */

?>
<div id="buddypress" class="<?php bp_nouveau_buddypress_classes(); ?>">

	<?php bp_nouveau_template_notices(); ?>

	<div class="activity" data-bp-single="<?php echo esc_attr( bp_current_action() ); ?>">

		<ul id="activity-stream" class="activity-list item-list bp-list" data-bp-list="activity">

			<li id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'single-activity-loading' ) ;?></li>

		</ul>

	</div>
</div>
