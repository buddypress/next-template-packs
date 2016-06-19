<?php
/**
 * Groups Template tags
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template tag to wrap all Legacy actions that was used
 * before the groups directory content
 *
 * @since 1.0.0
 */
function bp_nouveau_before_groups_directory_content() {
	/**
	 * Fires at the begining of the templates BP injected content.
	 *
	 * @since 2.3.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_groups_page' );

	/**
	 * Fires before the display of the groups.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_groups' );

	/**
	 * Fires before the display of the groups content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_groups_content' );
}

/**
 * Template tag to wrap all Legacy actions that was used
 * after the groups directory content
 *
 * @since 1.0.0
 */
function bp_nouveau_after_groups_directory_content() {
	/**
	 * Fires and displays the group content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_directory_groups_content' );

	/**
	 * Fires after the display of the groups content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_groups_content' );

	/**
	 * Fires after the display of the groups.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_groups' );

	/**
	 * Fires at the bottom of the groups directory template file.
	 *
	 * @since 1.5.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_groups_page' );
}

/**
 * Does the group has meta.
 *
 * @since  1.0.0
 *
 * @return bool True if the group has meta. False otherwise.
 */
function bp_nouveau_group_has_meta() {
	return (bool) bp_nouveau_get_group_meta();
}

/**
 * Display the group meta.
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_group_meta() {
	$meta = bp_nouveau_get_group_meta();

	if ( ! bp_is_group() ) {
		echo join( ' / ', array_map( 'esc_html', (array) $meta ) );
	} else {
		echo join( "\n", $meta );
	}
}

	/**
	 * Get the group meta.
	 *
	 * @since  1.0.0
	 *
	 * @return array The group meta.
	 */
	function bp_nouveau_get_group_meta() {
		$meta     = array();
		$is_group = bp_is_group();

		if ( ! empty( $GLOBALS['groups_template']->group ) ) {
			$group = $GLOBALS['groups_template']->group;
		}

		if ( empty( $group->id ) ) {
			return $meta;
		}

		if ( empty( $group->template_meta ) ) {
			// It's a single group
			if ( $is_group ) {
				$meta = array(
					'description' => bp_get_group_description(),
				);

				// Make sure to include hooked meta.
				$extra_meta = bp_nouveau_get_hooked_group_meta();

				if ( $extra_meta ) {
					$meta['extra'] = $extra_meta;
				}

			// We're in the groups loop
			} else {
				$meta = array(
					'status' => bp_get_group_type(),
					'count'  => bp_get_group_member_count(),
				);
			}

			/**
			 * Filter here to add/remove Group meta.
			 *
			 * @since  1.0.0
			 *
			 * @param array  $meta     The list of meta to output.
			 * @param object $group    The current Group of the loop object.
			 * @param bool   $is_group True if a single group is displayed. False otherwise.
			 */
			$group->template_meta = apply_filters( 'bp_nouveau_get_group_meta', $meta, $group, $is_group );
		}

		return $group->template_meta;
	}

/**
 * Load the appropriate content for the single group pages
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_group_template_part() {
	/**
	 * Fires before the display of the group home body.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_before_group_body' );

	$bp_is_group_home = bp_is_group_home();

	if ( $bp_is_group_home && ! bp_group_is_visible() ) {
		/**
		 * Fires before the display of the group status message.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_before_group_status_message' ); ?>

		<div id="message" class="info">
			<p><?php bp_group_status_message(); ?></p>
		</div>

		<?php

		/**
		 * Fires after the display of the group status message.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_after_group_status_message' );

	// We have a front template, Use BuddyPress function to load it.
	} elseif ( $bp_is_group_home && false !== bp_groups_get_front_template() ) {
		bp_groups_front_template_part();

	// Otherwise use BP_Nouveau template hierarchy
	} else {
		$template = 'plugins';

		// the home page
		if ( $bp_is_group_home ) {
			if ( bp_is_active( 'activity' ) ) {
				$template = 'activity';
			} else {
				$template = 'members';
			}

		// Not the home page
		} elseif ( bp_is_group_admin_page() ) {
			$template = 'admin';
		} elseif ( bp_is_group_activity() ) {
			$template = 'activity';
		} elseif ( bp_is_group_members() ) {
			$template = 'members';
		} elseif ( bp_is_group_invites() ) {
			$template = 'send-invites';
		} elseif ( bp_is_group_membership_request() ) {
			$template = 'request-membership';
		}

		bp_nouveau_group_get_template_part( $template );
	}

	/**
	 * Fires after the display of the group home body.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_after_group_body' );
}

/**
 * Use the appropriate Group header and enjoy a template hierarchy
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_group_header_template_part() {
	$template = 'group-header';

	if ( bp_group_use_cover_image_header() ) {
		$template = 'cover-image-header';
	}

	/**
	 * Fires before the display of a group's header.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_before_group_header' );

	// Get the template part for the header
	bp_nouveau_group_get_template_part( $template );

	/**
	 * Fires after the display of a group's header.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_after_group_header' );

	bp_nouveau_template_notices();
}
