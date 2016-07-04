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
 * Register the 2 sidebars for the Group & User default front page
 *
 * @since  1.0.0
 */
function bp_nouveau_register_sidebars() {
	$default_fronts     = bp_nouveau_get_appearance_settings();
	$default_user_front = 0;
	$is_active_groups   = bp_is_active( 'groups' );

	if ( isset( $default_fronts['user_front_page'] ) ) {
		$default_user_front = $default_fronts['user_front_page'];
	}

	if ( $is_active_groups ) {
		$default_group_front = 0;

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
	return array(
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
			'message'      => __( 'The \'groups_custom_group_fields_editable\' is deprecated in the BP Nouveau template pack, please use \'bp_after_group_details_creation_step\' or \'bp_after_group_details_admin\' instead', 'bp-nouveau' ),
		),
	);
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
				foreach( $as[0] as $ka => $a ) {
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
				foreach( $as[1] as $ks => $s ) {
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

	if ( isset( $temporary_setting['bp_nouveau_appearance[' . $option . ']'] ) ) {
		$retval = $temporary_setting['bp_nouveau_appearance[' . $option . ']'];
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
		'user_front_page' => 1,
		'user_front_bio'  => 0,
	);

	if ( bp_is_active( 'groups' ) ) {
		$default_args = array_merge( $default_args, array(
			'group_front_page'        => 1,
			'group_front_boxes'       => 1,
			'group_front_description' => 0,
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
			'description' => __( 'Set your preferences about the Users default front page.', 'bp-nouveau' ),
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
	) );

	// Add the controls to the customizer's section
	foreach ( $controls as $id_control => $control_args ) {
		$wp_customize->add_control( $id_control, $control_args );
	}
}

function bp_nouveau_customizer_enqueue_scripts() {
	$min = bp_core_get_minified_asset_suffix();

	wp_enqueue_script(
		'bp-nouveau-customizer',
		trailingslashit( bp_get_theme_compat_url() ) . "js/customizer{$min}.js",
		array( 'customize-controls', 'iris', 'underscore', 'wp-util' ),
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
				blog_checked.change(function() {
					$( \'#blog-details\' ).toggle();
				});
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
