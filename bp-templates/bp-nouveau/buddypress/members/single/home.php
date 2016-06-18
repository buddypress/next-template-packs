<?php
/**
 * BuddyPress - Members Home
 *
 * @since    1.0.0
 * @version  1.0.0
 *
 * @package BP Nouveau
 */

?>

<div id="buddypress">

	<?php

	/**
	 * Fires before the display of member home content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_before_member_home_content' ); ?>

	<div id="item-header" role="complementary" data-bp-item-id="<?php echo bp_displayed_user_id(); ?>" data-bp-item-component="members" class="users-header single-item-header">

		<?php bp_nouveau_member_header_template_part() ;?>

	</div><!-- #item-header -->

	<?php if ( ! bp_nouveau_is_object_nav_in_sidebar() ) : ?>

		<?php bp_get_template_part( 'members/single/item-nav' ); ?>

	<?php endif; ?>

	<div id="item-body">

		<?php bp_nouveau_member_template_part();?>

	</div><!-- #item-body -->

	<?php

	/**
	 * Fires after the display of member home content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_after_member_home_content' ); ?>

</div><!-- #buddypress -->
