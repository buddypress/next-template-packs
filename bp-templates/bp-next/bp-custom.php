<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BP_Next_Object_Nav_Widget' ) ) :
/**
 * BP Sidebar Item Nav_Widget
 *
 * Adds a widget to move avatar/item nav into the sidebar
 *
 * @since  1.0
 *
 * @uses   WP_Widget
 */
class BP_Next_Object_Nav_Widget extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @since  1.0
	 *
	 * @uses   WP_Widget::__construct() to init the widget
	 */
	public function __construct() {

		$widget_ops = array(
			'description' => __( 'Displays BuddyPress primary nav in the sidebar of your site. Make sure to use it as the first widget of the sidebar and only once.', 'bp-next' ),
			'classname'   => 'widget_nav_menu buddypress_object_nav'
		);

		parent::__construct(
			'bp_next_sidebar_object_nav_widget',
			__( '(BuddyPress) Primary nav', 'bp-next' ),
			$widget_ops
		);
	}

	/**
	 * Register the widget
	 *
	 * @since  1.0
	 *
	 * @uses   register_widget() to register the widget
	 */
	public static function register_widget() {
		register_widget( 'BP_Next_Object_Nav_Widget' );
	}

	/**
	 * Displays the output, the button to post new support topics
	 *
	 * @since  1.0
	 *
	 * @param  mixed $args Arguments
	 * @return string html output
	 */
	public function widget( $args, $instance ) {
		if ( ! is_buddypress() || bp_is_group_create() ) {
			return;
		}

		$item_nav_args = wp_parse_args( $instance, apply_filters( 'bp_next_object_nav_widget_args', array(
			'bp_next_widget_title' => true,
		) ) );

		$title = '';

		if ( ! empty( $item_nav_args[ 'bp_next_widget_title' ] ) ) {
			$title = '';

			if ( bp_is_active( 'groups' ) && bp_get_current_group_name() ) {
				$title = bp_get_current_group_name();
			} elseif ( bp_is_user() ) {
				$title = bp_get_displayed_user_fullname();
			} elseif ( bp_get_directory_title( bp_current_component() ) ) {
				$title = bp_get_directory_title( bp_current_component() );
			}
		}

		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( bp_is_activity_directory() ) {
			bp_get_template_part( 'activity/object-nav' );
		} elseif ( bp_is_members_directory() ) {
			bp_get_template_part( 'members/object-nav' );
		} elseif ( bp_is_user() ) {
			bp_get_template_part( 'members/single/item-nav' );
		} elseif ( bp_is_groups_directory() ) {
			bp_get_template_part( 'groups/object-nav' );
		} elseif ( bp_is_group() ) {
			bp_get_template_part( 'groups/single/item-nav' );
		}

		echo $args['after_widget'];
	}

	/**
	 * Update the new support topic widget options (title)
	 *
	 * @since  1.0
	 *
	 * @param  array $new_instance The new instance options
	 * @param  array $old_instance The old instance options
	 * @return array the instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['bp_next_widget_title'] = (bool) $new_instance['bp_next_widget_title'];

		return $instance;
	}

	/**
	 * Output the new support topic widget options form
	 *
	 * @since  1.0
	 *
	 * @param  $instance Instance
	 * @return string HTML Output
	 */
	public function form( $instance ) {
		$defaults = array(
			'bp_next_widget_title' => true,
		);

		$instance = wp_parse_args( (array) $instance, $defaults );

		$bp_next_widget_title = (bool) $instance['bp_next_widget_title'];
		?>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $bp_next_widget_title, true ) ?> id="<?php echo $this->get_field_id( 'bp_next_widget_title' ); ?>" name="<?php echo $this->get_field_name( 'bp_next_widget_title' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'bp_next_widget_title' ); ?>"><?php esc_html_e( 'Include navigation title', 'bp-next' ); ?></label>
		</p>

		<?php
	}
}

endif;

add_action( 'bp_widgets_init', array( 'BP_Next_Object_Nav_Widget', 'register_widget' ) );

