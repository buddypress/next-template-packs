<?php
/**
 * Common functions
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function bp_nouveau_ajax_button( $output ='', $button = null, $before ='', $after = '' ) {
	if ( empty( $button->component ) ) {
		return $output;
	}

	$data_attribute = $button->id;

	$reset_ids = array(
		'member_friendship' => true,
		'group_membership'  => true,
	);

	if ( ! empty( $reset_ids[ $button->id ] ) )  {
		$parse_class = array_map( 'sanitize_html_class', explode( ' ', $button->link_class ) );

		if ( false === $parse_class ) {
			return $output;
		}

		$find_id = array_intersect( $parse_class, array(
			'pending_friend',
			'is_friend',
			'not_friends',
			'leave-group',
			'join-group',
			'accept-invite',
			'membership-requested',
			'request-membership',
		) );

		if ( 1 !== count( $find_id ) ) {
			return $output;
		}

		$data_attribute = reset( $find_id );

		if ( 'pending_friend' === $data_attribute ) {
			$data_attribute = str_replace( '_friend', '', $data_attribute );

		} elseif ( 'group_membership' === $button->id ) {
			$data_attribute = str_replace( '-', '_', $data_attribute );
		}
	}

	// Add span bp-screen-reader-text class
	return $before . '<a'. $button->link_href . $button->link_title . $button->link_id . $button->link_rel . $button->link_class . ' data-bp-btn-action="' . $data_attribute . '">' . $button->link_text . '</a>' . $after;
}

/**
 * Output HTML content into a wrapper.
 *
 * @since  1.0.0
 *
 * @param  array  $args {
 *     Array of arguments.
 *
 *     @type string      $container         String HTML element type that should wrap
 *                                          the buttons: 'div', 'span', or 'p'. Required.
 *     @type array       $classes           Optional. DOM classes of the button wrapper
 *                                          Default: array ( 'action' ).
 *     @type string      $id                Optional. DOM ID of the button wrapper element.
 *                                          Default: ''.
 *     @type string      $output            The HTML to output. Required.
 * }
 * @return string       HTML Output
 */
function bp_nouveau_wrapper( $args = array() ) {
	$r = wp_parse_args( $args, array(
		'wrapper' => 'div',
		'classes' => array( 'action' ),
		'id'      => '',
		'output'  => '',
	) );

	$valid_wrappers = array(
		'div'  => true,
		'span' => true,
		'p'    => true,
	);

	if ( empty( $r['wrapper'] ) || ! isset( $valid_wrappers[ $r['wrapper'] ] ) || empty( $r['output'] ) ) {
		return;
	}

	$wrapper = $r['wrapper'];
	$id        = '';
	$class     = '';
	$output    = $r['output'];

	if ( ! empty( $r['id'] ) ) {
		$id = ' id="' . esc_attr( $r['id'] ) . '"';
	}

	if ( ! empty( $r['classes'] ) && is_array( $r['classes'] ) ) {
		$class = ' class="' . join( ' ', array_map( 'sanitize_html_class', $r['classes'] ) ) .'"';
	}

	// Print the wrapper and its content.
	printf( '<%1$s%2$s%3$s>%4$s</%1$s>', $wrapper, $id, $class, $output );
}

/**
 * Register the 2 sidebars for the Group & User default front page
 *
 * @since  1.0.0
 */
