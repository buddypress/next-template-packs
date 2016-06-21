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

/**
 * Localize the strings needed for the Group's Invite UI
 *
 * @since  1.0.0
 *
 * @param  array  $params Associative array containing the JS Strings needed by scripts
 * @return array          The same array with specific strings for the Group's Invite UI if needed.
 */
function bp_nouveau_groups_localize_scripts( $params = array() ) {
	if ( ! bp_is_group_invites() && ! ( bp_is_group_create() && bp_is_group_creation_step( 'group-invites' ) ) ) {
		return $params;
	}

	$show_pending = bp_group_has_invites( array( 'user_id' => 'any' ) ) && ! bp_is_group_create();

	// Init the Group invites nav
	$invites_nav = array(
		'members' => array( 'id' => 'members', 'caption' => __( 'All Members', 'bp-nouveau' ), 'order' => 0 ),
		'invited' => array( 'id' => 'invited', 'caption' => __( 'Pending Invites', 'bp-nouveau' ), 'order' => 90, 'hide' => (int) ! $show_pending ),
		'invites' => array( 'id' => 'invites', 'caption' => __( 'Send invites', 'bp-nouveau' ), 'order' => 100, 'hide' => 1 ),
	);

	if ( bp_is_active( 'friends' ) ) {
		$invites_nav['friends'] = array( 'id' => 'friends', 'caption' => __( 'My friends', 'bp-nouveau' ), 'order' => 5 );
	}

	$params['group_invites'] = array(
		'nav'                => bp_sort_by_key( $invites_nav, 'order', 'num' ),
		'loading'            => __( 'Loading members, please wait.', 'bp-nouveau' ),
		'invites_form'       => __( 'Use the "Send" button to send your invite, or the "Cancel" button to abort.', 'bp-nouveau' ),
		'invites_form_reset' => __( 'Invites cleared, please use one of the available tabs to select members to invite.', 'bp-nouveau' ),
		'invites_sending'    => __( 'Sending the invites, please wait.', 'bp-nouveau' ),
		'group_id'           => ! bp_get_current_group_id() ? bp_get_new_group_id() : bp_get_current_group_id(),
		'is_group_create'    => bp_is_group_create(),
		'nonces'             => array(
			'uninvite'     => wp_create_nonce( 'groups_invite_uninvite_user' ),
			'send_invites' => wp_create_nonce( 'groups_send_invites' )
		),
	);

	return $params;
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

/**
 * Add sections to the customizer for the groups component.
 *
 * @since 1.0.0
 *
 * @param  array $sections the Customizer sections to add.
 * @return array           the Customizer sections to add.
 */
function bp_nouveau_groups_customizer_sections( $sections = array() ) {
	return array_merge( $sections, array(
		'bp_nouveau_group_front_page' => array(
			'title'       => __( 'Group\'s front page', 'bp-nouveau' ),
			'panel'       => 'bp_nouveau_panel',
			'priority'    => 10,
			'description' => __( 'Activate or deactivate the default front page for your groups.', 'bp-nouveau' ),
		),
	) );
}

/**
 * Add settings to the customizer for the groups component.
 *
 * @since 1.0.0
 *
 * @param  array $settings the settings to add.
 * @return array           the settings to add.
 */
function bp_nouveau_groups_customizer_settings( $settings = array() ) {
	return array_merge( $settings, array(
		'bp_nouveau_appearance[group_front_page]' => array(
			'index'             => 'group_front_page',
			'capability'        => 'bp_moderate',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
			'type'              => 'option',
		),
	) );
}

/**
 * Add controls for the settings of the customizer for the groups component.
 *
 * @since 1.0.0
 *
 * @param  array $controls the controls to add.
 * @return array           the controls to add.
 */
function bp_nouveau_groups_customizer_controls( $controls = array() ) {
	return array_merge( $controls, array(
		'group_front_page' => array(
			'label'      => __( 'Enable default front page for groups.', 'bp-nouveau' ),
			'section'    => 'bp_nouveau_group_front_page',
			'settings'   => 'bp_nouveau_appearance[group_front_page]',
			'type'       => 'checkbox',
		),
	) );
}

/**
 * Add the default group front template to the front template hierarchy.
 *
 * @since  1.0.0
 *
 * @param  array  $templates The list of templates for the front.php template part.
 * @return array  The same list with the default front template if needed.
 */
function bp_nouveau_group_reset_front_template( $templates = array() ) {
	$group = groups_get_current_group();

	if ( empty( $group->id ) ) {
		return $templates;
	}

	/**
	 * So 2.6 forgot to update the Group's front hierarchy so that it includes the Group Type.
	 * This filter will be removed when https://buddypress.trac.wordpress.org/ticket/7129 will
	 * be fixed.
	 */
	if ( bp_groups_get_group_types() ) {
		$group_type = bp_groups_get_group_type( $group->id );
		if ( ! $group_type ) {
			$group_type = 'none';
		}

		$group_type_template = 'groups/single/front-group-type-' . sanitize_file_name( $group_type )   . '.php';

		// Insert the group type template if not in the hierarchy
		if ( ! in_array( $group_type_template, $templates ) ) {
			array_splice( $templates, 2, 0, array( $group_type_template ) );
		}
	}

	$use_default_front = bp_nouveau_get_appearance_settings( 'group_front_page' );

	// Setting the front template happens too early, so we need this!
	if ( is_customize_preview() ) {
		$use_default_front = bp_nouveau_get_temporary_setting( 'group_front_page', $use_default_front );
	}

	if ( ! empty( $use_default_front ) ) {
		array_push( $templates, 'groups/single/default-front.php' );
	}

	return $templates;
}

/**
 * Locate a single group template into a specific hierarchy.
 *
 * @since  1.0.0
 *
 * @param  string $template The template part to get (eg: activity, members...).
 * @return string The located template.
 */
function bp_nouveau_group_locate_template_part( $template = '' ) {
	$current_group = groups_get_current_group();
	$bp_nouveau     = bp_nouveau();

	if ( ! $template || empty( $current_group->id ) ) {
		return false;
	}

	// Use a global to avoid requesting the hierarchy for each template
	if ( ! isset( $bp_nouveau->groups->current_group_hierarchy ) ) {
		$bp_nouveau->groups->current_group_hierarchy = array(
			'groups/single/%s-id-' . sanitize_file_name( $current_group->id ) . '.php',
			'groups/single/%s-slug-' . sanitize_file_name( $current_group->slug ) . '.php',
		);

		/**
		 * Check for group types and add it to the hierarchy
		 */
		if ( bp_groups_get_group_types() ) {
			$current_group_type = bp_groups_get_group_type( $current_group->id );
			if ( ! $current_group_type ) {
				$current_group_type = 'none';
			}

			$bp_nouveau->groups->current_group_hierarchy[] = 'groups/single/%s-group-type-' . sanitize_file_name( $current_group_type )   . '.php';
		}

		$bp_nouveau->groups->current_group_hierarchy = array_merge( $bp_nouveau->groups->current_group_hierarchy, array(
			'groups/single/%s-status-' . sanitize_file_name( $current_group->status ) . '.php',
			'groups/single/%s.php'
		) );
	}

	// Init the templates
	$templates = array();

	// Loop in the hierarchy to fill it for the requested template part
	foreach ( $bp_nouveau->groups->current_group_hierarchy as $part ) {
		$templates[] = sprintf( $part, $template );
	}

	return bp_locate_template( apply_filters( 'bp_nouveau_group_locate_template_part', $templates ), false, true );
}

/**
 * Load a single group template part
 *
 * @since  1.0.0
 *
 * @param  string $template The template part to get (eg: activity, members...).
 * @return string HTML output.
 */
function bp_nouveau_group_get_template_part( $template = '' ) {
	$located = bp_nouveau_group_locate_template_part( $template );

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
