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

/**
 * Fire specific hooks into the blogs create template
 *
 * @since 1.0.0
 *
 * @param string $when    'before' or 'after'
 * @param string $suffix  Use it to add terms at the end of the hook name
 */
function bp_nouveau_blogs_create_hook( $when = '', $suffix = '' ) {
	if ( ! empty( $when ) ) {
		$when .= '_';
	}

	if ( ! empty( $suffix ) ) {
		$suffix = '_' . $suffix;
	}

	$hook = sprintf( 'bp_%1$screate_blog%2$s', $when, $suffix );

	/**
	 * @since 1.1.0 (BuddyPress) for the 'content' suffix
	 * @since 1.6.0 (BuddyPress) for the 'content_template' suffix
	 */
	do_action( $hook );
}

/**
 * Fire an isolated hook inside the blogs loop
 *
 * @since 1.0.0
 */
function bp_nouveau_blogs_loop_item() {
	/**
	 * Fires after the listing of a blog item in the blogs loop.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_directory_blogs_item' );
}

/**
 * Output the action buttons inside the blogs loop.
 *
 * @since 1.0.0
 *
 * @param  array $args @see bp_nouveau_wrapper() for the description of parameters.
 * @return string HTML Output
 */
function bp_nouveau_blogs_loop_buttons( $args = array() ) {
	if ( empty( $GLOBALS['blogs_template'] ) ) {
		return;
	}

	$output = join( ' ', bp_nouveau_get_blogs_buttons() );

	ob_start();
	/**
	 * Fires inside the blogs action listing area.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_directory_blogs_actions' );
	$output .= ob_get_clean();

	if ( empty( $output ) ) {
		return;
	}

	return bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

	/**
	 * Get the action buttons for the current blog in the loop.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $type Type of Group of buttons to get.
	 */
	function bp_nouveau_get_blogs_buttons( $type = 'loop' ) {
		// Not really sure why BP Legacy needed to do this...
		if ( 'loop' !== $type && is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$buttons = array();

		if ( isset( $GLOBALS['blogs_template']->blog ) ) {
			$blog = $GLOBALS['blogs_template']->blog;
		}

		if ( empty( $blog->blog_id ) ) {
			return $buttons;
		}

		/**
		 * This filter workaround is waiting for a core adaptation
		 * so that we can directly get the groups button arguments
		 * instead of the button.
		 * @see https://buddypress.trac.wordpress.org/ticket/7126
		 */
		add_filter( 'bp_get_blogs_visit_blog_button', 'bp_nouveau_blogs_catch_button_args', 100, 1 );

		bp_get_blogs_visit_blog_button();

		remove_filter( 'bp_get_blogs_visit_blog_button', 'bp_nouveau_blogs_catch_button_args', 100, 1 );

		if ( ! empty( bp_nouveau()->blogs->button_args ) ) {
			$buttons['visit_blog'] = wp_parse_args( array(
				'id'       => 'visit_blog',
				'position' => 5,
			), bp_nouveau()->blogs->button_args );

			unset( bp_nouveau()->blogs->button_args );
		}

		/**
		 * Filter here to add your buttons, use the position argument to choose where to insert it.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $buttons The list of buttons.
		 * @param object $blog    The current blog object.
		 * @param string $type    Whether we're displaying a blogs loop or a the blogs single item (in the future!).
		 */
		$buttons_group = apply_filters( 'bp_nouveau_get_blogs_buttons', $buttons, $blog, $type );

		if ( empty( $buttons_group ) ) {
			return $buttons;
		}

		// It's the first entry of the loop, so build the Group and sort it
		if ( ! isset( bp_nouveau()->blogs->group_buttons ) || false === is_a( bp_nouveau()->blogs->group_buttons, 'BP_Buttons_Group' ) ) {
			$sort = true;
			bp_nouveau()->blogs->group_buttons = new BP_Buttons_Group( $buttons_group );

		// It's not the first entry, the order is set, we simply need to update the Buttons Group
		} else {
			$sort = false;
			bp_nouveau()->blogs->group_buttons->update( $buttons_group );
		}

		$return = bp_nouveau()->blogs->group_buttons->get( $sort );

		if ( ! $return ) {
			return array();
		}

		/**
		 * Leave a chance to adjust the $return
		 *
		 * @since 1.0.0
		 *
		 * @param array  $return  The list of buttons ordered.
		 * @param object $blog    The current blog object.
		 * @param string $type    Whether we're displaying a blogs loop or a the blogs single item (in the future!).
		 */
		do_action_ref_array( 'bp_nouveau_return_blogs_buttons', array( &$return, $blog, $type ) );

		return $return;
	}
