<?php
/**
 * Activity functions
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function bp_nouveau_get_activity_directory_nav_items() {
	$nav_items = array();

	$nav_items['all'] = array(
		'component' => 'activity',
		'slug'      => 'all', // slug is used because BP_Core_Nav requires it, but it's the scope
		'li_class'  => array( 'dynamic' ),
		'link'      => bp_get_activity_directory_permalink(),
		'title'     => __( 'The public activity for everyone on this site.', 'bp-nouveau' ),
		'text'      => __( 'All Members', 'bp-nouveau' ),
		'count'     => '',
		'position'  => 5,
	);

	if ( is_user_logged_in() ) {

		// If the user has favorite create a nav item
		if ( bp_get_total_favorite_count_for_user( bp_loggedin_user_id() ) ) {
			$nav_items['favorites'] = array(
				'component' => 'activity',
				'slug'      => 'favorites', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array(),
				'link'      => bp_loggedin_user_domain() . bp_get_activity_slug() . '/favorites/',
				'title'     => __( 'The activity I\'ve marked as a favorite.', 'bp-nouveau' ),
				'text'      => __( 'My Favorites', 'bp-nouveau' ),
				'count'     => false,
				'position'  => 15,
			);
		}

		// The friends component is active and user has friends
		if ( bp_is_active( 'friends' ) && bp_get_total_friend_count( bp_loggedin_user_id() ) ) {
			$nav_items['friends'] = array(
				'component' => 'activity',
				'slug'      => 'friends', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array( 'dynamic' ),
				'link'      => bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . bp_get_friends_slug() . '/',
				'title'     => __( 'The activity of my friends only.', 'bp-nouveau' ),
				'text'      => __( 'My Friends', 'bp-nouveau' ),
				'count'     => '',
				'position'  => 25,
			);
		}

		// The groups component is active and user has groups
		if ( bp_is_active( 'groups' ) && bp_get_total_group_count_for_user( bp_loggedin_user_id() ) ) {
			$nav_items['groups'] = array(
				'component' => 'activity',
				'slug'      => 'groups', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array( 'dynamic' ),
				'link'      => bp_loggedin_user_domain() . bp_get_activity_slug() . '/' . bp_get_groups_slug() . '/',
				'title'     => __( 'The activity of groups I am a member of.', 'bp-nouveau' ),
				'text'      => __( 'My Groups', 'bp-nouveau' ),
				'count'     => '',
				'position'  => 35,
			);
		}

		// Mentions are allowed
		if ( bp_activity_do_mentions() ) {
			$count = '';
			if ( bp_get_total_mention_count_for_user( bp_loggedin_user_id() ) ) {
				$count = bp_total_mention_count_for_user( bp_loggedin_user_id() );
			}

			$nav_items['mentions'] = array(
				'component' => 'activity',
				'slug'      => 'mentions', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array( 'dynamic' ),
				'link'      => bp_loggedin_user_domain() . bp_get_activity_slug() . '/mentions/',
				'title'     => __( 'Activity that I have been mentioned in.', 'bp-nouveau' ),
				'text'      => __( 'Mentions', 'bp-nouveau' ),
				'count'     => $count,
				'position'  => 45,
			);
		}
	}

	/**
	 * Use this filter to introduce your custom nav items for the activity directory.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $nav_items The list of the activity directory nav items.
	 */
	return apply_filters( 'bp_nouveau_get_activity_directory_nav_items', $nav_items );
}

/**
 * Make sure bp_get_activity_show_filters() will return the filters and the context
 * instead of the output.
 *
 * @since 1.0.0
 *
 * @param string $output string HTML output
 * @param  'directory' see comment below
 */
function bp_nouveau_get_activity_filters_array( $output = '', $filters = array(), $context = '' ) {
	return array( 'filters' => $filters, 'context' => $context );
}

/**
 * Get Dropdown filters of the activity component
 *
 * @since 1.0.0
 *
 * @return array the filters
 */
function bp_nouveau_get_activity_filters() {
	add_filter( 'bp_get_activity_show_filters', 'bp_nouveau_get_activity_filters_array', 10, 3 );

	$filters_data = bp_get_activity_show_filters();

	remove_filter( 'bp_get_activity_show_filters', 'bp_nouveau_get_activity_filters_array', 10, 3 );

	$action = '';
	if ( 'group' === $filters_data['context'] ) {
		$action = 'bp_group_activity_filter_options';
	} elseif ( 'member' === $filters_data['context'] || 'member_groups' === $filters_data['context'] ) {
		$action = 'bp_member_activity_filter_options';
	} else {
		$action = 'bp_activity_filter_options';
	}

	$filters = $filters_data['filters'];

	if ( $action ) {
		return bp_nouveau_parse_hooked_options( $action, $filters );
	}

	return $filters;
}

if ( ! function_exists( 'bp_directory_activity_search_form' ) ) :

