<?php
/**
 * Ajax functions
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * This function looks scarier than it actually is. :)
 * Each object loop (activity/members/groups/blogs/forums) contains default
 * parameters to show specific information based on the page we are currently
 * looking at.
 *
 * The following function will take into account any cookies set in the JS and
 * allow us to override the parameters sent. That way we can change the results
 * returned without reloading the page.
 *
 * By using cookies we can also make sure that user settings are retained
 * across page loads.
 *
 * @param string $query_string Query string for the current request.
 * @param string $object       Object for cookie.
 * @return string Query string for the component loops
 * @since 1.2.0
 */
function bp_nouveau_ajax_querystring( $query_string, $object ) {
	if ( empty( $object ) ) {
		return '';
	}

	// Default query
	$post_query = array(
		'filter'       => '',
		'scope'        => 'all',
		'page'         => 1,
		'search_terms' => '',
		'extras'       => '',
	);

	if ( ! empty( $_POST ) ) {
		$post_query = wp_parse_args( $_POST, $post_query );

		// Make sure to transport the scope, filter etc.. in HeartBeat Requests
		if ( ! empty( $post_query['data']['bp_heartbeat'] ) ) {
			$bp_heartbeat = $post_query['data']['bp_heartbeat'];

			// Remove heartbeat specific vars
			$post_query = array_diff_key(
				wp_parse_args( $bp_heartbeat, $post_query ),
				array(
					'data'      => false,
					'interval'  => false,
					'_nonce'    => false,
					'action'    => false,
					'screen_id' => false,
					'has_focus' => false,
				)
			);
		}
	}

	// Init the query string
	$qs = array();

	// Activity stream filtering on action.
	if ( ! empty( $post_query['filter'] ) && '-1' !== $post_query['filter'] ) {
		$qs[] = 'type='   . $post_query['filter'];
		$qs[] = 'action=' . $post_query['filter'];
	}

	if ( 'personal' === $post_query['scope'] ) {
		$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();
		$qs[] = 'user_id=' . $user_id;
	}

	// Activity stream scope only on activity directory.
	if ( 'all' !== $post_query['scope'] && ! bp_displayed_user_id() && ! bp_is_single_item() ) {
		$qs[] = 'scope=' . $post_query['scope'];
	}

	// If page have been passed via the AJAX post request, use those.
	if ( '-1' != $post_query['page'] ) {
		$qs[] = 'page=' . absint( $post_query['page'] );
	}

	// Excludes activity just posted and avoids duplicate ids.
	if ( ! empty( $post_query['exclude_just_posted'] ) ) {
		$just_posted = wp_parse_id_list( $post_query['exclude_just_posted'] );
		$qs[] = 'exclude=' . implode( ',', $just_posted );
	}

	// To get newest activities.
	if ( ! empty( $post_query['offset'] ) ) {
		$qs[] = 'offset=' . intval( $post_query['offset'] );
	}

	$object_search_text = bp_get_search_default_text( $object );
	if ( ! empty( $post_query['search_terms'] ) && $object_search_text != $post_query['search_terms'] && 'false' != $post_query['search_terms'] && 'undefined' != $post_query['search_terms'] ) {
		$qs[] = 'search_terms=' . urlencode( $_POST['search_terms'] );
	}

	// Specific to messages
	if ( 'messages' === $object ) {
		if ( ! empty( $post_query['box'] ) ) {
			$qs[] = 'box=' . $post_query['box'];
		}
	}

	// Now pass the querystring to override default values.
	$query_string = empty( $qs ) ? '' : join( '&', (array) $qs );

	// List the variables for the filter
	list( $filter, $scope, $page, $search_terms, $extras ) = array_values( $post_query );

	/**
	 * Filters the AJAX query string for the component loops.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query_string The query string we are working with.
	 * @param string $object       The type of page we are on.
	 * @param string $filter       The current object filter.
	 * @param string $scope        The current object scope.
	 * @param string $page         The current object page.
	 * @param string $search_terms The current object search terms.
	 * @param string $extras       The current object extras.
	 */
	return apply_filters( 'bp_nouveau_ajax_querystring', $query_string, $object, $filter, $scope, $page, $search_terms, $extras );
}

/**
 * Load the template loop for the current object.
 *
 * @return string Prints template loop for the specified object
 * @since 1.0.0
 */
function bp_nouveau_ajax_object_template_loader() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error();
	}

	// Bail if no object passed.
	if ( empty( $_POST['object'] ) ) {
		wp_send_json_error();
	}

	// Sanitize the object.
	$object = sanitize_title( $_POST['object'] );

	// Bail if object is not an active component to prevent arbitrary file inclusion.
	if ( ! bp_is_active( $object ) ) {
		wp_send_json_error();
	}

	$result = array();

	if ( 'activity' === $object ) {
		$scope = '';
		if ( ! empty( $_POST['scope'] ) ) {
			$scope = $_POST['scope'];
		}

		// We need to calculate and return the feed URL for each scope.
		switch ( $scope ) {
			case 'friends':
				$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/friends/feed/';
				break;
			case 'groups':
				$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/groups/feed/';
				break;
			case 'favorites':
				$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/favorites/feed/';
				break;
			case 'mentions':
				$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/mentions/feed/';

				// Get user new mentions
				$new_mentions = bp_get_user_meta( bp_loggedin_user_id(), 'bp_new_mentions', true );

				// If we have some, include them into the returned json before deleting them
				if ( is_array( $new_mentions ) ) {
					$result['new_mentions'] = $new_mentions;

					// Clear new mentions
					bp_activity_clear_new_mentions( bp_loggedin_user_id() );
				}

				break;
			default:
				$feed_url = home_url( bp_get_activity_root_slug() . '/feed/' );
				break;
		}

		$result['feed_url'] = apply_filters( 'bp_legacy_theme_activity_feed_url', $feed_url, $scope );
	}

 	/**
	 * AJAX requests happen too early to be seen by bp_update_is_directory()
	 * so we do it manually here to ensure templates load with the correct
	 * context. Without this check, templates will load the 'single' version
	 * of themselves rather than the directory version.
	 */
	if ( ! bp_current_action() ) {
		bp_update_is_directory( true, bp_current_component() );
	}

	$template_part = $object . '/' . $object . '-loop';

	// The template part can be overridden by the calling JS function.
	if ( ! empty( $_POST['template'] ) ) {
		$template_part = sanitize_option( 'upload_path', $_POST['template'] );
	}

	ob_start();
	bp_get_template_part( $template_part );
	$result['contents'] = ob_get_contents();
	ob_end_clean();

	// Locate the object template.
	wp_send_json_success( $result );
}

/**
 * Friend/un-friend a user via a POST request.
 *
 * @since 1.0.0
 *
 * @return string HTML
 */
