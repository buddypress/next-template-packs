<?php
/**
 * BuddyPress - Users Activity
 *
 * @package BuddyPress
 * @subpackage bp-nouveau
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>

		<?php bp_get_options_nav(); ?>

	</ul>
</div><!-- .item-list-tabs#subnav -->

<?php

/**
 * Fires before the display of the member activity post form.
 *
 * @since 1.2.0
 */
do_action( 'bp_before_member_activity_post_form' ); ?>

<?php
if ( is_user_logged_in() && bp_is_my_profile() && ( !bp_current_action() || bp_is_current_action( 'just-me' ) ) )
	bp_get_template_part( 'activity/post-form' );

/**
 * Fires after the display of the member activity post form.
 *
 * @since 1.2.0
 */
do_action( 'bp_after_member_activity_post_form' ); ?>

<div class="item-list-tabs no-ajax" id="subsubnav">
	<ul>
		<li class="member-search" role="search" data-bp-search="activity">
			<?php bp_directory_activity_search_form(); ?>
		</li>
		<li id="activity-filter-select" class="last filter">
			<label for="activity-filter-by"><span class="bp-screen-reader-text"><?php _e( 'Show:', 'bp-nouveau' ); ?></span></label>
			<select id="activity-filter-by" data-bp-filter="activity">
				<option value="-1"><?php _e( '&mdash; Everything &mdash;', 'bp-nouveau' ); ?></option>

				<?php bp_activity_show_filters(); ?>

				<?php

				/**
				 * Fires inside the select input for member activity filter options.
				 *
				 * @since 1.2.0
				 */
				do_action( 'bp_member_activity_filter_options' ); ?>

			</select>
		</li>
	</ul>
</div><!-- .item-list-tabs#subsubnav -->

<?php
/**
 * Fires before the display of the member activities list.
 *
 * @since 1.2.0
 */
do_action( 'bp_before_member_activity_content' ); ?>

<div class="activity single-user">

	<ul id="activity-stream" class="activity-list item-list" data-bp-list="activity">

		<li id="bp-ajax-loader"><?php esc_html_e( 'Loading your updates, please wait.', 'bp-nouveau' ) ;?></li>

	</ul>

</div><!-- .activity -->

<?php

/**
 * Fires after the display of the member activities list.
 *
 * @since 1.2.0
 */
do_action( 'bp_after_member_activity_content' ); ?>
