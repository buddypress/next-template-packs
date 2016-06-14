<?php
/**
 * Blogs Template tags
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template tag to wrap all Legacy actions that was used
 * before the blogs directory content
 *
 * @since 1.0.0
 */
function bp_nouveau_before_blogs_directory_content() {
	/**
	 * Fires at the begining of the templates BP injected content.
	 *
	 * @since 2.3.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_blogs_page' );

	/**
	 * Fires before the display of the blogs.
	 *
	 * @since 1.5.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_blogs' );

	/**
	 * Fires before the display of the blogs listing content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_blogs_content' );

	/**
	 * Fires before the display of the blogs list tabs.
	 *
	 * @since 2.3.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_blogs_tabs' );
}

/**
 * Template tag to wrap all Legacy actions that was used
 * after the blogs directory content
 *
 * @since 1.0.0
 */
function bp_nouveau_after_blogs_directory_content() {
	/**
	 * Fires inside and displays the blogs content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_directory_blogs_content' );

	/**
	 * Fires after the display of the blogs listing content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_blogs_content' );

	/**
	 * Fires at the bottom of the blogs directory template file.
	 *
	 * @since 1.5.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_blogs' );

	/**
	 * Fires at the bottom of the blogs directory template file.
	 *
	 * @since 2.3.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_blogs_page' );
}