function bp_nouveau_ajax_addremove_friend() {
	$response = array(
		'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'bp-nouveau' )
		)
	);

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['nonce'] ) || empty( $_POST['item_id'] ) || ! bp_is_active( 'friends' ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce
	$nonce = $_POST['nonce'];
	$check = 'bp_nouveau_friends';

	// Use a specific one for actions needed it
	if ( ! empty( $_POST['_wpnonce'] ) && ! empty( $_POST['action'] ) ) {
		$nonce = $_POST['_wpnonce'];
		$check = $_POST['action'];
	}

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	// Cast fid as an integer.
	$friend_id = (int) $_POST['item_id'];

	// Trying to cancel friendship.
	if ( 'is_friend' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $friend_id ) ) {
		if ( ! friends_remove_friend( bp_loggedin_user_id(), $friend_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
				esc_html__( 'Friendship could not be canceled.', 'bp-nouveau' )
			);

			wp_send_json_error( $response );
		} else {
			wp_send_json_success( array( 'contents' => bp_get_add_friend_button( $friend_id ) ) );
		}

	// Trying to request friendship.
	} elseif ( 'not_friends' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $friend_id ) ) {
		if ( ! friends_add_friend( bp_loggedin_user_id(), $friend_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
				esc_html__( 'Friendship could not be requested.', 'bp-nouveau' )
			);

			wp_send_json_error( $response );
		} else {
			wp_send_json_success( array( 'contents' => bp_get_add_friend_button( $friend_id ) ) );
		}

	// Trying to cancel pending request.
	} elseif ( 'pending' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $friend_id ) ) {
		if ( friends_withdraw_friendship( bp_loggedin_user_id(), $friend_id ) ) {
			wp_send_json_success( array( 'contents' => bp_get_add_friend_button( $friend_id ) ) );
		} else {
			$response['feedback'] = sprintf(
				'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
				esc_html__( 'Friendship request could not be cancelled.', 'bp-nouveau' )
			);

			wp_send_json_error( $response );
		}

	// Request already pending.
	} else {
		$response['feedback'] = sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'Request Pending', 'bp-nouveau' )
		);

		wp_send_json_error( $response );
	}
}

/**
 * Load the activity loop template when activity is requested via AJAX.
 *
 * @return string JSON object containing 'contents' (output of the template loop
 * for the Activity component) and 'feed_url' (URL to the relevant RSS feed).
 *
 * @since 1.0.0
 */
function bp_nouveau_ajax_activity_template_loader() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error();
	}

	$scope = '';
	if ( ! empty( $_POST['scope'] ) ) {
		$scope = $_POST['scope'];
	}

	// We need to calculate and return the feed URL for each scope.
	switch ( $scope ) {
		case 'friends':
			$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/friends/feed/';
			break;
		case 'groups':
			$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/groups/feed/';
			break;
		case 'favorites':
			$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/favorites/feed/';
			break;
		case 'mentions':
			$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/mentions/feed/';
			bp_activity_clear_new_mentions( bp_loggedin_user_id() );
			break;
		default:
			$feed_url = home_url( bp_get_activity_root_slug() . '/feed/' );
			break;
	}

	$result = array();

	// Buffer the loop in the template to a var for JS to spit out.
	ob_start();
	bp_get_template_part( 'activity/activity-loop' );
	$result['contents'] = ob_get_contents();

	/**
	 * Filters the feed URL for when activity is requested via AJAX.
	 *
	 * @since 1.7.0
	 *
	 * @param string $feed_url URL for the feed to be used.
	 * @param string $scope    Scope for the activity request.
	 */
	$result['feed_url'] = apply_filters( 'bp_legacy_theme_activity_feed_url', $feed_url, $scope );
	ob_end_clean();

	wp_send_json_success( $result );
}

/**
 * Mark an activity as a favourite via a POST request.
 *
 * @since 1.0.0
 *
 * @return string HTML
 */
function bp_nouveau_ajax_mark_activity_favorite() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error();
	}

	// Nonce check!
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_nouveau_activity' ) ) {
		wp_send_json_error();
	}

	if ( bp_activity_add_user_favorite( $_POST['id'] ) ) {
		$response = array( 'content' => __( 'Remove Favorite', 'bp-nouveau' ) );

		if ( ! bp_is_user() ) {
			$fav_count = (int) bp_get_total_favorite_count_for_user( bp_loggedin_user_id() );

			if ( 1 === $fav_count ) {
				$response['directory_tab'] = '<li id="activity-favorites" data-bp-scope="favorites" data-bp-object="activity">
					<a href="' . bp_loggedin_user_domain() . bp_get_activity_slug() . '/favorites/" title="' . esc_attr__( "The activity I've marked as a favorite.", 'bp-nouveau' ) . '">
						' . esc_html__( 'My Favorites', 'bp-nouveau' ) . '
					</a>
				</li>';
			} else {
				$response['fav_count'] = $fav_count;
			}
		}

		wp_send_json_success( $response );
	} else {
		wp_send_json_error();
	}
}

/**
 * Un-favourite an activity via a POST request.
 *
 * @since 1.0.0
 *
 * @return string HTML
 */
function bp_nouveau_ajax_unmark_activity_favorite() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error();
	}

	// Nonce check!
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_nouveau_activity' ) ) {
		wp_send_json_error();
	}

	if ( bp_activity_remove_user_favorite( $_POST['id'] ) ) {
		$response = array( 'content' => __( 'Favorite', 'bp-nouveau' ) );

		$fav_count = (int) bp_get_total_favorite_count_for_user( bp_loggedin_user_id() );

		if ( 0 === $fav_count ) {
			$response['no_favorite'] = '<li id="activity-stream-message" class="info">
				<p>' . __( 'Sorry, there was no activity found. Please try a different filter.', 'bp-nouveau' ) . '</p>
			</li>';
		} else {
			$response['fav_count'] = $fav_count;
		}

		wp_send_json_success( $response );
	} else {
		wp_send_json_error();
	}
}

function bp_nouveau_ajax_clear_new_mentions() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error();
	}

	// Nonce check!
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_nouveau_activity' ) ) {
		wp_send_json_error();
	}

	bp_activity_clear_new_mentions( bp_loggedin_user_id() );
	wp_send_json_success();
}

/**
 * Deletes an Activity item received via a POST request.
 *
 * @since 1.0.0
 *
 * @return mixed String on error, void on success.
 */
function bp_nouveau_ajax_delete_activity() {
	$response = array(
		'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem when deleting. Please try again.', 'bp-nouveau' )
		)
	);

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( $response );
	}

	// Nonce check!
	if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'bp_activity_delete_link' ) ) {
		wp_send_json_error( $response );
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) ) {
		wp_send_json_error( $response );
	}

	$activity = new BP_Activity_Activity( (int) $_POST['id'] );

	// Check access.
	if ( ! bp_activity_user_can_delete( $activity ) ) {
		wp_send_json_error( $response );
	}

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_before_action_delete_activity', $activity->id, $activity->user_id );

	if ( ! bp_activity_delete( array( 'id' => $activity->id, 'user_id' => $activity->user_id ) ) ) {
		wp_send_json_error( $response );
	}

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_action_delete_activity', $activity->id, $activity->user_id );

	wp_send_json_success( array( 'deleted' => $activity->id ) );
}

/**
 * Deletes an Activity comment received via a POST request.
 *
 * @todo implement the delete_activity_comment ajax action
 * in buddypress-activity.js
 *
 * @since 1.0.0
 *
 * @return mixed String on error, void on success.
 */