function bp_nouveau_register_sidebars() {
	$default_fronts      = bp_nouveau_get_appearance_settings();
	$default_user_front  = 0;
	$default_group_front = 0;
	$is_active_groups    = bp_is_active( 'groups' );

	if ( isset( $default_fronts['user_front_page'] ) ) {
		$default_user_front = $default_fronts['user_front_page'];
	}

	if ( $is_active_groups ) {
		if ( isset( $default_fronts['group_front_page'] ) ) {
			$default_group_front = $default_fronts['group_front_page'];
		}
	}

	// Setting the front template happens too early, so we need this!
	if ( is_customize_preview() ) {
		$default_user_front = bp_nouveau_get_temporary_setting( 'user_front_page', $default_user_front );

		if ( $is_active_groups ) {
			$default_group_front = bp_nouveau_get_temporary_setting( 'group_front_page', $default_group_front );
		}
	}

	$sidebars = array();
	if ( $default_user_front ) {
		$sidebars[] = array(
			'name'          => __( 'BuddyPress User\'s Home', 'bp-nouveau' ),
			'id'            => 'sidebar-buddypress-members',
			'description'   => __( 'Add widgets here to appear in the front page of each member of your community.', 'bp-nouveau' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		);
	}

	if ( $default_group_front ) {
		$sidebars[] = array(
			'name'          => __( 'BuddyPress Group\'s Home', 'bp-nouveau' ),
			'id'            => 'sidebar-buddypress-groups',
			'description'   => __( 'Add widgets here to appear in the front page of each group of your community.', 'bp-nouveau' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		);
	}

	if ( empty( $sidebars ) ) {
		return;
	}

	// Register the sidebars if needed.
	foreach ( $sidebars as $sidebar ) {
		register_sidebar( $sidebar );
	}
}

function bp_nouveau_is_object_nav_in_sidebar() {
	return is_active_widget( false, false, 'bp_nouveau_sidebar_object_nav_widget', true );
}

function bp_nouveau_current_user_can( $capability = '' ) {
	return apply_filters( 'bp_nouveau_current_user_can', is_user_logged_in(), $capability, bp_loggedin_user_id() );
}

/**
 * BP Nouveau will not use this hooks anymore
 *
 * @since  1.0.0
 *
 * @return array the list of disused legacy hooks
 */
function bp_nouveau_get_forsaken_hooks() {
	$forsaken_hooks = array(
		'bp_members_directory_member_types' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'the \'bp_members_directory_member_types\' action will soon be deprecated in the BP Nouveau template pack, use the \'bp_nouveau_get_members_directory_nav_items\' filter instead', 'bp-nouveau' ),
		),
		'bp_before_activity_type_tab_all' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'the \'bp_before_activity_type_tab_all\' action will soon be deprecated in the BP Nouveau template pack, use the \'bp_nouveau_get_activity_directory_nav_items\' filter instead', 'bp-nouveau' ),
		),
		'bp_before_activity_type_tab_friends' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'the \'bp_before_activity_type_tab_friends\' action will soon be deprecated in the BP Nouveau template pack, use the \'bp_nouveau_get_activity_directory_nav_items\' filter instead', 'bp-nouveau' ),
		),
		'bp_before_activity_type_tab_groups' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'the \'bp_before_activity_type_tab_groups\' action will soon be deprecated in the BP Nouveau template pack, use the \'bp_nouveau_get_activity_directory_nav_items\' filter instead', 'bp-nouveau' ),
		),
		'bp_before_activity_type_tab_favorites' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'the \'bp_before_activity_type_tab_favorites\' action will soon be deprecated in the BP Nouveau template pack, use the \'bp_nouveau_get_activity_directory_nav_items\' filter instead', 'bp-nouveau' ),
		),
		'bp_before_activity_type_tab_mentions' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'the \'bp_before_activity_type_tab_mentions\' action will soon be deprecated in the BP Nouveau template pack, use the \'bp_nouveau_get_activity_directory_nav_items\' filter instead', 'bp-nouveau' ),
		),
		'bp_activity_type_tabs' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'the \'bp_activity_type_tabs\' action will soon be deprecated in the BP Nouveau template pack, use the \'bp_nouveau_get_activity_directory_nav_items\' filter instead', 'bp-nouveau' ),
		),
		'bp_groups_directory_group_filter' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'the \'bp_groups_directory_group_filter\' action will soon be deprecated in the BP Nouveau template pack, use the \'bp_nouveau_get_activity_directory_nav_items\' filter instead', 'bp-nouveau' ),
		),
		'bp_blogs_directory_blog_types' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'the \'bp_blogs_directory_blog_types\' action will soon be deprecated in the BP Nouveau template pack, use the \'bp_nouveau_get_activity_directory_nav_items\' filter instead', 'bp-nouveau' ),
		),
		'bp_members_directory_order_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_members_directory_order_options\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_members_filters\' filter instead', 'bp-nouveau' ),
		),
		'bp_groups_members_order_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_groups_members_order_options\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_members_filters\' filter instead', 'bp-nouveau' ),
		),
		'bp_member_friends_order_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_member_friends_order_options\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_members_filters\' filter instead', 'bp-nouveau' ),
		),
		'bp_activity_filter_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'Instead of using the \'bp_activity_filter_options\' action you should register your activity types using the function \'bp_activity_set_action\'', 'bp-nouveau' ),
		),
		'bp_member_activity_filter_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'Instead of using the \'bp_member_activity_filter_options\' action you should register your activity types using the function \'bp_activity_set_action\'', 'bp-nouveau' ),
		),
		'bp_group_activity_filter_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'Instead of using the \'bp_group_activity_filter_options\' action you should register your activity types using the function \'bp_activity_set_action\'', 'bp-nouveau' ),
		),
		'bp_groups_directory_order_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_groups_directory_order_options\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_groups_filters\' filter instead', 'bp-nouveau' ),
		),
		'bp_member_group_order_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_member_group_order_options\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_groups_filters\' filter instead', 'bp-nouveau' ),
		),
		'bp_member_blog_order_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_member_blog_order_options\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_blogs_filters\' filter instead', 'bp-nouveau' ),
		),
		'bp_blogs_directory_order_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_blogs_directory_order_options\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_blogs_filters\' filter instead', 'bp-nouveau' ),
		),
		'bp_activity_entry_meta' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_activity_entry_meta\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_activity_entry_buttons\' filter instead', 'bp-nouveau' ),
		),
		'bp_member_header_actions' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_member_header_actions\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_members_buttons\' filter instead', 'bp-nouveau' ),
		),
		'bp_directory_members_actions' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_directory_members_actions\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_members_buttons\' filter instead', 'bp-nouveau' ),
		),
		'bp_group_members_list_item_action' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_group_members_list_item_action\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_members_buttons\' filter instead', 'bp-nouveau' ),
		),
		'bp_friend_requests_item_action' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_friend_requests_item_action\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_members_buttons\' filter instead', 'bp-nouveau' ),
		),
		'bp_group_header_meta' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_group_header_meta\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_group_meta\' filter instead', 'bp-nouveau' ),
		),
		'bp_directory_members_item' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_directory_members_item\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_member_meta\' filter instead', 'bp-nouveau' ),
		),
		'bp_profile_header_meta' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_profile_header_meta\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_member_meta\' filter instead', 'bp-nouveau' ),
		),
		'bp_group_header_actions' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_group_header_actions\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_groups_buttons\' filter instead', 'bp-nouveau' ),
		),
		'bp_directory_groups_actions' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_directory_groups_actions\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_groups_buttons\' filter instead', 'bp-nouveau' ),
		),
		'bp_group_invites_item_action' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_group_invites_item_action\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_groups_buttons\' filter instead', 'bp-nouveau' ),
		),
		'bp_group_membership_requests_admin_item_action' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_group_membership_requests_admin_item_action\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_groups_buttons\' filter instead', 'bp-nouveau' ),
		),
		'bp_group_manage_members_admin_item' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_group_manage_members_admin_item\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_groups_buttons\' filter instead', 'bp-nouveau' ),
		),
		'bp_directory_blogs_actions' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_directory_blogs_actions\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_blogs_buttons\' filter instead', 'bp-nouveau' ),
		),
		'bp_activity_comment_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The \'bp_activity_comment_options\' action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the \'bp_nouveau_get_activity_comment_buttons\' filter instead', 'bp-nouveau' ),
		),
		'groups_custom_group_fields_editable' => array(
			'hook_type'    => 'action',
			'message_type' => 'error',
			'message'      => __( 'The \'groups_custom_group_fields_editable\' action is deprecated in the BP Nouveau template pack, please use \'bp_after_group_details_creation_step\' or \'bp_after_group_details_admin\' instead', 'bp-nouveau' ),
		),
	);

	/**
	 * Add warning messages for people using dynamic filters the BP Nouveau Nav Loop
	 * won't take in account unlike bp_get_displayed_user_nav() & bp_get_options_nav().
	 */
	$nav_items = array();
	if ( bp_is_user() ) {
		$nav_items = buddypress()->members->nav->get_item_nav();
	} elseif ( bp_is_group() && ! bp_is_group_create() ) {
		$nav_items = buddypress()->groups->nav->get_secondary( array( 'parent_slug' => bp_get_current_group_slug() ), false );
	}

	if ( $nav_items ) {

		// Set the common parts
		$common = array(
			'hook_type'    => 'filter',
			'message_type' => 'error',
		);

		// And the warning message
		$message = __( 'The \'%s\' filter is not used in the BP Nouveau template pack, please use one of the filters of the BP Nouveau Navigation Loop instead', 'bp-nouveau' );

		foreach ( $nav_items as $nav_item ) {
			if ( ! isset( $nav_item->children ) ) {
				$filter = 'bp_get_options_nav_' . $nav_item->css_id;
				$forsaken_hooks[ $filter ] = array_merge( $common, array(
					'message' => sprintf( $message, $filter ),
				) );
			} else {
				$filter = 'bp_get_displayed_user_nav_' . $nav_item->css_id;
				$forsaken_hooks[ $filter ] = array_merge( $common, array(
					'message' => sprintf( $message, $filter ),
				) );

				foreach ( $nav_item->children as $child ) {
					$filter = 'bp_get_options_nav_' . $child->css_id;
					$forsaken_hooks[ $filter ] = array_merge( $common, array(
						'message' => sprintf( $message, $filter ),
					) );
				}
			}
		}
	}

	return $forsaken_hooks;
}

