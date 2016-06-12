<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BP_Nouveau_Object_Nav_Widget' ) ) :
/**
 * BP Sidebar Item Nav_Widget
 *
 * Adds a widget to move avatar/item nav into the sidebar
 *
 * @since  1.0
 *
 * @uses   WP_Widget
 */
class BP_Nouveau_Object_Nav_Widget extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @since  1.0
	 *
	 * @uses   WP_Widget::__construct() to init the widget
	 */
	public function __construct() {

		$widget_ops = array(
			'description' => __( 'Displays BuddyPress primary nav in the sidebar of your site. Make sure to use it as the first widget of the sidebar and only once.', 'bp-nouveau' ),
			'classname'   => 'widget_nav_menu buddypress_object_nav'
		);

		parent::__construct(
			'bp_nouveau_sidebar_object_nav_widget',
			__( '(BuddyPress) Primary nav', 'bp-nouveau' ),
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
		register_widget( 'BP_Nouveau_Object_Nav_Widget' );
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

		$item_nav_args = wp_parse_args( $instance, apply_filters( 'bp_nouveau_object_nav_widget_args', array(
			'bp_nouveau_widget_title' => true,
		) ) );

		$title = '';

		if ( ! empty( $item_nav_args[ 'bp_nouveau_widget_title' ] ) ) {
			$title = '';

			if ( bp_is_group() ) {
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

		if ( bp_is_user() ) {
			bp_get_template_part( 'members/single/item-nav' );
		} elseif ( bp_is_group() ) {
			bp_get_template_part( 'groups/single/item-nav' );
		} elseif ( bp_is_directory() ) {
			bp_get_template_part( 'common/nav/directory-nav' );
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

		$instance['bp_nouveau_widget_title'] = (bool) $new_instance['bp_nouveau_widget_title'];

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
			'bp_nouveau_widget_title' => true,
		);

		$instance = wp_parse_args( (array) $instance, $defaults );

		$bp_nouveau_widget_title = (bool) $instance['bp_nouveau_widget_title'];
		?>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $bp_nouveau_widget_title, true ) ?> id="<?php echo $this->get_field_id( 'bp_nouveau_widget_title' ); ?>" name="<?php echo $this->get_field_name( 'bp_nouveau_widget_title' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'bp_nouveau_widget_title' ); ?>"><?php esc_html_e( 'Include navigation title', 'bp-nouveau' ); ?></label>
		</p>

		<?php
	}
}

endif;

add_action( 'bp_widgets_init', array( 'BP_Nouveau_Object_Nav_Widget', 'register_widget' ) );

if ( ! class_exists( 'BP_Nouveau_Group_Invite_Query' ) ) :
/**
 * Query to get members that are not already members of the group
 *
 * @since 1.0
 */
class BP_Nouveau_Group_Invite_Query extends BP_User_Query {
	/**
	 * Array of group member ids, cached to prevent redundant lookups
	 *
	 * @var null|array Null if not yet defined, otherwise an array of ints
	 * @package BP Nouveau
	 * @since 1.0
	 */
	protected $group_member_ids;

	/**
	 * Set up action hooks
	 *
	 * @package BP Nouveau
	 * @since 1.0
	 */
	public function setup_hooks() {
		add_action( 'bp_pre_user_query_construct', array( $this, 'build_exclude_args' ) );
	}

	/**
	 * Exclude group members from the user query
	 * as it's not needed to invite members to join the group
	 *
	 * @package BP Nouveau
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
	 * @package BP Nouveau
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

function bp_nouveau_groups_get_inviter_ids( $user_id, $group_id ) {
	if ( empty( $user_id ) || empty( $group_id ) ) {
		return false;
	}

	return BP_Nouveau_Group_Invite_Query::get_inviter_ids( $user_id, $group_id );
}

function bp_nouveau_prepare_group_potential_invites_for_js( $user ) {
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

		$inviter_ids = bp_nouveau_groups_get_inviter_ids( $user->ID, bp_get_current_group_id() );

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

	return apply_filters( 'bp_nouveau_prepare_group_potential_invites_for_js', $response, $user );
}

function bp_nouveau_get_group_potential_invites( $args = array() ) {
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

	$query = new BP_Nouveau_Group_Invite_Query( $r );

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

function bp_nouveau_is_object_nav_in_sidebar() {
	return is_active_widget( false, false, 'bp_nouveau_sidebar_object_nav_widget', true );
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

/**
 * BuddyPress shouldn't use the placeholder the way it does imho!
 *
 * @todo filter group members / sites ... well anywhere :)
 */
function bp_nouveau_directory_groups_search_form( $search_form_html = '' ) {

	$query_arg   = bp_core_get_component_search_query_arg( 'groups' );
	$placeholder = bp_get_search_default_text( 'groups' );

	$search_form_html = '<form action="" method="get" id="search-groups-form">
		<label for="groups_search"><input type="text" name="' . esc_attr( $query_arg ) . '" id="groups_search" placeholder="'. esc_attr( $placeholder ) .'" /></label>
		<input type="submit" id="groups_search_submit" name="groups_search_submit" value="'. __( 'Search', 'bp-nouveau' ) .'" />
	</form>';

	/**
	 * Filters the HTML markup for the groups search form.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_form_html HTML markup for the search form.
	 */
	echo apply_filters( 'bp_nouveau_directory_groups_search_form', $search_form_html );

}
add_filter( 'bp_directory_groups_search_form', 'bp_nouveau_directory_groups_search_form', 10, 1 );

function bp_nouveau_get_component_search_query_arg( $query_arg, $component = '' ) {
	if ( 'members' === $component ) {
		$query_arg = str_replace( '_s', '_search', $query_arg );

		if ( bp_is_group() ) {
			$query_arg = 'group_' . $query_arg;
		}
	}

	return $query_arg;
}
add_filter( 'bp_core_get_component_search_query_arg', 'bp_nouveau_get_component_search_query_arg', 10, 2 );

function bp_nouveau_directory_members_search_form() {

	$query_arg = bp_core_get_component_search_query_arg( 'members' );

	$placeholder = bp_get_search_default_text( 'members' );

	$search_form_html = '<form action="" method="get" id="search-members-form">
		<label for="members_search"><input type="text" name="' . esc_attr( $query_arg ) . '" id="members_search" placeholder="'. esc_attr( $placeholder ) .'" /></label>
		<input type="submit" id="members_search_submit" name="members_search_submit" value="' . __( 'Search', 'bp-nouveau' ) . '" />
	</form>';

	/**
	 * Filters the Members component search form.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_form_html HTML markup for the member search form.
	 */
	echo apply_filters( 'bp_nouveau_directory_members_search_form', $search_form_html );
}
add_filter( 'bp_directory_members_search_form', 'bp_nouveau_directory_members_search_form', 10, 1 );

function bp_nouveau_message_search_form() {
	$query_arg = bp_core_get_component_search_query_arg( 'messages' );

	// Get the default search text.
	$placeholder = bp_get_search_default_text( 'messages' );

	$search_form_html = '<form action="" method="get" id="search-messages-form">
		<label for="messages_search"><input type="text" name="' . esc_attr( $query_arg ) . '" id="messages_search" placeholder="'. esc_attr( $placeholder ) .'" /></label>
		<input type="submit" id="messages_search_submit" name="messages_search_submit" value="' . __( 'Search', 'bp-nouveau' ) . '" />
	</form>';

	/**
	 * Filters the private message component search form.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_form_html HTML markup for the message search form.
	 */
	echo apply_filters( 'bp_nouveau_message_search_form', $search_form_html );
}
add_filter( 'bp_message_search_form', 'bp_nouveau_message_search_form', 10, 1 );

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
add_filter( 'bp_get_activity_css_class', 'bp_nouveau_activity_scope_newest_class', 10, 1 );

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
add_filter( 'bp_activity_time_since', 'bp_nouveau_activity_time_since', 10, 2 );

function bp_nouveau_activity_allowed_tags( $activity_allowedtags = array() ) {
	$activity_allowedtags['time'] = array();
	$activity_allowedtags['time']['class'] = array();
	$activity_allowedtags['time']['datetime'] = array();
	$activity_allowedtags['time']['data-bp-timestamp'] = array();

	return $activity_allowedtags;
}
add_filter( 'bp_activity_allowed_tags', 'bp_nouveau_activity_allowed_tags', 10, 1 );

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
add_filter( 'bp_get_activity_delete_link', 'bp_nouveau_get_activity_delete_link', 10, 1 );

/**
 * Allow members to search inside their activity mentions
 *
 * This is a copy paste of bp_activity_filter_mentions_scope()
 * without the search_terms hard resetting. I think members should
 * be able to search into their activity mentions.
 *
 * @see https://buddypress.trac.wordpress.org/ticket/6713
 * Hooks are now commmented, as 6713 is now fixed!
 *
 * @since 1.0.0
 *
 * @param array $retval Empty array by default.
 * @param array $filter Current activity arguments.
 * @return array $retval
 */
function bp_nouveau_activity_filter_mentions_scope( $retval = array(), $filter = array() ) {

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
//add_filter( 'bp_activity_set_mentions_scope_args', 'bp_nouveau_activity_filter_mentions_scope', 10, 2 );
// Remove Core filter as it's not possible to search inside mentions otherwise
//remove_filter( 'bp_activity_set_mentions_scope_args', 'bp_activity_filter_mentions_scope', 10, 2 );

// I don't see any reason why to restrict group invites to friends..
function bp_nouveau_group_invites_create_steps( $steps = array() ) {
	if ( bp_is_active( 'friends' ) && isset( $steps['group-invites'] ) ) {
		// Simply change the name
		$steps['group-invites']['name'] = _x( 'Invites',  'Group screen nav', 'bp-nouveau' );
		return $steps;
	}

	// Add the create step if friends component is not active
	$steps['group-invites'] = array(
		'name'     => _x( 'Invites',  'Group screen nav', 'bp-nouveau' ),
		'position' => 30
	);

	return $steps;
}
add_filter( 'groups_create_group_steps', 'bp_nouveau_group_invites_create_steps', 10, 1 );

function bp_nouveau_group_setup_nav() {
	if ( ! bp_is_group() || ! bp_groups_user_can_send_invites() ) {
		return;
	}

	// Simply change the name
	if ( bp_is_active( 'friends' ) ) {
		$bp = buddypress();

		/**
		 * Since BuddyPress 2.6.0
		 *
		 * Direct access to bp_nav or bp_options_nav is deprecated.
		 */
		if ( class_exists( 'BP_Core_Nav' ) ) {
			$bp->groups->nav->edit_nav(
				array( 'name' => _x( 'Invites', 'My Group screen nav', 'bp-nouveau' ) ),
				'send-invites',
				bp_get_current_group_slug()
			);

		// We shouldn't do backcompat and bump BuddyPress required version to 2.6
		} else {
			if ( isset( $bp->bp_options_nav[ bp_get_current_group_slug() ]['send-invites'] ) ) {
				$bp->bp_options_nav[ bp_get_current_group_slug() ]['send-invites']['name'] = _x( 'Invites', 'My Group screen nav', 'bp-nouveau' );
			}
		}

	// Create the Subnav item for the group
	} else {
		$current_group = groups_get_current_group();
		$group_link    = bp_get_group_permalink( $current_group );

		bp_core_new_subnav_item( array(
			'name'            => _x( 'Invites', 'My Group screen nav', 'bp-nouveau' ),
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
add_action( 'groups_setup_nav', 'bp_nouveau_group_setup_nav' );

if ( ! function_exists( 'bp_group_accept_invite_button' ) ) :

function bp_group_accept_invite_button( $group = false ) {
	echo bp_get_group_accept_invite_button( $group );
}

endif;

if ( ! function_exists( 'bp_get_group_accept_invite_button' ) ) :

function bp_get_group_accept_invite_button( $group = false ) {
	global $groups_template;

	// Set group to current loop group if none passed
	if ( empty( $group ) ) {
		$group =& $groups_template->group;
	}

	// Don't show button if not logged in or previously banned
	if ( ! is_user_logged_in() || bp_group_is_user_banned( $group ) ) {
		return false;
	}

	// Group creation was not completed or status is unknown
	if ( empty( $group->status ) ) {
		return false;
	}

	// Setup button attributes
	$button = array(
		'id'                => 'accept_invite',
		'component'         => 'groups',
		'must_be_logged_in' => true,
		'block_self'        => false,
		'wrapper'           => false,
		'link_href'         => bp_get_group_accept_invite_link(),
		'link_text'         => __( 'Accept', 'bp-nouveau' ),
		'link_title'        => __( 'Accept', 'bp-nouveau' ),
		'link_class'        => 'button accept group-button accept-invite',
	);

	return bp_get_button( apply_filters( 'bp_get_group_accept_invite_button', $button, $group ) );
}

endif;

if ( ! function_exists( 'bp_group_reject_invite_button' ) ) :

function bp_group_reject_invite_button( $group = false ) {
	echo bp_get_group_reject_invite_button( $group );
}

endif;

if ( ! function_exists( 'bp_get_group_reject_invite_button' ) ) :

function bp_get_group_reject_invite_button( $group = false ) {
	global $groups_template;

	// Set group to current loop group if none passed
	if ( empty( $group ) ) {
		$group =& $groups_template->group;
	}

	// Don't show button if not logged in or previously banned
	if ( ! is_user_logged_in() || bp_group_is_user_banned( $group ) ) {
		return false;
	}

	// Group creation was not completed or status is unknown
	if ( empty( $group->status ) ) {
		return false;
	}

	// Setup button attributes
	$button = array(
		'id'                => 'reject_invite',
		'component'         => 'groups',
		'must_be_logged_in' => true,
		'block_self'        => false,
		'wrapper'           => false,
		'link_href'         => bp_get_group_reject_invite_link(),
		'link_text'         => __( 'Reject', 'bp-nouveau' ),
		'link_title'        => __( 'Reject', 'bp-nouveau' ),
		'link_class'        => 'button reject group-button reject-invite',
	);

	return bp_get_button( apply_filters( 'bp_get_group_reject_invite_button', $button, $group ) );
}

endif;

function bp_nouveau_messages_adjust_nav() {
	$bp = buddypress();

	/**
	 * Since BuddyPress 2.6.0
	 *
	 * Direct access to bp_nav or bp_options_nav is deprecated.
	 */
	if ( class_exists( 'BP_Core_Nav' ) ) {
		$secondary_nav_items = $bp->members->nav->get_secondary( array( 'parent_slug' => bp_get_messages_slug() ), false );

		if ( ! $secondary_nav_items ) {
			return;
		}

		foreach ( $secondary_nav_items as $secondary_nav_item ) {
			if ( empty( $secondary_nav_item->slug ) ) {
				continue;
			}

			if ( 'notices' === $secondary_nav_item->slug ) {
				bp_core_remove_subnav_item( bp_get_messages_slug(), $secondary_nav_item->slug, 'members' );
			} else {
				$bp->members->nav->edit_nav( array( 'link' => '#' . $secondary_nav_item->slug ), $secondary_nav_item->slug, bp_get_messages_slug() );
			}
		}

	// We shouldn't do backcompat and bump BuddyPress required version to 2.6
	} else {
		if ( ! isset( $bp->bp_options_nav[ bp_get_messages_slug() ] ) ) {
			return;
		}

		foreach ( $bp->bp_options_nav[ bp_get_messages_slug() ] as $nav_id => $nav_item ) {
			if ( $nav_id === 'notices' ) {
				bp_core_remove_subnav_item( bp_get_messages_slug(), $nav_id );
			} else {
				$bp->bp_options_nav[ bp_get_messages_slug() ][ $nav_id ]['link'] = '#' . $nav_id;
			}

		}
	}
}
add_action( 'bp_messages_setup_nav', 'bp_nouveau_messages_adjust_nav' );

function bp_nouveau_messages_adjust_admin_nav( $admin_nav ) {
	if ( empty( $admin_nav ) ) {
		return $admin_nav;
	}

	$user_messages_link = trailingslashit( bp_loggedin_user_domain() . bp_get_messages_slug() );

	foreach ( $admin_nav as $nav_iterator => $nav ) {
		$nav_id = str_replace( 'my-account-messages-', '', $nav['id'] );

		if ( 'my-account-messages' !== $nav_id ) {
			if ( 'notices' === $nav_id ) {
				$admin_nav[ $nav_iterator ]['href'] = esc_url( add_query_arg( array( 'page' => 'bp-notices' ), bp_get_admin_url( 'users.php' ) ) );
			} else {
				$admin_nav[ $nav_iterator ]['href'] = $user_messages_link . '#' . trim( $nav_id );
			}
		}
	}

	return $admin_nav;
}
add_filter( 'bp_messages_admin_nav', 'bp_nouveau_messages_adjust_admin_nav', 10, 1 );

if ( class_exists( 'WP_List_Table' ) ) :

class BP_Nouveau_Notices_List_Table extends WP_List_Table {
	public function __construct( $args = array() ) {
		parent::__construct( array(
			'plural' => 'notices',
			'singular' => 'notice',
			'ajax' => true,
			'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
	}

	public function ajax_user_can() {
		return bp_current_user_can( 'bp_moderate' );
	}

	public function prepare_items() {
		$page     = $this->get_pagenum();
		$per_page = $this->get_items_per_page( 'bp_nouveau_notices_per_page' );

		$this->items = BP_Messages_Notice::get_notices( array(
			'pag_num'  => $per_page,
			'pag_page' => $page
		) );

		$this->set_pagination_args( array(
			'total_items' => BP_Messages_Notice::get_total_notice_count(),
			'per_page' => $per_page,
		) );
	}

	public function get_columns() {
		return apply_filters( 'bp_nouveau_notices_list_table_get_columns', array(
			'subject'   => _x( 'Subject', 'Admin Notices column header', 'bp-nouveau' ),
			'message'   => _x( 'Content', 'Admin Notices column header', 'bp-nouveau' ),
			'date_sent' => _x( 'Created', 'Admin Notices column header', 'bp-nouveau' ),
		) );
	}

	public function single_row( $item ) {
		$class = '';

		if ( ! empty( $item->is_active ) ) {
			$class = ' class="notice-active"';
		}

		echo "<tr{$class}>";
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	public function column_subject( $item ) {
		$actions = array(
			'activate_deactivate' => '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
				'page' => 'bp-notices',
				'activate' => $item->id
			), bp_get_admin_url( 'users.php' ) ) ), 'messages_activate_notice' ) . '" data-bp-notice-id="' . $item->id . '" data-bp-action="activate">' . esc_html__( 'Activate Notice', 'bp-nouveau' ) . '</a>',
			'delete' => '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
				'page' => 'bp-notices',
				'delete' => $item->id
			), bp_get_admin_url( 'users.php' ) ) ), 'messages_delete_thread' ) . '" data-bp-notice-id="' . $item->id . '" data-bp-action="delete">' . esc_html__( 'Delete Notice', 'bp-nouveau' ) . '</a>',
		);

		if ( ! empty( $item->is_active ) ) {
			$actions['activate_deactivate'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
				'page' => 'bp-notices',
				'deactivate' => $item->id
			), bp_get_admin_url( 'users.php' ) ) ), 'messages_deactivate_notice' ) . '" data-bp-notice-id="' . $item->id . '" data-bp-action="deactivate">' . esc_html__( 'Deactivate Notice', 'bp-nouveau' ) . '</a>';
		}

		echo '<strong>' . apply_filters( 'bp_get_message_notice_subject', $item->subject ) . '</strong> ' . $this->row_actions( $actions );
	}

	public function column_message( $item ) {
		echo apply_filters( 'bp_get_message_notice_text', $item->message );
	}

	public function column_date_sent( $item ) {
		echo apply_filters( 'bp_get_message_notice_post_date', bp_format_time( strtotime( $item->date_sent ) ) );
	}
}

