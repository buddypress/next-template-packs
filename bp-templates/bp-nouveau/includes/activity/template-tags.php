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
function bp_nouveau_activity_hook( $when = '', $suffix = '' ) {
	$hook = array( 'bp' );

	if ( ! empty( $when ) ) {
		$hook[] = $when;
	}

	// It's a activity entry hook
	$hook[] = 'activity';

	if ( ! empty( $suffix ) ) {
		$hook[] = $suffix;
	}

	/**
	 * @since 1.2.0 (BuddyPress)
	 */
	return bp_nouveau_hook( $hook );
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
 *
 * @param  array $args @see bp_nouveau_wrapper() for the description of parameters.
 * @return string HTML Output
 */
function bp_nouveau_activity_entry_buttons( $args = array() ) {
	$output = join( ' ', bp_nouveau_get_activity_entry_buttons( $args ) );

	ob_start();

	/**
	 * Fires at the end of the activity entry meta data area.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_activity_entry_meta' );

	$output .= ob_get_clean();

	$has_content = trim( $output, ' ' );

	if ( empty( $has_content ) ) {
		return;
	}

	if ( empty( $args ) ) {
		$args = array( 'classes' => array( 'activity-meta' ) );
	}

	return bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

	/**
	 * Get the action buttons inside an Activity Loop
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_activity_entry_buttons( $args ) {
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
		* If the wrapper is set to 'ul'
		* use to pass through a boolean to set:
		* $li_item  => true / false
		* Will render li elements around anchors/buttons.
		*/
		if( 'ul' == $args['container']  ) {
			$parent_element = 'li';
		} elseif( ! empty( $args['parent_element'] ) ) {
			$parent_element = esc_html( $args['parent_element'] );
		} else {
			$parent_element = false;
		}

		$parent_attr = ( ! empty( $args['parent_attr'] ) )? $args['parent_attr']  : '';

		/**
		 * If we have a arg value for $button_element passed through
		 * use it to default all the $buttons['element'] values
		 * otherwise pass through as empyty string and class BP_button() will use it's default
		 * 'anchor' or override & hardcode the 'element' string on $buttons array.
		 *
		 */
		if( !empty( $args['button_element'] ) ) {
			$button_element = $args['button_element'] ;
		} else {
			$button_element = 'a';
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
				'parent_element'    => $parent_element,
				'parent_attr'       => $parent_attr,
				'must_be_logged_in' => false,
				'button_element'    => $button_element,
				'button_attr'       => array(
					'href'         => esc_url( bp_get_activity_thread_permalink() ),
					'class'        => 'button view bp-secondary-action',
					),
				'link_text'  => sprintf('<span class="bp-screen-reader-text">%1$s</span>',esc_html__( 'View Conversation', 'bp-nouveau' ) ),
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
				'parent_element'    => $parent_element,
				'parent_attr'       => $parent_attr,
				'must_be_logged_in' => true,
				'button_element'    => $button_element,
				'button_attr'       => array(
					'id'           => 'acomment-comment-' . $activity_id,
					'href'         => esc_url( bp_get_activity_comment_link() ),
					'class'        => 'button acomment-reply bp-primary-action',
					'title'        => esc_attr__( 'Comment', 'bp-nouveau' ),
					),
				'link_text'  => sprintf( '<span class="bp-screen-reader-text">%1$s</span> <span class="comment-count">%2$s</span>', esc_html__( 'Comment', 'bp-nouveau' ), bp_activity_get_comment_count() ),
			);
		}

		if ( bp_activity_can_favorite() ) {
			if ( ! bp_get_activity_is_favorite() ) {
				$fav_args = array(
					'parent_element'  => $parent_element,
					'parent_attr'     => $parent_attr,
					'button_element'  => $button_element,
					'link_href'       => bp_get_activity_favorite_link(),
					'link_class'      => 'button fav bp-secondary-action',
					'link_title'      => __( 'Mark as Favorite', 'bp-nouveau' ),
					'link_text'       => __( 'Favorite', 'bp-nouveau' ),
				);
			} else {
				$fav_args = array(
					'parent_element'  => $parent_element,
					'parent_attr'     => $parent_attr,
					'button_element'  => $button_element,
					'link_href'       => bp_get_activity_unfavorite_link(),
					'link_class'      => 'button unfav bp-secondary-action',
					'link_title'      => __( 'Remove Favorite', 'bp-nouveau' ),
					'link_text'   => __( 'Remove Favorite', 'bp-nouveau' ),
				);
			}

			$buttons['activity_favorite'] =  array(
				'id'                => 'activity_favorite',
				'position'          => 15,
				'component'         => 'activity',
				'parent_element'    => $parent_element,
				'parent_attr'       => $parent_attr,
				'must_be_logged_in' => true,
				'button_element'    => $button_element,
				'button_attr'       => array(
					'href'    => esc_url( $fav_args['link_href'] ),
					'class'   => $fav_args['link_class'],
					'title'   => esc_attr( $fav_args['link_title'] ),
					),
				'link_text'   => sprintf( '<span class="bp-screen-reader-text">%1$s</span>', esc_html( $fav_args['link_text'] ) ),
			);
		}

		// The delete button is always created, and removed later on if needed.
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
				'button_attr'  => array(
					'link_id'    => '',
					'link_href'  => '',
					'link_class' => '',
					'link_rel'   => 'nofollow',
					'link_title' => '',
					),
				'link_text'  => '',
			) );
		}

		if ( empty( $delete_args['link_href'] ) ) {
			$delete_args[] = bp_get_activity_delete_url();
			$class = 'delete-activity';

			$delete_args = array(
				'button_element'    => $button_element,
				'link_id'           => '',
				'link_href'         => bp_get_activity_delete_url(),
				'link_class'        => 'button item-button bp-secondary-action ' . $class . ' confirm',
				'link_rel'          => 'nofollow',
				'link_title'   => __( 'Delete', 'bp-nouveau' ),
				'link_text'  => __( 'Delete', 'bp-nouveau' ),
			);
		}

		$buttons['activity_delete'] = array(
			'id'                => 'activity_delete',
			'position'          => 35,
			'component'         => 'activity',
			'parent_element'    => $parent_element,
			'parent_attr'       => $parent_attr,
			'must_be_logged_in' => true,
			'button_element'    => $button_element,
			'button_attr'       => array(
				'id'       => esc_attr( $delete_args['link_id'] ),
				'href'     => esc_url( $delete_args['link_href'] ),
				'class'    => $delete_args['link_class'],
				'title'    => esc_attr( $delete_args['link_title'] ),
				),
			'link_text'  => sprintf( '<span class="bp-screen-reader-text">%s</span>', esc_html( $delete_args['link_text'] ) ),
		);

		// Add the Spam Button if supported
		if ( bp_is_akismet_active() && isset( buddypress()->activity->akismet ) && bp_activity_user_can_mark_spam() ) {
			$buttons['activity_spam'] = array(
				'id'                => 'activity_spam',
				'position'          => 45,
				'component'         => 'activity',
				'parent_element'    => $parent_element,
				'parent_attr'       => $parent_attr,
				'must_be_logged_in' => true,
				'button_element'    => $button_element,
				'button_attr'       => array(
					'href'    =>  wp_nonce_url( bp_get_root_domain() . '/' . bp_get_activity_slug() . '/spam/' . $activity_id . '/', 'bp_activity_akismet_spam_' . $activity_id ),
					'class'   => 'bp-secondary-action spam-activity confirm button item-button',
					'id'      =>  'activity_make_spam_' . $activity_id,
					'title'   =>  esc_attr__( 'Spam', 'bp-nouveau' ),
					),
				'link_text'  => sprintf(
					/** @todo: use a specific css rule for this *************************************************************/
					'<span class="dashicons dashicons-flag" style="color:#a00;vertical-align:baseline;width:18px;height:18px"></span><span class="bp-screen-reader-text">%s</span>',
					esc_html__( 'Spam', 'bp-nouveau' )
				),
			);
		}

		/**
		 * Filter here to add your buttons, use the position argument to choose where to insert it.
		 *
		 * @since 1.0.0
		 *
		 * @param array $buttons     The list of buttons.
		 * @param int   $activity_id The current activity ID.
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

		// Remove the Delete button if the user can't delete
		if ( ! bp_activity_user_can_delete() ) {
			unset( $return['activity_delete'] );
		}

		if ( isset( $return['activity_spam'] ) && ! in_array( $activity_type, BP_Akismet::get_activity_types() ) ) {
			unset( $return['activity_spam'] );
		}

		/**
		 * Leave a chance to adjust the $return
		 *
		 * @since 1.0.0
		 *
		 * @param array $return      The list of buttons ordered.
		 * @param int   $activity_id The current activity ID.
		 */
		do_action_ref_array( 'bp_nouveau_return_activity_entry_buttons', array( &$return, $activity_id ) );

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
 *
 * @param  array $args @see bp_nouveau_wrapper() for the description of parameters.
 * @return string HTML Output
 */