if ( ! class_exists( 'BP_Next_Group_Invite_Query' ) ) :
/**
 * Query to get members that are not already members of the group
 *
 * @since 1.0
 */
class BP_Next_Group_Invite_Query extends BP_User_Query {
	/**
	 * Array of group member ids, cached to prevent redundant lookups
	 *
	 * @var null|array Null if not yet defined, otherwise an array of ints
	 * @package BP Next
	 * @since 1.0
	 */
	protected $group_member_ids;

	/**
	 * Set up action hooks
	 *
	 * @package BP Next
	 * @since 1.0
	 */
	public function setup_hooks() {
		add_action( 'bp_pre_user_query_construct', array( $this, 'build_exclude_args' ) );
	}

	/**
	 * Exclude group members from the user query
	 * as it's not needed to invite members to join the group
	 *
	 * @package BP Next
	 * @since 1.0
	 */
	public function build_exclude_args() {
		$this->query_vars = wp_parse_args( $this->query_vars, array(
			'group_id'     => 0,
			'is_confirmed' => true,
		) );

		$group_member_ids = $this->get_group_member_ids();

		// We want to get users that are already members of the group
		$type = 'exclude';

		// We want to get invited users who did not confirmed yet
		if ( false === $this->query_vars['is_confirmed'] ) {
			$type = 'include';
		}

		if ( ! empty( $group_member_ids ) ) {
			$this->query_vars[ $type ] = $group_member_ids;
		}
	}

	/**
	 * Get the members of the queried group
	 *
	 * @package BP Next
	 * @since 1.0
	 *
	 * @return array $ids User IDs of relevant group member ids
	 */
	protected function get_group_member_ids() {
		global $wpdb;

		if ( is_array( $this->group_member_ids ) ) {
			return $this->group_member_ids;
		}

		$bp  = buddypress();
		$sql = array(
			'select'  => "SELECT user_id FROM {$bp->groups->table_name_members}",
			'where'   => array(),
			'orderby' => '',
			'order'   => '',
			'limit'   => '',
		);

		/** WHERE clauses *****************************************************/

		// Group id
		$sql['where'][] = $wpdb->prepare( "group_id = %d", $this->query_vars['group_id'] );

		if ( false === $this->query_vars['is_confirmed'] ) {
			$sql['where'][] = $wpdb->prepare( "is_confirmed = %d", (int) $this->query_vars['is_confirmed'] );
		}

		// Join the query part
		$sql['where'] = ! empty( $sql['where'] ) ? 'WHERE ' . implode( ' AND ', $sql['where'] ) : '';

		/** ORDER BY clause ***************************************************/
		$sql['orderby'] = "ORDER BY date_modified";
		$sql['order']   = "DESC";

		/** LIMIT clause ******************************************************/
		$this->group_member_ids = $wpdb->get_col( "{$sql['select']} {$sql['where']} {$sql['orderby']} {$sql['order']} {$sql['limit']}" );

		return $this->group_member_ids;
	}

	public static function get_inviter_ids( $user_id = 0, $group_id = 0 ) {
		global $wpdb;

		if ( empty( $group_id ) || empty( $user_id ) ) {
			return array();
		}

		$bp  = buddypress();

		return $wpdb->get_col( $wpdb->prepare( "SELECT inviter_id FROM {$bp->groups->table_name_members} WHERE user_id = %d AND group_id = %d", $user_id, $group_id ) );
	}
}

endif;

function bp_next_groups_get_inviter_ids( $user_id, $group_id ) {
	if ( empty( $user_id ) || empty( $group_id ) ) {
		return false;
	}

	return BP_Next_Group_Invite_Query::get_inviter_ids( $user_id, $group_id );
}

