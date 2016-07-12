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
			'file'         => 'js/buddypress-group-invites%s.js',
			'dependencies' => array( 'bp-nouveau', 'json2', 'wp-backbone' ),
			'footer'       => true,
		),
	) );
}

/**
 * Enqueue the groups scripts
 *
 * @since 1.0.0
 */
function bp_nouveau_groups_enqueue_scripts() {
	// Neutralize Ajax when using BuddyPress Groups & member widgets on default front page
	if ( bp_is_group_home() && bp_nouveau_get_appearance_settings( 'group_front_page' ) ) {
		wp_add_inline_style( 'bp-nouveau', '
			#group-front-widgets #groups-list-options,
			#group-front-widgets #members-list-options {
				display: none;
			}
		' );
	}

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

// I don't see any reason why to restrict group invites to friends..
function bp_nouveau_group_invites_create_steps( $steps = array() ) {
	if ( bp_is_active( 'friends' ) && isset( $steps['group-invites'] ) ) {
		// Simply change the name
		$steps['group-invites']['name'] = _x( 'Invite',  'Group screen nav', 'bp-nouveau' );
		return $steps;
	}

	// Add the create step if friends component is not active
	$steps['group-invites'] = array(
		'name'     => _x( 'Invite',  'Group screen nav', 'bp-nouveau' ),
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

		$bp->groups->nav->edit_nav(
			array( 'name' => _x( 'Invite', 'My Group screen nav', 'bp-nouveau' ) ),
			'send-invites',
			bp_get_current_group_slug()
		);

	// Create the Subnav item for the group
	} else {
		$current_group = groups_get_current_group();
		$group_link    = bp_get_group_permalink( $current_group );

		bp_core_new_subnav_item( array(
			'name'            => _x( 'Invite', 'My Group screen nav', 'bp-nouveau' ),
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

	// Check for the deprecated hook :
	$extra_nav_items = bp_nouveau_parse_hooked_dir_nav( 'bp_groups_directory_group_filter', 'groups', 20 );

	if ( ! empty( $extra_nav_items ) ) {
		$nav_items = array_merge( $nav_items, $extra_nav_items );
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
 * Catch the arguments for buttons
 *
 * @since 1.0.0
 *
 * @param  array $button The arguments of the button that BuddyPress is about to create.
 *
 * @return array An empty array to stop the button creation process.
 */
function bp_nouveau_groups_catch_button_args( $button = array() ) {
	/**
	 * Globalize the arguments so that we can use it
	 * in bp_nouveau_get_groups_buttons().
	 */
	bp_nouveau()->groups->button_args = $button;

	// return an empty array to stop the button creation process
	return array();
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
 * Display the Widgets of Group extensions into the default front page?
 *
 * @since  1.0.0
 *
 * @return bool True to display. False otherwise.
 */
function bp_nouveau_groups_do_group_boxes() {
	$group_settings = bp_nouveau_get_appearance_settings();

	return ! empty( $group_settings['group_front_page'] ) && ! empty( $group_settings['group_front_boxes'] );
}

/**
 * Display description of the Group into the default front page?
 *
 * @since  1.0.0
 *
 * @return bool True to display. False otherwise.
 */
function bp_nouveau_groups_front_page_description() {
	$group_settings = bp_nouveau_get_appearance_settings();

	return ! empty( $group_settings['group_front_page'] ) && ! empty( $group_settings['group_front_description'] );
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
			'priority'    => 20,
			'description' => __( 'Set your preferences for the groups default front page.', 'bp-nouveau' ),
		),
		'bp_nouveau_group_nav_order' => array(
			'title'       => __( 'Group\'s navigation', 'bp-nouveau' ),
			'panel'       => 'bp_nouveau_panel',
			'priority'    => 40,
			'description' => __( 'Set the order for the groups navigation items.', 'bp-nouveau' ),
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
		'bp_nouveau_appearance[group_front_boxes]' => array(
			'index'             => 'group_front_boxes',
			'capability'        => 'bp_moderate',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
			'type'              => 'option',
		),
		'bp_nouveau_appearance[group_front_description]' => array(
			'index'             => 'group_front_description',
			'capability'        => 'bp_moderate',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
			'type'              => 'option',
		),
		'bp_nouveau_appearance[group_nav_order]' => array(
			'index'             => 'group_nav_order',
			'capability'        => 'bp_moderate',
			'sanitize_callback' => 'bp_nouveau_sanitize_nav_order',
			'transport'         => 'refresh',
			'type'              => 'option',
		),
		'bp_nouveau_appearance[groups_layout]' => array(
			'index'             => 'groups_layout',
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
		'group_front_boxes' => array(
			'label'      => __( 'Enable Group extensions widgets.', 'bp-nouveau' ),
			'section'    => 'bp_nouveau_group_front_page',
			'settings'   => 'bp_nouveau_appearance[group_front_boxes]',
			'type'       => 'checkbox',
		),
		'group_front_description' => array(
			'label'      => __( 'Display the Group\'s description in the front page body.', 'bp-nouveau' ),
			'section'    => 'bp_nouveau_group_front_page',
			'settings'   => 'bp_nouveau_appearance[group_front_description]',
			'type'       => 'checkbox',
		),
		'group_nav_order' => array(
			'class'       => 'BP_Nouveau_Nav_Customize_Control',
			'label'      => __( 'Reorder the Groups single items primary navigation.', 'bp-nouveau' ),
			'section'    => 'bp_nouveau_group_nav_order',
			'settings'   => 'bp_nouveau_appearance[group_nav_order]',
			'type'       => 'group',
		),
		'groups_layout' => array(
			'label'      => __( 'Groups loop:', 'bp-nouveau' ),
			'section'    => 'bp_nouveau_loops_layout',
			'settings'   => 'bp_nouveau_appearance[groups_layout]',
			'type'       => 'select',
			'choices'    => array(
				'1' => __( 'One column', 'bp-nouveau' ),
				'2' => __( 'Two columns', 'bp-nouveau' ),
				'3' => __( 'Three columns', 'bp-nouveau' ),
			),
		),
	) );
}

/**
 * Add the default group front template to the front template hierarchy.
 *
 * @since  1.0.0
 *
 * @param  array           $templates The list of templates for the front.php template part.
 * @param  BP_Groups_Group The group object.
 * @return array           The same list with the default front template if needed.
 */
function bp_nouveau_group_reset_front_template( $templates = array(), $group = null ) {
	if ( empty( $group->id ) ) {
		return $templates;
	}

	$use_default_front = bp_nouveau_get_appearance_settings( 'group_front_page' );

	// Setting the front template happens too early, so we need this!
	if ( is_customize_preview() ) {
		$use_default_front = bp_nouveau_get_temporary_setting( 'group_front_page', $use_default_front );
	}

	if ( ! empty( $use_default_front ) ) {
		array_push( $templates, 'groups/single/default-front.php' );
	}

	return apply_filters( '_bp_nouveau_group_reset_front_template', $templates );
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

/**
 * Are we inside the Current group's default front page sidebar?
 *
 * @since  1.0.0
 *
 * @return bool True if in the group's home sidebar. False otherwise.
 */
function bp_nouveau_group_is_home_widgets() {
	return true === bp_nouveau()->groups->is_group_home_sidebar;
}

/**
 * Filter the Latest activities Widget to only keep the one of the group displayed
 *
 * @since  1.0.0
 *
 * @param  array  $args The Activities Template arguments.
 * @return array        The Activities Template arguments.
 */
function bp_nouveau_group_activity_widget_overrides( $args = array() ) {
	return array_merge( $args, array(
		'object'     => 'groups',
		'primary_id' => bp_get_current_group_id(),
	) );
}

/**
 * Filter the Groups widget to only keep the displayed group.
 *
 * @since  1.0.0
 *
 * @param  array  $args The Groups Template arguments.
 * @return array        The Groups Template arguments.
 */
function bp_nouveau_group_groups_widget_overrides( $args = array() ) {
	return array_merge( $args, array(
		'include' => bp_get_current_group_id(),
	) );
}

/**
 * Filter the Members widgets to only keep members of the displayed group.
 *
 * @since  1.0.0
 *
 * @param  array  $args The Members Template arguments.
 * @return array        The Members Template arguments.
 */
function bp_nouveau_group_members_widget_overrides( $args = array() ) {
	$group_members = groups_get_group_members( array( 'exclude_admins_mods' => false ) );

	if ( empty( $group_members['members'] ) ) {
		return $args;
	}

	return array_merge( $args, array(
		'include' => wp_list_pluck( $group_members['members'], 'ID' ),
	) );
}

/**
 * Init the Group's default front page filters as we're in the sidebar
 *
 * @since  1.0.0
 */
function bp_nouveau_groups_add_home_widget_filters() {
	add_filter( 'bp_nouveau_activity_widget_query', 'bp_nouveau_group_activity_widget_overrides', 10, 1 );
	add_filter( 'bp_before_has_groups_parse_args',  'bp_nouveau_group_groups_widget_overrides',   10, 1 );
	add_filter( 'bp_before_has_members_parse_args', 'bp_nouveau_group_members_widget_overrides',  10, 1 );

	do_action( 'bp_nouveau_groups_add_home_widget_filters' );
}

/**
 * Remove the Group's default front page filters as we're no more in the sidebar
 *
 * @since  1.0.0
 */
function bp_nouveau_groups_remove_home_widget_filters() {
	remove_filter( 'bp_nouveau_activity_widget_query', 'bp_nouveau_group_activity_widget_overrides', 10, 1 );
	remove_filter( 'bp_before_has_groups_parse_args',  'bp_nouveau_group_groups_widget_overrides',   10, 1 );
	remove_filter( 'bp_before_has_members_parse_args', 'bp_nouveau_group_members_widget_overrides',  10, 1 );

	do_action( 'bp_nouveau_groups_remove_home_widget_filters' );
}

/**
 * Get the hook, nonce, and eventually a specific template for Core Group's create screens.
 *
 * @since  1.0.0
 *
 * @param  string $id  The screen id
 * @return mixed       An array containing the hook dynamic part, the nonce, and eventually a specific template.
 *                     False if it's not a core create screen.
 */
function bp_nouveau_group_get_core_create_screens( $id = '' ) {
	/**
	 * screen id => dynamic part of the hooks, nonce & specific template to use.
	 */
	$screens = array(
		'group-details'     => array( 'hook' => 'group_details_creation_step',     'nonce' => 'groups_create_save_group-details',     'template' => 'groups/single/admin/edit-details' ),
		'group-settings'    => array( 'hook' => 'group_settings_creation_step',    'nonce' => 'groups_create_save_group-settings',                                                     ),
		'group-avatar'      => array( 'hook' => 'group_avatar_creation_step',      'nonce' => 'groups_create_save_group-avatar',                                                       ),
		'group-cover-image' => array( 'hook' => 'group_cover_image_creation_step', 'nonce' => 'groups_create_save_group-cover-image',                                                  ),
		'group-invites'     => array( 'hook' => 'group_invites_creation_step',     'nonce' => 'groups_create_save_group-invites',     'template' => '_accessoires/invites/index'       ),
	);

	if ( isset( $screens[ $id ] ) ) {
		return $screens[ $id ];
	}

	return false;
}

/**
 * Get the hook and nonce for Core Group's manage screens.
 *
 * @since  1.0.0
 *
 * @param  string $id  The screen id
 * @return mixed       An array containing the hook dynamic part and the nonce. False if it's not a core manage screen.
 */
function bp_nouveau_group_get_core_manage_screens( $id = '' ) {
	/**
	 * screen id => dynamic part of the hooks & nonce.
	 */
	$screens = array(
		'edit-details'        => array( 'hook' => 'group_details_admin',             'nonce' => 'groups_edit_group_details'  ),
		'group-settings'      => array( 'hook' => 'group_settings_admin',            'nonce' => 'groups_edit_group_settings' ),
		'group-avatar'        => array(                                                                                      ),
		'group-cover-image'   => array( 'hook' => 'group_settings_cover_image',      'nonce' => ''                           ),
		'manage-members'      => array( 'hook' => 'group_manage_members_admin',      'nonce' => ''                           ),
		'membership-requests' => array( 'hook' => 'group_membership_requests_admin', 'nonce' => ''                           ),
		'delete-group'        => array( 'hook' => 'group_delete_admin',              'nonce' => 'groups_delete_group'        ),
	);

	if ( isset( $screens[ $id ] ) ) {
		return $screens[ $id ];
	}

	return false;
}

/**
 * Register notifications filters for the groups component.
 *
 * @since 1.0.0
 */
function bp_nouveau_groups_notification_filters() {
	$notifications = array(
		array(
			'id'       => 'new_membership_request',
			'label'    => __( 'Pending Group membership requests', 'bp-nouveau' ),
			'position' => 55,
		),
		array(
			'id'       => 'membership_request_accepted',
			'label'    => __( 'Accepted Group membership requests', 'bp-nouveau' ),
			'position' => 65,
		),
		array(
			'id'       => 'membership_request_rejected',
			'label'    => __( 'Rejected Group membership requests', 'bp-nouveau' ),
			'position' => 75,
		),
		array(
			'id'       => 'member_promoted_to_admin',
			'label'    => __( 'Group Admin promotions', 'bp-nouveau' ),
			'position' => 85,
		),
		array(
			'id'       => 'member_promoted_to_mod',
			'label'    => __( 'Group Mod promotions', 'bp-nouveau' ),
			'position' => 95,
		),
		array(
			'id'       => 'group_invite',
			'label'    => __( 'Group invites', 'bp-nouveau' ),
			'position' => 105,
		),
	);

	foreach ( $notifications as $notification ) {
		bp_nouveau_notifications_register_filter( $notification );
	}
}
