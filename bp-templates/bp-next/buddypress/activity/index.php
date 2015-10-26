<?php
/**
 * BuddyPress Activity templates
 *
 * @since 2.3.0
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

/**
 * Fires before the activity directory listing.
 *
 * @since 1.5.0
 */
do_action( 'bp_before_directory_activity' ); ?>

<div id="buddypress">

	<?php

	/**
	 * Fires before the activity directory display content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_before_directory_activity_content' ); ?>

	<?php if ( is_user_logged_in() ) : ?>

		<?php bp_get_template_part( 'activity/post-form' ); ?>

	<?php endif; ?>

	<?php

	/**
	 * Fires towards the top of template pages for notice display.
	 *
	 * @since 1.0.0
	 */
	do_action( 'template_notices' ); ?>

	<?php if ( ! bp_next_is_object_nav_in_sidebar() ) : ?>

		<?php bp_get_template_part( 'activity/object-nav' ); ?>

	<?php endif; ?>

	<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
		<ul>
			<li class="feed"><a href="<?php bp_sitewide_activity_feed_link(); ?>" title="<?php esc_attr_e( 'RSS Feed', 'bp-next' ); ?>"><?php _e( 'RSS', 'bp-next' ); ?></a></li>
			<li class="dir-search" role="search" data-search="activity">
				<?php bp_directory_activity_search_form(); ?>
			</li>

			<?php

			/**
			 * Fires before the display of the activity syndication options.
			 *
			 * @since 1.2.0
			 */
			do_action( 'bp_activity_syndication_options' ); ?>

			<li id="activity-filter-select" class="last filter">
				<label for="activity-filter-by"><?php _e( 'Show:', 'bp-next' ); ?></label>
				<select id="activity-filter-by" data-filter="activity">
					<option value="-1"><?php _e( '&mdash; Everything &mdash;', 'bp-next' ); ?></option>

					<?php bp_activity_show_filters(); ?>

					<?php

					/**
					 * Fires inside the select input for activity filter by options.
					 *
					 * @since 1.2.0
					 */
					do_action( 'bp_activity_filter_options' ); ?>

				</select>
			</li>
		</ul>
	</div><!-- .item-list-tabs -->

	<?php

	/**
	 * Fires before the display of the activity list.
	 *
	 * @since 1.5.0
	 */
	do_action( 'bp_before_directory_activity_list' ); ?>

	<div class="activity bp-activity-list">

		<ul id="activity-stream" class="activity-list item-list">

		 	<li id="bp-ajax-loader">loading</li>

		</ul>

		<form action="" name="activity-loop-form" id="activity-loop-form" method="post">

			<?php wp_nonce_field( 'activity_filter', '_wpnonce_activity_filter' ); ?>

		</form><!-- activity-loop-form -->

	</div><!-- .activity -->

	<?php

	/**
	 * Fires after the display of the activity list.
	 *
	 * @since 1.5.0
	 */
	do_action( 'bp_after_directory_activity_list' ); ?>

	<?php

	/**
	 * Fires inside and displays the activity directory display content.
	 */
	do_action( 'bp_directory_activity_content' ); ?>

	<?php

	/**
	 * Fires after the activity directory display content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_after_directory_activity_content' ); ?>

	<?php

	/**
	 * Fires after the activity directory listing.
	 *
	 * @since 1.5.0
	 */
	do_action( 'bp_after_directory_activity' ); ?>

</div>