/**
 * Parse an html output to a list of component's directory nav item.
 *
 * @since  1.0.0
 *
 * @param  string  $hook      The hook to fire.
 * @param  string  $component The component nav belongs to.
 * @param  int     $position  The position of the nav item.
 * @return array              A list of component's dir nav items
 */
function bp_nouveau_parse_hooked_dir_nav( $hook = '', $component = '', $position = 99 ) {
	$extra_nav_items = array();

	if ( empty( $hook ) || empty( $component ) || ! has_action( $hook ) ) {
		return $extra_nav_items;
	}

	// Get the hook output.
	ob_start();
	do_action( $hook );
	$output = ob_get_clean();

	if ( ! empty( $output ) ) {
		preg_match_all( "/<li\sid=\"{$component}\-(.*)\"[^>]*>/siU", $output, $lis );

		if ( ! empty( $lis[1] ) ) {
			$extra_nav_items = array_fill_keys( $lis[1], array( 'component' => $component, 'position' => $position ) );

			preg_match_all( '/<a\s[^>]*>(.*)<\/a>/siU', $output, $as );

			if ( ! empty( $as[0] ) ) {
				foreach ( $as[0] as $ka => $a ) {
					$extra_nav_items[ $lis[1][ $ka ] ]['slug'] = $lis[1][ $ka ];
					$extra_nav_items[ $lis[1][ $ka ] ]['text'] = $as[1][ $ka ];
					preg_match_all( '/([\w\-]+)=([^"\'> ]+|([\'"]?)(?:[^\3]|\3+)+?\3)/', $a, $attrs );

					if ( ! empty( $attrs[1] ) ) {
						foreach ( $attrs[1] as $katt => $att ) {
							if ( 'href' === $att ) {
								$extra_nav_items[ $lis[1][ $ka ] ]['link'] = trim( $attrs[2][ $katt ], '"' );
							} else {
								$extra_nav_items[ $lis[1][ $ka ] ][ $att ] = trim( $attrs[2][ $katt ], '"' );
							}
						}
					}
				}
			}

			if ( ! empty( $as[1] ) ) {
				foreach ( $as[1] as $ks => $s ) {
					preg_match_all( '/<span>(.*)<\/span>/siU', $s, $spans );

					if ( empty( $spans[0] ) ) {
						$extra_nav_items[ $lis[1][ $ks ] ]['count'] = false;
					} elseif ( ! empty( $spans[1][0] ) ) {
						$extra_nav_items[ $lis[1][ $ks ] ]['count'] = (int) $spans[1][0];
					} else {
						$extra_nav_items[ $lis[1][ $ks ] ]['count'] = '';
					}
				}
			}
		}
	}

	return $extra_nav_items;
}

/**
 * Run specific "select filter" hooks to catch the options and build an array out of them
 *
 * @since 1.0.0
 *
 * @param string $hook the do_action
 * @param array  $filters the array of options
 * @return array the filters
 */
function bp_nouveau_parse_hooked_options( $hook = '', $filters = array() ) {
	if ( empty( $hook ) ) {
		return $filters;
	}

	ob_start();
	do_action( $hook );

	$output = ob_get_clean();

	preg_match_all( '/<option value="(.*?)"\s*>(.*?)<\/option>/', $output, $matches );

	if ( ! empty( $matches[1] ) && ! empty( $matches[2] ) ) {
		foreach ( $matches[1] as $ik => $key_action ) {
			if ( ! empty( $matches[2][ $ik ] ) && ! isset( $filters[ $key_action ] ) ) {
				$filters[ $key_action ] = $matches[2][ $ik ];
			}
		}
	}

	return $filters;
}

/**
 * Get Dropdawn filters for the current component of the one passed in params
 *
 * @since 1.0.0
 *
 * @param string $context   'directory', 'user' or 'group'
 * @param string $component The BuddyPress component ID
 * @return array the dropdown filters
 */
function bp_nouveau_get_component_filters( $context = '', $component = '' ) {
	$filters = array();

	if ( empty( $context ) ) {
		if ( bp_is_user() ) {
			$context = 'user';
		} elseif ( bp_is_group() ) {
			$context = 'group';

		// Defaults to directory
		} else {
			$context = 'directory';
		}
	}

	if ( empty( $component ) ) {
		if ( 'directory' === $context || 'user' === $context ) {
			$component = bp_current_component();

			if ( 'friends' === $component ) {
				$context   = 'friends';
				$component = 'members';
			}
		} elseif ( 'group' === $context && bp_is_group_activity() ) {
			$component = 'activity';
		} elseif ( 'group' === $context && bp_is_group_members() ) {
			$component = 'members';
		}
	}

	if ( ! bp_is_active( $component ) ) {
		return $filters;
	}

	if ( 'members' === $component ) {
		$filters = bp_nouveau_get_members_filters( $context );
	} elseif ( 'activity' === $component ) {
		$filters = bp_nouveau_get_activity_filters();

		// Specific case for the activity dropdown
		$filters = array_merge( array( '-1' => __( '&mdash; Everything &mdash;', 'bp-nouveau' ) ), $filters );
	} elseif ( 'groups' === $component ) {
		$filters = bp_nouveau_get_groups_filters( $context );
	} elseif ( 'blogs' === $component ) {
		$filters = bp_nouveau_get_blogs_filters( $context );
	}

	return $filters;
}

/**
 * When previewing make sure to get the temporary setting of the customizer.
 * This is necessary when we need to get these very early.
 *
 * @since 1.0.0
 *
 * @param  string $option the index of the setting to get.
 * @param  mixed  $retval the value to use as default.
 * @return mixed          the value for the requested option.
 */
function bp_nouveau_get_temporary_setting( $option = '', $retval = false ) {
	if ( empty( $option ) || ! isset( $_POST['customized'] ) ) {
		return $retval;
	}

	$temporary_setting = json_decode( wp_unslash( $_POST['customized'] ), true );

	// This is used to transport the customizer settings into Ajax requests.
	if ( 'any' === $option ) {
		$retval = array();

		foreach ( $temporary_setting as $key => $setting ) {
			if ( 0 !== strpos( $key, 'bp_nouveau_appearance' ) ) {
				continue;
			}
			$k = str_replace( array( '[', ']'), array( '_', '' ), $key );
			$retval[ $k ] = $setting;
		}

	// Used when it's an early regular request
	} elseif ( isset( $temporary_setting['bp_nouveau_appearance[' . $option . ']'] ) ) {
		$retval = $temporary_setting['bp_nouveau_appearance[' . $option . ']'];

	// Used when it's an ajax request
	} elseif ( isset( $_POST['customized'][ 'bp_nouveau_appearance_' . $option ] ) ) {
		$retval = $_POST['customized'][ 'bp_nouveau_appearance_' . $option ];
	}

	return $retval;
}