function bp_nouveau_ajax_delete_activity_comment() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce.
	check_admin_referer( 'bp_activity_delete_link' );

	if ( ! is_user_logged_in() )
		exit( '-1' );

	$comment = new BP_Activity_Activity( $_POST['id'] );

	// Check access.
	if ( ! bp_current_user_can( 'bp_moderate' ) && $comment->user_id != bp_loggedin_user_id() )
		exit( '-1' );

	if ( empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) )
		exit( '-1' );

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_before_action_delete_activity', $_POST['id'], $comment->user_id );

	if ( ! bp_activity_delete_comment( $comment->item_id, $comment->id ) )
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . __( 'There was a problem when deleting. Please try again.', 'bp-nouveau' ) . '</p></div>' );

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_action_delete_activity', $_POST['id'], $comment->user_id );
	exit;
}

/**
 * Fetches an activity's full, non-excerpted content via a POST request.
 * Used for the 'Read More' link on long activity items.
 *
 * @since 1.0.0
 *
 * @return string HTML
 */
function bp_nouveau_ajax_get_single_activity_content() {
	$response = array(
		'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem displaying the content. Please try again.', 'bp-nouveau' )
		)
	);

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( $response );
	}

	// Nonce check!
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_nouveau_activity' ) ) {
		wp_send_json_error( $response );
	}

	$activity_array = bp_activity_get_specific( array(
		'activity_ids'     => $_POST['id'],
		'display_comments' => 'stream'
	) );

	if ( empty( $activity_array['activities'][0] ) ) {
		wp_send_json_error( $response );
	}

	$activity = $activity_array['activities'][0];

	/**
	 * Fires before the return of an activity's full, non-excerpted content via a POST request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $activity Activity content. Passed by reference.
	 */
	do_action_ref_array( 'bp_nouveau_get_single_activity_content', array( &$activity ) );

	// Activity content retrieved through AJAX should run through normal filters, but not be truncated.
	remove_filter( 'bp_get_activity_content_body', 'bp_activity_truncate_entry', 5 );

	/** This filter is documented in bp-activity/bp-activity-template.php */
	$content = apply_filters( 'bp_get_activity_content_body', $activity->content );

	wp_send_json_success( array( 'contents' => $content ) );
}

/**
 * Posts new Activity comments received via a POST request.
 *
 * @since 1.0.0
 *
 * @global BP_Activity_Template $activities_template
 *
 * @return string HTML
 */
function bp_nouveau_ajax_new_activity_comment() {
	global $activities_template;
	$bp = buddypress();

	$response = array(
		'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was an error posting your reply. Please try again.', 'bp-nouveau' )
		)
	);

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( $response );
	}

	// Nonce check!
	if ( empty( $_POST['_wpnonce_new_activity_comment'] ) || ! wp_verify_nonce( $_POST['_wpnonce_new_activity_comment'], 'new_activity_comment' ) ) {
		wp_send_json_error( $response );
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['content'] ) ) {
		wp_send_json_error( array( 'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'Please do not leave the comment area blank.', 'bp-nouveau' )
		) ) );
	}

	if ( empty( $_POST['form_id'] ) || empty( $_POST['comment_id'] ) || ! is_numeric( $_POST['form_id'] ) || ! is_numeric( $_POST['comment_id'] ) ) {
		wp_send_json_error( $response );
	}

	$comment_id = bp_activity_new_comment( array(
		'activity_id' => $_POST['form_id'],
		'content'     => $_POST['content'],
		'parent_id'   => $_POST['comment_id'],
	) );

	if ( ! $comment_id ) {
		if ( ! empty( $bp->activity->errors['new_comment'] ) && is_wp_error( $bp->activity->errors['new_comment'] ) ) {
			$response = array( 'feedback' => sprintf(
				'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
				esc_html( $bp->activity->errors['new_comment']->get_error_message() )
			) );
			unset( $bp->activity->errors['new_comment'] );
		}

		wp_send_json_error( $response );
	}

	// Load the new activity item into the $activities_template global.
	bp_has_activities( array(
		'display_comments' => 'stream',
		'hide_spam'        => false,
		'show_hidden'      => true,
		'include'          => $comment_id,
	) );

	// Swap the current comment with the activity item we just loaded.
	if ( isset( $activities_template->activities[0] ) ) {
		$activities_template->activity = new stdClass();
		$activities_template->activity->id = $activities_template->activities[0]->item_id;
		$activities_template->activity->current_comment = $activities_template->activities[0];

		// Because the whole tree has not been loaded, we manually
		// determine depth.
		$depth = 1;
		$parent_id = (int) $activities_template->activities[0]->secondary_item_id;
		while ( $parent_id !== (int) $activities_template->activities[0]->item_id ) {
			$depth++;
			$p_obj = new BP_Activity_Activity( $parent_id );
			$parent_id = (int) $p_obj->secondary_item_id;
		}
		$activities_template->activity->current_comment->depth = $depth;
	}

	ob_start();
	// Get activity comment template part.
	bp_get_template_part( 'activity/comment' );
	$response = array( 'contents' => ob_get_contents() );
	ob_end_clean();

	unset( $activities_template );

	wp_send_json_success( $response );
}

/**
 * Get items to attach the activity to.
 * This is used within the activity post form autocomplete field.
 *
 * @since 1.0.0
 *
 * @return string JSON reply
 */
function bp_nouveau_ajax_get_activity_objects() {
	$response = array();

	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_nouveau_activity' ) ) {
		wp_send_json_error( $response );
	}

	if ( 'group' === $_POST['type'] ) {
		$groups = groups_get_groups( array(
			'user_id'           => bp_loggedin_user_id(),
			'search_terms'      => $_POST['search'],
			'show_hidden'       => true,
			'per_page'          => 2,
		) );

		wp_send_json_success( array_map( 'bp_nouveau_prepare_group_for_js', $groups['groups'] ) );
	} else {
		$response = apply_filters( 'bp_nouveau_get_activity_custom_objects', $response, $_POST['type'] );
	}

	if ( empty( $response ) ) {
		wp_send_json_error( array( 'error' => __( 'No items were found.', 'bp-nouveau' ) ) );
	} else {
		wp_send_json_success( $response );
	}
}

/**
 * Processes Activity updates received via a POST request.
 *
 * @since 1.0.0
 *
 * @return string JSON reply
 */
