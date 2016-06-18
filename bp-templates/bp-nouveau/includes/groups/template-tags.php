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

	echo join( ' / ', array_map( 'esc_html', (array) $meta ) );
}
	/**
	 * Get the group meta.
	 *
	 * @since  1.0.0
	 *
	 * @return array The group meta.
	 */
	function bp_nouveau_get_group_meta() {
		$meta = array();

		if ( ! empty( $GLOBALS['groups_template']->group ) ) {
			$group = $GLOBALS['groups_template']->group;
		}

		if ( empty( $group->id ) ) {
			return $meta;
		}

		if ( empty( $group->template_meta ) ) {
			/**
			 * Filter here to add/remove Group meta.
			 *
			 * @since  1.0.0
			 *
			 * @param array  $value The list of meta to output.
			 * @param object $group The current Group of the loop object.
			 */
			$meta = apply_filters( 'bp_nouveau_get_group_meta', array(
				bp_get_group_type(),
				bp_get_group_member_count(),
			), $group );

			$group->template_meta = $meta;
		} else {
			$meta = $group->template_meta;
		}

		return $meta;
	}
