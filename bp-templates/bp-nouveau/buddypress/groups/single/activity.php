<?php
/**
 * BuddyPress - Groups Activity
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

?>

<?php bp_nouveau_groups_activity_post_form(); ?>

<div class="bp-navs bp-subnavs group-subnav" id="subnav" role="navigation">
	<ul class="subnav">
		<li class="feed"><a href="<?php bp_group_activity_feed_link(); ?>" title="<?php esc_attr_e( 'RSS Feed', 'bp-nouveau' ); ?>" class="no-ajax"><span class="bp-screen-reader-text"><?php _e( 'RSS', 'bp-nouveau' ); ?></span></a></li>

		<?php bp_nouveau_search_form(); ?>

		<li id="activity-filter-select" class="last filter">
			<label for="activity-filter-by"><span class="bp-screen-reader-text"><?php _e( 'Show:', 'bp-nouveau' ); ?></span></label>
			<select id="activity-filter-by" data-bp-filter="activity">

				<?php bp_nouveau_filter_options() ;?>

			</select>
		</li>
	</ul>
</div><!-- .item-list-tabs -->

<?php bp_nouveau_group_hook( 'before', 'activity_content' ); ?>

<div class="activity single-group">

	<ul id="activity-stream" class="activity-list item-list" data-bp-list="activity">

		<li id="bp-activity-ajax-loader"><?php bp_nouveau_user_feedback( 'group-activity-loading' ) ;?></li>

	</ul>

</div><!-- .activity -->

<?php bp_nouveau_group_hook( 'after', 'activity_content' );