function bp_nouveau_ajax_post_update() {
	$bp = buddypress();

	if ( ! is_user_logged_in() || empty( $_POST['_wpnonce_post_update'] ) || ! wp_verify_nonce( $_POST['_wpnonce_post_update'], 'post_update' ) ) {
		wp_send_json_error();
	}

	if ( empty( $_POST['content'] ) ) {
		wp_send_json_error( array(
			'message' => __( 'Please enter some content to post.', 'bp-nouveau' ),
		) );
	}

	$activity_id = 0;
	$item_id     = 0;
	$object      = '';
	$is_private  = false;


	// Try to get the item id from posted variables.
	if ( ! empty( $_POST['item_id'] ) ) {
		$item_id = (int) $_POST['item_id'];
	}

	// Try to get the object from posted variables.
	if ( ! empty( $_POST['object'] ) ) {
		$object  = sanitize_key( $_POST['object'] );

	// If the object is not set and we're in a group, set the item id and the object
	} elseif ( bp_is_group() ) {
		$item_id = bp_get_current_group_id();
		$object = 'group';
		$status = groups_get_current_group()->status;
	}

	if ( 'user' === $object && bp_is_active( 'activity' ) ) {
		$activity_id = bp_activity_post_update( array( 'content' => $_POST['content'] ) );

	} elseif ( 'group' === $object ) {
		if ( $item_id && bp_is_active( 'groups' ) ) {
			// This function is setting the current group!
			$activity_id = groups_post_update( array( 'content' => $_POST['content'], 'group_id' => $item_id ) );

			if ( empty( $status ) ) {
				if ( ! empty( $bp->groups->current_group->status ) ) {
					$status = $bp->groups->current_group->status;
				} else {
					$group  = groups_get_group( array( 'group_id' => $group_id ) );
					$status = $group->status;
				}

				$is_private = 'public' !== $status;
			}
		}

	} else {

		/** This filter is documented in bp-activity/bp-activity-actions.php */
		$activity_id = apply_filters( 'bp_activity_custom_update', false, $object, $item_id, $_POST['content'] );
	}

	if ( empty( $activity_id ) ) {
		wp_send_json_error( array(
			'message' => __( 'There was a problem posting your update. Please try again.', 'bp-nouveau' ),
		) );
	}

	ob_start();
	if ( bp_has_activities( array( 'include' => $activity_id, 'show_hidden' => $is_private ) ) ) {
		while ( bp_activities() ) {
			bp_the_activity();
			bp_get_template_part( 'activity/entry' );
		}
	}
	$acivity = ob_get_contents();
	ob_end_clean();

	wp_send_json_success( array(
		'id'           => $activity_id,
		'message'      => sprintf( __( 'Update posted <a href="%s" class="just-posted">View activity</a>', 'bp-nouveau' ), esc_url( bp_activity_get_permalink( $activity_id ) ) ),
		'activity'     => $acivity,
		'is_private'   => apply_filters( 'bp_nouveau_ajax_post_update_is_private', $is_private ),
		'is_directory' => bp_is_activity_directory(),
	) );
}

/**
 * AJAX spam an activity item or comment.
 *
 * @todo implement the delete_activity_comment ajax action
 * in buddypress-activity.js
 *
 * @since 1.0.0
 *
 * @return mixed String on error, void on success.
 */
function bp_nouveau_ajax_spam_activity() {
	$bp = buddypress();

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check that user is logged in, Activity Streams are enabled, and Akismet is present.
	if ( ! is_user_logged_in() || ! bp_is_active( 'activity' ) || empty( $bp->activity->akismet ) )
		exit( '-1' );

	// Check an item ID was passed.
	if ( empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) )
		exit( '-1' );

	// Is the current user allowed to spam items?
	if ( ! bp_activity_user_can_mark_spam() )
		exit( '-1' );

	// Load up the activity item.
	$activity = new BP_Activity_Activity( (int) $_POST['id'] );
	if ( empty( $activity->component ) )
		exit( '-1' );

	// Check nonce.
	check_admin_referer( 'bp_activity_akismet_spam_' . $activity->id );

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_before_action_spam_activity', $activity->id, $activity );

	// Mark as spam.
	bp_activity_mark_as_spam( $activity );
	$activity->save();

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_action_spam_activity', $activity->id, $activity->user_id );
	exit;
}

/**
 * Join or leave a group when clicking the "join/leave" button via a POST request.
 *
 * @since 1.0.0
 *
 * @return string HTML
 */
function bp_nouveau_ajax_joinleave_group() {
	$response = array(
		'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'bp-nouveau' )
		)
	);

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) || empty( $_POST['action'] ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['nonce'] ) || empty( $_POST['item_id'] )  || ! bp_is_active( 'groups' ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce
	$nonce = $_POST['nonce'];
	$check = 'bp_nouveau_groups';

	// Use a specific one for actions needed it
	if ( ! empty( $_POST['_wpnonce'] ) && ! empty( $_POST['action'] ) ) {
		$nonce = $_POST['_wpnonce'];
		$check = $_POST['action'];
	}

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	// Cast gid as integer.
	$group_id = (int) $_POST['item_id'];

	if ( groups_is_user_banned( bp_loggedin_user_id(), $group_id ) ) {
		$response['feedback'] = sprintf(
			'<div class="bp-feedback error"><p>%s</p></div>',
			esc_html__( 'You cannot join this group.', 'bp-nouveau' )
		);

		wp_send_json_error( $response );
	}

	// Validate and get the group
	$group = groups_get_group( array( 'group_id' => $group_id ) );

	if ( empty( $group->id ) ) {
		wp_send_json_error( $response );
	}

	/**
	 *

	 Every action should be handled here

	 */
	switch ( $_POST['action'] ) {

		case 'groups_accept_invite' :
			if ( ! groups_accept_invite( bp_loggedin_user_id(), $group_id ) ) {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback error">%s</div>',
						esc_html__( 'Group invite could not be accepted', 'bp-nouveau' )
					),
					'type'     => 'error'
				);

			} else {
				groups_record_activity( array(
					'type'    => 'joined_group',
					'item_id' => $group->id
				) );

				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'Group invite accepted', 'bp-nouveau' )
					),
					'type'     => 'success',
					'is_user'  => bp_is_user(),
				);
			}
			break;

		case 'groups_reject_invite' :
			if ( ! groups_reject_invite( bp_loggedin_user_id(), $group_id ) ) {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback error">%s</div>',
						esc_html__( 'Group invite could not be rejected', 'bp-nouveau' )
					),
					'type'     => 'error'
				);
			} else {
				$response = array(
					'feedback' => sprintf(
						'<div class="bp-feedback success">%s</div>',
						esc_html__( 'Group invite rejected', 'bp-nouveau' )
					),
					'type'     => 'success',
					'is_user'  => bp_is_user(),
				);
			}
			break;
	}

	if ( 'error' === $response['type'] ) {
		wp_send_json_error( $response );
	} else {
		wp_send_json_success( $response );
	}

	if ( ! groups_is_user_member( bp_loggedin_user_id(), $group->id ) ) {
		if ( 'public' == $group->status ) {

			if ( ! groups_join_group( $group->id ) ) {
				$response['feedback'] = sprintf(
					'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
					esc_html__( 'Error joining group', 'bp-nouveau' )
				);

				wp_send_json_error( $response );
			} else {
				// User is now a member of the group
				$group->is_member = '1';

				wp_send_json_success( array(
					'contents' => bp_get_group_join_button( $group ),
					'is_group' => bp_is_group()
				) );
			}

		} elseif ( 'private' == $group->status ) {

			// If the user has already been invited, then this is
			// an Accept Invitation button.
			if ( groups_check_user_has_invite( bp_loggedin_user_id(), $group->id ) ) {

				if ( ! groups_accept_invite( bp_loggedin_user_id(), $group->id ) ) {
					$response['feedback'] = sprintf(
						'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
						esc_html__( 'Error requesting membership', 'bp-nouveau' )
					);

					wp_send_json_error( $response );
				} else {
					// User is now a member of the group
					$group->is_member = '1';

					wp_send_json_success( array(
						'contents' => bp_get_group_join_button( $group ),
						'is_group' => bp_is_group()
					) );
				}

			// Otherwise, it's a Request Membership button.
			} else {

				if ( ! groups_send_membership_request( bp_loggedin_user_id(), $group->id ) ) {
					$response['feedback'] = sprintf(
						'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
						esc_html__( 'Error requesting membership', 'bp-nouveau' )
					);

					wp_send_json_error( $response );
				} else {
					// Request is pending
					$group->is_pending = '1';

					wp_send_json_success( array(
						'contents' => bp_get_group_join_button( $group ),
						'is_group' => bp_is_group()
					) );
				}
			}
		}

	} else {

		if ( ! groups_leave_group( $group->id ) ) {
			$response['feedback'] = sprintf(
				'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
				esc_html__( 'Error leaving group', 'bp-nouveau' )
			);

			wp_send_json_error( $response );
		} else {
			// User is no more a member of the group
			$group->is_member = '0';

			wp_send_json_success( array(
				'contents' => bp_get_group_join_button( $group ),
				'is_group' => bp_is_group()
			) );
		}
	}
}