function bp_next_prepare_group_potential_invites_for_js( $user ) {
	$bp = buddypress();

	$response = array(
		'id'           => intval( $user->ID ),
		'name'         => $user->display_name,
		'avatar'       => htmlspecialchars_decode( bp_core_fetch_avatar( array(
			'item_id' => $user->ID,
			'object'  => 'user',
			'type'    => 'thumb',
			'width'   => 50,
			'height'  => 50,
			'html'    => false )
		) ),
	);

	// Do extra queries only if needed
	if ( ! empty( $bp->groups->invites_scope ) && 'invited' === $bp->groups->invites_scope ) {
		$response['is_sent']  = (bool) groups_check_user_has_invite( $user->ID, bp_get_current_group_id() );

		$inviter_ids = bp_next_groups_get_inviter_ids( $user->ID, bp_get_current_group_id() );

		foreach ( $inviter_ids as $inviter_id ) {
			$class = false;

			if ( bp_loggedin_user_id() === (int) $inviter_id ) {
				$class = 'group-self-inviter';
			}

			$response['invited_by'][] = array(
				'avatar' => htmlspecialchars_decode( bp_core_fetch_avatar( array(
					'item_id' => $inviter_id,
					'object'  => 'user',
					'type'    => 'thumb',
					'width'   => 50,
					'height'  => 50,
					'html'    => false,
					'class'   => $class,
				) ) ),
				'user_link' => bp_core_get_userlink( $inviter_id, false, true ),
				'user_name' => bp_core_get_username( $inviter_id ),
			);
		}

		if ( bp_is_item_admin() ) {
			$response['can_edit'] = true;
		} else {
			$response['can_edit'] = in_array( bp_loggedin_user_id(), $inviter_ids );
		}
	}

	return apply_filters( 'bp_next_prepare_group_potential_invites_for_js', $response, $user );
}

function bp_next_get_group_potential_invites( $args = array() ) {
	$r = bp_parse_args( $args, array(
		'group_id'     => bp_get_current_group_id(),
		'type'         => 'alphabetical',
		'per_page'     => 20,
		'page'         => 1,
		'search_terms' => false,
		'member_type'  => false,
		'user_id'      => 0,
		'is_confirmed' => true,
	) );

	if ( empty( $r['group_id'] ) ) {
		return false;
	}

	$query = new BP_Next_Group_Invite_Query( $r );

	$response = new stdClass();

	$response->meta = array( 'total_page' => 0, 'current_page' => 0 );
	$response->users = array();

	if ( ! empty( $query->results ) ) {
		$response->users = $query->results;

		if ( ! empty( $r['per_page'] ) ) {
			$response->meta = array(
				'total_page'   => ceil( (int) $query->total_users / (int) $r['per_page'] ),
				'page' => (int) $r['page'],
			);
		}
	}

	return $response;
}

function bp_next_is_object_nav_in_sidebar() {
	return is_active_widget( false, false, 'bp_next_sidebar_object_nav_widget', true );
}

if ( ! function_exists( 'bp_directory_activity_search_form' ) ) :

