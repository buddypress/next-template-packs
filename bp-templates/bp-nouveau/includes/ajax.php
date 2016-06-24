<?php
/**
 * Common Ajax functions
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

/** @todo remove ??? **********************************************************/

/**
 * Load group membership requests loop to handle pagination requests sent via AJAX.
 *
 * @since 2.0.0
 */
function bp_legacy_theme_requests_template_loader() {
	bp_get_template_part( 'groups/single/requests-loop' );
	exit();
}