function bp_nouveau_ajax_get_users_to_invite() {
	$bp = buddypress();

	$response = array(
		'feedback' => esc_html__( 'There was a problem performing this action. Please try again.', 'bp-nouveau' ),
		'type'     => 'error',
	);

	if ( empty( $_POST['nonce'] ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce
	$nonce = $_POST['nonce'];
	$check = 'bp_nouveau_groups';

	// Use a specific one for actions needed it
	if ( ! empty( $_POST['_wpnonce'] ) && ! empty( $_POST['action'] ) ) {
		$nonce = $_POST['_wpnonce'];
		$check = $_POST['action'];
	}

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	$request = wp_parse_args( $_POST, array(
		'scope' => 'members',
	) );

	$bp->groups->invites_scope = 'members';
	$message = __( 'You can invite members using the + button, a new nav will appear to let you send your invites', 'bp-nouveau' );

	if ( 'friends' === $request['scope'] ) {
		$request['user_id'] = bp_loggedin_user_id();
		$bp->groups->invites_scope = 'friends';
		$message = __( 'You can invite friends using the + button, a new nav will appear to let you send your invites', 'bp-nouveau' );
	}

	if ( 'invited' === $request['scope'] ) {

		if ( ! bp_group_has_invites( array( 'user_id' => 'any' ) ) ) {
			wp_send_json_error( array(
				'feedback' => __( 'No pending invites found.', 'bp-nouveau' ),
				'type'     => 'info',
			) );
		}

		$request['is_confirmed'] = false;
		$bp->groups->invites_scope = 'invited';
		$message = __( 'You can view all the group\'s pending invites from this screen.', 'bp-nouveau' );
	}

	$potential_invites = bp_nouveau_get_group_potential_invites( $request );

	if ( empty( $potential_invites->users ) ) {
		$error = array(
			'feedback' => __( 'No members were found, try another filter.', 'bp-nouveau' ),
			'type'     => 'info',
		);

		if ( 'friends' === $bp->groups->invites_scope ) {
			$error = array(
				'feedback' => __( 'All your friends are already members of this group or already received an invite to join this group.', 'bp-nouveau' ),
				'type'     => 'info',
			);

			if ( 0 === (int) bp_get_total_friend_count( bp_loggedin_user_id() ) ) {
				$error = array(
					'feedback' => __( 'You have no friends!', 'bp-nouveau' ),
					'type'     => 'info',
				);
			}

		}

		unset( $bp->groups->invites_scope );

		wp_send_json_error( $error );
	}

	$potential_invites->users = array_map( 'bp_nouveau_prepare_group_potential_invites_for_js', array_values( $potential_invites->users ) );
	$potential_invites->users = array_filter( $potential_invites->users );

	// Set a message to explain use of the current scope
	$potential_invites->feedback = $message;

	unset( $bp->groups->invites_scope );

	wp_send_json_success( $potential_invites );
}
add_action( 'wp_ajax_groups_get_group_potential_invites', 'bp_nouveau_ajax_get_users_to_invite' );

function bp_nouveau_ajax_send_group_invites() {
	$bp = buddypress();

	$response = array(
		'feedback' => __( 'Invites could not be sent, please try again.', 'bp-nouveau' ),
	);

	// Verify nonce
	if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'groups_send_invites' ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Invites could not be sent, please try again.', 'bp-nouveau' ),
			'type'     => 'error',
		) );
	}

	$group_id = bp_get_current_group_id();

	if ( bp_is_group_create() && ! empty( $_POST['group_id'] ) ) {
		$group_id = (int) $_POST['group_id'];
	}

	if ( ! bp_groups_user_can_send_invites( $group_id ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'You are not allowed to send invites for this group.', 'bp-nouveau' ),
			'type'     => 'error',
		) );
	}

	if ( empty( $_POST['users'] ) ) {
		wp_send_json_error( $response );
	}

	// For feedback
	$invited = array();

	foreach ( (array) $_POST['users'] as $user_id ) {
		$invited[ $user_id ] = groups_invite_user( array( 'user_id' => $user_id, 'group_id' => $group_id ) );
	}

	if ( ! empty( $_POST['message'] ) ) {
		$bp->groups->invites_message = wp_kses( wp_unslash( $_POST['message'] ), array() );

		add_filter( 'groups_notification_group_invites_message', 'bp_nouveau_groups_invites_custom_message', 10, 1 );
	}

	// Send the invites.
	groups_send_invites( bp_loggedin_user_id(), $group_id );

	if ( ! empty( $_POST['message'] ) ) {
		unset( $bp->groups->invites_message );

		remove_filter( 'groups_notification_group_invites_message', 'bp_nouveau_groups_invites_custom_message', 10, 1 );
	}

	if ( array_search( false, $invited ) ) {
		$errors = array_keys( $invited, false );

		wp_send_json_error( array(
			'feedback' => sprintf( __( 'Invites failed for %d user(s).', 'bp-nouveau' ), count( $errors ) ),
			'users'    => $errors,
			'type'     => 'error',
		) );
	}

	wp_send_json_success( array(
		'feedback' => __( 'Invites sent.', 'bp-nouveau' )
	) );
}
add_action( 'wp_ajax_groups_send_group_invites', 'bp_nouveau_ajax_send_group_invites' );

function bp_nouveau_ajax_remove_group_invite() {
	$user_id  = $_POST['user'];
	$group_id = bp_get_current_group_id();

	// Verify nonce
	if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'groups_invite_uninvite_user' ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Invites could not be removed, please try again.', 'bp-nouveau' ),
		) );
	}

	if ( BP_Groups_Member::check_for_membership_request( $user_id, $group_id ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Too late, the user is now a member of the group.', 'bp-nouveau' ),
			'code'     => 1,
		) );
	}

	// Remove the unsent invitation.
	if ( ! groups_uninvite_user( $user_id, $group_id ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Removing the invite for the user failed.', 'bp-nouveau' ),
			'code'     => 0,
		) );
	}

	wp_send_json_success( array(
		'feedback'    => __( 'No more pending invites for the group.', 'bp-nouveau' ),
		'has_invites' => bp_group_has_invites( array( 'user_id' => 'any' ) ),
	) );
}
add_action( 'wp_ajax_groups_delete_group_invite', 'bp_nouveau_ajax_remove_group_invite' );

