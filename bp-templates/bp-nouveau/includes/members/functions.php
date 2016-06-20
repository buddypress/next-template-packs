<?php
/**
 * Members functions
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function bp_nouveau_members_directory_search_form() {
	$query_arg = bp_core_get_component_search_query_arg( 'members' );

	$placeholder = bp_get_search_default_text( 'members' );

	$search_form_html = '<form action="" method="get" id="search-members-form">
		<label for="members_search"><input type="text" name="' . esc_attr( $query_arg ) . '" id="members_search" placeholder="'. esc_attr( $placeholder ) .'" /></label>
		<input type="submit" id="members_search_submit" name="members_search_submit" value="' . __( 'Search', 'bp-nouveau' ) . '" />
	</form>';

	/**
	 * Filters the Members component search form.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_form_html HTML markup for the member search form.
	 */
	echo apply_filters( 'bp_nouveau_members_directory_search_form', $search_form_html );
}

/**
 * Get the nav items for the Members directory
 *
 * @since 1.0.0
 *
 * @return array An associative array of nav items.
 */
function bp_nouveau_get_members_directory_nav_items() {
	$nav_items = array();

	$nav_items['all'] = array(
		'component' => 'members',
		'slug'      => 'all', // slug is used because BP_Core_Nav requires it, but it's the scope
		'li_class'  => array(),
		'link'      => bp_get_members_directory_permalink(),
		'title'     => __( 'The members of your community.', 'bp-nouveau' ),
		'text'      => __( 'All Members', 'bp-nouveau' ),
		'count'     => bp_get_total_member_count(),
		'position'  => 5,
	);

	if ( is_user_logged_in() ) {

		// If friends component is active and the user has friends
		if ( bp_is_active( 'friends' ) && bp_get_total_friend_count( bp_loggedin_user_id() ) ) {
			$nav_items['personal'] = array(
				'component' => 'members',
				'slug'      => 'personal', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array(),
				'link'      => bp_loggedin_user_domain() . bp_get_friends_slug() . '/my-friends/',
				'title'     => __( 'The members I\'m friend with.', 'bp-nouveau' ),
				'text'      => __( 'My Friends', 'bp-nouveau' ),
				'count'     => bp_get_total_friend_count( bp_loggedin_user_id() ),
				'position'  => 15,
			);
		}
	}

	/**
	 * Use this filter to introduce your custom nav items for the members directory.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $nav_items The list of the members directory nav items.
	 */
	return apply_filters( 'bp_nouveau_get_members_directory_nav_items', $nav_items );
}

/**
 * Get Dropdown filters for the members component
 *
 * @since 1.0.0
 *
 * @param string $context 'directory' see comment below
 * @return array the filters
 */
function bp_nouveau_get_members_filters( $context = '' ) {
	/**
	 * The group context is managed in bp_groups_members_template_part()
	 * This was done for backcompat reasons in BP Legacy.
	 *
	 * @todo We should probably bring back this template part into BP Nouveau
	 * as we're building a brand new template pack. (Javascript would be more simple)
	 */
	if ( 'directory' !== $context ) {
		return array();
	}

	$filters = array(
		'active' => __( 'Last Active', 'bp-nouveau' ),
		'newest' => __( 'Newest Registered', 'bp-nouveau' ),
	);

	if ( bp_is_active( 'xprofile' ) ) {
		$filters['alphabetical'] = __( 'Alphabetical', 'bp-nouveau' );
	}

	/**
	 * Recommended, filter here instead of adding an action to 'bp_members_directory_order_options'
	 *
	 * @since 1.0.0
	 *
	 * @param array  the members filters.
	 * @param string the context.
	 */
	$filters = apply_filters( 'bp_nouveau_get_members_filters', $filters, $context );

	return bp_nouveau_parse_hooked_options( 'bp_members_directory_order_options', $filters );
}

