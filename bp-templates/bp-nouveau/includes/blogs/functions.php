<?php
/**
 * Blogs functions
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function bp_nouveau_get_blogs_directory_nav_items() {
	$nav_items = array();

	$nav_items['all'] = array(
		'component' => 'blogs',
		'slug'      => 'all', // slug is used because BP_Core_Nav requires it, but it's the scope
		'li_class'  => array( 'selected' ),
		'link'      => bp_get_root_domain() . '/' . bp_get_blogs_root_slug(),
		'title'     => __( 'The public sites of this network.', 'bp-nouveau' ),
		'text'      => __( 'All Sites', 'bp-nouveau' ),
		'count'     => bp_get_total_blog_count(),
		'position'  => 5,
	);

	if ( is_user_logged_in() ) {

		$my_blogs_count = bp_get_total_blog_count_for_user( bp_loggedin_user_id() );

		// If the user has blogs create a nav item
		if ( $my_blogs_count ) {
			$nav_items['personal'] = array(
				'component' => 'blogs',
				'slug'      => 'personal', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array(),
				'link'      => bp_loggedin_user_domain() . bp_get_blogs_slug(),
				'title'     => __( 'The sites I\'m a contributor of.', 'bp-nouveau' ),
				'text'      => __( 'My Sites', 'bp-nouveau' ),
				'count'     => $my_blogs_count,
				'position'  => 15,
			);
		}

		// If the user can create blogs, add the create nav
		if ( bp_blog_signup_enabled() ) {
			$nav_items['create'] = array(
				'component' => 'blogs',
				'slug'      => 'create', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array( 'no-ajax' ),
				'link'      => trailingslashit( bp_get_blogs_directory_permalink() . 'create' ),
				'title'     => __( 'Create a Site', 'bp-nouveau' ),
				'text'      => __( 'Create a Site', 'bp-nouveau' ),
				'count'     => false,
				'position'  => 999,
			);
		}
	}

	// Check for the deprecated hook :
	$extra_nav_items = bp_nouveau_parse_hooked_dir_nav( 'bp_blogs_directory_blog_types', 'blogs', 20 );

	if ( ! empty( $extra_nav_items ) ) {
		$nav_items = array_merge( $nav_items, $extra_nav_items );
	}

	/**
	 * Use this filter to introduce your custom nav items for the blogs directory.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $nav_items The list of the blogs directory nav items.
	 */
	return apply_filters( 'bp_nouveau_get_blogs_directory_nav_items', $nav_items );
}

/**
 * Get Dropdown filters for the blogs component
 *
 * @since 1.0.0
 *
 * @param string $context 'directory' or 'user'
 * @return array the filters
 */
function bp_nouveau_get_blogs_filters( $context = '' ) {
	if ( empty( $context ) ) {
		return array();
	}

	$action = '';
	if ( 'user' === $context ) {
		$action = 'bp_member_blog_order_options';
	} elseif ( 'directory' === $context ) {
		$action = 'bp_blogs_directory_order_options';
	}

	/**
	 * Recommended, filter here instead of adding an action to 'bp_member_blog_order_options'
	 * or 'bp_blogs_directory_order_options'
	 *
	 * @since 1.0.0
	 *
	 * @param array  the blogs filters.
	 * @param string the context.
	 */
	$filters = apply_filters( 'bp_nouveau_get_blogs_filters', array(
		'active'       => __( 'Last Active', 'bp-nouveau' ),
		'newest'       => __( 'Newest', 'bp-nouveau' ),
		'alphabetical' => __( 'Alphabetical', 'bp-nouveau' ),
	), $context );

	if ( $action ) {
		return bp_nouveau_parse_hooked_options( $action, $filters );
	}

	return $filters;
}

/**
 * Catch the arguments for buttons
 *
 * @since 1.0.0
 *
 * @param  array $buttons The arguments of the button that BuddyPress is about to create.
 * @return array An empty array to stop the button creation process.
 */
function bp_nouveau_blogs_catch_button_args( $button = array() ) {
	/**
	 * Globalize the arguments so that we can use it
	 * in bp_nouveau_get_blogs_buttons().
	 */
	bp_nouveau()->blogs->button_args = $button;

	// return an empty array to stop the button creation process
	return array();
}

/**
 * Add settings to the customizer for the blogs component.
 *
 * @since 1.0.0
 *
 * @param  array $settings the settings to add.
 * @return array           the settings to add.
 */
function bp_nouveau_blogs_customizer_settings( $settings = array() ) {
	return array_merge( $settings, array(
		'bp_nouveau_appearance[blogs_layout]' => array(
			'index'             => 'blogs_layout',
			'capability'        => 'bp_moderate',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
			'type'              => 'option',
		),
	) );
}

/**
 * Add controls for the settings of the customizer for the blogs component.
 *
 * @since 1.0.0
 *
 * @param  array $controls the controls to add.
 * @return array           the controls to add.
 */
function bp_nouveau_blogs_customizer_controls( $controls = array() ) {
	return array_merge( $controls, array(
		'blogs_layout' => array(
			'label'      => __( 'Blogs loop:', 'bp-nouveau' ),
			'section'    => 'bp_nouveau_loops_layout',
			'settings'   => 'bp_nouveau_appearance[blogs_layout]',
			'type'       => 'select',
			'choices'    => array(
				'1' => __( 'One column', 'bp-nouveau' ),
				'2' => __( 'Two columns', 'bp-nouveau' ),
				'3' => __( 'Three columns', 'bp-nouveau' ),
			),
		),
	) );
}
