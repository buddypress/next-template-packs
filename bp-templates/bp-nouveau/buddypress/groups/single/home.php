<?php
/**
 * BuddyPress - Groups Home
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

?>
<div id="buddypress">

	<?php if ( bp_has_groups() ) : while ( bp_groups() ) : bp_the_group(); ?>

	<?php bp_nouveau_group_hook( 'before', 'home_content' ); ?>

	<div id="item-header" role="complementary" data-bp-item-id="<?php bp_group_id(); ?>" data-bp-item-component="groups" class="groups-header single-item-header">

		<?php bp_nouveau_group_header_template_part(); ?>

	</div><!-- #item-header -->

	<?php if ( ! bp_nouveau_is_object_nav_in_sidebar() ) : ?>

		<?php bp_get_template_part( 'groups/single/item-nav' ); ?>

	<?php endif; ?>

	<div id="item-body">

		<?php bp_nouveau_group_template_part(); ?>

	</div><!-- #item-body -->

	<?php bp_nouveau_group_hook( 'after', 'home_content' ); ?>

	<?php endwhile; endif; ?>

</div><!-- #buddypress -->