function bp_nouveau_ajax_messages_send_message() {
	$response = array(
		'feedback' => __( 'Your message could not be sent, please try again.', 'bp-nouveau' ),
		'type'     => 'error',
	);

	// Verify nonce
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'messages_send_message' ) ) {
		wp_send_json_error( $response );
	}

	// Validate subject and message content
	if ( empty( $_POST['subject'] ) || empty( $_POST['message_content'] ) ) {
		if ( empty( $_POST['subject'] ) ) {
			$response['feedback'] = __( 'Your message was not sent. Please enter a subject line.', 'bp-nouveau' );
		} else {
			$response['feedback'] = __( 'Your message was not sent. Please enter some content.', 'bp-nouveau' );
		}

		wp_send_json_error( $response );
	}

	// Validate recipients
	if ( empty( $_POST['send_to'] ) || ! is_array( $_POST['send_to'] ) ) {
		$response['feedback'] = __( 'Your message was not sent. Please enter at least one @username.', 'bp-nouveau' );

		wp_send_json_error( $response );
	}

	// Trim @ from usernames
	$recipients = apply_filters( 'bp_messages_recipients', array_map( create_function( '$r', "return trim( \$r, '@' );" ), $_POST['send_to'] ) );

	// Attempt to send the message.
	$send = messages_new_message( array(
		'recipients' => $recipients,
		'subject'    => $_POST['subject'],
		'content'    => $_POST['message_content'],
		'error_type' => 'wp_error'
	) );

	// Send the message.
	if ( true === is_int( $send ) ) {
		wp_send_json_success( array(
			'feedback' => __( 'Message successfully sent.', 'bp-nouveau' ),
			'type'     => 'success',
		) );

	// Message could not be sent.
	} else {
		$response['feedback'] = $send->get_error_message();

		wp_send_json_error( $response );
	}
}
add_action( 'wp_ajax_messages_send_message', 'bp_nouveau_ajax_messages_send_message' );

function bp_nouveau_ajax_messages_send_reply() {
	$response = array(
		'feedback' => __( 'There was a problem sending your reply. Please try again.', 'bp-nouveau' ),
		'type'     => 'error',
	);

	// Verify nonce
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'messages_send_message' ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['content'] ) || empty( $_POST['thread_id'] ) ) {
		$response['feedback'] = __( 'Your reply was not sent. Please enter some content.', 'bp-nouveau' );

		wp_send_json_error( $response );
	}

	$new_reply = messages_new_message( array(
		'thread_id' => (int) $_POST['thread_id'],
		'subject'   => ! empty( $_POST['subject'] ) ? $_POST['subject'] : false,
		'content'   => $_POST['content']
	) );

	// Send the reply.
	if ( empty( $new_reply ) ) {
		wp_send_json_error( $response );
	}

	// Get the message bye pretending we're in the message loop.
	global $thread_template;

	bp_thread_has_messages( array( 'thread_id' => (int) $_POST['thread_id'] ) );

	// Set the current message to the 2nd last.
	$thread_template->message = end( $thread_template->thread->messages );
	$thread_template->message = prev( $thread_template->thread->messages );

	// Set current message to current key.
	$thread_template->current_message = key( $thread_template->thread->messages );

	// Now manually iterate message like we're in the loop.
	bp_thread_the_message();

	// Manually call oEmbed
	// this is needed because we're not at the beginning of the loop.
	bp_messages_embed();

	// Output single message template part.
	$reply = array(
		'id'            => bp_get_the_thread_message_id(),
		'content'       => html_entity_decode( do_shortcode( bp_get_the_thread_message_content() ) ),
		'sender_id'     => bp_get_the_thread_message_sender_id(),
		'sender_name'   => esc_html( bp_get_the_thread_message_sender_name() ),
		'sender_link'   => bp_get_the_thread_message_sender_link(),
		'sender_avatar' => htmlspecialchars_decode( bp_core_fetch_avatar( array(
			'item_id' => bp_get_the_thread_message_sender_id(),
			'object'  => 'user',
			'type'    => 'thumb',
			'width'   => 32,
			'height'  => 32,
			'html'    => false,
		) ) ),
		'date'          => bp_get_the_thread_message_date_sent() * 1000,
		'display_date'  => bp_get_the_thread_message_time_since(),
	);

	if ( bp_is_active( 'messages', 'star' ) ) {
		$star_link = bp_get_the_message_star_action_link( array(
			'message_id' => bp_get_the_thread_message_id(),
			'url_only'  => true,
		) );

		$reply['star_link']  = $star_link;
		$reply['is_starred'] = array_search( 'unstar', explode( '/', $star_link ) );
	}

	// Clean up the loop.
	bp_thread_messages();

	wp_send_json_success( array(
		'messages' => array( $reply ),
		'feedback' => __( 'Your reply was sent successfully', 'bp-nouveau' ),
		'type'     => 'success',
	) );
}
add_action( 'wp_ajax_messages_send_reply', 'bp_nouveau_ajax_messages_send_reply' );

function bp_nouveau_ajax_get_user_message_threads() {
	global $messages_template;

	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_nouveau_messages' ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Unauthorized request.', 'bp-nouveau' ),
			'type'     => 'error'
		) );
	}

	if ( isset( $_POST['box'] ) && 'starred' === $_POST['box'] ) {
		$star_filter = true;

		// Add the message thread filter.
		add_filter( 'bp_after_has_message_threads_parse_args', 'bp_messages_filter_starred_message_threads' );
	}

	// Simulate the loop.
	if ( ! bp_has_message_threads( bp_ajax_querystring( 'messages' ) ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Sorry, no messages were found.', 'bp-nouveau' ),
			'type'     => 'info'
		) );
	}

	if ( ! empty( $star_filter ) ) {
		// remove the message thread filter.
		remove_filter( 'bp_after_has_message_threads_parse_args', 'bp_messages_filter_starred_message_threads' );
	}

	$threads = new stdClass;
	$threads->meta = array(
		'total_page' => ceil( (int) $messages_template->total_thread_count / (int) $messages_template->pag_num ),
		'page'       => $messages_template->pag_page
	);

	$threads->threads = array();
	$i = 0;

	while ( bp_message_threads() ) : bp_message_thread();
		$threads->threads[ $i ] = array(
			'id'            => bp_get_message_thread_id(),
			'message_id'    => (int) $messages_template->thread->last_message_id,
			'subject'       => html_entity_decode( bp_get_message_thread_subject() ),
			'excerpt'       => html_entity_decode( bp_get_message_thread_excerpt() ),
			'content'       => html_entity_decode( do_shortcode( bp_get_message_thread_content() ) ),
			'unread'        => bp_message_thread_has_unread(),
			'sender_name'   => bp_core_get_user_displayname( $messages_template->thread->last_sender_id ),
			'sender_link'   => bp_core_get_userlink( $messages_template->thread->last_sender_id, false, true ),
			'sender_avatar' => htmlspecialchars_decode( bp_core_fetch_avatar( array(
				'item_id' => $messages_template->thread->last_sender_id,
				'object'  => 'user',
				'type'    => 'thumb',
				'width'   => 32,
				'height'  => 32,
				'html'    => false,
			) ) ),
			'count'         => bp_get_message_thread_total_count(),
			'date'          => strtotime( bp_get_message_thread_last_post_date_raw() ) * 1000,
			'display_date'  => bp_nouveau_get_message_date( bp_get_message_thread_last_post_date_raw() ),
		);

		if ( is_array( $messages_template->thread->recipients ) ) {
			foreach ( $messages_template->thread->recipients as $recipient ) {
				$threads->threads[ $i ]['recipients'][] = array(
					'avatar' => htmlspecialchars_decode( bp_core_fetch_avatar( array(
						'item_id' => $recipient->user_id,
						'object'  => 'user',
						'type'    => 'thumb',
						'width'   => 28,
						'height'  => 28,
						'html'    => false,
					) ) ),
					'user_link' => bp_core_get_userlink( $recipient->user_id, false, true ),
					'user_name' => bp_core_get_username( $recipient->user_id ),
				);
			}
		}

		if ( bp_is_active( 'messages', 'star' ) ) {
			$star_link = bp_get_the_message_star_action_link( array(
				'thread_id' => bp_get_message_thread_id(),
				'url_only'  => true,
			) );

			$threads->threads[ $i ]['star_link']  = $star_link;

			$star_link_data = explode( '/', $star_link );
			$threads->threads[ $i ]['is_starred'] = array_search( 'unstar', $star_link_data );

			// Defaults to last
			$sm_id = (int) $messages_template->thread->last_message_id;

			if ( $threads->threads[ $i ]['is_starred'] ) {
				$sm_id = (int) $star_link_data[ $threads->threads[ $i ]['is_starred'] + 1 ];
			}

			$threads->threads[ $i ]['star_nonce'] = wp_create_nonce( 'bp-messages-star-' . $sm_id );
			$threads->threads[ $i ]['starred_id'] = $sm_id;
		}

		$i += 1;
	endwhile;

	$threads->threads = array_filter( $threads->threads );

	wp_send_json_success( $threads );
}
add_action( 'wp_ajax_messages_get_user_message_threads', 'bp_nouveau_ajax_get_user_message_threads' );

