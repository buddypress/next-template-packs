<?php
/**
 * BuddyPress - Members
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

/**
 * Fires at the top of the members directory template file.
 *
 * @since 1.5.0
 */
do_action( 'bp_before_directory_members_page' ); ?>

<div id="buddypress">

	<?php

	/**
	 * Fires before the display of the members.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_before_directory_members' ); ?>

	<?php

	/**
	 * Fires before the display of the members content.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_before_directory_members_content' ); ?>

	<?php

	/**
	 * Fires before the display of the members list tabs.
	 *
	 * @since 1.8.0
	 */
	do_action( 'bp_before_directory_members_tabs' ); ?>


	<?php if ( ! bp_nouveau_is_object_nav_in_sidebar() ) : ?>

		<?php bp_get_template_part( 'members/object-nav' ); ?>

	<?php endif; ?>

	<div class="item-list-tabs" id="subnav" role="navigation">
		<ul>
			<li class="dir-search" role="search" data-bp-search="members">
				<?php bp_directory_members_search_form(); ?>
			</li>
			<?php

			/**
			 * Fires inside the members directory member sub-types.
			 *
			 * @since 1.5.0
			 */
			do_action( 'bp_members_directory_member_sub_types' ); ?>

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
		</ul>
	</div>

	<div id="members-dir-list" class="members dir-list" data-bp-list="members">
		<div id="bp-ajax-loader">loading</div>
	</div><!-- #members-dir-list -->

	<?php

	/**
		 * Fires and displays the members content.
		 *
		 * @since 1.1.0
		 */
	do_action( 'bp_directory_members_content' ); ?>

	<?php wp_nonce_field( 'directory_members', '_wpnonce-member-filter' ); ?>

	<?php

	/**
	 * Fires after the display of the members content.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_after_directory_members_content' ); ?>

	<?php

	/**
	 * Fires after the display of the members.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_after_directory_members' ); ?>

</div><!-- #buddypress -->

<?php

/**
 * Fires at the bottom of the members directory template file.
 *
 * @since 1.5.0
 */
do_action( 'bp_after_directory_members_page' );