function bp_directory_activity_search_form() {

	$query_arg = bp_core_get_component_search_query_arg( 'activity' );
	$placeholder = bp_get_search_default_text( 'activity' );

	$search_form_html = '<form action="" method="get" id="search-activity-form">
		<label for="activity_search"><input type="text" name="' . esc_attr( $query_arg ) . '" id="activity_search" placeholder="'. esc_attr( $placeholder ) .'" /></label>
		<input type="submit" id="activity_search_submit" name="activity_search_submit" value="'. __( 'Search', 'bp-next' ) .'" />
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

/**
 * BuddyPress shouldn't use the placeholder the way it does imho!
 *
 * @todo filter group members / sites ... well anywhere :)
 */
function bp_next_directory_groups_search_form( $search_form_html = '' ) {

	$query_arg   = bp_core_get_component_search_query_arg( 'groups' );
	$placeholder = bp_get_search_default_text( 'groups' );

	$search_form_html = '<form action="" method="get" id="search-groups-form">
		<label for="groups_search"><input type="text" name="' . esc_attr( $query_arg ) . '" id="groups_search" placeholder="'. esc_attr( $placeholder ) .'" /></label>
		<input type="submit" id="groups_search_submit" name="groups_search_submit" value="'. __( 'Search', 'bp-next' ) .'" />
	</form>';

	/**
	 * Filters the HTML markup for the groups search form.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_form_html HTML markup for the search form.
	 */
	echo apply_filters( 'bp_next_directory_groups_search_form', $search_form_html );

}
add_filter( 'bp_directory_groups_search_form', 'bp_next_directory_groups_search_form', 10, 1 );

function bp_next_get_component_search_query_arg( $query_arg, $component = '' ) {
	if ( 'members' === $component ) {
		$query_arg = str_replace( '_s', '_search', $query_arg );

		if ( bp_is_group() ) {
			$query_arg = 'group_' . $query_arg;
		}
	}

	return $query_arg;
}
add_filter( 'bp_core_get_component_search_query_arg', 'bp_next_get_component_search_query_arg', 10, 2 );

function bp_next_directory_members_search_form() {

	$query_arg = bp_core_get_component_search_query_arg( 'members' );

	$placeholder = bp_get_search_default_text( 'members' );

	$search_form_html = '<form action="" method="get" id="search-members-form">
		<label for="members_search"><input type="text" name="' . esc_attr( $query_arg ) . '" id="members_search" placeholder="'. esc_attr( $placeholder ) .'" /></label>
		<input type="submit" id="members_search_submit" name="members_search_submit" value="' . __( 'Search', 'bp-next' ) . '" />
	</form>';

	/**
	 * Filters the Members component search form.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_form_html HTML markup for the member search form.
	 */
	echo apply_filters( 'bp_next_directory_members_search_form', $search_form_html );
}
add_filter( 'bp_directory_members_search_form', 'bp_next_directory_members_search_form', 10, 1 );

function bp_next_activity_scope_newest_class( $classes = '' ) {
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
	 * @see bp_next_ajax_querystring()
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
		$myclasses = (array) apply_filters( 'bp_next_activity_scope_newest_class', $my_classes, $scope );

		if ( ! empty( $my_classes ) ) {
			$classes .= ' ' . join( ' ', $my_classes );
		}
	}

	return $classes;
}
add_filter( 'bp_get_activity_css_class', 'bp_next_activity_scope_newest_class', 10, 1 );

function bp_next_activity_time_since( $time_since, $activity = null ) {
	if ( ! isset ( $activity->date_recorded ) ) {
		return $time_since;
	}

	return apply_filters( 'bp_next_activity_time_since', sprintf(
		'<time class="time-since" datetime="%1$s" data-bp-timestamp="%2$d">%3$s</time>',
		esc_attr( $activity->date_recorded ),
		esc_attr( strtotime( $activity->date_recorded ) ),
		esc_attr( bp_core_time_since( $activity->date_recorded ) )
	) );
}
add_filter( 'bp_activity_time_since', 'bp_next_activity_time_since', 10, 2 );

function bp_next_activity_allowed_tags( $activity_allowedtags = array() ) {
	$activity_allowedtags['time'] = array();
	$activity_allowedtags['time']['class'] = array();
	$activity_allowedtags['time']['datetime'] = array();
	$activity_allowedtags['time']['data-bp-timestamp'] = array();

	return $activity_allowedtags;
}
add_filter( 'bp_activity_allowed_tags', 'bp_next_activity_allowed_tags', 10, 1 );

function bp_next_get_activity_delete_link( $delete_link = '' ) {
	preg_match( '/<a\s[^>]*href=\"([^\"]*)\"[^>]*>(.*)<\/a>/siU', $delete_link, $matches );

	if ( empty( $matches[0] ) || empty( $matches[1] ) || empty( $matches[2] ) ) {
		return $delete_link;
	}

	$delete_link = str_replace( '>' . $matches[2], sprintf(
		' title="%1$s"><span class="bp-screen-reader-text">%2$s</span>',
		esc_attr( $matches[2] ),
		esc_html( $matches[2] )
	), $delete_link );

	return apply_filters( 'bp_next_get_activity_delete_link', $delete_link );
}
add_filter( 'bp_get_activity_delete_link', 'bp_next_get_activity_delete_link', 10, 1 );

