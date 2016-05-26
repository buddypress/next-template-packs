<?php
/**
 * BuddyPress - Groups Activity
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */

?>

<?php

/**
 * Fires before the display of the group activity post form.
 *
 * @since 1.2.0
 */
do_action( 'bp_before_group_activity_post_form' ); ?>

<?php if ( is_user_logged_in() && bp_group_is_member() ) : ?>

	<?php bp_get_template_part( 'activity/post-form' ); ?>

<?php endif; ?>

<?php

/**
 * Fires after the display of the group activity post form.
 *
 * @since 1.2.0
 */
do_action( 'bp_after_group_activity_post_form' ); ?>

<div class="item-list-tabs" id="subnav" role="navigation">
	<ul>
		<li class="feed"><a href="<?php bp_group_activity_feed_link(); ?>" title="<?php esc_attr_e( 'RSS Feed', 'bp-nouveau' ); ?>" class="no-ajax"><span class="bp-screen-reader-text"><?php _e( 'RSS', 'bp-nouveau' ); ?></span></a></li>

		<li class="group-search" role="search" data-bp-search="activity">
			<?php bp_directory_activity_search_form(); ?>
		</li>

		<?php

		/**
		 * Fires inside the syndication options list, after the RSS option.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_group_activity_syndication_options' ); ?>

		<li id="activity-filter-select" class="last filter">
			<label for="activity-filter-by"><span class="bp-screen-reader-text"><?php _e( 'Show:', 'bp-nouveau' ); ?></span></label>
			<select id="activity-filter-by" data-bp-filter="activity">
				<option value="-1"><?php _e( '&mdash; Everything &mdash;', 'bp-nouveau' ); ?></option>

				<?php bp_activity_show_filters( 'group' ); ?>

				<?php

				/**
				 * Fires inside the select input for group activity filter options.
				 *
				 * @since 1.2.0
				 */
				do_action( 'bp_group_activity_filter_options' ); ?>
			</select>
		</li>
	</ul>
</div><!-- .item-list-tabs -->

<?php

/**
 * Fires before the display of the group activities list.
 *
 * @since 1.2.0
 */
do_action( 'bp_before_group_activity_content' ); ?>

<div class="activity single-group">

	<ul id="activity-stream" class="activity-list item-list" data-bp-list="activity">

		<li id="bp-activity-ajax-loader">loading</li>

	</ul>

</div><!-- .activity -->

<?php

/**
 * Fires after the display of the group activities list.
 *
 * @since 1.2.0
 */
do_action( 'bp_after_group_activity_content' ); ?>
