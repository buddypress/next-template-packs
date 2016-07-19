<?php
/**
 * BuddyPress - Members Home
 *
 * @since    1.0.0
 *
 * @package BP Nouveau
 */

?>

<div id="buddypress" class="buddypress bp-vertical-nav">

	<?php bp_nouveau_member_hook( 'before', 'home_content' ); ?>

	<div id="item-header" role="complementary" data-bp-item-id="<?php echo bp_displayed_user_id(); ?>" data-bp-item-component="members" class="users-header single-headers">

		<?php bp_nouveau_member_header_template_part() ;?>

	</div><!-- #item-header -->

	<?php if ( ! bp_nouveau_is_object_nav_in_sidebar() ) : ?>

		<?php bp_get_template_part( 'members/single/parts/item-nav' ); ?>

	<?php endif; ?>

	<div id="item-body" class="item-body">

		<?php bp_nouveau_member_template_part();?>

	</div><!-- #item-body -->

	<?php bp_nouveau_member_hook( 'after', 'home_content' ); ?>

</div><!-- #buddypress -->