function bp_nouveau_activity_comment_buttons( $args = array() ) {
	$output = join( ' ', bp_nouveau_get_activity_comment_buttons($args) );

	ob_start();
	/**
	 * Fires after the defualt comment action options display.
	 *
	 * @since 1.6.0 (BuddyPress)
	 */
	do_action( 'bp_activity_comment_options' );
	$output .= ob_get_clean();

	$has_content = trim( $output, ' ' );

	if ( empty( $has_content ) ) {
		return;
	}

	if ( empty( $args ) ) {
		$args = array( 'classes' => array( 'acomment-options' ) );
	}

	return bp_nouveau_wrapper( array_merge( $args, array( 'output' => $output ) ) );
}

	/**
	 * Get the action buttons for the activity comments
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_activity_comment_buttons($args) {
		$buttons = array();

		if ( ! isset( $GLOBALS['activities_template'] ) ) {
			return $buttons;
		}

		$activity_comment_id = (int) bp_get_activity_comment_id();
		$activity_id         = (int) bp_get_activity_id();

		if ( empty( $activity_comment_id ) || empty( $activity_id ) ) {
			return $buttons;
		}

		/**
		 * If the 'container' is set to 'ul'
		 * set a var $parent_element to li
		 * otherwise simply pass any value found in args
		 * or set var false.
		 */
		if( 'ul' == $args['container']  ) {
			$parent_element = 'li';
		} elseif( ! empty( $args['parent_element'] ) ) {
			$parent_element = esc_html( $args['parent_element'] );
		} else {
			$parent_element = false;
		}

		$parent_attr = ( ! empty( $args['parent_attr'] ) )? $args['parent_attr']  : '';

		/**
		 * If we have a arg value for $button_element passed through
		 * use it to default all the $buttons['button_element'] values
		 * otherwise default to 'a' (anchor)
		 * Or override & hardcode the 'element' string on $buttons array.
		 *
		 * Icons sets a class for icon display if not using the button element
		 */
		$icons = '';
		if( !empty( $args['button_element'] ) ) {
			$button_element = $args['button_element'] ;

				// If this is a true button element then we need to move the href values
				// around onto button value & empty the href attr.
				if( 'button' === $args['button_element'] ) {
					$data_attr_delete = esc_url( bp_get_activity_comment_delete_link() );
					$data_attr_reply  = sprintf( '#acomment-%s', $activity_comment_id );
					$href_reply = $href_delete = '';
				}
		} else {
			$button_element   = 'a';
			$data_attr_reply  = '';
			$data_attr_delete = '';
			$href_reply  = sprintf( '#acomment-%s', $activity_comment_id );
			$href_delete = esc_url( bp_get_activity_comment_delete_link() );
			$icons = ' icons';
		}

		$buttons = array( 'activity_comment_reply' => array(
				'id'                => 'activity_comment_reply',
				'position'          => 5,
				'component'         => 'activity',
				'must_be_logged_in' => true,
				'parent_element'    => $parent_element,
				'parent_attr'       => $parent_attr,
				'button_element'    => $button_element,
				'button_attr'       =>  array(
					'data-do-comment'  => $data_attr_reply,
					'href'   => $href_reply,
					'class'  => 'acomment-reply bp-primary-action' . $icons . '',
					'id'     => sprintf( 'acomment-reply-%1$s-from-%2$s', $activity_id, $activity_comment_id ),
				),
				'link_text'         => esc_html__( 'reply', 'bp-nouveau' ),
			),
			'activity_comment_delete' => array(
				'id'                => 'activity_comment_delete',
				'position'          => 15,
				'component'         => 'activity',
				'must_be_logged_in' => true,
				'parent_element'    => $parent_element,
				'parent_attr'       => $parent_attr,
				'button_element'    => $button_element,
				'button_attr'       => array(
					'data-do-delete'   => $data_attr_delete,
					'href'   => $href_delete,
					'class'  => 'delete acomment-delete confirm bp-secondary-action' . $icons . '',
					'rel'    => 'nofollow',
					),
				'link_text'         => esc_html__( 'Delete', 'bp-nouveau' ),
			),
		);

		// Add the Spam Button if supported
		if ( bp_is_akismet_active() && isset( buddypress()->activity->akismet ) && bp_activity_user_can_mark_spam() ) {
			$buttons['activity_comment_spam'] = array(
				'id'                => 'activity_comment_spam',
				'position'          => 25,
				'component'         => 'activity',
				'must_be_logged_in' => true,
				'parent_element'    => $parent_element,
				'parent_attr'       => $parent_attr,
				'button_element'    => $button_element,
				'button_attr'       =>  array(
					'id'     => 'activity_make_spam_' . $activity_comment_id,
					'href'   => wp_nonce_url( bp_get_root_domain() . '/' . bp_get_activity_slug() . '/spam/' . $activity_comment_id . '/?cid=' . $activity_comment_id, 'bp_activity_akismet_spam_' . $activity_comment_id ),
					'class'  => 'bp-secondary-action spam-activity-comment confirm' . $icon . '' ,
					'rel'    => 'nofollow',
				),
				'link_text'          => esc_html__( 'Spam', 'bp-nouveau' ),
			);
		}

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

		if ( isset( $return['activity_comment_spam'] ) && ( ! bp_activity_current_comment() || ! in_array( bp_activity_current_comment()->type, BP_Akismet::get_activity_types() ) ) ) {
			unset( $return['activity_comment_spam'] );
		}

		/**
		 * Leave a chance to adjust the $return
		 *
		 * @since 1.0.0
		 *
		 * @param array $return              The list of buttons ordered.
		 * @param int   $activity_comment_id The current activity comment ID.
		 * @param int   $activity_id         The current activity ID.
		 */
		do_action_ref_array( 'bp_nouveau_return_activity_comment_buttons', array( &$return, $activity_comment_id, $activity_id ) );

		return $return;
	}
