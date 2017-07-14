<?php
/**
 * BuddyPress - Groups Activity
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

?>

<?php bp_nouveau_groups_activity_post_form(); ?>


	<div class="subnav-filters filters clearfix">

		<ul>
			<li class="feed"><a href="<?php bp_group_activity_feed_link(); ?>" class="no-ajax"><span class="bp-screen-reader-text"><?php _e( 'RSS', 'buddypress' ); ?></span></a></li>

			<li class="group-act-search"><?php bp_nouveau_search_form(); ?></li>
		</ul>

		<?php bp_get_template_part( 'common/filters/groups-screens-filters' ); ?>
	</div><!-- // .subnav-filters -->


<?php bp_nouveau_group_hook( 'before', 'activity_content' ); ?>

<div class="activity single-group">

	<ul id="activity-stream" class="activity-list item-list bp-list" data-bp-list="activity">

		<li id="bp-activity-ajax-loader"><?php bp_nouveau_user_feedback( 'group-activity-loading' ); ?></li>

	</ul>

</div><!-- .activity -->

<?php bp_nouveau_group_hook( 'after', 'activity_content' );