/**
 * Allow members to search inside their activity mentions
 *
 * This is a copy paste of bp_activity_filter_mentions_scope()
 * without the search_terms hard resetting. I think members should
 * be able to search into their activity mentions.
 *
 * @see https://buddypress.trac.wordpress.org/ticket/6713
 *
 * @since 1.0.0
 *
 * @param array $retval Empty array by default.
 * @param array $filter Current activity arguments.
 * @return array $retval
 */
function bp_next_activity_filter_mentions_scope( $retval = array(), $filter = array() ) {

	// Are mentions disabled?
	if ( ! bp_activity_do_mentions() ) {
		return $retval;
	}

	// Determine the user_id.
	if ( ! empty( $filter['user_id'] ) ) {
		$user_id = $filter['user_id'];
	} else {
		$user_id = bp_displayed_user_id()
			? bp_displayed_user_id()
			: bp_loggedin_user_id();
	}

	// Should we show all items regardless of sitewide visibility?
	$show_hidden = array();
	if ( ! empty( $user_id ) && $user_id !== bp_loggedin_user_id() ) {
		$show_hidden = array(
			'column' => 'hide_sitewide',
			'value'  => 0
		);
	}

	$retval = array(
		'relation' => 'AND',
		array(
			'column'  => 'content',
			'compare' => 'LIKE',

			// Start search at @ symbol and stop search at closing tag delimiter.
			'value'   => '@' . bp_activity_get_user_mentionname( $user_id ) . '<'
		),
		$show_hidden,

		// Overrides.
		'override' => array(
			'display_comments' => 'stream',
			'filter'           => array( 'user_id' => 0 ),
			'show_hidden'      => true
		),
	);

	return $retval;
}
add_filter( 'bp_activity_set_mentions_scope_args', 'bp_next_activity_filter_mentions_scope', 10, 2 );
// Remove Core filter as it's not possible to search inside mentions otherwise
remove_filter( 'bp_activity_set_mentions_scope_args', 'bp_activity_filter_mentions_scope', 10, 2 );

// I don't see any reason why to restrict group invites to friends..
function bp_next_group_invites_create_steps( $steps = array() ) {
	if ( bp_is_active( 'friends' ) && isset( $steps['group-invites'] ) ) {
		// Simply change the name
		$steps['group-invites']['name'] = _x( 'Invites',  'Group screen nav', 'bp-next' );
		return $steps;
	}

	// Add the create step if friends component is not active
	$steps['group-invites'] = array(
		'name'     => _x( 'Invites',  'Group screen nav', 'bp-next' ),
		'position' => 30
	);

	return $steps;
}
add_filter( 'groups_create_group_steps', 'bp_next_group_invites_create_steps', 10, 1 );

function bp_next_group_setup_nav() {
	if ( ! bp_is_group() || ! bp_groups_user_can_send_invites() ) {
		return;
	}

	// Simply change the name
	if ( bp_is_active( 'friends' ) ) {
		$bp = buddypress();

		if ( isset( $bp->bp_options_nav[ bp_get_current_group_slug() ]['send-invites'] ) ) {
			$bp->bp_options_nav[ bp_get_current_group_slug() ]['send-invites']['name'] = _x( 'Invites', 'My Group screen nav', 'bp-next' );
		}

	// Create the Subnav item for the group
	} else {
		$current_group = groups_get_current_group();
		$group_link    = bp_get_group_permalink( $current_group );

		bp_core_new_subnav_item( array(
			'name'            => _x( 'Invites', 'My Group screen nav', 'bp-next' ),
			'slug'            => 'send-invites',
			'parent_url'      => $group_link,
			'parent_slug'     => $current_group->slug,
			'screen_function' => 'groups_screen_group_invite',
			'item_css_id'     => 'invite',
			'position'        => 70,
			'user_has_access' => $current_group->user_has_access,
			'no_access_url'   => $group_link,
		) );
	}
}
add_action( 'groups_setup_nav', 'bp_next_group_setup_nav' );
