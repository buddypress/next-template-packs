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
			'description' => __( 'Displays BuddyPress item primary nav & avatar in the sidebar of your site.', 'bp-next' ),
			'classname'   => 'widget_nav_menu buddypress_object_nav'
		);

		parent::__construct(
			'bp_next_sidebar_object_nav_widget',
			__( '(BuddyPress) Sidebar item nav', 'bp-next' ),
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

		<input class="checkbox" type="checkbox" <?php checked( $bp_next_widget_title, true ) ?> id="<?php echo $this->get_field_id( 'bp_next_widget_title' ); ?>" name="<?php echo $this->get_field_name( 'bp_next_widget_title' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'bp_next_widget_title' ); ?>"><?php esc_html_e( 'Include Navigation title', 'bp-next' ); ?></label><br />

		<?php
	}
}

endif;

add_action( 'bp_widgets_init', array( 'BP_Next_Object_Nav_Widget', 'register_widget' ) );


function bp_next_is_object_nav_in_sidebar() {
	return is_active_widget( false, false, 'bp_next_sidebar_object_nav_widget', true );
}
