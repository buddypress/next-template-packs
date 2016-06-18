<?php
/**
 * Groups functions
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register Scripts for the Groups component
 *
 * @since  1.0.0
 *
 * @param  array  $scripts  The array of scripts to register
 * @return array  The same array with the specific groups scripts.
 */
function bp_nouveau_groups_register_scripts( $scripts = array() ) {
	if ( ! isset( $scripts['bp-nouveau'] ) ) {
		return $scripts;
	}

	return array_merge( $scripts, array(
		'bp-nouveau-group-invites' => array(
			'file' => 'js/buddypress-group-invites%s.js', 'dependencies' => array( 'bp-nouveau', 'json2', 'wp-backbone' ), 'footer' => true,
		),
	) );
}

/**
 * Enqueue the groups scripts
 *
 * @since 1.0.0
 */
function bp_nouveau_groups_enqueue_scripts() {
	if ( ! bp_is_group_invites() && ! ( bp_is_group_create() && bp_is_group_creation_step( 'group-invites' ) ) ) {
		return;
	}

	wp_enqueue_script( 'bp-nouveau-group-invites' );
}

function bp_nouveau_groups_get_inviter_ids( $user_id, $group_id ) {
	if ( empty( $user_id ) || empty( $group_id ) ) {
		return false;
	}

	return BP_Nouveau_Group_Invite_Query::get_inviter_ids( $user_id, $group_id );
}

function bp_nouveau_prepare_group_potential_invites_for_js( $user ) {
	$bp = buddypress();

	$response = array(
		'id'           => intval( $user->ID ),
		'name'         => $user->display_name,
		'avatar'       => htmlspecialchars_decode( bp_core_fetch_avatar( array(
			'item_id' => $user->ID,
			'object'  => 'user',
			'type'    => 'thumb',
			'width'   => 50,
			'height'  => 50,
			'html'    => false )
		) ),
	);

	// Do extra queries only if needed
	if ( ! empty( $bp->groups->invites_scope ) && 'invited' === $bp->groups->invites_scope ) {
		$response['is_sent']  = (bool) groups_check_user_has_invite( $user->ID, bp_get_current_group_id() );

		$inviter_ids = bp_nouveau_groups_get_inviter_ids( $user->ID, bp_get_current_group_id() );

		foreach ( $inviter_ids as $inviter_id ) {
			$class = false;

			if ( bp_loggedin_user_id() === (int) $inviter_id ) {
				$class = 'group-self-inviter';
			}

			$response['invited_by'][] = array(
				'avatar' => htmlspecialchars_decode( bp_core_fetch_avatar( array(
					'item_id' => $inviter_id,
					'object'  => 'user',
					'type'    => 'thumb',
					'width'   => 50,
					'height'  => 50,
					'html'    => false,
					'class'   => $class,
				) ) ),
				'user_link' => bp_core_get_userlink( $inviter_id, false, true ),
				'user_name' => bp_core_get_username( $inviter_id ),
			);
		}

		if ( bp_is_item_admin() ) {
			$response['can_edit'] = true;
		} else {
			$response['can_edit'] = in_array( bp_loggedin_user_id(), $inviter_ids );
		}
	}

	return apply_filters( 'bp_nouveau_prepare_group_potential_invites_for_js', $response, $user );
}

function bp_nouveau_get_group_potential_invites( $args = array() ) {
	$r = bp_parse_args( $args, array(
		'group_id'     => bp_get_current_group_id(),
		'type'         => 'alphabetical',
		'per_page'     => 20,
		'page'         => 1,
		'search_terms' => false,
		'member_type'  => false,
		'user_id'      => 0,
		'is_confirmed' => true,
	) );

	if ( empty( $r['group_id'] ) ) {
		return false;
	}

	$query = new BP_Nouveau_Group_Invite_Query( $r );

	$response = new stdClass();

	$response->meta = array( 'total_page' => 0, 'current_page' => 0 );
	$response->users = array();

	if ( ! empty( $query->results ) ) {
		$response->users = $query->results;

		if ( ! empty( $r['per_page'] ) ) {
			$response->meta = array(
				'total_page'   => ceil( (int) $query->total_users / (int) $r['per_page'] ),
				'page' => (int) $r['page'],
			);
		}
	}

	return $response;
}

/**
 * Groups search form
 *
 * @todo Remove as there's now the common/search/dir-search-form.php ?
 */
function bp_nouveau_directory_groups_search_form( $search_form_html = '' ) {

	$query_arg   = bp_core_get_component_search_query_arg( 'groups' );
	$placeholder = bp_get_search_default_text( 'groups' );

	$search_form_html = '<form action="" method="get" id="search-groups-form">
		<label for="groups_search"><input type="text" name="' . esc_attr( $query_arg ) . '" id="groups_search" placeholder="'. esc_attr( $placeholder ) .'" /></label>
		<input type="submit" id="groups_search_submit" name="groups_search_submit" value="'. __( 'Search', 'bp-nouveau' ) .'" />
	</form>';

	/**
	 * Filters the HTML markup for the groups search form.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_form_html HTML markup for the search form.
	 */
	echo apply_filters( 'bp_nouveau_directory_groups_search_form', $search_form_html );

}
add_filter( 'bp_directory_groups_search_form', 'bp_nouveau_directory_groups_search_form', 10, 1 );