/**
 * Get the BP Nouveau Appearance settings.
 *
 * @since 1.0.0
 *
 * @param string $option Leave empty to get all settings, specify a value for a specific one.
 * @param mixed          An array of settings, the value of the requested setting.
 */
function bp_nouveau_get_appearance_settings( $option = '' ) {
	$default_args = array(
		'user_front_page'  => 1,
		'user_front_bio'   => 0,
		'user_nav_display' => 0,       // O is default (horizontally). 1 is vertically.
		'user_nav_order'   => array(),
		'members_layout'   => 1,
	);

	if ( bp_is_active( 'groups' ) ) {
		$default_args = array_merge( $default_args, array(
			'group_front_page'        => 1,
			'group_front_boxes'       => 1,
			'group_front_description' => 0,
			'group_nav_display'       => 0,       // O is default (horizontally). 1 is vertically.
			'group_nav_order'         => array(),
			'groups_layout'           => 1,
		) );
	}

	if ( is_multisite() && bp_is_active( 'blogs' ) ) {
		$default_args = array_merge( $default_args, array(
			'blogs_layout' => 1,
		) );
	}

	$settings = bp_parse_args(
		bp_get_option( 'bp_nouveau_appearance', array() ),
		$default_args,
		'nouveau_appearance_settings'
	);

	if ( ! empty( $option ) ) {
		if ( isset( $settings[ $option ] ) ) {
			return $settings[ $option ];
		} else {
			return false;
		}
	}

	return $settings;
}

/**
 * Returns the choices for the Layout option of the customizer
 * or the list of corresponding css classes.
 *
 * @since  1.0.0
 *
 * @param  string $type 'option' to get the labels, 'classes' to get the classes
 * @return array  the list of labels or classes preserving keys.
 */
function bp_nouveau_customizer_grid_choices( $type = 'option' ) {
	$columns = array(
		array( 'key' => '1', 'label' => __( 'One column', 'bp-nouveau'    ), 'class' => ''      ),
		array( 'key' => '2', 'label' => __( 'Two columns', 'bp-nouveau'   ), 'class' => 'two'   ),
		array( 'key' => '3', 'label' => __( 'Three columns', 'bp-nouveau' ), 'class' => 'three' ),
		array( 'key' => '4', 'label' => __( 'Four columns', 'bp-nouveau'  ), 'class' => 'four'  ),
	);

	if ( 'option' === $type ) {
		return wp_list_pluck( $columns, 'label', 'key' );
	}

	return wp_list_pluck( $columns, 'class', 'key' );
}

/**
 * Add a specific panel for the BP Nouveau Template Pack.
 *
 * @since 1.0.0
 *
 * @param WP_Customize_Manager $wp_customize WordPress customizer.
 */
function bp_nouveau_customize_register( WP_Customize_Manager $wp_customize ) {
	if ( ! bp_is_root_blog() ) {
		return;
	}

	// include the Customizer control.
	require_once( trailingslashit( bp_nouveau()->includes_dir ) . 'customizer-controls.php' );

	// Add it to control types
	$wp_customize->register_control_type( 'BP_Nouveau_Nav_Customize_Control' );

	$bp_nouveau_options = bp_nouveau_get_appearance_settings();

	$wp_customize->add_panel( 'bp_nouveau_panel', array(
		'description' => __( 'Customize the appearance of your BuddyPress Template pack.', 'bp-nouveau' ),
		'title'       => _x( 'BuddyPress Template Pack', 'Customizer Panel', 'bp-nouveau' ),
		'priority'    => 200,
	) );

	$sections = apply_filters( 'bp_nouveau_customizer_sections', array(
		'bp_nouveau_user_front_page' => array(
			'title'       => __( 'User\'s front page', 'bp-nouveau' ),
			'panel'       => 'bp_nouveau_panel',
			'priority'    => 10,
			'description' => __( 'Set your preferences about the members default front page.', 'bp-nouveau' ),
		),
		'bp_nouveau_user_primary_nav' => array(
			'title'       => __( 'User\'s navigation', 'bp-nouveau' ),
			'panel'       => 'bp_nouveau_panel',
			'priority'    => 30,
			'description' => __( 'Customize the members primary navigations. Navigate to any random member\'s profile to live preview your changes.', 'bp-nouveau' ),
		),
		'bp_nouveau_loops_layout' => array(
			'title'       => __( 'Directories layout', 'bp-nouveau' ),
			'panel'       => 'bp_nouveau_panel',
			'priority'    => 50,
			'description' => __( 'Set the number of columns to use for the BuddyPress Directories.', 'bp-nouveau' ),
		),
	) );

	// Add the sections to the customizer
	foreach ( $sections as $id_section => $section_args ) {
		$wp_customize->add_section( $id_section, $section_args );
	}

	$settings = apply_filters( 'bp_nouveau_customizer_settings', array(
		'bp_nouveau_appearance[user_front_page]' => array(
			'index'             => 'user_front_page',
			'capability'        => 'bp_moderate',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
			'type'              => 'option',
		),
		'bp_nouveau_appearance[user_front_bio]' => array(
			'index'             => 'user_front_bio',
			'capability'        => 'bp_moderate',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
			'type'              => 'option',
		),
		'bp_nouveau_appearance[user_nav_display]' => array(
			'index'             => 'user_nav_display',
			'capability'        => 'bp_moderate',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
			'type'              => 'option',
		),
		'bp_nouveau_appearance[user_nav_order]' => array(
			'index'             => 'user_nav_order',
			'capability'        => 'bp_moderate',
			'sanitize_callback' => 'bp_nouveau_sanitize_nav_order',
			'transport'         => 'refresh',
			'type'              => 'option',
		),
		'bp_nouveau_appearance[members_layout]' => array(
			'index'             => 'members_layout',
			'capability'        => 'bp_moderate',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
			'type'              => 'option',
		),
	) );

	// Add the settings
	foreach ( $settings as $id_setting => $setting_args ) {
		$args = array();

		if ( empty( $setting_args['index'] ) ) {
			continue;
		}

		$args = array_merge( $setting_args, array( 'default' => $bp_nouveau_options[ $setting_args['index'] ] ) );

		$wp_customize->add_setting( $id_setting, $args );
	}

	$controls = apply_filters( 'bp_nouveau_customizer_controls', array(
		'user_front_page' => array(
			'label'      => __( 'Enable default front page for user profiles.', 'bp-nouveau' ),
			'section'    => 'bp_nouveau_user_front_page',
			'settings'   => 'bp_nouveau_appearance[user_front_page]',
			'type'       => 'checkbox',
		),
		'user_front_bio' => array(
			'label'      => __( 'Display the WordPress Biographical Info of the user.', 'bp-nouveau' ),
			'section'    => 'bp_nouveau_user_front_page',
			'settings'   => 'bp_nouveau_appearance[user_front_bio]',
			'type'       => 'checkbox',
		),
		'user_nav_display' => array(
			'label'      => __( 'Display the User\'s primary nav vertically.', 'bp-nouveau' ),
			'section'    => 'bp_nouveau_user_primary_nav',
			'settings'   => 'bp_nouveau_appearance[user_nav_display]',
			'type'       => 'checkbox',
		),
		'user_nav_order' => array(
			'class'       => 'BP_Nouveau_Nav_Customize_Control',
			'label'      => __( 'Reorder the Members single items primary navigation.', 'bp-nouveau' ),
			'section'    => 'bp_nouveau_user_primary_nav',
			'settings'   => 'bp_nouveau_appearance[user_nav_order]',
			'type'       => 'user',
		),
		'members_layout' => array(
			'label'      => __( 'Members loop:', 'bp-nouveau' ),
			'section'    => 'bp_nouveau_loops_layout',
			'settings'   => 'bp_nouveau_appearance[members_layout]',
			'type'       => 'select',
			'choices'    => bp_nouveau_customizer_grid_choices(),
		),
	) );

	// Add the controls to the customizer's section
	foreach ( $controls as $id_control => $control_args ) {
		if ( empty( $control_args['class'] ) )  {
			$wp_customize->add_control( $id_control, $control_args );
		} else {
			$wp_customize->add_control( new $control_args['class']( $wp_customize, $id_control, $control_args ) );
		}
	}
}

