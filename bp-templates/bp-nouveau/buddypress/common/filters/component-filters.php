<?php
/**
* BuddyPress  Directory Component filters
*
* @since 1.0.0
*
* @package BP Next
 */
$component = bp_current_component();
?>
<menu id="dir-filters" class="dir-component-filters clearfix">

<?php
switch( $component ) {
	case 'activity':

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

	case 'blogs':

	break;
} ?>

</menu><!-- // menu.dir-component-filters -->