// I don't see any reason why to restrict group invites to friends..
function bp_nouveau_group_invites_create_steps( $steps = array() ) {
	if ( bp_is_active( 'friends' ) && isset( $steps['group-invites'] ) ) {
		// Simply change the name
		$steps['group-invites']['name'] = _x( 'Invites',  'Group screen nav', 'bp-nouveau' );
		return $steps;
	}

	// Add the create step if friends component is not active
	$steps['group-invites'] = array(
		'name'     => _x( 'Invites',  'Group screen nav', 'bp-nouveau' ),
		'position' => 30
	);

	return $steps;
}

function bp_nouveau_group_setup_nav() {
	if ( ! bp_is_group() || ! bp_groups_user_can_send_invites() ) {
		return;
	}

	// Simply change the name
	if ( bp_is_active( 'friends' ) ) {
		$bp = buddypress();

		/**
		 * Since BuddyPress 2.6.0
		 *
		 * Direct access to bp_nav or bp_options_nav is deprecated.
		 */
		if ( class_exists( 'BP_Core_Nav' ) ) {
			$bp->groups->nav->edit_nav(
				array( 'name' => _x( 'Invites', 'My Group screen nav', 'bp-nouveau' ) ),
				'send-invites',
				bp_get_current_group_slug()
			);

		// We shouldn't do backcompat and bump BuddyPress required version to 2.6
		} else {
			if ( isset( $bp->bp_options_nav[ bp_get_current_group_slug() ]['send-invites'] ) ) {
				$bp->bp_options_nav[ bp_get_current_group_slug() ]['send-invites']['name'] = _x( 'Invites', 'My Group screen nav', 'bp-nouveau' );
			}
		}

	// Create the Subnav item for the group
	} else {
		$current_group = groups_get_current_group();
		$group_link    = bp_get_group_permalink( $current_group );

		bp_core_new_subnav_item( array(
			'name'            => _x( 'Invites', 'My Group screen nav', 'bp-nouveau' ),
			'slug'            => 'send-invites',
			'parent_url'      => $group_link,
			'parent_slug'     => $current_group->slug,
			'screen_function' => 'groups_screen_group_invite',
			'item_css_id'     => 'invite',
			'position'        => 70,
			'user_has_access' => $current_group->user_has_access,
			'no_access_url'   => $group_link,
		) );
	}
}

if ( ! function_exists( 'bp_group_accept_invite_button' ) ) :

function bp_group_accept_invite_button( $group = false ) {
	echo bp_get_group_accept_invite_button( $group );
}

endif;

if ( ! function_exists( 'bp_get_group_accept_invite_button' ) ) :

function bp_get_group_accept_invite_button( $group = false ) {
	global $groups_template;

	// Set group to current loop group if none passed
	if ( empty( $group ) ) {
		$group =& $groups_template->group;
	}

	// Don't show button if not logged in or previously banned
	if ( ! is_user_logged_in() || bp_group_is_user_banned( $group ) ) {
		return false;
	}

	// Group creation was not completed or status is unknown
	if ( empty( $group->status ) ) {
		return false;
	}

	// Setup button attributes
	$button = array(
		'id'                => 'accept_invite',
		'component'         => 'groups',
		'must_be_logged_in' => true,
		'block_self'        => false,
		'wrapper'           => false,
		'link_href'         => bp_get_group_accept_invite_link(),
		'link_text'         => __( 'Accept', 'bp-nouveau' ),
		'link_title'        => __( 'Accept', 'bp-nouveau' ),
		'link_class'        => 'button accept group-button accept-invite',
	);

	return bp_get_button( apply_filters( 'bp_get_group_accept_invite_button', $button, $group ) );
}

endif;

if ( ! function_exists( 'bp_group_reject_invite_button' ) ) :

function bp_group_reject_invite_button( $group = false ) {
	echo bp_get_group_reject_invite_button( $group );
}

endif;

if ( ! function_exists( 'bp_get_group_reject_invite_button' ) ) :

function bp_get_group_reject_invite_button( $group = false ) {
	global $groups_template;

	// Set group to current loop group if none passed
	if ( empty( $group ) ) {
		$group =& $groups_template->group;
	}

	// Don't show button if not logged in or previously banned
	if ( ! is_user_logged_in() || bp_group_is_user_banned( $group ) ) {
		return false;
	}

	// Group creation was not completed or status is unknown
	if ( empty( $group->status ) ) {
		return false;
	}

	// Setup button attributes
	$button = array(
		'id'                => 'reject_invite',
		'component'         => 'groups',
		'must_be_logged_in' => true,
		'block_self'        => false,
		'wrapper'           => false,
		'link_href'         => bp_get_group_reject_invite_link(),
		'link_text'         => __( 'Reject', 'bp-nouveau' ),
		'link_title'        => __( 'Reject', 'bp-nouveau' ),
		'link_class'        => 'button reject group-button reject-invite',
	);

	return bp_get_button( apply_filters( 'bp_get_group_reject_invite_button', $button, $group ) );
}