endif;

class BP_Nouveau_Admin_Notices {

	public static function register_notices_admin() {
		if ( ! is_admin() || ! bp_is_active( 'messages' ) ) {
			return;
		}

		$bp = buddypress();

		if ( empty( $bp->messages->admin ) ) {
			$bp->messages->admin = new self;
		}

		return $bp->messages->admin;
	}

	/**
	 * Constructor method.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	private function setup_globals() {
		$this->screen_id = '';
		$this->url       = add_query_arg( array( 'page' => 'bp-notices' ), bp_get_admin_url( 'users.php' ) );
	}

	private function setup_actions() {
		add_action( bp_core_admin_hook(), array( $this, 'admin_menu' ) );
	}

	public function admin_menu() {
		// Bail if current user cannot moderate community.
		if ( ! bp_current_user_can( 'bp_moderate' ) || ! bp_is_active( 'messages' ) ) {
			return false;
		}

		$this->screen_id = add_users_page(
			_x( 'All Member Notices', 'Notices admin page title', 'bp-nouveau' ),
			_x( 'All Member Notices', 'Admin Users menu', 'bp-nouveau' ),
			'manage_options',
			'bp-notices',
			array( $this, 'admin_index' )
		);

		add_action( 'load-' . $this->screen_id, array( $this, 'admin_load' ) );
	}

	public function admin_load() {
		if ( ! empty( $_POST['bp_notice']['send'] ) ) {
			$notice = wp_parse_args( $_POST['bp_notice'], array(
				'subject' => '',
				'content' => ''
			) );

			if ( messages_send_notice( $notice['subject'], $notice['content'] ) ) {
				$redirect_to = add_query_arg( 'success', 1, $this->url );

			// Notice could not be sent.
			} else {
				$redirect_to = add_query_arg( 'error', 1, $this->url );
			}

			wp_safe_redirect( $redirect_to );
			exit();
		}

		$this->list_table = new BP_Nouveau_Notices_List_Table( array( 'screen' => get_current_screen()->id ) );
	}

	public function admin_index() {
		$this->list_table->prepare_items();
		?>
		<div class="wrap">

			<h1>
				<?php echo esc_html_x( 'All Member Notices', 'Notices admin page title', 'bp-nouveau' ); ?>
				<a id="add_notice" class="add-new-h2" href="#"><?php esc_html_e( 'Add New Notice', 'bp-nouveau' ); ?></a>
			</h1>

			<form action=<?php echo esc_url( $this->url ); ?> method="post">
				<table class="widefat">
					<tr>
						<td><label for="bp_notice_subject"><?php esc_html_e( 'Subject', 'bp-nouveau' ); ?></label></td>
						<td><input type="text" class="widefat" id="bp_notice_subject" name="bp_notice[subject]"/></td>
					</tr>
					<tr>
						<td><label for="bp_notice_content"><?php esc_html_e( 'Content', 'bp-nouveau' ); ?></label></td>
						<td><textarea class="widefat" id="bp_notice_content" name="bp_notice[content]"></textarea></td>
					</tr>
					<tr class="submit">
						<td>&nbsp;</td>
						<td style="float:right">
							<input type="reset" value="<?php esc_attr_e( 'Cancel Notice', 'bp-nouveau' ); ?>" class="button-secondary">
							<input type="submit" value="<?php esc_attr_e( 'Save Notice', 'bp-nouveau' ); ?>" name="bp_notice[send]" class="button-primary">
						</td>
					</tr>
				</table>
			<form>

			<?php if ( isset( $_GET['success'] ) || isset( $_GET['error'] ) ) : ?>

				<div id="message" class="<?php echo isset( $_GET['success'] ) ? 'updated' : 'error' ; ?>">

					<p>
						<?php if ( isset( $_GET['error'] ) ) :
							esc_html_e( 'Notice was not created. Please try again.', 'bp-nouveau' );
						else:
							esc_html_e( 'Notice successfully created.', 'bp-nouveau' );
						endif; ?>
					</p>

				</div>

			<?php endif; ?>

			<?php $this->list_table->display(); ?>

		</div>
		<?php
	}
}
add_action( 'bp_init', array( 'BP_Nouveau_Admin_Notices', 'register_notices_admin' ) );

function bp_nouveau_add_notice_notification_for_user( $notifications, $user_id ) {
	if ( ! bp_is_active( 'messages' ) || ! doing_action( 'admin_bar_menu' ) ) {
		return $notifications;
	}

	$notice = BP_Messages_Notice::get_active();

	if ( empty( $notice->id ) ) {
		return $notifications;
	}

	$closed_notices = bp_get_user_meta( bp_loggedin_user_id(), 'closed_notices', true );

	if ( empty( $closed_notices ) ) {
		$closed_notices = array();
	}

	if ( in_array( $notice->id, $closed_notices ) ) {
		return $notifications;
	}

	$notice_notification = new stdClass;
	$notice_notification->id                = 0;
	$notice_notification->user_id           = bp_loggedin_user_id();
	$notice_notification->item_id           = $notice->id;
	$notice_notification->secondary_item_id = '';
	$notice_notification->component_name    = 'messages';
	$notice_notification->component_action  = 'new_notice';
	$notice_notification->date_notified     = $notice->date_sent;
	$notice_notification->is_new            = '1';

	return array_merge( $notifications, array( $notice_notification ) );
}
add_filter( 'bp_notifications_get_all_notifications_for_user', 'bp_nouveau_add_notice_notification_for_user', 10, 2 );

function bp_nouveau_format_notice_notification_for_user( $array ) {
	if ( ! empty( $array['text'] ) || ! doing_action( 'admin_bar_menu' ) ) {
		return $array;
	}

	return array(
		'text' => esc_html__( 'New site wide notice', 'bp-nouveau' ),
		'link' => bp_loggedin_user_domain(),
	);
}
add_filter( 'bp_messages_single_new_message_notification', 'bp_nouveau_format_notice_notification_for_user', 10, 1 );

function bp_nouveau_unregister_notices_widget() {
	unregister_widget( 'BP_Messages_Sitewide_Notices_Widget' );
}
add_action( 'widgets_init', 'bp_nouveau_unregister_notices_widget' );

function bp_nouveau_mce_buttons( $buttons = array() ) {
	$remove_buttons = array(
		'wp_more',
		'spellchecker',
		'wp_adv',
		'fullscreen',
	);

	// Remove unused buttons
	$buttons = array_diff( $buttons, $remove_buttons );

	// Add the image button
	array_push( $buttons, 'image' );

	return $buttons;
}

function bp_nouveau_messages_at_on_tinymce_init( $settings, $editor_id ) {
	// We only apply the mentions init to the visual post editor in the WP dashboard.
	if ( 'message_content' === $editor_id ) {
		$settings['init_instance_callback'] = 'window.bp.Nouveau.Messages.tinyMCEinit';
	}

	return $settings;
}

remove_filter( 'messages_notice_message_before_save',  'wp_filter_kses', 1 );
remove_filter( 'messages_message_content_before_save',  'wp_filter_kses', 1 );
remove_filter( 'bp_get_the_thread_message_content',    'wp_filter_kses', 1 );
add_filter( 'messages_notice_message_before_save',  'wp_filter_post_kses', 1 );
add_filter( 'messages_message_content_before_save',  'wp_filter_post_kses', 1 );
add_filter( 'bp_get_the_thread_message_content', 'wp_filter_post_kses', 1 );

add_filter( 'bp_get_message_thread_content', 'wp_filter_post_kses', 1 );
add_filter( 'bp_get_message_thread_content', 'wptexturize' );
add_filter( 'bp_get_message_thread_content', 'stripslashes_deep', 1 );
add_filter( 'bp_get_message_thread_content', 'convert_smilies', 2 );
add_filter( 'bp_get_message_thread_content', 'convert_chars' );
add_filter( 'bp_get_message_thread_content', 'make_clickable', 9 );
add_filter( 'bp_get_message_thread_content', 'wpautop' );

function bp_nouveau_get_message_date( $date ) {
	$now = bp_core_current_time( true, 'timestamp' );
	$date = strtotime( $date );

	$now_date  = getdate( $now );
	$date_date = getdate( $date );
	$compare   = array_diff( $date_date, $now_date );
	$date_format = 'Y/m/d';

	// Use Timezone string if set
	$timezone_string = bp_get_option( 'timezone_string' );
	if ( ! empty( $timezone_string ) ) {
		$timezone_object = timezone_open( $timezone_string );
		$datetime_object = date_create( "@{$date}" );
		$timezone_offset = timezone_offset_get( $timezone_object, $datetime_object ) / HOUR_IN_SECONDS;

	// Fall back on less reliable gmt_offset
	} else {
		$timezone_offset = bp_get_option( 'gmt_offset' );
	}

	// Calculate time based on the offset
	$calculated_time = $date + ( $timezone_offset * HOUR_IN_SECONDS );

	if ( empty( $compare['mday'] ) && empty( $compare['mon'] ) && empty( $compare['year'] ) ) {
		$date_format = 'H:i';

	} elseif ( empty( $compare['mon'] ) || empty( $compare['year'] ) ) {
		$date_format = 'M j';
	}

	return apply_filters( 'bp_nouveau_get_message_date', date_i18n( $date_format, $calculated_time, true ), $calculated_time, $date, $date_format );
}

function bp_nouveau_messages_get_bulk_actions() {
	ob_start();
	bp_messages_bulk_management_dropdown();

	$bulk_actions = array();

	$bulk_options = ob_get_clean();

	$matched = preg_match_all( '/<option value="(.*?)"\s*>(.*?)<\/option>/', $bulk_options, $matches, PREG_SET_ORDER );

	if ( $matched && is_array( $matches ) ) {
		foreach ( $matches as $i => $match ) {
			if ( 0 === $i ) {
				continue;
			}

			if ( isset( $match[1] ) && isset( $match[2] ) ) {
				$bulk_actions[] = array( 'value' => trim( $match[1] ), 'label' => trim( $match[2] ) );
			}
		}
	}

	return $bulk_actions;
}

function bp_nouveau_groups_invites_custom_message( $message = '' ) {
	if ( empty( $message ) ) {
		return $message;
	}

	$bp = buddypress();

	if ( empty( $bp->groups->invites_message ) ) {
		return $message;
	}

	$message = str_replace( '---------------------', "
---------------------\n
" . $bp->groups->invites_message . "\n
---------------------
	", $message );

	return $message;
}

function bp_nouveau_current_user_can( $capability = '' ) {
	return apply_filters( 'bp_nouveau_current_user_can', is_user_logged_in(), $capability, bp_loggedin_user_id() );
}

/**
 * Format a Group for a json reply
 */
function bp_nouveau_prepare_group_for_js( $item ) {
	if ( empty( $item->id ) ) {
		return array();
	}

	$item_avatar_url = bp_core_fetch_avatar( array(
		'item_id'    => $item->id,
		'object'     => 'group',
		'type'       => 'thumb',
		'html'       => false
	) );

	return array(
		'id'          => $item->id,
		'name'        => $item->name,
		'avatar_url'  => $item_avatar_url,
		'object_type' => 'group',
		'is_public'   => 'public' === $item->status,
	);
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
			'message_type' => 'error',
			'message'      => __( 'the &#39;bp_members_directory_member_types&#39; action is not available in the BP Nouveau template pack, use the &#39;bp_nouveau_get_members_directory_nav_items&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_before_activity_type_tab_all' => array(
			'hook_type'    => 'action',
			'message_type' => 'error',
			'message'      => __( 'the &#39;bp_before_activity_type_tab_all&#39; action is not available in the BP Nouveau template pack, use the &#39;bp_nouveau_get_activity_directory_nav_items&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_before_activity_type_tab_friends' => array(
			'hook_type'    => 'action',
			'message_type' => 'error',
			'message'      => __( 'the &#39;bp_before_activity_type_tab_friends&#39; action is not available in the BP Nouveau template pack, use the &#39;bp_nouveau_get_activity_directory_nav_items&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_before_activity_type_tab_groups' => array(
			'hook_type'    => 'action',
			'message_type' => 'error',
			'message'      => __( 'the &#39;bp_before_activity_type_tab_groups&#39; action is not available in the BP Nouveau template pack, use the &#39;bp_nouveau_get_activity_directory_nav_items&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_before_activity_type_tab_favorites' => array(
			'hook_type'    => 'action',
			'message_type' => 'error',
			'message'      => __( 'the &#39;bp_before_activity_type_tab_favorites&#39; action is not available in the BP Nouveau template pack, use the &#39;bp_nouveau_get_activity_directory_nav_items&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_before_activity_type_tab_mentions' => array(
			'hook_type'    => 'action',
			'message_type' => 'error',
			'message'      => __( 'the &#39;bp_before_activity_type_tab_mentions&#39; action is not available in the BP Nouveau template pack, use the &#39;bp_nouveau_get_activity_directory_nav_items&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_activity_type_tabs' => array(
			'hook_type'    => 'action',
			'message_type' => 'error',
			'message'      => __( 'the &#39;bp_activity_type_tabs&#39; action is not available in the BP Nouveau template pack, use the &#39;bp_nouveau_get_activity_directory_nav_items&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_groups_directory_group_filter' => array(
			'hook_type'    => 'action',
			'message_type' => 'error',
			'message'      => __( 'the &#39;bp_groups_directory_group_filter&#39; action is not available in the BP Nouveau template pack, use the &#39;bp_nouveau_get_activity_directory_nav_items&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_blogs_directory_blog_types' => array(
			'hook_type'    => 'action',
			'message_type' => 'error',
			'message'      => __( 'the &#39;bp_blogs_directory_blog_types&#39; action is not available in the BP Nouveau template pack, use the &#39;bp_nouveau_get_activity_directory_nav_items&#39; filter instead', 'bp-nouveau' ),
		),
	);
}

function bp_nouveau_get_members_directory_nav_items() {
	$nav_items = array();

	$nav_items['all'] = array(
		'component' => 'members',
		'slug'      => 'all', // slug is used because BP_Core_Nav requires it, but it's the scope
		'li_class'  => array(),
		'link'      => bp_get_members_directory_permalink(),
		'title'     => __( 'The members of your community.', 'bp-nouveau' ),
		'text'      => __( 'All Members', 'bp-nouveau' ),
		'count'     => bp_get_total_member_count(),
		'position'  => 5,
	);

	if ( is_user_logged_in() ) {

		// If friends component is active and the user has friends
		if ( bp_is_active( 'friends' ) && bp_get_total_friend_count( bp_loggedin_user_id() ) ) {
			$nav_items['personal'] = array(
				'component' => 'members',
				'slug'      => 'personal', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array(),
				'link'      => bp_loggedin_user_domain() . bp_get_friends_slug() . '/my-friends/',
				'title'     => __( 'The members I\'m friend with.', 'bp-nouveau' ),
				'text'      => __( 'My Friends', 'bp-nouveau' ),
				'count'     => bp_get_total_friend_count( bp_loggedin_user_id() ),
				'position'  => 15,
			);
		}
	}

	/**
	 * Use this filter to introduce your custom nav items for the members directory.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $nav_items The list of the members directory nav items.
	 */
	return apply_filters( 'bp_nouveau_get_members_directory_nav_items', $nav_items );
}

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

function bp_nouveau_get_groups_directory_nav_items() {
	$nav_items = array();

	$nav_items['all'] = array(
		'component' => 'groups',
		'slug'      => 'all', // slug is used because BP_Core_Nav requires it, but it's the scope
		'li_class'  => array( 'selected' ),
		'link'      => bp_get_groups_directory_permalink(),
		'title'     => __( 'The public and private groups of this site.', 'bp-nouveau' ),
		'text'      => __( 'All Groups', 'bp-nouveau' ),
		'count'     => bp_get_total_group_count(),
		'position'  => 5,
	);

	if ( is_user_logged_in() ) {

		$my_groups_count = bp_get_total_group_count_for_user( bp_loggedin_user_id() );

		// If the user has groups create a nav item
		if ( $my_groups_count ) {
			$nav_items['personal'] = array(
				'component' => 'groups',
				'slug'      => 'personal', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array(),
				'link'      => bp_loggedin_user_domain() . bp_get_groups_slug() . '/my-groups/',
				'title'     => __( 'The groups I\'m a member of.', 'bp-nouveau' ),
				'text'      => __( 'My Groups', 'bp-nouveau' ),
				'count'     => $my_groups_count,
				'position'  => 15,
			);
		}

		// If the user can create groups, add the create nav
		if ( bp_user_can_create_groups() ) {
			$nav_items['create'] = array(
				'component' => 'groups',
				'slug'      => 'create', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array( 'no-ajax' ),
				'link'      => trailingslashit( bp_get_groups_directory_permalink() . 'create' ),
				'title'     => __( 'Create a Group', 'bp-nouveau' ),
				'text'      => __( 'Create a Group', 'bp-nouveau' ),
				'count'     => false,
				'position'  => 999,
			);
		}
	}

	/**
	 * Use this filter to introduce your custom nav items for the groups directory.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $nav_items The list of the groups directory nav items.
	 */
	return apply_filters( 'bp_nouveau_get_groups_directory_nav_items', $nav_items );
}

function bp_nouveau_get_blogs_directory_nav_items() {
	$nav_items = array();

	$nav_items['all'] = array(
		'component' => 'blogs',
		'slug'      => 'all', // slug is used because BP_Core_Nav requires it, but it's the scope
		'li_class'  => array( 'selected' ),
		'link'      => bp_get_root_domain() . '/' . bp_get_blogs_root_slug(),
		'title'     => __( 'The public sites of this network.', 'bp-nouveau' ),
		'text'      => __( 'All Sites', 'bp-nouveau' ),
		'count'     => bp_get_total_blog_count(),
		'position'  => 5,
	);

	if ( is_user_logged_in() ) {

		$my_blogs_count = bp_get_total_blog_count_for_user( bp_loggedin_user_id() );

		// If the user has blogs create a nav item
		if ( $my_blogs_count ) {
			$nav_items['personal'] = array(
				'component' => 'blogs',
				'slug'      => 'personal', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array(),
				'link'      => bp_loggedin_user_domain() . bp_get_blogs_slug(),
				'title'     => __( 'The sites I\'m a contributor of.', 'bp-nouveau' ),
				'text'      => __( 'My Sites', 'bp-nouveau' ),
				'count'     => $my_blogs_count,
				'position'  => 15,
			);
		}

		// If the user can create blogs, add the create nav
		if ( bp_blog_signup_enabled() ) {
			$nav_items['create'] = array(
				'component' => 'blogs',
				'slug'      => 'create', // slug is used because BP_Core_Nav requires it, but it's the scope
				'li_class'  => array( 'no-ajax' ),
				'link'      => trailingslashit( bp_get_blogs_directory_permalink() . 'create' ),
				'title'     => __( 'Create a Site', 'bp-nouveau' ),
				'text'      => __( 'Create a Site', 'bp-nouveau' ),
				'count'     => false,
				'position'  => 999,
			);
		}
	}

	/**
	 * Use this filter to introduce your custom nav items for the blogs directory.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $nav_items The list of the blogs directory nav items.
	 */
	return apply_filters( 'bp_nouveau_get_blogs_directory_nav_items', $nav_items );
}

/**
 * BP Nouveau's callback for the cover image feature.
 *
 * @todo implement the cover image for this template pack!
 *
 * @since  2.4.0
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
