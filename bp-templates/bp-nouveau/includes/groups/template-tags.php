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