function bp_nouveau_ajax_messages_thread_read() {
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_nouveau_messages' ) ) {
		wp_send_json_error();
	}

	if ( empty( $_POST['id'] ) || empty( $_POST['message_id'] ) ) {
		wp_send_json_error();
	}

	$thread_id  = (int) $_POST['id'];
	$message_id = (int) $_POST['message_id'];

	if ( ! messages_is_valid_thread( $thread_id ) || ( ! messages_check_thread_access( $thread_id ) && ! bp_current_user_can( 'bp_moderate' ) ) ) {
		wp_send_json_error();
	}

	// Mark thread as read
	messages_mark_thread_read( $thread_id );

	// Mark latest message as read
	if ( bp_is_active( 'notifications' ) ) {
		bp_notifications_mark_notifications_by_item_id( bp_loggedin_user_id(), (int) $message_id, buddypress()->messages->id, 'new_message' );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_messages_thread_read', 'bp_nouveau_ajax_messages_thread_read' );

function bp_nouveau_ajax_get_thread_messages() {
	global $thread_template;

	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_nouveau_messages' ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Unauthorized request.', 'bp-nouveau' ),
			'type'     => 'error'
		) );
	}

	$response = array(
		'feedback' => __( 'Sorry, no messages were found.', 'bp-nouveau' ),
		'type'     => 'info'
	);

	if ( empty( $_POST['id'] ) ) {
		wp_send_json_error( $response );
	}

	$thread_id = (int) $_POST['id'];

	// Simulate the loop.
	if ( ! bp_thread_has_messages( array( 'thread_id' => $thread_id ) ) ) {
		wp_send_json_error( $response );
	}

	$thread = new stdClass;

	if ( empty( $_POST['js_thread'] ) ) {
		$thread->thread = array(
			'id'            => bp_get_the_thread_id(),
			'subject'       => html_entity_decode( bp_get_the_thread_subject() ),
		);

		if ( is_array( $thread_template->thread->recipients ) ) {
			foreach ( $thread_template->thread->recipients as $recipient ) {
				$thread->thread['recipients'][] = array(
					'avatar' => htmlspecialchars_decode( bp_core_fetch_avatar( array(
						'item_id' => $recipient->user_id,
						'object'  => 'user',
						'type'    => 'thumb',
						'width'   => 28,
						'height'  => 28,
						'html'    => false,
					) ) ),
					'user_link' => bp_core_get_userlink( $recipient->user_id, false, true ),
					'user_name' => bp_core_get_username( $recipient->user_id ),
				);
			}
		}
	}

	$thread->messages = array();
	$i = 0;

	while ( bp_thread_messages() ) : bp_thread_the_message();
		$thread->messages[ $i ] = array(
			'id'            => bp_get_the_thread_message_id(),
			'content'       => html_entity_decode( do_shortcode( bp_get_the_thread_message_content() ) ),
			'sender_id'     => bp_get_the_thread_message_sender_id(),
			'sender_name'   => esc_html( bp_get_the_thread_message_sender_name() ),
			'sender_link'   => bp_get_the_thread_message_sender_link(),
			'sender_avatar' => htmlspecialchars_decode( bp_core_fetch_avatar( array(
				'item_id' => bp_get_the_thread_message_sender_id(),
				'object'  => 'user',
				'type'    => 'thumb',
				'width'   => 32,
				'height'  => 32,
				'html'    => false,
			) ) ),
			'date'          => bp_get_the_thread_message_date_sent() * 1000,
			'display_date'  => bp_get_the_thread_message_time_since(),
		);

		if ( bp_is_active( 'messages', 'star' ) ) {
			$star_link = bp_get_the_message_star_action_link( array(
				'message_id' => bp_get_the_thread_message_id(),
				'url_only'  => true,
			) );

			$thread->messages[ $i ]['star_link']  = $star_link;
			$thread->messages[ $i ]['is_starred'] = array_search( 'unstar', explode( '/', $star_link ) );
			$thread->messages[ $i ]['star_nonce'] = wp_create_nonce( 'bp-messages-star-' . bp_get_the_thread_message_id() );
		}

		$i += 1;
	endwhile;

	$thread->messages = array_filter( $thread->messages );

	wp_send_json_success( $thread );
}
add_action( 'wp_ajax_messages_get_thread_messages', 'bp_nouveau_ajax_get_thread_messages' );

function bp_nouveau_ajax_delete_thread_messages() {
	$response = array(
		'feedback' => __( 'There was a problem deleting your message(s). Please try again.', 'bp-nouveau' ),
		'type'     => 'error',
	);

	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_nouveau_messages' ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['id'] ) ) {
		wp_send_json_error( $response );
	}

	$thread_ids = wp_parse_id_list( $_POST['id'] );

	foreach ( $thread_ids as $thread_id ) {
		if ( ! messages_check_thread_access( $thread_id ) && ! bp_current_user_can( 'bp_moderate' ) ) {
			wp_send_json_error( $response );
		}

		messages_delete_thread( $thread_id );
	}

	wp_send_json_success( array(
		'feedback' => __( 'Message(s) deleted', 'bp-nouveau' ),
		'type'     => 'success',
	) );
}
add_action( 'wp_ajax_messages_delete', 'bp_nouveau_ajax_delete_thread_messages' );

