<?php
/**
 * Groups Template tags
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template tag to wrap all Legacy actions that was used
 * before the groups directory content
 *
 * @since 1.0.0
 */
function bp_nouveau_before_groups_directory_content() {
	/**
	 * Fires at the begining of the templates BP injected content.
	 *
	 * @since 2.3.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_groups_page' );

	/**
	 * Fires before the display of the groups.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_groups' );

	/**
	 * Fires before the display of the groups content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_groups_content' );
}

/**
 * Template tag to wrap all Legacy actions that was used
 * after the groups directory content
 *
 * @since 1.0.0
 */
function bp_nouveau_after_groups_directory_content() {
	/**
	 * Fires and displays the group content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_directory_groups_content' );

	/**
	 * Fires after the display of the groups content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_groups_content' );

	/**
	 * Fires after the display of the groups.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_groups' );

	/**
	 * Fires at the bottom of the groups directory template file.
	 *
	 * @since 1.5.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_groups_page' );
}

/**
 * Fire specific hooks into the groups create template
 *
 * @since 1.0.0
 *
 * @param string $when    'before' or 'after'
 * @param string $suffix  Use it to add terms at the end of the hook name
 */
function bp_nouveau_groups_create_hook( $when = '', $suffix = '' ) {
	$hook = array( 'bp' );

	if ( ! empty( $when ) ) {
		$hook[] = $when;
	}

	// It's a create group hook
	$hook[] = 'create_group';

	if ( ! empty( $suffix ) ) {
		$hook[] = $suffix;
	}

	/**
	 * @since 1.2.0 (BuddyPress) for no suffix
	 * @since 1.6.0 (BuddyPress) for the 'content_template' suffix
	 * @since 1.7.0 (BuddyPress) for the 'page' suffix
	 */
	return bp_nouveau_hook( $hook );
}

/**
 * Fire specific hooks into the single groups templates
 *
 * @since 1.0.0
 *
 * @param string $when    'before' or 'after'
 * @param string $suffix  Use it to add terms at the end of the hook name
 */
function bp_nouveau_group_hook( $when = '', $suffix = '' ) {
	$hook = array( 'bp' );

	if ( ! empty( $when ) ) {
		$hook[] = $when;
	}

	// It's a group hook
	$hook[] = 'group';

	if ( ! empty( $suffix ) ) {
		$hook[] = $suffix;
	}

	/**
	 * @since 1.1.0 (BuddyPress) for the 'menu_admins', 'menu_mods', 'members_content',
	 *                           'members_list', 'members_list_item', 'request_membership_content'
	 *                           'membership_requests_admin_item', 'invites_item', 'invites_content' suffixes
	 * @since 1.2.0 (BuddyPress) for the 'activity_content', 'header_meta', 'home_content',
	 *                           'plugin_template', 'friend_requests_content' suffixes.
	 */
	return bp_nouveau_hook( $hook );
}

/**
 * Fire an isolated hook inside the groups loop
 *
 * @since 1.0.0
 */
function bp_nouveau_groups_loop_item() {
	/**
	 * Fires inside the listing of an individual group listing item.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_directory_groups_item' );
}

/**
 * Display the current group activity post form if needed
 *
 * @since  1.0.0
 *
 * @return string HTML Outpur
 */
function bp_nouveau_groups_activity_post_form() {
	/**
	 * Fires before the display of the group activity post form.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_before_group_activity_post_form' );

	if ( is_user_logged_in() && bp_group_is_member() ) {
		bp_get_template_part( 'activity/post-form' );
	}

	/**
	 * Fires after the display of the group activity post form.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_after_group_activity_post_form' );
}

/**
 * Load the Group Invites UI.
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_group_invites_interface() {
	/**
	 * Fires before the send invites content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_before_group_send_invites_content' );

	bp_get_template_part( '_accessoires/invites/index' );

	/**
	 * Fires after the send invites content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_after_group_send_invites_content' );
}

/**
 * Gets the displayed user group invites preferences
 *
 * @since  1.0.0
 *
 * @return int 1 if user chose to restrict to friends. O otherwise.
 */
function bp_nouveau_groups_get_group_invites_setting() {
	return (int) bp_get_user_meta( bp_displayed_user_id(), '_bp_nouveau_restrict_invites_to_friends' );
}

/**
 * Load the requested Create Screen for the new group.
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_group_creation_screen() {
	return bp_nouveau_group_manage_screen();
}

/**
 * Load the requested Manage Screen for the current group.
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_group_manage_screen() {
	$action          = bp_action_variable(0);
	$is_group_create = bp_is_group_create();
	$output          = '';

	if ( $is_group_create ) {
		$action = bp_action_variable(1);
	}

	$screen_id = sanitize_file_name( $action );

	if ( ! bp_is_group_admin_screen( $screen_id ) && ! bp_is_group_creation_step( $screen_id ) ) {
		return;
	}

	if ( ! $is_group_create ) {
		/**
		 * Fires inside the group admin form and before the content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_before_group_admin_content' );

		$core_screen = bp_nouveau_group_get_core_manage_screens( $screen_id );

	// It's a group step, get the creation screens.
	} else {
		$core_screen = bp_nouveau_group_get_core_create_screens( $screen_id );
	}

	if ( false === $core_screen ) {
		if ( ! $is_group_create ) {
			/**
			 * Fires inside the group admin template.
			 *
			 * Allows plugins to add custom group edit screens.
			 *
			 * @since 1.1.0
			 */
			do_action( 'groups_custom_edit_steps' );

		// Else use the group create hook
		} else {
			/**
			 * Fires inside the group admin template.
			 *
			 * Allows plugins to add custom group creation steps.
			 *
			 * @since 1.1.0
			 */
			do_action( 'groups_custom_create_steps' );
		}

	// Else we load the core screen.
	} else {
		if ( ! empty( $core_screen['hook'] ) ) {
			/**
			 * Fires before the display of group delete admin.
			 *
			 * @since 1.1.0 For most hooks.
			 * @since 2.4.0 For the cover image hook.
			 */
			do_action( 'bp_before_' . $core_screen['hook'] );
		}

		$template = 'groups/single/admin/' . $screen_id;

		if ( ! empty( $core_screen['template'] ) ) {
			$template = $core_screen['template'];
		}

		bp_get_template_part( $template );

		if ( ! empty( $core_screen['hook'] ) ) {
			/**
			 * Fires before the display of group delete admin.
			 *
			 * @since 1.1.0 For most hooks.
			 * @since 2.4.0 For the cover image hook.
			 */
			do_action( 'bp_after_' . $core_screen['hook'] );
		}

		if ( ! empty( $core_screen['nonce'] ) ) {

			if ( ! $is_group_create ) {
				$output = sprintf( '<p><input type="submit" value="%s" id="save" name="save" /></p>', esc_attr__( 'Save Changes', 'bp-nouveau' ) );

				// Specific case for the delete group screen
				if ( 'delete-group' === $screen_id ) {
					$output = sprintf( '<div class="submit">
							<input type="submit" disabled="disabled" value="%s" id="delete-group-button" name="delete-group-button" />
						</div>',
						esc_attr__( 'Delete Group', 'bp-nouveau' )
					);
				}
			}

			wp_nonce_field( $core_screen['nonce'] );
		}
	}

	if ( $is_group_create ) {
		/**
		 * Fires before the display of the group creation step buttons.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_before_group_creation_step_buttons' );

		if ( 'crop-image' != bp_get_avatar_admin_step() ) {
			$creation_step_buttons = '';

			if ( ! bp_is_first_group_creation_step() ) {
				$creation_step_buttons .= sprintf( '<input type="button" value="%1$s" id="group-creation-previous" name="previous" onclick="%2$s" />',
					esc_attr__( 'Back to Previous Step', 'bp-nouveau' ),
					"location.href='" . esc_url( bp_get_group_creation_previous_link() ) . "'"
				);
			}

			if ( ! bp_is_last_group_creation_step() && ! bp_is_first_group_creation_step() ) {
				$creation_step_buttons .= sprintf( '<input type="submit" value="%s" id="group-creation-next" name="save" />',
					esc_attr__( 'Next Step', 'bp-nouveau' )
				);
			}

			if ( bp_is_first_group_creation_step() ) {
				$creation_step_buttons .= sprintf( '<input type="submit" value="%s" id="group-creation-create" name="save" />',
					esc_attr__( 'Create Group and Continue', 'bp-nouveau' )
				);
			}

			if ( bp_is_last_group_creation_step() ) {
				$creation_step_buttons .= sprintf( '<input type="submit" value="%s" id="group-creation-finish" name="save" />',
					esc_attr__( 'Finish', 'bp-nouveau' )
				);
			}

			// Set the output for the buttons
			$output = sprintf( '<div class="submit" id="previous-next">%s</div>', $creation_step_buttons );
		}

		/**
		 * Fires after the display of the group creation step buttons.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_after_group_creation_step_buttons' );
	}

	// This way we are absolutely sure this hidden field won't be removed from the template :)
	printf( '<input type="hidden" name="group-id" id="group-id" value="%s" />', $is_group_create ? bp_get_new_group_id() : bp_get_group_id() );

	// The submit actions
	echo $output;

	if ( ! $is_group_create ) {
		/**
		 * Fires inside the group admin form and after the content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_after_group_admin_content' );

	// We use a strange hook for the create screens...??
	} else {
		/**
		 * Fires and displays the groups directory content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_directory_groups_content' );
	}
}

/**
 * Output the action buttons for the displayed group
 *
 * @since 1.0.0
 *
 * @param  array $args @see bp_nouveau_wrapper() for the description of parameters.
 * @return string HTML Output
 */
function bp_nouveau_group_header_buttons( $args = array() ) {
	$bp_nouveau = bp_nouveau();

	$output = join( ' ', bp_nouveau_get_groups_buttons() );

	/**
	 * On the group's header we need to reset the group button's global
	 */
	if ( ! empty( $bp_nouveau->groups->group_buttons ) ) {
		unset( $bp_nouveau->groups->group_buttons );
	}

	ob_start();
	/**
	 * Fires in the group header actions section.
	 *
	 * @since 1.2.6
	 */
	do_action( 'bp_group_header_actions' );
	$output .= ob_get_clean();

	if ( empty( $output ) ) {
		return;
	}

	if ( empty( $args ) ) {
		$args = array( 'classes' => array( 'item-buttons' ) );
	}

	return bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

/**
 * Output the action buttons inside the groups loop.
 *
 * @since 1.0.0
 *
 * @param  array $args @see bp_nouveau_wrapper() for the description of parameters.
 * @return string HTML Output
 */
function bp_nouveau_groups_loop_buttons( $args = array() ) {
	if ( empty( $GLOBALS['groups_template'] ) ) {
		return;
	}

	$output = join( ' ', bp_nouveau_get_groups_buttons( 'loop' ) );

	ob_start();
	/**
	 * Fires inside the action section of an individual group listing item.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_directory_groups_actions' );
	$output .= ob_get_clean();

	if ( empty( $output ) ) {
		return;
	}

	return bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

/**
 * Output the action buttons inside the invites loop of the displayed user.
 *
 * @since 1.0.0
 *
 * @param  array $args @see bp_nouveau_wrapper() for the description of parameters.
 * @return string HTML Output
 */
function bp_nouveau_groups_invite_buttons( $args = array() ) {
	if ( empty( $GLOBALS['groups_template'] ) ) {
		return;
	}

	$output = join( ' ', bp_nouveau_get_groups_buttons( 'invite' ) );

	ob_start();
	/**
	 * Fires inside the member group item action markup.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_group_invites_item_action' );
	$output .= ob_get_clean();

	if ( empty( $output ) ) {
		return;
	}

	return bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

/**
 * Output the action buttons inside the requests loop of the group's manage screen.
 *
 * @since 1.0.0
 *
 * @param  array $args @see bp_nouveau_wrapper() for the description of parameters.
 * @return string HTML Output
 */
function bp_nouveau_groups_request_buttons( $args = array() ) {
	if ( empty( $GLOBALS['requests_template'] ) ) {
		return;
	}

	$output = join( ' ', bp_nouveau_get_groups_buttons( 'request' ) );

	ob_start();
	/**
	 * Fires inside the list of membership request actions.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_group_membership_requests_admin_item_action' );
	$output .= ob_get_clean();

	if ( empty( $output ) ) {
		return;
	}

	return bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

/**
 * Output the action buttons inside the manage members loop of the group's manage screen.
 *
 * @since 1.0.0
 *
 * @param  array $args @see bp_nouveau_wrapper() for the description of parameters.
 * @return string HTML Output
 */
function bp_nouveau_groups_manage_members_buttons( $args = array() ) {
	if ( empty( $GLOBALS['members_template'] ) ) {
		return;
	}

	$output = join( ' ', bp_nouveau_get_groups_buttons( 'manage_members' ) );

	ob_start();
	/**
	 * Fires inside the display of a member admin item in group management area.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_group_manage_members_admin_item' );
	$output .= ob_get_clean();

	if ( empty( $output ) ) {
		return;
	}

	if ( empty( $args ) ) {
		$args = array( 'wrapper' => 'span', 'classes' => array( 'small' ) );
	}

	return bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

	/**
	 * Get the action buttons for the current group in the loop,
	 * or the current displayed group
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_groups_buttons( $type = 'group' ) {
		// Not really sure why BP Legacy needed to do this...
		if ( 'group' === $type && is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$buttons = array();

		if ( ( 'loop' === $type || 'invite' ===  $type ) && isset( $GLOBALS['groups_template']->group ) ) {
			$group = $GLOBALS['groups_template']->group;
		} else {
			$group = groups_get_current_group();
		}

		if ( empty( $group->id ) ) {
			return $buttons;
		}

		// Invite buttons on member's invites screen
		if ( 'invite' === $type ) {
			// Don't show button if not logged in or previously banned
			if ( ! is_user_logged_in() || bp_group_is_user_banned( $group ) || empty( $group->status ) ) {
				return $buttons;
			}

			// Setup Accept button attributes
			$buttons['accept_invite'] =  array(
				'id'                => 'accept_invite',
				'position'          => 5,
				'component'         => 'groups',
				'must_be_logged_in' => true,
				'link_href'         => esc_url( bp_get_group_accept_invite_link() ),
				'link_class'        => 'button accept group-button accept-invite',
				'link_title'        => esc_attr__( 'Accept', 'bp-nouveau' ),
				'link_text'         => esc_html__( 'Accept', 'bp-nouveau' ),
			);

			// Setup Reject button attributes
			$buttons['reject_invite'] = array(
				'id'                => 'reject_invite',
				'position'          => 15,
				'component'         => 'groups',
				'must_be_logged_in' => true,
				'link_href'         => esc_url( bp_get_group_reject_invite_link() ),
				'link_text'         => __( 'Reject', 'bp-nouveau' ),
				'link_title'        => __( 'Reject', 'bp-nouveau' ),
				'link_class'        => 'button reject group-button reject-invite',
			);

		// Request button for the group's manage screen
		} elseif ( 'request' === $type ) {
			// Setup Accept button attributes
			$buttons['group_membership_accept'] =  array(
				'id'                => 'group_membership_accept',
				'position'          => 5,
				'component'         => 'groups',
				'must_be_logged_in' => true,
				'wrapper_class'     => 'accept',
				'link_href'         => esc_url( bp_get_group_request_accept_link() ),
				'link_title'        => esc_attr__( 'Accept', 'bp-nouveau' ),
				'link_text'         => esc_html__( 'Accept', 'bp-nouveau' ),
			);

			// Setup Reject button attributes
			$buttons['group_membership_reject'] = array(
				'id'                => 'group_membership_reject',
				'position'          => 15,
				'component'         => 'groups',
				'must_be_logged_in' => true,
				'wrapper_class'     => 'reject',
				'link_href'         => esc_url( bp_get_group_request_reject_link() ),
				'link_text'         => __( 'Reject', 'bp-nouveau' ),
				'link_title'        => __( 'Reject', 'bp-nouveau' ),
			);

		// Manage group members for the group's manage screen
		} elseif ( 'manage_members' === $type && isset( $GLOBALS['members_template']->member->user_id ) ) {
			$user_id = $GLOBALS['members_template']->member->user_id;

			$buttons = array( 'unban_member' => array(
					'id'                => 'unban_member',
					'position'          => 5,
					'component'         => 'groups',
					'must_be_logged_in' => true,
					'link_href'         => esc_url( bp_get_group_member_unban_link( $user_id ) ),
					'link_text'         => __( 'Remove Ban', 'bp-nouveau' ),
					'link_title'        => __( 'Unban this member', 'bp-nouveau' ),
					'link_class'        => 'button confirm member-unban',
				), 'ban_member' => array(
					'id'                => 'ban_member',
					'position'          => 15,
					'component'         => 'groups',
					'must_be_logged_in' => true,
					'link_href'         => esc_url( bp_get_group_member_ban_link( $user_id ) ),
					'link_text'         => __( 'Kick &amp; Ban', 'bp-nouveau' ),
					'link_title'        => __( 'Kick and ban this member', 'bp-nouveau' ),
					'link_class'        => 'button confirm member-ban',
				), 'promote_mod' => array(
					'id'                => 'promote_mod',
					'position'          => 25,
					'component'         => 'groups',
					'must_be_logged_in' => true,
					'link_href'         => esc_url( bp_get_group_member_promote_mod_link() ),
					'link_text'         => __( 'Promote to Mod', 'bp-nouveau' ),
					'link_title'        => __( 'Promote to Mod', 'bp-nouveau' ),
					'link_class'        => 'button confirm member-promote-to-mod',
				), 'promote_admin' => array(
					'id'                => 'promote_admin',
					'position'          => 35,
					'component'         => 'groups',
					'must_be_logged_in' => true,
					'link_href'         => esc_url( bp_get_group_member_promote_admin_link() ),
					'link_text'         => __( 'Promote to Admin', 'bp-nouveau' ),
					'link_title'        => __( 'Promote to Admin', 'bp-nouveau' ),
					'link_class'        => 'button confirm member-promote-to-admin',
				), 'remove_member' => array(
					'id'                => 'remove_member',
					'position'          => 45,
					'component'         => 'groups',
					'must_be_logged_in' => true,
					'link_href'         => esc_url( bp_get_group_member_remove_link( $user_id ) ),
					'link_text'         => __( 'Remove from group', 'bp-nouveau' ),
					'link_title'        => __( 'Remove this member', 'bp-nouveau' ),
					'link_class'        => 'button confirm',
				),
			);

		// Membership button on groups loop or single group's header
		} else {
			/**
			 * This filter workaround is waiting for a core adaptation
			 * so that we can directly get the groups button arguments
			 * instead of the button.
			 * @see https://buddypress.trac.wordpress.org/ticket/7126
			 */
			add_filter( 'bp_get_group_join_button', 'bp_nouveau_groups_catch_button_args', 100, 1 );

			bp_get_group_join_button( $group );

			remove_filter( 'bp_get_group_join_button', 'bp_nouveau_groups_catch_button_args', 100, 1 );

			if ( ! empty( bp_nouveau()->groups->button_args ) ) {
				$buttons['group_membership'] = wp_parse_args( array(
					'id'       => 'group_membership',
					'position' => 5,
				), bp_nouveau()->groups->button_args );

				unset( bp_nouveau()->groups->button_args );
			}
		}

		/**
		 * Filter here to add your buttons, use the position argument to choose where to insert it.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $buttons The list of buttons.
		 * @param int    $group   The current group object.
		 * @parem string $type    Whether we're displaying a groups loop or a groups single item.
		 */
		$buttons_group = apply_filters( 'bp_nouveau_get_groups_buttons', $buttons, $group, $type );

		if ( empty( $buttons_group ) ) {
			return $buttons;
		}

		// It's the first entry of the loop, so build the Group and sort it
		if ( ! isset( bp_nouveau()->groups->group_buttons ) || false === is_a( bp_nouveau()->groups->group_buttons, 'BP_Buttons_Group' ) ) {
			$sort = true;
			bp_nouveau()->groups->group_buttons = new BP_Buttons_Group( $buttons_group );

		// It's not the first entry, the order is set, we simply need to update the Buttons Group
		} else {
			$sort = false;
			bp_nouveau()->groups->group_buttons->update( $buttons_group );
		}

		$return = bp_nouveau()->groups->group_buttons->get( $sort );

		if ( ! $return ) {
			return array();
		}

		// Remove buttons according to the user's membership type.
		if ( 'manage_members' === $type && isset( $GLOBALS['members_template'] ) ) {
			if ( bp_get_group_member_is_banned() ) {
				unset( $return['ban_member'], $return['promote_mod'], $return['promote_admin'] );
			} else {
				unset( $return['unban_member'] );
			}
		}

		/**
		 * Leave a chance to adjust the $return
		 *
		 * @since 1.0.0
		 *
		 * @param array  $return  The list of buttons.
		 * @param int    $group   The current group object.
		 * @parem string $type    Whether we're displaying a groups loop or a groups single item.
		 */
		do_action_ref_array( 'bp_nouveau_return_groups_buttons', array( &$return, $group, $type ) );

		return $return;
	}

/**
 * Does the group has meta.
 *
 * @since  1.0.0
 *
 * @return bool True if the group has meta. False otherwise.
 */
function bp_nouveau_group_has_meta() {
	return (bool) bp_nouveau_get_group_meta();
}

/**
 * Display the group meta.
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_group_meta() {
	$meta = bp_nouveau_get_group_meta();

	if ( ! bp_is_group() ) {
		echo join( ' / ', array_map( 'esc_html', (array) $meta ) );
	} else {
		echo join( "\n", $meta );
	}
}

	/**
	 * Get the group meta.
	 *
	 * @since  1.0.0
	 *
	 * @return array The group meta.
	 */
	function bp_nouveau_get_group_meta() {
		$meta     = array();
		$is_group = bp_is_group();

		if ( ! empty( $GLOBALS['groups_template']->group ) ) {
			$group = $GLOBALS['groups_template']->group;
		}

		if ( empty( $group->id ) ) {
			return $meta;
		}

		if ( empty( $group->template_meta ) ) {
			// It's a single group
			if ( $is_group ) {
				/**
				 * If the Group's default front page issn't set to display
				 * the description inside it, include the description to metas
				 */
				if ( ! bp_nouveau_groups_front_page_description() ) {
					$meta = array(
						'description' => bp_get_group_description(),
					);
				}

				// Make sure to include hooked meta.
				$extra_meta = bp_nouveau_get_hooked_group_meta();

				if ( $extra_meta ) {
					$meta['extra'] = $extra_meta;
				}

			// We're in the groups loop
			} else {
				$meta = array(
					'status' => bp_get_group_type(),
					'count'  => bp_get_group_member_count(),
				);
			}

			/**
			 * Filter here to add/remove Group meta.
			 *
			 * @since  1.0.0
			 *
			 * @param array  $meta     The list of meta to output.
			 * @param object $group    The current Group of the loop object.
			 * @param bool   $is_group True if a single group is displayed. False otherwise.
			 */
			$group->template_meta = apply_filters( 'bp_nouveau_get_group_meta', $meta, $group, $is_group );
		}

		return $group->template_meta;
	}

/**
 * Load the appropriate content for the single group pages
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_group_template_part() {
	/**
	 * Fires before the display of the group home body.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_before_group_body' );

	$bp_is_group_home = bp_is_group_home();

	if ( $bp_is_group_home && ! bp_group_is_visible() ) {
		/**
		 * Fires before the display of the group status message.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_before_group_status_message' ); ?>

		<div id="message" class="info">
			<p><?php bp_group_status_message(); ?></p>
		</div>

		<?php

		/**
		 * Fires after the display of the group status message.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_after_group_status_message' );

	// We have a front template, Use BuddyPress function to load it.
	} elseif ( $bp_is_group_home && false !== bp_groups_get_front_template() ) {
		bp_groups_front_template_part();

	// Otherwise use BP_Nouveau template hierarchy
	} else {
		$template = 'plugins';

		// the home page
		if ( $bp_is_group_home ) {
			if ( bp_is_active( 'activity' ) ) {
				$template = 'activity';
			} else {
				$template = 'members';
			}

		// Not the home page
		} elseif ( bp_is_group_admin_page() ) {
			$template = 'admin';
		} elseif ( bp_is_group_activity() ) {
			$template = 'activity';
		} elseif ( bp_is_group_members() ) {
			$template = 'members';
		} elseif ( bp_is_group_invites() ) {
			$template = 'send-invites';
		} elseif ( bp_is_group_membership_request() ) {
			$template = 'request-membership';
		}

		bp_nouveau_group_get_template_part( $template );
	}

	/**
	 * Fires after the display of the group home body.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_after_group_body' );
}

/**
 * Use the appropriate Group header and enjoy a template hierarchy
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_group_header_template_part() {
	$template = 'group-header';

	if ( bp_group_use_cover_image_header() ) {
		$template = 'cover-image-header';
	}

	/**
	 * Fires before the display of a group's header.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_before_group_header' );

	// Get the template part for the header
	bp_nouveau_group_get_template_part( $template );

	/**
	 * Fires after the display of a group's header.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_after_group_header' );

	bp_nouveau_template_notices();
}

/**
 * Get a link to set the Group's default front page and directly
 * reach the Customizer section where it's possible to do it.
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_groups_get_customizer_option_link() {
	return bp_nouveau_get_customizer_link( array(
		'object'    => 'group',
		'autofocus' => 'bp_nouveau_group_front_page',
		'text'      => esc_html__( 'Groups default front page', 'bp-nouveau' ),
	) );
}

/**
 * Get a link to set the Group's front page widgets and directly
 * reach the Customizer section where it's possible to do it.
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_groups_get_customizer_widgets_link() {
	return bp_nouveau_get_customizer_link( array(
		'object'    => 'group',
		'autofocus' => 'sidebar-widgets-sidebar-buddypress-groups',
		'text'      => esc_html__( '(BuddyPress) Widgets', 'bp-nouveau' ),
	) );
}
