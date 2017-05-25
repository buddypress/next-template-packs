<?php
/**
 * Members template tags
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template tag to wrap all Legacy actions that was used
 * before the members directory content
 *
 * @since 1.0.0
 */
function bp_nouveau_before_members_directory_content() {
	/**
	 * Fires at the begining of the templates BP injected content.
	 *
	 * @since 2.3.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_members_page' );

	/**
	 * Fires before the display of the members.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_members' );

	/**
	 * Fires before the display of the members content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_members_content' );

	/**
	 * Fires before the display of the members list tabs.
	 *
	 * @since 1.8.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_members_tabs' );
}

/**
 * Template tag to wrap all Legacy actions that was used
 * after the members directory content
 *
 * @since 1.0.0
 */
function bp_nouveau_after_members_directory_content() {
	/**
	 * Fires and displays the members content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_directory_members_content' );

	/**
	 * Fires after the display of the members content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_members_content' );

	/**
	 * Fires after the display of the members.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_members' );

	/**
	 * Fires at the bottom of the members directory template file.
	 *
	 * @since 1.5.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_members_page' );
}

/**
 * Fire specific hooks into the single members templates
 *
 * @since 1.0.0
 *
 * @param string $when    'before' or 'after'
 * @param string $suffix  Use it to add terms at the end of the hook name
 */
function bp_nouveau_member_hook( $when = '', $suffix = '' ) {
	$hook = array( 'bp' );

	if ( ! empty( $when ) ) {
		$hook[] = $when;
	}

	// It's a member hook
	$hook[] = 'member';

	if ( ! empty( $suffix ) ) {
		$hook[] = $suffix;
	}

	/**
	 * @since 1.2.0 (BuddyPress) for the 'activity_content', 'blogs_content', 'header_meta',
	 *                           'friends_content', 'groups_content', 'home_content', 'plugin_template'
	 *                           'friend_requests_content' suffixes.
	 * @since 1.5.0 (BuddyPress) for the 'settings_template' suffix.
	 */
	return bp_nouveau_hook( $hook );
}

/**
 * Template tag to wrap the notification settings hook
 *
 * @since 1.0.0
 */
function bp_nouveau_member_email_notice_settings() {
	/**
	 * Fires at the top of the member template notification settings form.
	 *
	 * @since 1.0.0
	 */
	do_action( 'bp_notification_settings' );
}

/**
 * Output the action buttons for the displayed user profile
 *
 * @since 1.0.0
 *
 * @param  array $args @see bp_nouveau_wrapper() for the description of parameters.
 * @return string HTML Output
 */
function bp_nouveau_member_header_buttons( $args = array() ) {
	$bp_nouveau = bp_nouveau();

	if( bp_is_user() ) {
		$args['type'] = 'profile';
	} else {
		$args['type'] = 'header';// we have no real need for this 'type' on header actions
	}

	$output = join( ' ', bp_nouveau_get_members_buttons( $args ) );

	/**
	 * On the member's header we need to reset the group button's global
	 * once displayed as the friends component will use the member's loop
	 */
	if ( ! empty( $bp_nouveau->members->member_buttons ) ) {
		unset( $bp_nouveau->members->member_buttons );
	}

	ob_start();
	/**
	 * Fires in the member header actions section.
	 *
	 * @since 1.2.6 (BuddyPress)
	 */
	do_action( 'bp_member_header_actions' );
	$output .= ob_get_clean();

	if ( empty( $output ) ) {
		return;
	}

	if ( empty( $args ) ) {
		$args = array( 'id' => 'item-buttons', 'classes' => false );
	}

	return bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

/**
 * Output the action buttons in member loops
 *
 * @since 1.0.0
 *
 * @param  array $args @see bp_nouveau_wrapper() for the description of parameters.
 * @return string HTML Output
 */
function bp_nouveau_members_loop_buttons( $args = array() ) {
	if ( empty( $GLOBALS['members_template'] ) ) {
		return;
	}

	$args['type'] = 'loop';
	$action = 'bp_directory_members_actions';

	// specific case for group members
	if ( bp_is_active( 'groups' ) && bp_is_group_members() ) {
		$args['type']   = 'group_member';
		$action = 'bp_group_members_list_item_action';
	} elseif ( bp_is_active( 'friends' ) && bp_is_user_friend_requests() ) {
		$args['type']   = 'friendship_request';
		$action = 'bp_friend_requests_item_action';
	}

	$output = join( ' ', bp_nouveau_get_members_buttons( $args ) );

	ob_start();
	/**
	 * Fires inside the members action HTML markup to display actions.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( $action );
	$output .= ob_get_clean();

	if ( empty( $output ) ) {
		return;
	}

	return bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

	/**
	 * Get the action buttons for the displayed user profile
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_members_buttons( $args ) {

		$type = ( ! empty( $args['type'] ) ) ?  $args['type'] : '';

		// Not really sure why BP Legacy needed to do this...
		if ( 'profile' === $type && is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$buttons = array();

		if ( 'loop' === $type || 'friendship_request' === $type ) {
			$user_id = bp_get_member_user_id();
		} elseif ( 'group_member' === $type ) {
			$user_id = bp_get_group_member_id();
		} else {
			$user_id = bp_displayed_user_id();
		}

		if ( ! $user_id ) {
			return $buttons;
		}

		/**
		 * If the 'container' is set to 'ul'
		 * set a var $parent_element to li
		 * otherwise simply pass any value found in args
		 * or set var false.
		 */
		if( ! empty( $args['container'] ) && 'ul' == $args['container']  ) {
			$parent_element = 'li';
		} elseif( ! empty( $args['parent_element'] ) ) {
			$parent_element = esc_html( $args['parent_element'] );
		} else {
			$parent_element = false;
		}

		/**
		 * If we have a arg value for $button_element passed through
		 * use it to default all the $buttons['button_element'] values
		 * otherwise default to 'a' (anchor)
		 * Or override & hardcode the 'element' string on $buttons array.
		 *
		 * Icons sets a class for icon display if not using the button element
		 */
		$icons = '';
		if( ! empty( $args['button_element'] ) ) {
			$button_element = $args['button_element'] ;
		} else {
			$button_element = 'a';
			$icons = ' icons';
		}

		// If we pass through parent classes add them to $button array
		$parent_class = '';
		if( ! empty( $args['parent_attr']['class'] ) ) {
			$parent_class = esc_html( $args['parent_attr']['class'] );
		}

		if ( bp_is_active( 'friends' ) ) {
			// It's the member's friendship requests screen
			if (  'friendship_request' === $type ) {
				$buttons = array( 'accept_friendship' => array(
						'id'                => 'accept_friendship',
						'position'          => 5,
						'component'         => 'friends',
						'must_be_logged_in' => true,
						'parent_element'    => $parent_element,
						'parent_attr'       => array(
							'id'               => '',
							'class'            => $parent_class ,
							),
						'button_element'    => $button_element,
						'button_attr'       => array (
							'class'            => 'button accept',
							'rel'              => '',
							'title'            => __( 'Accept', 'bp-nouveau' ),
							),
						'link_text'         => __( 'Accept', 'bp-nouveau' ),
					), 'reject_friendship' => array(
						'id'                => 'reject_friendship',
						'position'          => 15,
						'component'         => 'friends',
						'must_be_logged_in' => true,
						'parent_element'    => $parent_element,
						'parent_attr'       => array(
							'id'               => '',
							'class'            => $parent_class,
							),
						'button_element'    => $button_element,
						'button_attr'       => array (
							'class'            => 'button reject',
							'rel'              => '',
							'title'            => __( 'Reject', 'bp-nouveau' ),
							),
						'link_text'         => __( 'Reject', 'bp-nouveau' ),
					),
				);

				// If button element set add nonce link to data attr
				if ( 'button' === $button_element ) {
					$buttons['accept_friendship']['button_attr']['data-bp-nonce']  = esc_url( bp_get_friend_accept_request_link() );
					$buttons['reject_friendship']['button_attr']['data-bp-nonce']  = esc_url( bp_get_friend_reject_request_link() );
				} else {
					$buttons['accept_request']['button_attr']['href']     = esc_url( bp_get_friend_accept_request_link() );
					$buttons['reject_friendship']['button_attr']['href']  = esc_url( bp_get_friend_reject_request_link() );
				}

			// It's any other members screen
			} else {
				/**
				 * This filter workaround is waiting for a core adaptation
				 * so that we can directly get the friends button arguments
				 * instead of the button.
				 * @see https://buddypress.trac.wordpress.org/ticket/7126
				 */
				add_filter( 'bp_get_add_friend_button', 'bp_nouveau_members_catch_button_args', 100, 1 );

				bp_get_add_friend_button( $user_id );

				remove_filter( 'bp_get_add_friend_button', 'bp_nouveau_members_catch_button_args', 100, 1 );

				if ( ! empty( bp_nouveau()->members->button_args ) ) {
					$button_args = bp_nouveau()->members->button_args;

					$buttons['member_friendship'] = array(
						'id'                => 'member_friendship',
						'position'          => 5,
						'component'         => $button_args['component'],
						'must_be_logged_in' => $button_args['must_be_logged_in'],
						'block_self'        => $button_args['block_self'],
						'parent_element'    => $parent_element,
						'parent_attr'       => array(
								'id'              => $button_args['wrapper_id'],
								'class'           => $parent_class . ' ' . $button_args['wrapper_class'],
							),
						'button_element'    => $button_element,
						'button_attr'       => array(
							'id'               => $button_args['link_id'],
							'class'            => $button_args['link_class'],
							'rel'              => $button_args['link_rel'],
							'title'            => '',
							),
						'link_text'         => $button_args['link_text'],
					);

					// If button element set add nonce link to data attr
					if ( 'button' === $button_element ) {
						$buttons['member_friendship']['button_attr']['data-bp-nonce'] = $button_args['link_href'];
					} else {
						$buttons['member_friendship']['button_attr']['href'] = $button_args['link_href'];
					}

					unset( bp_nouveau()->members->button_args );
				}
			}

		}

		// Only add The public and private messages when not in a loop
		if ( 'profile' === $type ) {
			if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) {
				/**
				 * This filter workaround is waiting for a core adaptation
				 * so that we can directly get the public message button arguments
				 * instead of the button.
				 * @see https://buddypress.trac.wordpress.org/ticket/7126
				 */
				add_filter( 'bp_get_send_public_message_button', 'bp_nouveau_members_catch_button_args', 100, 1 );

				bp_get_send_public_message_button();

				remove_filter( 'bp_get_send_public_message_button', 'bp_nouveau_members_catch_button_args', 100, 1 );

				if ( ! empty( bp_nouveau()->members->button_args ) ) {
					$button_args = bp_nouveau()->members->button_args;

					// This button should remain as an anchor link
					// Hardcode the use of anchor elements if button arg passed in for other elements
					$buttons['public_message'] = array(
						'id'                => $button_args['id'],
						'position'          => 15,
						'component'         => $button_args['component'],
						'must_be_logged_in' => $button_args['must_be_logged_in'],
						'block_self'        => $button_args['block_self'],
						'parent_element'    => $parent_element,
						'parent_attr'       => array(
								'id'              => $button_args['wrapper_id'],
								'class'           => $parent_class,
							),
						'button_element'    => 'a',
						'button_attr'       => array(
							'href'             => $button_args['link_href'],
							'id'               => '',
							'class'            => $button_args['link_class'],
							//'rel'              => '',
							//'title'            => '',
							),
						'link_text'         => $button_args['link_text'],
					);
					unset( bp_nouveau()->members->button_args );
				}
			}

			if ( bp_is_active( 'messages' ) ) {
				/**
				 * This filter workaround is waiting for a core adaptation
				 * so that we can directly get the private messages button arguments
				 * instead of the button.
				 * @see https://buddypress.trac.wordpress.org/ticket/7126
				 */
				add_filter( 'bp_get_send_message_button_args', 'bp_nouveau_members_catch_button_args', 100, 1 );

				bp_get_send_message_button();

				remove_filter( 'bp_get_send_message_button_args', 'bp_nouveau_members_catch_button_args', 100, 1 );

				if ( ! empty( bp_nouveau()->members->button_args ) ) {
					$button_args = bp_nouveau()->members->button_args;

					// This button should remain as an anchor link
					// Hardcode the use of anchor elements if button arg passed in for other elements
					$buttons['private_message'] = array(
						'id'                => $button_args['id'],
						'position'          => 25,
						'component'         => $button_args['component'],
						'must_be_logged_in' => $button_args['must_be_logged_in'],
						'block_self'        => $button_args['block_self'],
						'parent_element'    => $parent_element,
						'parent_attr'       => array(
								'id'              => $button_args['wrapper_id'],
								'class'           => $parent_class,
							),
						'button_element'    => 'a',
						'button_attr'       => array(
							'href'             => esc_url( trailingslashit( bp_loggedin_user_domain() . bp_get_messages_slug() ) . '#compose?r=' . bp_core_get_username( $user_id ) ),
							'id'               => false,
							'class'            => $button_args['link_class'],
							'rel'              => '',
							'title'            => __('', 'buddypress'),
							),
						'link_text'         => $button_args['link_text'],
					);

					unset( bp_nouveau()->members->button_args );
				}
			}
		}

		/**
		 * Filter here to add your buttons, use the position argument to choose where to insert it.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $buttons The list of buttons.
		 * @param int    $user_id The displayed user ID.
		 * @param string $type    Whether we're displaying a members loop or a user's page
		 */
		$buttons_group = apply_filters( 'bp_nouveau_get_members_buttons', $buttons, $user_id, $type );

		if ( empty( $buttons_group ) ) {
			return $buttons;
		}

		// It's the first entry of the loop, so build the Group and sort it
		if ( ! isset( bp_nouveau()->members->member_buttons ) || false === is_a( bp_nouveau()->members->member_buttons, 'BP_Buttons_Group' ) ) {
			$sort = true;
			bp_nouveau()->members->member_buttons = new BP_Buttons_Group( $buttons_group );

		// It's not the first entry, the order is set, we simply need to update the Buttons Group
		} else {
			$sort = false;
			bp_nouveau()->members->member_buttons->update( $buttons_group );
		}

		$return = bp_nouveau()->members->member_buttons->get( $sort );

		if ( ! $return ) {
			return array();
		}

		/**
		 * Leave a chance to adjust the $return
		 *
		 * @since 1.0.0
		 *
		 * @param array  $return  The list of buttons ordered.
		 * @param int    $user_id The displayed user ID.
		 * @param string $type    Whether we're displaying a members loop or a user's page
		 */
		do_action_ref_array( 'bp_nouveau_return_members_buttons', array( &$return, $user_id, $type ) );

		return $return;
	}

/**
 * Does the member has meta.
 *
 * @since  1.0.0
 *
 * @return bool True if the member has meta. False otherwise.
 */
function bp_nouveau_member_has_meta() {
	return (bool) bp_nouveau_get_member_meta();
}

/**
 * Display the member meta.
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_member_meta() {
	$meta = bp_nouveau_get_member_meta();

	echo join( "\n", $meta );
}

	/**
	 * Get the member meta.
	 *
	 * @since  1.0.0
	 *
	 * @return array The member meta.
	 */
	function bp_nouveau_get_member_meta() {
		$meta     = array();
		$is_loop  = false;

		if ( ! empty( $GLOBALS['members_template']->member ) ) {
			$member = $GLOBALS['members_template']->member;
			$is_loop = true;
		} else {
			$member = bp_get_displayed_user();
		}

		if ( empty( $member->id ) ) {
			return $meta;
		}

		if ( empty( $member->template_meta ) ) {
			// It's a single user's header
			if ( ! $is_loop ) {
				$meta['last_activity'] = sprintf( '<span class="activity">%s</span>', bp_get_last_activity( bp_displayed_user_id() ) );

			// We're in the members loop
			} else {
				$meta = array(
					'last_activity' => sprintf( '%s', bp_get_member_last_active() ),
				);
			}

			// Make sure to include hooked meta.
			$extra_meta = bp_nouveau_get_hooked_member_meta();

			if ( $extra_meta ) {
				$meta['extra'] = $extra_meta;
			}

			/**
			 * Filter here to add/remove Member meta.
			 *
			 * @since  1.0.0
			 *
			 * @param array  $meta     The list of meta to output.
			 * @param object $member   The member object
			 * @param bool   $is_loop  True if in the members loop. False otherwise.
			 */
			$member->template_meta = apply_filters( 'bp_nouveau_get_member_meta', $meta, $member, $is_loop );
		}

		return $member->template_meta;
	}

/**
 * Load the appropriate content for the single member pages
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_member_template_part() {
	/**
	 * Fires before the display of member body content.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_before_member_body' );

	if ( bp_is_user_front() ) {
		bp_displayed_user_front_template_part();
	} else {
		$template = 'plugins';

		if ( bp_is_user_activity() ) {
			$template = 'activity';
		} elseif ( bp_is_user_blogs() ) {
			$template = 'blogs';
		} elseif ( bp_is_user_friends() ) {
			$template = 'friends';
		} elseif ( bp_is_user_groups() ) {
			$template = 'groups';
		} elseif ( bp_is_user_messages() ) {
			$template = 'messages';
		} elseif ( bp_is_user_profile() ) {
			$template = 'profile';
		} elseif ( bp_is_user_notifications() ) {
			$template = 'notifications';
		} elseif ( bp_is_user_settings() ) {
			$template = 'settings';
		}

		bp_nouveau_member_get_template_part( $template );
	}

	/**
	 * Fires after the display of member body content.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_after_member_body' );
}

/**
 * Use the appropriate Member header and enjoy a template hierarchy
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_member_header_template_part() {
	$template = 'member-header';

	if ( bp_displayed_user_use_cover_image_header() ) {
		$template = 'cover-image-header';
	}

	/**
	 * Fires before the display of a member's header.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_before_member_header' );

	// Get the template part for the header
	bp_nouveau_member_get_template_part( $template );

	/**
	 * Fires after the display of a member's header.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_after_member_header' );

	// Display template notices if any
	bp_nouveau_template_notices();
}

/**
 * Get a link to set the Member's default front page and directly
 * reach the Customizer section where it's possible to do it.
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_members_get_customizer_option_link() {
	return bp_nouveau_get_customizer_link( array(
		'object'    => 'user',
		'autofocus' => 'bp_nouveau_user_front_page',
		'text'      => esc_html__( 'Members default front page', 'bp-nouveau' ),
	) );
}

/**
 * Get a link to set the Member's front page widgets and directly
 * reach the Customizer section where it's possible to do it.
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_members_get_customizer_widgets_link() {
	return bp_nouveau_get_customizer_link( array(
		'object'    => 'user',
		'autofocus' => 'sidebar-widgets-sidebar-buddypress-members',
		'text'      => esc_html__( '(BuddyPress) Widgets', 'bp-nouveau' ),
	) );
}

/**
 * Display the Member description making sure linefeeds are taking in account
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_member_description( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		$user_id = bp_loggedin_user_id();

		if ( bp_displayed_user_id() ) {
			$user_id = bp_displayed_user_id();
		}
	}

	add_filter( 'the_author_description', 'make_clickable', 9 );
	add_filter( 'the_author_description', 'wpautop'           );
	add_filter( 'the_author_description', 'wptexturize'       );
	add_filter( 'the_author_description', 'convert_smilies'   );
	add_filter( 'the_author_description', 'convert_chars'     );
	add_filter( 'the_author_description', 'stripslashes'      );

	the_author_meta( 'description', $user_id );

	remove_filter( 'the_author_description', 'make_clickable', 9 );
	remove_filter( 'the_author_description', 'wpautop'           );
	remove_filter( 'the_author_description', 'wptexturize'       );
	remove_filter( 'the_author_description', 'convert_smilies'   );
	remove_filter( 'the_author_description', 'convert_chars'     );
	remove_filter( 'the_author_description', 'stripslashes'      );
}

/**
 * Display the Edit profile link (temporary)
 * @todo  replace with Ajax feature
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_member_description_edit_link() {
	echo bp_nouveau_member_get_description_edit_link();
}

	/**
	 * Get the Edit profile link (temporary)
	 * @todo  replace with Ajax featur
	 *
	 * @since  1.0.0
	 *
	 * @return string HTML Output
	 */
	function bp_nouveau_member_get_description_edit_link() {
		// Disable the filter
		remove_filter( 'edit_profile_url', 'bp_members_edit_profile_url', 10, 3 );

		if ( is_multisite() && ! current_user_can( 'read' ) ) {
			$link = get_dashboard_url( bp_displayed_user_id(), 'profile.php' );
		} else {
			$link = get_edit_profile_url( bp_displayed_user_id() );
		}

		// Restore the filter
		add_filter( 'edit_profile_url', 'bp_members_edit_profile_url', 10, 3 );

		$link .= '#description';

		return sprintf( '<a href="%1$s">%2$s</a>', esc_url( $link ), esc_html__( 'Edit your bio', 'bp-nouveau' ) );
	}

/** WP Profile tags **********************************************************/

/**
 * Template tag to wrap all Legacy actions that was used
 * before and after the WP User's Profile.
 *
 * @since 1.0.0
 */
function bp_nouveau_wp_profile_hooks( $type = 'before' ) {
	if ( 'before' === $type ) {
		/**
		 * Fires before the display of member profile loop content.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_before_profile_loop_content' );

		/**
		 * Fires before the display of member profile field content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_before_profile_field_content' );
	} else {
		/**
		 * Fires after the display of member profile field content.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_after_profile_field_content' );

		/**
		 * Fires and displays the profile field buttons.
		 *
		 * @since 1.1.0
		 */
		do_action( 'bp_profile_field_buttons' );

		/**
		 * Fires after the display of member profile loop content.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_after_profile_loop_content' );
	}
}

/**
 * Does the displayed user has WP profile fields?
 *
 * @since  1.0.0
 *
 * @return bool True if user has profile fields. False otherwise.
 */
function bp_nouveau_has_wp_profile_fields() {
	$user_id = bp_displayed_user_id();

	if ( empty( $user_id ) ) {
		return false;
	}

	$user = get_userdata( $user_id );

	if ( ! $user ) {
		return false;
	}

	$fields              = bp_nouveau_get_wp_profile_fields( $user );
	$user_profile_fields = array();

	foreach ( $fields as $key => $field ) {
		if ( empty( $user->{$key} ) ) {
			continue;
		}

		$user_profile_fields[] = (object) array( 'id' => 'wp_' . $key, 'label' => $field, 'data' => $user->{$key} );
	}

	if ( empty( $user_profile_fields ) ) {
		return false;
	}

	// Keep it for a later use.
	$bp_nouveau = bp_nouveau();
	$bp_nouveau->members->wp_profile       = $user_profile_fields;
	$bp_nouveau->members->wp_profile_index = 0;

	return true;
}

/**
 * Check if there are still profile fields to output.
 *
 * @since  1.0.0
 *
 * @return bool True if the profile field exists. False otherwise.
 */
function bp_nouveau_wp_profile_fields() {
	$bp_nouveau = bp_nouveau();

	if ( isset( $bp_nouveau->members->wp_profile[ $bp_nouveau->members->wp_profile_index ] ) ) {
		return true;
	}

	$bp_nouveau->members->wp_profile_index = 0;
	unset( $bp_nouveau->members->wp_profile_current );

	return false;
}

/**
 * Set the current profile field and iterate into the loop.
 *
 * @since  1.0.0
 */
function bp_nouveau_wp_profile_field() {
	$bp_nouveau = bp_nouveau();

	$bp_nouveau->members->wp_profile_current = $bp_nouveau->members->wp_profile[ $bp_nouveau->members->wp_profile_index ];
	$bp_nouveau->members->wp_profile_index += 1;
}

/**
 * Output the WP profile field ID.
 *
 * @since  1.0.0
 */
function bp_nouveau_wp_profile_field_id() {
	echo bp_nouveau_get_wp_profile_field_id();
}
	/**
	 * Get the WP profile field ID.
	 *
	 * @since  1.0.0
	 *
	 * @return string the profile field ID.
	 */
	function bp_nouveau_get_wp_profile_field_id() {
		$field = bp_nouveau()->members->wp_profile_current;

		return esc_attr( apply_filters( 'bp_nouveau_get_wp_profile_field_id', $field->id ) );
	}

/**
 * Output the WP profile field label.
 *
 * @since  1.0.0
 */
function bp_nouveau_wp_profile_field_label() {
	echo bp_nouveau_get_wp_profile_field_label();
}

	/**
	 * Get the WP profile label.
	 *
	 * @since  1.0.0
	 *
	 * @return string the profile field label.
	 */
	function bp_nouveau_get_wp_profile_field_label() {
		$field = bp_nouveau()->members->wp_profile_current;

		return esc_html( apply_filters( 'bp_nouveau_get_wp_profile_field_label', $field->label ) );
	}

/**
 * Output the WP profile field data.
 *
 * @since  1.0.0
 */
function bp_nouveau_wp_profile_field_data() {
	echo bp_nouveau_get_wp_profile_field_data();
}

	/**
	 * Get the WP profile field data.
	 *
	 * @since  1.0.0
	 *
	 * @return string the profile field data.
	 */
	function bp_nouveau_get_wp_profile_field_data() {
		$field = bp_nouveau()->members->wp_profile_current;

		$data = make_clickable( $field->data );

		return wp_kses( apply_filters( 'bp_nouveau_get_wp_profile_field_data', $data ), array( 'a' => array( 'href' => true, 'rel' => true ) ) );
	}