/**
 * Catch the arguments for buttons
 *
 * @since 1.0.0
 *
 * @param  array $buttons The arguments of the button that BuddyPress is about to create.
 * @return array An empty array to stop the button creation process.
 */
function bp_nouveau_members_catch_button_args( $button = array() ) {
	/**
	 * Globalize the arguments so that we can use it
	 * in bp_nouveau_get_member_header_buttons().
	 */
	bp_nouveau()->members->button_args = $button;

	// return an empty array to stop the button creation process
	return array();
}

/**
 * Catch the content hooked to the do_action hooks in single member header
 * and in the members loop
 *
 * @since  1.0.0
 *
 * @return string|bool HTML Output if hooked. False otherwise.
 */
function bp_nouveau_get_hooked_member_meta() {
	ob_start();

	if ( ! empty( $GLOBALS['members_template'] ) ) {
		/**
		 * Fires inside the display of a directory member item.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_directory_members_item' );

	// It's the user's header
	} else {
		/**
		 * Fires after the group header actions section.
		 *
		 * If you'd like to show specific profile fields here use:
		 * bp_member_profile_data( 'field=About Me' ); -- Pass the name of the field
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_profile_header_meta' );
	}

	$output = ob_get_clean();

	if ( ! empty( $output ) ) {
		return $output;
	}

	return false;
}

/**
 * Locate a single member template into a specific hierarchy.
 *
 * @since  1.0.0
 *
 * @param  string $template The template part to get (eg: activity, groups...).
 * @return string The located template.
 */
function bp_nouveau_member_locate_template_part( $template = '' ) {
	$displayed_user = bp_get_displayed_user();
	$bp_nouveau     = bp_nouveau();

	if ( ! $template || empty( $displayed_user->id ) ) {
		return false;
	}

	// Use a global to avoid requesting the hierarchy for each template
	if ( ! isset( $bp_nouveau->members->displayed_user_hierarchy ) ) {
		$bp_nouveau->members->displayed_user_hierarchy = array(
			'members/single/%s-id-' . sanitize_file_name( $displayed_user->id ) . '.php',
			'members/single/%s-nicename-' . sanitize_file_name( $displayed_user->userdata->user_nicename ) . '.php',
		);

		/**
		 * Check for member types and add it to the hierarchy
		 *
		 * Make sure to register your member
		 * type using the hook 'bp_register_member_types'
		 */
		if ( bp_get_member_types() ) {
			$displayed_user_member_type = bp_get_member_type( $displayed_user->id );
			if ( ! $displayed_user_member_type ) {
				$displayed_user_member_type = 'none';
			}

			$bp_nouveau->members->displayed_user_hierarchy[] = 'members/single/%s-member-type-' . sanitize_file_name( $displayed_user_member_type )   . '.php';
		}

		// And the regular one
		$bp_nouveau->members->displayed_user_hierarchy[] = 'members/single/%s.php';
	}

	// Init the templates
	$templates = array();

	// Loop in the hierarchy to fill it for the requested template part
	foreach ( $bp_nouveau->members->displayed_user_hierarchy as $part ) {
		$templates[] = sprintf( $part, $template );
	}

	return bp_locate_template( apply_filters( 'bp_nouveau_member_locate_template_part', $templates ), false, true );
}

/**
 * Load a single member template part
 *
 * @since  1.0.0
 *
 * @param  string $template The template part to get (eg: activity, groups...).
 * @return string HTML output.
 */
function bp_nouveau_member_get_template_part( $template = '' ) {
	$located = bp_nouveau_member_locate_template_part( $template );

	if ( false !== $located ) {
		$slug = str_replace( '.php', '', $located );
		$name = null;

		/**
		 * Let plugins adding an action to bp_get_template_part get it from here
		 *
		 * @param string $slug Template part slug requested.
		 * @param string $name Template part name requested.
		 */
		do_action( 'get_template_part_' . $slug, $slug, $name );

		load_template( $located, true );
	}

	return $located;
}
