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

			if ( bp_get_directory_title( bp_current_component() ) ) {
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
		'<span class="time-since" data-timestamp="%1$d">%2$s</span>',
		strtotime( $activity->date_recorded ),
		bp_core_time_since( $activity->date_recorded )
	) );
}
add_filter( 'bp_activity_time_since', 'bp_next_activity_time_since', 10, 2 );

function bp_next_activity_allowed_tags( $activity_allowedtags = array() ) {
	if ( isset( $activity_allowedtags['span'] ) ) {
		$activity_allowedtags['span']['data-timestamp'] = array();
	}

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