function bp_nouveau_ajax_star_thread_messages() {
	if ( empty( $_POST['action'] ) ) {
		wp_send_json_error();
	}

	$action = str_replace( 'messages_', '', $_POST['action'] );

	$response = array(
		'feedback' => sprintf( __( 'There was a problem marking your message(s) as %s. Please try again.', 'bp-nouveau' ), $action ),
		'type'     => 'error',
	);

	if ( false === bp_is_active( 'messages', 'star' ) || empty( $_POST['id'] ) ) {
		wp_send_json_error( $response );
	}

	// Check capability.
	if ( ! is_user_logged_in() || ! bp_core_can_edit_settings() ) {
		wp_send_json_error( $response );
	}

	$ids      = wp_parse_id_list( $_POST['id'] );
	$messages = array();

	// Use global nonce for bulk actions involving more than one id
	if ( 1 !== count( $ids ) ) {
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_nouveau_messages' ) ) {
			wp_send_json_error( $response );
		}

		foreach ( $ids as $mid ) {
			if ( 'star' === $action ) {
				bp_messages_star_set_action( array(
					'action'     => 'star',
					'message_id' => $mid,
				) );
			} else {
				$thread_id = messages_get_message_thread_id( $mid );

				bp_messages_star_set_action( array(
					'action'    => 'unstar',
					'thread_id' => $thread_id,
					'bulk'      => true
				) );
			}

			$messages[ $mid ] = array(
				'star_link' => bp_get_the_message_star_action_link( array(
					'message_id' => $mid,
					'url_only'  => true,
				) ),
				'is_starred' => 'star' === $action,
			);
		}

	// Use global star nonce for bulk actions involving one id or regular action
	} else {
		$id = reset( $ids );

		if ( empty( $_POST['star_nonce'] ) || ! wp_verify_nonce( $_POST['star_nonce'], 'bp-messages-star-' . $id ) ) {
			wp_send_json_error( $response );
		}

		bp_messages_star_set_action( array(
			'action'     => $action,
			'message_id' => $id,
		) );

		$messages[ $id ] = array(
			'star_link' => bp_get_the_message_star_action_link( array(
				'message_id' => $id,
				'url_only'  => true,
			) ),
			'is_starred' => 'star' === $action,
		);
	}

	wp_send_json_success( array(
		'feedback' => sprintf( __( 'Message(s) mark as %s', 'bp-nouveau' ), $action ),
		'type'     => 'success',
		'messages' => $messages,
	) );
}
add_action( 'wp_ajax_messages_star', 'bp_nouveau_ajax_star_thread_messages' );
add_action( 'wp_ajax_messages_unstar', 'bp_nouveau_ajax_star_thread_messages' );

function bp_nouveau_ajax_readunread_thread_messages() {
	if ( empty( $_POST['action'] ) ) {
		wp_send_json_error();
	}

	$action = str_replace( 'messages_', '', $_POST['action'] );

	$response = array(
		'feedback' => __( 'There was a problem marking your message(s) as read. Please try again.', 'bp-nouveau' ),
		'type'     => 'error',
	);

	if ( 'unread' === $action ) {
		$response = array(
			'feedback' => __( 'There was a problem marking your message(s) as unread. Please try again.', 'bp-nouveau' ),
			'type'     => 'error',
		);
	}

	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_nouveau_messages' ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['id'] ) ) {
		wp_send_json_error( $response );
	}

	$thread_ids = wp_parse_id_list( $_POST['id'] );

	$response['messages'] = array();

	if ( 'unread' === $action ) {
		$response['feedback'] = __( 'Message(s) marked as unread.', 'bp-nouveau' );
	} else {
		$response['feedback'] = __( 'Message(s) marked as read.', 'bp-nouveau' );
	}

	foreach ( $thread_ids as $thread_id ) {
		if ( ! messages_check_thread_access( $thread_id ) && ! bp_current_user_can( 'bp_moderate' ) ) {
			wp_send_json_error( $response );
		}

		if ( 'unread' === $action ) {
			// Mark unread
			messages_mark_thread_unread( $thread_id );
		} else {
			// Mark read
			messages_mark_thread_read( $thread_id );
		}

		$response['messages'][ $thread_id ] = array(
			'unread' => 'unread' === $action,
		);
	}

	$response['type'] = 'success';

	wp_send_json_success( $response );
}
add_action( 'wp_ajax_messages_read',   'bp_nouveau_ajax_readunread_thread_messages' );
add_action( 'wp_ajax_messages_unread', 'bp_nouveau_ajax_readunread_thread_messages' );

/** @todo remove ??? **********************************************************/

/**
 * Load messages template loop when searched on the private message page
 *
 * @since 1.6.0
 *
 * @return string Prints template loop for the Messages component.
 */
function bp_legacy_theme_messages_template_loader() {
	bp_get_template_part( 'members/single/messages/messages-loop' );
	exit();
}

/**
 * Load group invitations loop to handle pagination requests sent via AJAX.
 *
 * @since 2.0.0
 */
function bp_legacy_theme_invite_template_loader() {
	bp_get_template_part( 'groups/single/invites-loop' );
	exit();
}

/**
 * Load group membership requests loop to handle pagination requests sent via AJAX.
 *
 * @since 2.0.0
 */
function bp_legacy_theme_requests_template_loader() {
	bp_get_template_part( 'groups/single/requests-loop' );
	exit();
}

/**
 * Accept a user friendship request via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function bp_legacy_theme_ajax_accept_friendship() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	check_admin_referer( 'friends_accept_friendship' );

	if ( ! friends_accept_friendship( (int) $_POST['id'] ) )
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem accepting that request. Please try again.', 'bp-nouveau' ) . '</p></div>';

	exit;
}

/**
 * Reject a user friendship request via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function bp_legacy_theme_ajax_reject_friendship() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	check_admin_referer( 'friends_reject_friendship' );

	if ( ! friends_reject_friendship( (int) $_POST['id'] ) )
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem rejecting that request. Please try again.', 'bp-nouveau' ) . '</p></div>';

	exit;
}

/**
 * Mark a private message as unread in your inbox via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function bp_legacy_theme_ajax_message_markunread() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( ! isset($_POST['thread_ids']) ) {
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem marking messages as unread.', 'bp-nouveau' ) . '</p></div>';

	} else {
		$thread_ids = explode( ',', $_POST['thread_ids'] );

		for ( $i = 0, $count = count( $thread_ids ); $i < $count; ++$i ) {
			BP_Messages_Thread::mark_as_unread( (int) $thread_ids[$i] );
		}
	}

	exit;
}

/**
 * Mark a private message as read in your inbox via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function bp_legacy_theme_ajax_message_markread() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( ! isset($_POST['thread_ids']) ) {
		echo "-1<div id='message' class='error'><p>" . __('There was a problem marking messages as read.', 'bp-nouveau' ) . '</p></div>';

	} else {
		$thread_ids = explode( ',', $_POST['thread_ids'] );

		for ( $i = 0, $count = count( $thread_ids ); $i < $count; ++$i ) {
			BP_Messages_Thread::mark_as_read( (int) $thread_ids[$i] );
		}
	}

	exit;
}
