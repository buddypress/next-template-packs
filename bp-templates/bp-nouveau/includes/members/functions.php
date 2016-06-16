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
