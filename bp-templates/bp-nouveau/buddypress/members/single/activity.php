<?php
/**
 * BuddyPress - Users Activity
 *
 * @since  1.0.0
 *
 * @package BP Nouveau
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul class="subnav">

		<?php bp_get_template_part( 'members/single/parts/item-subnav' ); ?>

	</ul>
</div><!-- .item-list-tabs#subnav -->

<?php bp_nouveau_activity_member_post_form() ;?>

<div class="item-list-tabs bp-navs user-subnav no-ajax" id="subsubnav">
	<ul>
		<?php bp_nouveau_search_form(); ?>

		<li id="activity-filter-select" class="last filter">
			<label for="activity-filter-by"><span class="bp-screen-reader-text"><?php _e( 'Show:', 'bp-nouveau' ); ?></span></label>
			<select id="activity-filter-by" data-bp-filter="activity">

				<?php bp_nouveau_filter_options() ;?>

			</select>
		</li>
	</ul>
</div><!-- .item-list-tabs#subsubnav -->

<?php bp_nouveau_member_hook( 'before', 'activity_content' ); ?>

<div class="activity single-user">

	<ul id="activity-stream" class="activity-list item-list" data-bp-list="activity">

		<li id="bp-ajax-loader"><?php bp_nouveau_user_feedback( 'member-activity-loading' ) ;?></li>

	</ul>

</div><!-- .activity -->

<?php bp_nouveau_member_hook( 'after', 'activity_content' );
