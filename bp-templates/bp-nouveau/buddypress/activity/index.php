<?php
/**
 * BuddyPress Activity templates
 *
 * @since 2.3.0
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */
?>

<div id="buddypress" class="buddypress">

	<?php bp_nouveau_before_activity_directory_content(); ?>

	<?php if ( is_user_logged_in() ) : ?>

		<?php bp_get_template_part( 'activity/post-form' ); ?>

	<?php endif; ?>

	<?php bp_nouveau_template_notices(); ?>

	<?php if ( ! bp_nouveau_is_object_nav_in_sidebar() ) : ?>

		<?php bp_get_template_part( 'common/nav/directory-nav' ); ?>

	<?php endif; ?>

	<div class="item-list-tabs no-ajax" id="subnav" role="navigation">

		<ul type="list" class="subnav clearfix">
			<li class="feed"><a href="<?php bp_sitewide_activity_feed_link(); ?>" title="<?php esc_attr_e( 'RSS Feed', 'bp-nouveau' ); ?>"><span class="bp-screen-reader-text"><?php _e( 'RSS', 'bp-nouveau' ); ?></span></a></li>

			<?php bp_nouveau_search_form(); ?>

		</ul>

		<?php bp_get_template_part( 'common/filters/directory-filters' ); ?>

	</div><!-- .item-list-tabs -->

	<?php bp_nouveau_activity_hook( 'before_directory', 'list' ); ?>

	<div class="activity">

		<ul id="activity-stream" class="activity-list item-list bp-list" data-bp-list="activity">

		 	<li id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'directory-activity-loading' ) ;?></li>

		</ul>

	</div><!-- .activity -->

	<?php bp_nouveau_after_activity_directory_content() ;?>

</div>
