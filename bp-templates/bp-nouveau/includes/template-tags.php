<?php
/**
 * Common template tags
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Directory pagination
 */
function bp_pagination( $position = null ) {
	$component = bp_current_component();
	$screen = ( bp_is_user() )? 'user' : 'dir';

	switch( $component ) {

		case 'blogs' :

			$bp_pag_count = bp_get_blogs_pagination_count();
			$bp_pag_links = bp_get_blogs_pagination_links();

		break;

		case 'members' || 'friends' :

			$bp_pag_count =	bp_get_members_pagination_count();
			$bp_pag_links = bp_get_members_pagination_links();

		break;

		case 'groups' :

			$bp_pag_count =	bp_get_groups_pagination_count();
			$bp_pag_links = bp_get_groups_pagination_links();

		break;
	}
	?>

	<div class="pagination <?php echo $position; ?>" data-bp-nav="pagination">

		<?php if( $bp_pag_count ) : ?>
		<div class="pag-count <?php echo $component; ?>-<?php echo $screen; ?>-count-<?php echo $position; ?>">

			<p class="pag-data">
				<?php echo $bp_pag_count; ?>
			</p>

		</div>
		<?php endif; ?>

		<?php if( $bp_pag_links ) : ?>
		<div class="pagination-links <?php echo $component; ?>-<?php echo $screen; ?>-links-<?php echo $position; ?>">

			<p class="pag-data">
				<?php echo $bp_pag_links; ?>
			</p>

		</div>
		<?php endif; ?>

	</div>

	<?php

	return;
}

/**
 * Add the Create a Group nav to the Groups directory navigation.
 *
 * @since 1.0.0
 *
 * @uses   bp_group_create_nav_item() to output the create a Group nav item.
 */
function bp_nouveau_group_create_nav() {
	bp_group_create_nav_item();
}

/**
 * Add the Create a Site nav to the Sites directory navigation.
 *
 * @since 1.0.0
 *
 * @uses   bp_blog_create_nav_item() to output the Create a Site nav item
 */
function bp_nouveau_blog_create_nav() {
	bp_blog_create_nav_item();
}

function bp_nouveau_before_activity_post_form() {
	// Enqueue needed script.
	if ( bp_nouveau_current_user_can( 'publish_activity' ) ) {
		wp_enqueue_script( 'bp-nouveau-activity-post-form' );
	}

	do_action( 'bp_before_activity_post_form' );
}

function bp_nouveau_after_activity_post_form() {
	bp_get_template_part( 'assets/activity/form' );

	do_action( 'bp_after_activity_post_form' );
}
