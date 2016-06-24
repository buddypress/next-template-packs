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

<?php bp_nouveau_activity_member_post_form() ;?>

<div class="item-list-tabs no-ajax" id="subsubnav">
	<ul>
		<?php bp_get_template_part( 'common/search/object-search-form' ); ?>

		<li id="activity-filter-select" class="last filter">
			<label for="activity-filter-by"><span class="bp-screen-reader-text"><?php _e( 'Show:', 'bp-nouveau' ); ?></span></label>
			<select id="activity-filter-by" data-bp-filter="activity">

				<?php bp_nouveau_filter_options() ;?>

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