function bp_directory_activity_search_form() {

	$query_arg = bp_core_get_component_search_query_arg( 'activity' );
	$placeholder = bp_get_search_default_text( 'activity' );

	$search_form_html = '<form action="" method="get" id="search-activity-form">
		<label for="activity_search"><input type="text" name="' . esc_attr( $query_arg ) . '" id="activity_search" placeholder="'. esc_attr( $placeholder ) .'" /></label>
		<input type="submit" id="activity_search_submit" name="activity_search_submit" value="'. __( 'Search', 'bp-nouveau' ) .'" />
	</form>';

	/**
	 * Filters the HTML markup for the groups search form.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_form_html HTML markup for the search form.
	 */
	echo apply_filters( 'bp_directory_activity_search_form', $search_form_html );

}

endif;

function bp_nouveau_activity_secondary_avatars( $action, $activity ) {
	switch ( $activity->component ) {
		case 'groups' :
		case 'friends' :
			// Only insert avatar if one exists.
			if ( $secondary_avatar = bp_get_activity_secondary_avatar() ) {
				$reverse_content = strrev( $action );
				$position        = strpos( $reverse_content, 'a<' );
				$action          = substr_replace( $action, $secondary_avatar, -$position - 2, 0 );
			}
			break;
	}

	return $action;
}

function bp_nouveau_activity_scope_newest_class( $classes = '' ) {
	if ( ! is_user_logged_in() ) {
		return $classes;
	}

	// We'll use this several times
	$user_id = bp_loggedin_user_id();

	// New classes to add.
	$my_classes = array();

	/**
	 * HeartBeat requests will transport the scope
	 *
	 * @see bp_nouveau_ajax_querystring()
	 */
	$scope = '';

	if ( ! empty( $_POST['data']['bp_heartbeat']['scope'] ) ) {
		$scope = sanitize_key( $_POST['data']['bp_heartbeat']['scope'] );
	}

	/**
	 * Add specific classes to perform specific actions on the client side
	 */
	if ( $scope && bp_is_activity_directory() ) {
		$component  = bp_get_activity_object_name();

		/**
		 * These classes will be used to count the number of newest activities for
		 * the 'Mentions', 'My Groups' & 'My Friends' tabs
		 */
		if ( 'all' === $scope ) {
			if ( 'groups' === $component && bp_is_active( $component ) ) {
				// Is the current user a member of the group the activity is attached to?
				if ( groups_is_user_member( $user_id, bp_get_activity_item_id() ) ) {
					$my_classes[] = 'bp-my-groups';
				}
			}

			// Friends can post in groups the user is a member of
			if ( bp_is_active( 'friends' ) && (int) $user_id !== (int) bp_get_activity_user_id() ) {
				if ( friends_check_friendship( $user_id, bp_get_activity_user_id() ) ) {
					$my_classes[] = 'bp-my-friends';
				}
			}

			// A mention can be posted by a friend within a group
			if ( true === bp_activity_do_mentions() ) {
				$new_mentions = bp_get_user_meta( $user_id, 'bp_new_mentions', true );

				// The current activity is one of the new mentions
				if ( is_array( $new_mentions ) && in_array( bp_get_activity_id(), $new_mentions ) ) {
					$my_classes[] = 'bp-my-mentions';
				}
			}

		/**
		 * This class will be used to highlight the newest activities when
		 * viewing the 'Mentions', 'My Groups' or the 'My Friends' tabs
		 */
		} elseif ( 'friends' === $scope || 'groups' === $scope || 'mentions' === $scope ) {
			$my_classes[] = 'newest_' . $scope . '_activity';
		}

		/**
		 * Leave other components do their specific stuff if needed.
		 */
		$myclasses = (array) apply_filters( 'bp_nouveau_activity_scope_newest_class', $my_classes, $scope );

		if ( ! empty( $my_classes ) ) {
			$classes .= ' ' . join( ' ', $my_classes );
		}
	}

	return $classes;
}

function bp_nouveau_activity_time_since( $time_since, $activity = null ) {
	if ( ! isset ( $activity->date_recorded ) ) {
		return $time_since;
	}

	return apply_filters( 'bp_nouveau_activity_time_since', sprintf(
		'<time class="time-since" datetime="%1$s" data-bp-timestamp="%2$d">%3$s</time>',
		esc_attr( $activity->date_recorded ),
		esc_attr( strtotime( $activity->date_recorded ) ),
		esc_attr( bp_core_time_since( $activity->date_recorded ) )
	) );
}

function bp_nouveau_activity_allowed_tags( $activity_allowedtags = array() ) {
	$activity_allowedtags['time'] = array();
	$activity_allowedtags['time']['class'] = array();
	$activity_allowedtags['time']['datetime'] = array();
	$activity_allowedtags['time']['data-bp-timestamp'] = array();

	return $activity_allowedtags;
}

function bp_nouveau_get_activity_delete_link( $delete_link = '' ) {
	preg_match( '/<a\s[^>]*href=\"([^\"]*)\"[^>]*>(.*)<\/a>/siU', $delete_link, $matches );

	if ( empty( $matches[0] ) || empty( $matches[1] ) || empty( $matches[2] ) ) {
		return $delete_link;
	}

	$delete_link = str_replace( '>' . $matches[2], sprintf(
		' title="%1$s"><span class="bp-screen-reader-text">%2$s</span>',
		esc_attr( $matches[2] ),
		esc_html( $matches[2] )
	), $delete_link );

	return apply_filters( 'bp_nouveau_get_activity_delete_link', $delete_link );
}