/**
 * Sanitize a list of slugs to save it as an array
 *
 * @since  1.0.0
 *
 * @param  string $option A comma separated list of nav items slugs.
 * @return array          An array of nav items slugs.
 */
function bp_nouveau_sanitize_nav_order( $option = '' ) {
	$option = explode( ',', $option );
	return array_map( 'sanitize_key', $option );
}

/**
 * Enqueue needed JS for our customizer Settings & Controls
 *
 * @since  1.0.0
 */
function bp_nouveau_customizer_enqueue_scripts() {
	$min = bp_core_get_minified_asset_suffix();

	wp_enqueue_script(
		'bp-nouveau-customizer',
		trailingslashit( bp_get_theme_compat_url() ) . "js/customizer{$min}.js",
		array( 'jquery', 'jquery-ui-sortable', 'customize-controls', 'iris', 'underscore', 'wp-util' ),
		bp_nouveau()->version,
		true
	);

	do_action( 'bp_nouveau_customizer_enqueue_scripts' );
}

/**
 * Inline script to toggle the signup blog form
 *
 * @since  1.0.0
 *
 * @return string Javascript output
 */
function bp_nouveau_get_blog_signup_inline_script() {
	return '
		( function( $ ) {
			if ( $( \'body\' ).hasClass( \'register\' ) ) {
				var blog_checked = $( \'#signup_with_blog\' );

				// hide "Blog Details" block if not checked by default
				if ( ! blog_checked.prop( \'checked\' ) ) {
					$( \'#blog-details\' ).toggle();
				}

				// toggle "Blog Details" block whenever checkbox is checked
				blog_checked.change( function( event ) {
					// Toggle HTML5 required attribute.
					$.each( $( \'#blog-details\' ).find( \'[aria-required]\' ), function( i, input ) {
						$( input ).prop( \'required\',  $( event.target ).prop( \'checked\' ) );
					} );

					$( \'#blog-details\' ).toggle();
				} );
			}
		} )( jQuery );
	';
}

/**
 * BP Nouveau's callback for the cover image feature.
 *
 * @since  1.0.0
 *
 * @param  array $params the current component's feature parameters.
 * @return array          an array to inform about the css handle to attach the css rules to
 */
function bp_nouveau_theme_cover_image( $params = array() ) {
	if ( empty( $params ) ) {
		return;
	}

	// Avatar height - padding - 1/2 avatar height.
	$avatar_offset = $params['height'] - 5 - round( (int) bp_core_avatar_full_height() / 2 );

	// Header content offset + spacing.
	$top_offset  = bp_core_avatar_full_height() - 10;
	$left_offset = bp_core_avatar_full_width() + 20;

	$cover_image = isset( $params['cover_image'] ) ? 'background-image: url(' . $params['cover_image'] . ');' : '';

	$hide_avatar_style = '';

	// Adjust the cover image header, in case avatars are completely disabled.
	if ( ! buddypress()->avatar->show_avatars ) {
		$hide_avatar_style = '
			#buddypress #item-header-cover-image #item-header-avatar {
				display:  none;
			}
		';

		if ( bp_is_user() ) {
			$hide_avatar_style = '
				#buddypress #item-header-cover-image #item-header-avatar a {
					display: block;
					height: ' . $top_offset . 'px;
					margin: 0 15px 19px 0;
				}

				#buddypress div#item-header #item-header-cover-image #item-header-content {
					margin-left:auto;
				}
			';
		}
	}

	return '
		/* Cover image */
		#buddypress #item-header-cover-image {
			overflow: hidden;
			min-height: ' . $params["height"] . 'px;
			margin-bottom: 1em;
		}

		#buddypress #header-cover-image {
			height: ' . $params["height"] . 'px;
			' . $cover_image . '
		}

		#buddypress #create-group-form #header-cover-image {
			position: relative;
			margin: 1em 0;
		}

		.bp-user #buddypress #item-header {
			padding-top: 0;
		}

		#buddypress #item-header-cover-image #item-header-avatar {
			margin-top: '. $avatar_offset .'px;
			float: left;
			overflow: visible;
			width:auto;
		}

		#buddypress div#item-header #item-header-cover-image #item-header-content {
			clear: both;
			float: left;
			margin-left: ' . $left_offset . 'px;
			margin-top: -' . $top_offset . 'px;
			width:auto;
		}

		body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-header-content,
		body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-actions {
			margin-top: ' . $params["height"] . 'px;
			margin-left: 0;
			clear: none;
			max-width: 50%;
		}

		body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-actions {
			padding-top: 20px;
			max-width: 20%;
		}

		' . $hide_avatar_style . '

		#buddypress div#item-header-cover-image h2 a,
		#buddypress div#item-header-cover-image h2 {
			color: #FFF;
			text-rendering: optimizelegibility;
			text-shadow: 0px 0px 3px rgba( 0, 0, 0, 0.8 );
			margin: 0 0 .6em;
			font-size:200%;
		}

		#buddypress #item-header-cover-image #item-header-avatar img.avatar {
			border: solid 2px #FFF;
			background: rgba( 255, 255, 255, 0.8 );
		}

		#buddypress #item-header-cover-image #item-header-avatar a {
			border: none;
			text-decoration: none;
		}

		#buddypress #item-header-cover-image #item-buttons {
			margin: 0 0 10px;
			padding: 0 0 5px;
		}

		#buddypress #item-header-cover-image #item-buttons:after {
			clear: both;
			content: "";
			display: table;
		}

		@media screen and (max-width: 782px) {
			#buddypress #item-header-cover-image #item-header-avatar,
			.bp-user #buddypress #item-header #item-header-cover-image #item-header-avatar,
			#buddypress div#item-header #item-header-cover-image #item-header-content {
				width:100%;
				text-align:center;
			}

			#buddypress #item-header-cover-image #item-header-avatar a {
				display:inline-block;
			}

			#buddypress #item-header-cover-image #item-header-avatar img {
				margin:0;
			}

			#buddypress div#item-header #item-header-cover-image #item-header-content,
			body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-header-content,
			body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-actions {
				margin:0;
			}

			body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-header-content,
			body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-actions {
				max-width: 100%;
			}

			#buddypress div#item-header-cover-image h2 a,
			#buddypress div#item-header-cover-image h2 {
				color: inherit;
				text-shadow: none;
				margin:25px 0 0;
				font-size:200%;
			}

			#buddypress #item-header-cover-image #item-buttons div {
				float:none;
				display:inline-block;
			}

			#buddypress #item-header-cover-image #item-buttons:before {
				content:"";
			}

			#buddypress #item-header-cover-image #item-buttons {
				margin: 5px 0;
			}
		}
	';
}