endif;

function bp_nouveau_groups_invites_custom_message( $message = '' ) {
	if ( empty( $message ) ) {
		return $message;
	}

	$bp = buddypress();

	if ( empty( $bp->groups->invites_message ) ) {
		return $message;
	}

	$message = str_replace( '---------------------', "
---------------------\n
" . $bp->groups->invites_message . "\n
---------------------
	", $message );

	return $message;
}

/**
 * Format a Group for a json reply
 */
function bp_nouveau_prepare_group_for_js( $item ) {
	if ( empty( $item->id ) ) {
		return array();
	}

	$item_avatar_url = bp_core_fetch_avatar( array(
		'item_id'    => $item->id,
		'object'     => 'group',
		'type'       => 'thumb',
		'html'       => false
	) );

	return array(
		'id'          => $item->id,
		'name'        => $item->name,
		'avatar_url'  => $item_avatar_url,
		'object_type' => 'group',
		'is_public'   => 'public' === $item->status,
	);
}

function bp_nouveau_get_groups_directory_nav_items() {
	$nav_items = array();

	$nav_items['all'] = array(
		'component' => 'groups',
		'slug'      => 'all', // slug is used because BP_Core_Nav requires it, but it's the scope
		'li_class'  => array( 'selected' ),
		'link'      => bp_get_groups_directory_permalink(),
		'title'     => __( 'The public and private groups of this site.', 'bp-nouveau' ),
		'text'      => __( 'All Groups', 'bp-nouveau' ),
		'count'     => bp_get_total_group_count(),
		'position'  => 5,
	);

	if ( is_user_logged_in() ) {

		$my_groups_count = bp_get_total_group_count_for_user( bp_loggedin_user_id() );

		// If the user has groups create a nav item
		if ( $my_groups_count ) {
			$nav_items['personal'] = array(
				'component' => 'groups',
				'slug'      => 'personal', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array(),
				'link'      => bp_loggedin_user_domain() . bp_get_groups_slug() . '/my-groups/',
				'title'     => __( 'The groups I\'m a member of.', 'bp-nouveau' ),
				'text'      => __( 'My Groups', 'bp-nouveau' ),
				'count'     => $my_groups_count,
				'position'  => 15,
			);
		}

		// If the user can create groups, add the create nav
		if ( bp_user_can_create_groups() ) {
			$nav_items['create'] = array(
				'component' => 'groups',
				'slug'      => 'create', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array( 'no-ajax' ),
				'link'      => trailingslashit( bp_get_groups_directory_permalink() . 'create' ),
				'title'     => __( 'Create a Group', 'bp-nouveau' ),
				'text'      => __( 'Create a Group', 'bp-nouveau' ),
				'count'     => false,
				'position'  => 999,
			);
		}
	}

	/**
	 * Use this filter to introduce your custom nav items for the groups directory.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $nav_items The list of the groups directory nav items.
	 */
	return apply_filters( 'bp_nouveau_get_groups_directory_nav_items', $nav_items );
}

/**
 * Get Dropdown filters for the groups component
 *
 * @since 1.0.0
 *
 * @param string $context 'directory' or 'user'
 * @return array the filters
 */
function bp_nouveau_get_groups_filters( $context = '' ) {
	if ( empty( $context ) ) {
		return array();
	}

	$action = '';
	if ( 'user' === $context ) {
		$action = 'bp_member_group_order_options';
	} elseif ( 'directory' === $context ) {
		$action = 'bp_groups_directory_order_options';
	}

	/**
	 * Recommended, filter here instead of adding an action to 'bp_member_group_order_options'
	 * or 'bp_groups_directory_order_options'
	 *
	 * @since 1.0.0
	 *
	 * @param array  the members filters.
	 * @param string the context.
	 */
	$filters = apply_filters( 'bp_nouveau_get_groups_filters', array(
		'active'       => __( 'Last Active', 'bp-nouveau' ),
		'popular'      => __( 'Most Members', 'bp-nouveau' ),
		'newest'       => __( 'Newly Created', 'bp-nouveau' ),
		'alphabetical' => __( 'Alphabetical', 'bp-nouveau' ),
	), $context );

	if ( $action ) {
		return bp_nouveau_parse_hooked_options( $action, $filters );
	}

	return $filters;
}

/**
 * Catch the content hooked to the 'bp_group_header_meta' action
 *
 * @since  1.0.0
 *
 * @return string|bool HTML Output if hooked. False otherwise.
 */
function bp_nouveau_get_hooked_group_meta() {
	ob_start();

	/**
	 * Fires after inside the group header item meta section.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_group_header_meta' );

	$output = ob_get_clean();

	if ( ! empty( $output ) ) {
		return $output;
	}

	return false;
}
