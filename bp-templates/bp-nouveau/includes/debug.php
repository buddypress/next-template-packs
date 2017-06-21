<?php
/**
 * Debug code loader.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

/**
 * BP Nouveau will not use this hooks anymore
 *
 * @since 1.0.0
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
 * Inform developers about the Legacy hooks we are not using.
 *
 * This will be output as warnings inside the Browser console to avoid
 * messing with the page display.
 *
 * @since 1.0.0
 *
 * @return string HTML Output
 */
add_filter( 'bp_core_get_js_strings', function( $retval ) {
	// Get The forsaken hooks.
	$forsaken_hooks = bp_nouveau_get_forsaken_hooks();

	$notices = array();

	// Loop to check if deprecated hooks are used.
	foreach ( $forsaken_hooks as $hook => $feedback ) {
		if ( 'action' === $feedback['hook_type'] ) {
			if ( ! has_action( $hook ) ) {
				continue;
			}

		} elseif ( 'filter' === $feedback['hook_type'] ) {
			if ( ! has_filter( $hook ) ) {
				continue;
			}
		}

		$notices[] = $feedback['message'];
	}

	$retval['warnings'] = $notices;
	return $retval;
} );