/**
 * All user feedback messages are available here
 *
 * @since 1.0.0
 *
 * @param  string $feedback_id The ID of the message.
 * @return array  The list of parameters for the message
 */
function bp_nouveau_get_user_feedback( $feedback_id = '' ) {
	/**
	 * Filter here to add your custom feedback messages
	 *
	 * @param array $value The list of feedback messages.
	 */
	$feedback_messages = apply_filters( 'bp_nouveau_feedback_messages', array(
		'registration-disabled' => array(
			'type'    => 'info',
			'message' => __( 'User registration is currently not allowed.', 'bp-nouveau' ),
			'before'  => 'bp_before_registration_disabled',
			'after'   => 'bp_after_registration_disabled'
		),
		'request-details' => array(
			'type'    => 'info',
			'message' => __( 'Registering for this site is easy. Just fill in the fields below, and we\'ll get a new account set up for you in no time.', 'bp-nouveau' ),
			'before'  => false,
			'after'   => false,
		),
		'completed-confirmation' => array(
			'type'    => 'info',
			'message' => __( 'You have successfully created your account! Please log in using the username and password you have just created.', 'bp-nouveau' ),
			'before'  => 'bp_before_registration_confirmed',
			'after'   => 'bp_after_registration_confirmed',
		),
		'directory-activity-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Loading the community updates, please wait.', 'bp-nouveau' ),
		),
		'single-activity-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Loading the update, please wait.', 'bp-nouveau' ),
		),
		'activity-loop-none' => array(
			'type'    => 'info',
			'message' => __( 'Sorry, there was no activity found. Please try a different filter.', 'bp-nouveau' ),
		),
		'blogs-loop-none' => array(
			'type'    => 'info',
			'message' => __( 'Sorry, there were no sites found.', 'bp-nouveau' ),
		),
		'blogs-no-signup' => array(
			'type'    => 'info',
			'message' => __( 'Site registration is currently disabled.', 'bp-nouveau' ),
		),
		'directory-blogs-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Loading the sites of the network, please wait.', 'bp-nouveau' ),
		),
		'directory-groups-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Loading the groups of the community, please wait.', 'bp-nouveau' ),
		),
		'groups-loop-none' => array(
			'type'    => 'info',
			'message' => __( 'Sorry, there were no groups found.', 'bp-nouveau' ),
		),
		'group-activity-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Loading the group updates, please wait.', 'bp-nouveau' ),
		),
		'group-members-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Requesting the group members, please wait.', 'bp-nouveau' ),
		),
		'group-members-none' => array(
			'type'    => 'info',
			'message' => __( 'Sorry, there were no group members found.', 'bp-nouveau' ),
		),
		'group-members-search-none' => array(
			'type'    => 'info',
			'message' => __( 'Sorry, there was no member of that name found in this group.', 'bp-nouveau' ),
		),
		'group-manage-members-none' => array(
			'type'    => 'info',
			'message' => __( 'This group has no members.', 'bp-nouveau' ),
		),
		'group-requests-none' => array(
			'type'    => 'info',
			'message' => __( 'There are no pending membership requests.', 'bp-nouveau' ),
		),
		'group-requests-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Loading the members who requested to join the group, please wait.', 'bp-nouveau' ),
		),
		'group-delete-warning' => array(
			'type'    => 'warning',
			'message' => __( 'WARNING: Deleting this group will completely remove ALL content associated with it. There is no way back, please be careful with this option.', 'bp-nouveau' ),
		),
		'group-avatar-delete-info' => array(
			'type'    => 'info',
			'message' => __( 'If you\'d like to remove the existing group profile photo but not upload a new one, please use the delete group profile photo button.', 'bp-nouveau' ),
		),
		'directory-members-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Loading the members of your community, please wait.', 'bp-nouveau' ),
		),
		'members-loop-none' => array(
			'type'    => 'info',
			'message' => __( 'Sorry, no members were found.', 'bp-nouveau' ),
		),
		'member-requests-none' => array(
			'type'    => 'info',
			'message' => __( 'You have no pending friendship requests.', 'bp-nouveau' ),
		),
		'member-invites-none' => array(
			'type'    => 'info',
			'message' => __( 'You have no outstanding group invites.', 'bp-nouveau' ),
		),
		'member-notifications-none' => array(
			'type'    => 'info',
			'message' => __( 'This member has no notifications.', 'bp-nouveau' ),
		),
		'member-wp-profile-none' => array(
			'type'    => 'info',
			'message' => __( '%s did not save any profile informations yet.', 'bp-nouveau' ),
		),
		'member-delete-account' => array(
			'type'    => 'warning',
			'message' => __( 'Deleting this account will delete all of the content it has created. It will be completely unrecoverable.', 'bp-nouveau' ),
		),
		'member-activity-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Loading the user\'s updates, please wait.', 'bp-nouveau' ),
		),
		'member-blogs-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Loading the blogs the user is a contributor of, please wait.', 'bp-nouveau' ),
		),
		'member-friends-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Loading the members the user is friend with, please wait.', 'bp-nouveau' ),
		),
		'member-groups-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Loading the groups the user is a member of, please wait.', 'bp-nouveau' ),
		),
		'member-notifications-loading' => array(
			'type'    => 'loading',
			'message' => __( 'Loading notifications, please wait.', 'bp-nouveau' ),
		),
		'member-group-invites' => array(
			'type'    => 'info',
			'message' => __( 'Currently every member of the community can invite you to join their groups. If you are not comfortable with it, you can always restrict group invites to your friends only.', 'bp-nouveau' ),
		),
	) );

	if ( isset( $feedback_messages[ $feedback_id ] ) ) {
		/**
		 * Adjust some messages to the context.
		 */
		if ( 'completed-confirmation' === $feedback_id && bp_registration_needs_activation() ) {
			$feedback_messages['completed-confirmation']['message'] = __( 'You have successfully created your account! To begin using this site you will need to activate your account via the email we have just sent to your address.', 'bp-nouveau' );
		} elseif ( 'member-notifications-none' === $feedback_id ) {
			$is_myprofile = bp_is_my_profile();

			if ( bp_is_current_action( 'unread' ) ) {
				$feedback_messages['member-notifications-none']['message'] = __( 'This member has no unread notifications.', 'bp-nouveau' );

				if ( $is_myprofile ) {
					$feedback_messages['member-notifications-none']['message'] = __( 'You have no unread notifications.', 'bp-nouveau' );
				}
			} elseif ( $is_myprofile ) {
				$feedback_messages['member-notifications-none']['message'] = __( 'You have no notifications.', 'bp-nouveau' );
			}
		} elseif ( 'member-wp-profile-none' === $feedback_id && bp_is_user_profile() ) {
			$feedback_messages['member-wp-profile-none']['message'] = sprintf( $feedback_messages['member-wp-profile-none']['message'], bp_get_displayed_user_fullname() );
		} elseif ( 'member-delete-account' === $feedback_id && bp_is_my_profile() ) {
			$feedback_messages['member-delete-account']['message'] = __( 'Deleting your account will delete all of the content you have created. It will be completely irrecoverable.', 'bp-nouveau' );
		} elseif ( 'member-activity-loading' === $feedback_id && bp_is_my_profile() ) {
			$feedback_messages['member-activity-loading']['message'] = __( 'Loading your updates, please wait.', 'bp-nouveau' );
		} elseif ( 'member-blogs-loading' === $feedback_id && bp_is_my_profile() ) {
			$feedback_messages['member-blogs-loading']['message'] = __( 'Loading the blogs you are a contributor of, please wait.', 'bp-nouveau' );
		} elseif ( 'member-friends-loading' === $feedback_id && bp_is_my_profile() ) {
			$feedback_messages['member-friends-loading']['message'] = __( 'Loading your friends, please wait.', 'bp-nouveau' );
		}  elseif ( 'member-groups-loading' === $feedback_id && bp_is_my_profile() ) {
			$feedback_messages['member-groups-loading']['message'] = __( 'Loading the groups you are a member of, please wait.', 'bp-nouveau' );
		}

		/**
		 * Filter here if you wish to edit the message just before being displayed
		 *
		 * @since 1.0.0
		 *
		 * @param array $feedback_messages
		 */
		return apply_filters( 'bp_nouveau_get_user_feedback', $feedback_messages[ $feedback_id ] );
	}

	return false;
}

