<?php
/**
 * Activity Template tags
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Before Activity's directory content legacy do_action hooks wrapper
 *
 * @since 1.0.0
 */
function bp_nouveau_before_activity_directory_content() {
	/**
	 * Fires at the begining of the templates BP injected content.
	 *
	 * @since 2.3.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_activity' );

	/**
	 * Fires before the activity directory display content.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_activity_content' );
}

/**
 * After Activity's directory content legacy do_action hooks wrapper
 *
 * @since 1.0.0
 */
function bp_nouveau_after_activity_directory_content() {
	/**
	 * Fires after the display of the activity list.
	 *
	 * @since 1.5.0
	 */
	do_action( 'bp_after_directory_activity_list' );

	/**
	 * Fires inside and displays the activity directory display content.
	 */
	do_action( 'bp_directory_activity_content' );

	/**
	 * Fires after the activity directory display content.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_after_directory_activity_content' );

	/**
	 * Fires after the activity directory listing.
	 *
	 * @since 1.5.0
	 */
	do_action( 'bp_after_directory_activity' );
}

/**
 * Enqueue needed scripts for the Activity Post Form
 *
 * @since 1.0.0
 */
function bp_nouveau_before_activity_post_form() {
	// Enqueue needed script.
	if ( bp_nouveau_current_user_can( 'publish_activity' ) ) {
		wp_enqueue_script( 'bp-nouveau-activity-post-form' );
	}

	do_action( 'bp_before_activity_post_form' );
}

/**
 * Load JS Templates for the Activity Post Form
 *
 * @since 1.0.0
 */
function bp_nouveau_after_activity_post_form() {
	if ( bp_nouveau_current_user_can( 'publish_activity' ) ) {
		bp_get_template_part( '_accessoires/activity/form' );
	}

	do_action( 'bp_after_activity_post_form' );
}

/**
 * Display the displayed user activity post form if needed
 *
 * @since  1.0.0
 *
 * @return string HTML Outpur
 */
function bp_nouveau_activity_member_post_form() {
	/**
	 * Fires before the display of the member activity post form.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_before_member_activity_post_form' );

	if ( is_user_logged_in() && bp_is_my_profile() && ( ! bp_current_action() || bp_is_current_action( 'just-me' ) ) ) {
		bp_get_template_part( 'activity/post-form' );
	}

	/**
	 * Fires after the display of the member activity post form.
	 *
	 * @since 1.2.0
	 */
	do_action( 'bp_after_member_activity_post_form' );
}

/**
 * Fire specific hooks into the activity entry template
 *
 * @since 1.0.0
 *
 * @param string $when    'before' or 'after'
 * @param string $suffix  Use it to add terms at the end of the hook name
 */
function bp_nouveau_activity_entry_hook( $when = '', $suffix = '' ) {
	if ( ! empty( $when ) ) {
		$when .= '_';
	}

	if ( ! empty( $suffix ) ) {
		$suffix = '_' . $suffix;
	}

	$hook = sprintf( 'bp_%1$sactivity_entry%2$s', $when, $suffix );

	/**
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( $hook );
}

/**
 * Checks if an activity of the loop has some content.
 *
 * @since 1.0.0
 *
 * @return bool True if the activity has some content. False Otherwise.
 */
function bp_nouveau_activity_has_content() {
	return bp_activity_has_content() || (bool) has_action( 'bp_activity_entry_content' );
}

/**
 * Output the Activity content into the loop.
 *
 * @since 1.0.0
 */
function bp_nouveau_activity_content() {
	if ( bp_activity_has_content() ) {
		bp_activity_content_body();
	}

	/**
	 * Fires after the display of an activity entry content.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_activity_entry_content' );
}

/**
 * Output the action buttons inside an Activity Loop
 *
 * @since 1.0.0
 */
function bp_nouveau_activity_entry_buttons() {
	echo join( ' ', bp_nouveau_get_activity_entry_buttons() );

	/**
	 * Fires at the end of the activity entry meta data area.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_activity_entry_meta' );
}

	/**
	 * Get the action buttons inside an Activity Loop
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_activity_entry_buttons() {
		$buttons = array();

		if ( ! isset( $GLOBALS['activities_template'] ) ) {
			return $buttons;
		}

		$activity_id   = bp_get_activity_id();
		$activity_type = bp_get_activity_type();

		if ( empty( $activity_id ) ) {
			return $buttons;
		}

		/**
		 * The view conversation button and the comment one are sharing
		 * the same id because when display_comments is on stream mode,
		 * it is not possible to comment an activity comment and as we
		 * are updating the links to avoid sorting the activity buttons
		 * for each entry of the loop, it's a convenient way to make
		 * sure the right button will be displayed.
		 */
		if ( $activity_type === 'activity_comment' ) {
			$buttons['activity_conversation'] =  array(
				'id'                => 'activity_conversation',
				'position'          => 5,
				'component'         => 'activity',
				'must_be_logged_in' => false,
				'link_href'         => esc_url( bp_get_activity_thread_permalink() ),
				'link_class'        => 'button view bp-secondary-action',
				'link_title'        => esc_attr__( 'View Conversation', 'bp-nouveau' ),
				'link_text'         => esc_html__( 'View Conversation', 'bp-nouveau' ),
			);

		/**
		 * We always create the Button to make sure
		 * we always have the right numbers of buttons
		 * no matter the previous activity had less
		 */
		} else {
			$buttons['activity_conversation'] =  array(
				'id'                => 'activity_conversation',
				'position'          => 5,
				'component'         => 'activity',
				'must_be_logged_in' => true,
				'link_id'           => 'acomment-comment-' . $activity_id,
				'link_href'         => esc_url( bp_get_activity_comment_link() ),
				'link_class'        => 'button acomment-reply bp-primary-action',
				'link_title'        => esc_attr__( 'Comment', 'bp-nouveau' ),
				'link_text'         => sprintf( '<span class="bp-screen-reader-text">%1$s</span> <span class="comment-count">%2$s</span>', esc_html__( 'Comment', 'bp-nouveau' ), bp_activity_get_comment_count() ),
			);
		}

		if ( bp_activity_can_favorite() ) {
			if ( ! bp_get_activity_is_favorite() ) {
				$fav_args = array(
					'link_href'  => bp_get_activity_favorite_link(),
					'link_class' => 'button fav bp-secondary-action',
					'link_title' => __( 'Mark as Favorite', 'bp-nouveau' ),
					'link_text'    => __( 'Favorite', 'bp-nouveau' ),
				);
			} else {
				$fav_args = array(
					'link_href'  => bp_get_activity_unfavorite_link(),
					'link_class' => 'button unfav bp-secondary-action',
					'link_title' => __( 'Remove Favorite', 'bp-nouveau' ),
					'link_text'    => __( 'Remove Favorite', 'bp-nouveau' ),
				);
			}

			$buttons['activity_favorite'] =  array(
				'id'                => 'activity_favorite',
				'position'          => 15,
				'component'         => 'activity',
				'must_be_logged_in' => true,
				'link_href'         => esc_url( $fav_args['link_href'] ),
				'link_class'        => $fav_args['link_class'],
				'link_title'        => esc_attr( $fav_args['link_title'] ),
				'link_text'         => sprintf( '<span class="bp-screen-reader-text">%1$s</span>', esc_html( $fav_args['link_text'] ) ),
			);
		}

		if ( bp_activity_user_can_delete() ) {
			$delete_args = array();

			/**
			 * As the delete link is filterable we need this workaround
			 * to try to intercept the edits the filter made and build
			 * a button out of it.
			 */
			if ( has_filter( 'bp_get_activity_delete_link' ) ) {
				preg_match( '/<a\s[^>]*>(.*)<\/a>/siU', bp_get_activity_delete_link(), $link );

				if ( ! empty( $link[0] ) && ! empty( $link[1] ) ) {
					$delete_args['link_text'] = $link[1];
					$subject = str_replace( $delete_args['link_text'], '', $link[0] );
				}

				preg_match_all( '/([\w\-]+)=([^"\'> ]+|([\'"]?)(?:[^\3]|\3+)+?\3)/', $subject, $attrs );

				if ( ! empty( $attrs[1] ) && ! empty( $attrs[2] ) ) {
					foreach ( $attrs[1] as $key_attr => $key_value ) {
						$delete_args[ 'link_'. $key_value ] = trim( $attrs[2][$key_attr], '"' );
					}
				}

				$delete_args = wp_parse_args( $delete_args, array(
					'link_href'  => '',
					'link_class' => '',
					'link_rel'   => 'nofollow',
					'link_text'  => '',
					'link_title' => '',
					'link_id'    => '',
				) );
			}

			if ( empty( $delete_args['link_href'] ) ) {
				$delete_args[] = bp_get_activity_delete_url();
				$class = 'delete-activity';

				if ( bp_is_activity_component() && is_numeric( bp_current_action() ) ) {
					$class = 'delete-activity-single';
				}

				$delete_args = array(
					'link_href'  => bp_get_activity_delete_url(),
					'link_class' => 'button item-button bp-secondary-action ' . $class . ' confirm',
					'link_rel'   => 'nofollow',
					'link_text'  => __( 'Delete', 'bp_nouveau' ),
					'link_title' => __( 'Delete', 'bp_nouveau' ),
					'link_id'    => '',
				);
			}

			$buttons['activity_delete'] = array(
				'id'                => 'activity_delete',
				'position'          => 35,
				'component'         => 'activity',
				'must_be_logged_in' => true,
				'link_id'           => esc_attr( $delete_args['link_id'] ),
				'link_href'         => esc_url( $delete_args['link_href'] ),
				'link_class'        => $delete_args['link_class'],
				'link_title'        => esc_attr( $delete_args['link_title'] ),
				'link_text'         => sprintf( '<span class="bp-screen-reader-text">%s</span>', esc_html( $delete_args['link_text'] ) ),
			);
		}

		/**
		 * Filter here to add your buttons, use the position argument to choose where to insert it.
		 *
		 * @since 1.0.0
		 *
		 * @param array the list of buttons
		 * @param int   the current activity ID.
		 */
		$buttons_group = apply_filters( 'bp_nouveau_get_activity_entry_buttons', $buttons, $activity_id );

		if ( empty( $buttons_group ) ) {
			return $buttons;
		}

		// It's the first entry of the loop, so build the Group and sort it
		if ( ! isset( bp_nouveau()->activity->entry_buttons ) || false === is_a( bp_nouveau()->activity->entry_buttons, 'BP_Buttons_Group' ) ) {
			$sort = true;
			bp_nouveau()->activity->entry_buttons = new BP_Buttons_Group( $buttons_group );

		// It's not the first entry, the order is set, we simply need to update the Buttons Group
		} else {
			$sort = false;
			bp_nouveau()->activity->entry_buttons->update( $buttons_group );
		}

		$return = bp_nouveau()->activity->entry_buttons->get( $sort );

		if ( ! $return ) {
			return array();
		}

		// Remove the Comment button if the user can't comment
		if ( ! bp_activity_can_comment() && $activity_type !== 'activity_comment' ) {
			unset( $return['activity_conversation'] );
		}

		return $return;
	}

/**
 * Output Activity Comments if any
 *
 * @since 1.0.0
 */
function bp_nouveau_activity_comments() {
	global $activities_template;

	if ( empty( $activities_template->activity->children ) ) {
		return false;
	}

	bp_nouveau_activity_recurse_comments( $activities_template->activity );
}

/**
 * Loops through a level of activity comments and loads the template for each.
 *
 * Note: This is an adaptation of the bp_activity_recurse_comments() BuddyPress core function
 *
 * @since 1.0.0
 *
 * @global object $activities_template {@link BP_Activity_Template}
 *
 * @param object $comment The activity object currently being recursed.
 * @return bool|string
 */
function bp_nouveau_activity_recurse_comments( $comment ) {
	global $activities_template;

	if ( empty( $comment ) ) {
		return false;
	}

	if ( empty( $comment->children ) ) {
		return false;
	}

	/**
	 * Filters the opening tag for the template that lists activity comments.
	 *
	 * @since 1.6.0 (BuddyPress)
	 *
	 * @param string $value Opening tag for the HTML markup to use.
	 */
	echo apply_filters( 'bp_activity_recurse_comments_start_ul', '<ul>' );
	foreach ( (array) $comment->children as $comment_child ) {

		// Put the comment into the global so it's available to filters.
		$activities_template->activity->current_comment = $comment_child;

		/**
		 * Fires before the display of an activity comment.
		 *
		 * @since 1.5.0 (BuddyPress)
		 */
		do_action( 'bp_before_activity_comment' );

		bp_get_template_part( 'activity/comment' );

		/**
		 * Fires after the display of an activity comment.
		 *
		 * @since 1.5.0 (BuddyPress)
		 */
		do_action( 'bp_after_activity_comment' );

		unset( $activities_template->activity->current_comment );
	}

	/**
	 * Filters the closing tag for the template that list activity comments.
	 *
	 * @since  1.6.0 (BuddyPress)
	 *
	 * @param string $value Closing tag for the HTML markup to use.
	 */
	echo apply_filters( 'bp_activity_recurse_comments_end_ul', '</ul>' );
}

/**
 * Ouptut the Activity comment action string
 *
 * @since 1.0.0
 */
function bp_nouveau_activity_comment_action() {
	echo bp_nouveau_get_activity_comment_action();
}

	/**
	 * Get the Activity comment action string
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_activity_comment_action() {
		/**
		 * Filter here to edit the activity comment action.
		 *
		 * @since 1.0.0
		 *
		 * @param string $value HTML Output
		 */
		return apply_filters( 'bp_nouveau_get_activity_comment_action',
			/* translators: 1: user profile link, 2: user name, 3: activity permalink, 4: activity recorded date, 5: activity timestamp, 6: activity human time since */
			sprintf( __( '<a href="%1$s">%2$s</a> replied <a href="%3$s" class="activity-time-since"><time class="time-since" datetime="%4$s" data-bp-timestamp="%5$d">%6$s</time></a>', 'bp-nouveau' ),
				esc_url( bp_get_activity_comment_user_link() ),
				esc_html( bp_get_activity_comment_name() ),
				esc_url( bp_get_activity_comment_permalink() ),
				esc_attr( bp_get_activity_comment_date_recorded_raw() ),
				esc_attr( strtotime( bp_get_activity_comment_date_recorded_raw() ) ),
				esc_attr( bp_get_activity_comment_date_recorded() )
		) );
	}

/**
 * Load the Activity comment form
 *
 * @since 1.0.0
 */
function bp_nouveau_activity_comment_form() {
	bp_get_template_part( 'activity/comment-form' );

	/**
	 * Fires after the activity entry comment form.
	 *
	 * @since 1.5.0 (BuddyPress)
	 */
	do_action( 'bp_activity_entry_comments' );
}

/**
 * Output the action buttons for the activity comments
 *
 * @since 1.0.0
 */
function bp_nouveau_activity_comment_buttons() {
	echo join( ' ', bp_nouveau_get_activity_comment_buttons() );

	/**
	 * Fires after the defualt comment action options display.
	 *
	 * @since 1.6.0 (BuddyPress)
	 */
	do_action( 'bp_activity_comment_options' );
}

	/**
	 * Get the action buttons for the activity comments
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_activity_comment_buttons() {
		$buttons = array();

		if ( ! isset( $GLOBALS['activities_template'] ) ) {
			return $buttons;
		}

		$activity_comment_id = (int) bp_get_activity_comment_id();
		$activity_id         = (int) bp_get_activity_id();

		if ( empty( $activity_comment_id ) || empty( $activity_id ) ) {
			return $buttons;
		}

		$buttons = array( 'activity_comment_reply' => array(
				'id'                => 'activity_comment_reply',
				'position'          => 5,
				'component'         => 'activity',
				'must_be_logged_in' => true,
				'link_href'         => sprintf( '#acomment-%s', $activity_comment_id ),
				'link_class'        => 'acomment-reply bp-primary-action',
				'link_id'           => sprintf( 'acomment-reply-%1$s-from-%2$s', $activity_id, $activity_comment_id ),
				'link_text'         => esc_html__( 'Reply', 'bp-nouveau' ),
			),
			'activity_comment_delete' => array(
				'id'                => 'activity_comment_delete',
				'position'          => 15,
				'component'         => 'activity',
				'must_be_logged_in' => true,
				'link_href'         => esc_url( bp_get_activity_comment_delete_link() ),
				'link_class'        => 'delete acomment-delete confirm bp-secondary-action',
				'link_rel'          => 'nofollow',
				'link_text'         => esc_html__( 'Delete', 'bp-nouveau' ),
			),
		);

		/**
		 * Filter here to add your buttons, use the position argument to choose where to insert it.
		 *
		 * @since 1.0.0
		 *
		 * @param array $buttons             The list of buttons.
		 * @param int   $activity_comment_id The current activity comment ID.
		 * @param int   $activity_id         The current activity ID.
		 */
		$buttons_group = apply_filters( 'bp_nouveau_get_activity_comment_buttons', $buttons, $activity_comment_id, $activity_id );

		if ( empty( $buttons_group ) ) {
			return $buttons;
		}

		// It's the first comment of the loop, so build the Group and sort it
		if ( ! isset( bp_nouveau()->activity->comment_buttons ) || false === is_a( bp_nouveau()->activity->comment_buttons, 'BP_Buttons_Group' ) ) {
			$sort = true;
			bp_nouveau()->activity->comment_buttons = new BP_Buttons_Group( $buttons_group );

		// It's not the first comment, the order is set, we simply need to update the Buttons Group
		} else {
			$sort = false;
			bp_nouveau()->activity->comment_buttons->update( $buttons_group );
		}

		$return = bp_nouveau()->activity->comment_buttons->get( $sort );

		if ( ! $return ) {
			return array();
		}

		/**
		 * If post comment / Activity comment sync is on, it's safer
		 * to unset the comment button just before returning it.
		 */
		if ( ! bp_activity_can_comment_reply( bp_activity_current_comment() ) ) {
			unset( $return['activity_comment_reply'] );
		}

		/**
		 * If there was an activity of the user before one af another
		 * user as we're updating buttons, we need to unset the delete link
		 */
		if ( ! bp_activity_user_can_delete() ) {
			unset( $return['activity_comment_delete'] );
		}

		return $return;
	}
