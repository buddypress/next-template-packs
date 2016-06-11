<?php
/**
* BuddyPress  Directory Component filters
*
* @since 1.0.0
*
* @package BP Nouveau
 */
$component = bp_current_component();
?>
<ul id="dir-filters" class="dir-component-filters clearfix">

<?php
switch( $component ) {
	case 'activity': ?>

		<li id="activity-filter-select" class="last filter">
			<label for="activity-filter-by"><span class="bp-screen-reader-text"><?php _e( 'Show:', 'bp-nouveau' ); ?></span></label>
			<select id="activity-filter-by" data-bp-filter="activity">
				<option value="-1"><?php _e( '&mdash; Everything &mdash;', 'bp-nouveau' ); ?></option>

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

	<?php
	break;

	case 'members': ?>

		<li id="members-order-select" class="last filter">
			<label for="members-order-by"><span class="bp-screen-reader-text"><?php _e( 'Order By:', 'bp-nouveau' ); ?></span></label>
			<select id="members-order-by" data-bp-filter="members">
				<option value="active"><?php _e( 'Last Active', 'bp-nouveau' ); ?></option>
				<option value="newest"><?php _e( 'Newest Registered', 'bp-nouveau' ); ?></option>

				<?php if ( bp_is_active( 'xprofile' ) ) : ?>
					<option value="alphabetical"><?php _e( 'Alphabetical', 'bp-nouveau' ); ?></option>
				<?php endif; ?>

				<?php

				/**
				 * Fires inside the members directory member order options.
				 *
				 * @since 1.2.0
				 */
				do_action( 'bp_members_directory_order_options' ); ?>
			</select>
		</li>

	<?php
	break;

	case 'groups': ?>

		<li id="groups-order-select" class="last filter">

			<label for="groups-order-by"><span class="bp-screen-reader-text"><?php _e( 'Order By:', 'bp-nouveau' ); ?></span></label>

			<select id="groups-order-by" data-bp-filter="groups">
				<option value="active"><?php _e( 'Last Active', 'bp-nouveau' ); ?></option>
				<option value="popular"><?php _e( 'Most Members', 'bp-nouveau' ); ?></option>
				<option value="newest"><?php _e( 'Newly Created', 'bp-nouveau' ); ?></option>
				<option value="alphabetical"><?php _e( 'Alphabetical', 'bp-nouveau' ); ?></option>

				<?php

				/**
				 * Fires inside the groups directory group order options.
				 *
				 * @since 1.2.0
				 */
				do_action( 'bp_groups_directory_order_options' ); ?>
			</select>
		</li>

	<?php
	break;

	case 'blogs': ?>

		<li id="blogs-order-select" class="last filter">

			<label for="blogs-order-by"><?php _e( 'Order By:', 'bp-nouveau' ); ?></label>
			<select id="blogs-order-by">
				<option value="active"><?php _e( 'Last Active', 'bp-nouveau' ); ?></option>
				<option value="newest"><?php _e( 'Newest', 'bp-nouveau' ); ?></option>
				<option value="alphabetical"><?php _e( 'Alphabetical', 'bp-nouveau' ); ?></option>

				<?php

				/**
				 * Fires inside the select input listing blogs orderby options.
				 *
				 * @since 1.2.0
				 */
				do_action( 'bp_blogs_directory_order_options' ); ?>

			</select>
		</li>

	<?php
	break;
} ?>

</ul><!-- // menu.dir-component-filters -->