/**
 * Get the signup fields for the requested section
 *
 * @since 1.0.0
 *
 * @param  string     $section The section of fields to get 'account_details' or 'blog_details'. Required.
 * @return array|bool          The list of signup fields for the requested section. False if not found.
 */
function bp_nouveau_get_signup_fields( $section = '' ) {
	if ( empty( $section ) ) {
		return false;
	}

	/**
	 * Filter here to add your specific 'text' or 'password' inputs
	 *
	 * If you need to use other types of field, please use the
	 * do_action( 'bp_account_details_fields' ) or do_action( 'blog_details' )
	 * hooks instead.
	 *
	 * @since 1.0.0
	 *
	 * @param array $value The list of fields organized into sections.
	 */
	$fields = apply_filters( 'bp_nouveau_get_signup_fields', array(
		'account_details' => array(
			'signup_username' => array(
				'label'          => _x( 'Username%s', 'signup field label', 'bp-nouveau' ),
				'required'       => true,
				'value'          => 'bp_get_signup_username_value',
				'attribute_type' => 'username',
				'type'           => 'text',
				'class'          => '',
			),
			'signup_email' => array(
				'label'          => _x( 'Email Address%s', 'signup field label', 'bp-nouveau' ),
				'required'       => true,
				'value'          => 'bp_get_signup_email_value',
				'attribute_type' => 'email',
				'type'           => 'email',
				'class'          => '',
			),
			'signup_password' => array(
				'label'          => _x( 'Choose a Password%s', 'signup field label', 'bp-nouveau' ),
				'required'       => true,
				'value'          => '',
				'attribute_type' => 'password',
				'type'           => 'password',
				'class'          => 'password-entry',
			),
			'signup_password_confirm' => array(
				'label'          => _x( 'Confirm Password%s', 'signup field label', 'bp-nouveau' ),
				'required'       => true,
				'value'          => '',
				'attribute_type' => 'password',
				'type'           => 'password',
				'class'          => 'password-entry-confirm',
			),
		),
		'blog_details' => array(
			'signup_blog_url' => array(
				'label'          => _x( 'Site URL%s', 'signup field label', 'bp-nouveau' ),
				'required'       => true,
				'value'          => 'bp_get_signup_blog_url_value',
				'attribute_type' => 'slug',
				'type'           => 'text',
				'class'          => '',
			),
			'signup_blog_title' => array(
				'label'          => _x( 'Site Title%s', 'signup field label', 'bp-nouveau' ),
				'required'       => true,
				'value'          => 'bp_get_signup_blog_title_value',
				'attribute_type' => 'title',
				'type'           => 'text',
				'class'          => '',
			),
			'signup_blog_privacy_public' => array(
				'label'          => __( 'Yes', 'bp-nouveau' ),
				'required'       => false,
				'value'          => 'public',
				'attribute_type' => '',
				'type'           => 'radio',
				'class'          => '',
			),
			'signup_blog_privacy_private' => array(
				'label'          => __( 'No', 'bp-nouveau' ),
				'required'       => false,
				'value'          => 'private',
				'attribute_type' => '',
				'type'           => 'radio',
				'class'          => '',
			),
		),
	) );

	if ( ! bp_get_blog_signup_allowed() ) {
		unset( $fields['blog_details'] );
	}

	if ( isset( $fields[ $section ] ) ) {
		return $fields[ $section ];
	}

	return false;
}

/**
 * Get Some submit buttons data.
 *
 * @since 1.0.0
 *
 * @param  string $action The action requested.
 * @return array|bool The list of the submit button parameters for the requested action
 *                    False if no actions were found.
 */
function bp_nouveau_get_submit_button( $action = '' ) {
	if ( empty( $action ) ) {
		return false;
	}

	/**
	 * Filter the Submit buttons to add your own.
	 * We strongly advise to not edit the existing ones.
	 * You can eventually add classes though.
	 *
	 * @since 1.0.0
	 *
	 * @param array $value The list of submit buttons.
	 */
	$actions = apply_filters( 'bp_nouveau_get_submit_button', array(
		'register' => array(
			/**
			 * Fires after the display of the registration submit buttons.
			 *
			 * @since 1.1.0 (BuddyPress)
			 */
			'before'     => 'bp_before_registration_submit_buttons',
			/**
			 * Fires after the display of the registration submit buttons.
			 *
			 * @since 1.1.0 (BuddyPress)
			 */
			'after'      => 'bp_after_registration_submit_buttons',
			'nonce'      => 'bp_new_signup',
			'attributes' => array(
				'name'  => 'signup_submit',
				'id'    => 'signup_submit',
				'value' => __( 'Complete Sign Up', 'bp-nouveau' ),
			),
		),
		'member-profile-edit' => array(
			'before' => '',
			'after'  => '',
			'nonce'  => 'bp_xprofile_edit',
			'attributes' => array(
				'name'  => 'profile-group-edit-submit',
				'id'    => 'profile-group-edit-submit',
				'value' => __( 'Save Changes', 'bp-nouveau' ),
			),
		),
		'member-capabilities' => array(
			/**
			 * Fires before the display of the submit button for user capabilities saving.
			 *
			 * @since 1.6.0 (BuddyPress)
			 */
			'before' => 'bp_members_capabilities_account_before_submit',
			/**
			 * Fires after the display of the submit button for user capabilities saving.
			 *
			 * @since 1.6.0 (BuddyPress)
			 */
			'after'  => 'bp_members_capabilities_account_after_submit',
			'nonce'  => 'capabilities',
			'attributes' => array(
				'name'  => 'capabilities-submit',
				'id'    => 'capabilities-submit',
				'value' => __( 'Save', 'bp-nouveau' ),
			),
		),
		'member-delete-account' => array(
			/**
			 * Fires before the display of the submit button for user delete account submitting.
			 *
			 * @since 1.5.0 (BuddyPress)
			 */
			'before' => 'bp_members_delete_account_before_submit',
			/**
			 * Fires after the display of the submit button for user delete account submitting.
			 *
			 * @since 1.5.0 (BuddyPress)
			 */
			'after'  => 'bp_members_delete_account_after_submit',
			'nonce'  => 'delete-account',
			'attributes' => array(
				'disabled' => 'disabled',
				'name'     => 'delete-account-button',
				'id'       => 'delete-account-button',
				'value'    => __( 'Delete Account', 'bp-nouveau' ),
			),
		),
		'members-general-settings' => array(
			/**
			 * Fires before the display of the submit button for user general settings saving.
			 *
			 * @since 1.5.0 (BuddyPress)
			 */
			'before' => 'bp_core_general_settings_before_submit',
			/**
			 * Fires after the display of the submit button for user general settings saving.
			 *
			 * @since 1.5.0 (BuddyPress)
			 */
			'after'  => 'bp_core_general_settings_after_submit',
			'nonce'  => 'bp_settings_general',
			'attributes' => array(
				'name'  => 'submit',
				'id'    => 'submit',
				'value' => __( 'Save Changes', 'bp-nouveau' ),
				'class' => 'auto',
			),
		),
		'member-notifications-settings' => array(
			/**
			 * Fires before the display of the submit button for user notification saving.
			 *
			 * @since 1.5.0 (BuddyPress)
			 */
			'before' => 'bp_members_notification_settings_before_submit',
			/**
			 * Fires after the display of the submit button for user notification saving.
			 *
			 * @since 1.5.0 (BuddyPress)
			 */
			'after'  => 'bp_members_notification_settings_after_submit',
			'nonce'  => 'bp_settings_notifications',
			'attributes' => array(
				'name'  => 'submit',
				'id'    => 'submit',
				'value' => __( 'Save Changes', 'bp-nouveau' ),
				'class' => 'auto',
			),
		),
		'members-profile-settings' => array(
			/**
			 * Fires before the display of the submit button for user profile saving.
			 *
			 * @since 2.0.0 (BuddyPress)
			 */
			'before' => 'bp_core_xprofile_settings_before_submit',
			/**
			 * Fires after the display of the submit button for user profile saving.
			 *
			 * @since 2.0.0 (BuddyPress)
			 */
			'after'  => 'bp_core_xprofile_settings_after_submit',
			'nonce'  => 'bp_xprofile_settings',
			'attributes' => array(
				'name'  => 'xprofile-settings-submit',
				'id'    => 'submit',
				'value' => __( 'Save Changes', 'bp-nouveau' ),
				'class' => 'auto',
			),
		),
		'member-group-invites' => array(
			'nonce'  => 'bp_nouveau_group_invites_settings',
			'attributes' => array(
				'name'  => 'member-group-invites-submit',
				'id'    => 'submit',
				'value' => __( 'Save', 'bp-nouveau' ),
				'class' => 'auto',
			),
		),
	) );

	if ( isset( $actions[ $action ] ) ) {
		return $actions[ $action ];
	}

	return false;
}

/**
 * Reorder a BuddyPress item nav according to a given list of nav item slugs
 *
 * @since  1.0.0
 *
 * @param  object $nav         The BuddyPress Item Nav object to reorder
 * @param  array  $order       A list of slugs ordered (eg: array( 'profile', 'activity', etc..) )
 * @param  string $parent_slug A parent slug if it's a secondary nav we are reordering (case of the Groups single item)
 * @return bool                True on success. False otherwise.
 */
function bp_nouveau_set_nav_item_order( $nav = null, $order = array(), $parent_slug = '' ) {
	if ( ! is_object( $nav ) || empty( $order ) || ! is_array( $order ) ) {
		return false;
	}

	$position = 0;

	foreach ( $order as $slug ) {
		$position += 10;

		$key = $slug;
		if ( ! empty( $parent_slug ) ) {
			$key = $parent_slug . '/' . $key;
		}

		$item_nav = $nav->get( $key );

		if ( ! $item_nav ) {
			continue;
		}

		if ( (int) $item_nav->position !== (int) $position ) {
			$nav->edit_nav( array( 'position' => $position ), $slug, $parent_slug );
		}
	}

	return true;
}
