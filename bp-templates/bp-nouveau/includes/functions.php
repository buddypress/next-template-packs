<?php
/**
 * Common functions
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function bp_nouveau_ajax_button( $output ='', $button = null, $before ='', $after = '' ) {
	if ( empty( $button->component ) ) {
		return $output;
	}

	$data_attribute = $button->id;

	if ( 'member_friendship' === $button->id ) {
		$parse_class = explode( ' ', $button->link_class );

		if ( false !== $parse_class ) {
			$find_id = array_intersect( $parse_class, array(
				'pending_friend',
				'awaiting_response_friend',
				'is_friend',
				'not_friends',
			) );

			if ( 1 === count( $find_id ) ) {
				$data_attribute = reset( $find_id );

				if ( in_array( $data_attribute, array( 'pending_friend', 'awaiting_response_friend' ) ) ) {
					$data_attribute = str_replace( '_friend', '', $data_attribute );
				}
			}
		}
	}

	// Add span bp-screen-reader-text class
	return $before . '<a'. $button->link_href . $button->link_title . $button->link_id . $button->link_rel . $button->link_class . ' data-bp-btn-action="' . $data_attribute . '">' . $button->link_text . '</a>' . $after;
}

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

function bp_nouveau_is_object_nav_in_sidebar() {
	return is_active_widget( false, false, 'bp_nouveau_sidebar_object_nav_widget', true );
}

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

function bp_nouveau_current_user_can( $capability = '' ) {
	return apply_filters( 'bp_nouveau_current_user_can', is_user_logged_in(), $capability, bp_loggedin_user_id() );
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
		'bp_members_directory_order_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The &#39;bp_members_directory_order_options&#39; action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the &#39;bp_nouveau_get_members_filters&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_activity_filter_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'Instead of using the &#39;bp_activity_filter_options&#39; action you should register your activity types using the function &#39;bp_activity_set_action&#39;', 'bp-nouveau' ),
		),
		'bp_member_activity_filter_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'Instead of using the &#39;bp_member_activity_filter_options&#39; action you should register your activity types using the function &#39;bp_activity_set_action&#39;', 'bp-nouveau' ),
		),
		'bp_group_activity_filter_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'Instead of using the &#39;bp_group_activity_filter_options&#39; action you should register your activity types using the function &#39;bp_activity_set_action&#39;', 'bp-nouveau' ),
		),
		'bp_groups_directory_order_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The &#39;bp_groups_directory_order_options&#39; action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the &#39;bp_nouveau_get_groups_filters&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_member_group_order_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The &#39;bp_member_group_order_options&#39; action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the &#39;bp_nouveau_get_groups_filters&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_member_blog_order_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The &#39;bp_member_blog_order_options&#39; action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the &#39;bp_nouveau_get_blogs_filters&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_blogs_directory_order_options' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The &#39;bp_blogs_directory_order_options&#39; action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the &#39;bp_nouveau_get_blogs_filters&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_activity_entry_meta' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The &#39;bp_activity_entry_meta&#39; action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the &#39;bp_nouveau_get_activity_entry_buttons&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_member_header_actions' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The &#39;bp_member_header_actions&#39; action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the &#39;bp_nouveau_get_members_buttons&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_directory_members_actions' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The &#39;bp_directory_members_actions&#39; action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the &#39;bp_nouveau_get_members_buttons&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_group_header_meta' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The &#39;bp_group_header_meta&#39; action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the &#39;bp_nouveau_get_group_meta&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_directory_members_item' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The &#39;bp_directory_members_item&#39; action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the &#39;bp_nouveau_get_member_meta&#39; filter instead', 'bp-nouveau' ),
		),
		'bp_profile_header_meta' => array(
			'hook_type'    => 'action',
			'message_type' => 'warning',
			'message'      => __( 'The &#39;bp_profile_header_meta&#39; action will soon be deprecated in the BP Nouveau template pack, we recommend you now use the &#39;bp_nouveau_get_member_meta&#39; filter instead', 'bp-nouveau' ),
		),
	);
}

/**
 * Run specific "select filter" hooks to catch the options and build an array out of them
 *
 * @since 1.0.0
 *
 * @param string $hook the do_action
 * @param array  $filters the array of options
 * @return array the filters
 */
function bp_nouveau_parse_hooked_options( $hook = '', $filters = array() ) {
	if ( empty( $hook ) ) {
		return $filters;
	}

	ob_start();
	do_action( $hook );

	$output = ob_get_clean();

	preg_match_all( '/<option value="(.*?)"\s*>(.*?)<\/option>/', $output, $matches );

	if ( ! empty( $matches[1] ) && ! empty( $matches[2] ) ) {
		foreach ( $matches[1] as $ik => $key_action ) {
			if ( ! empty( $matches[2][ $ik ] ) && ! isset( $filters[ $key_action ] ) ) {
				$filters[ $key_action ] = $matches[2][ $ik ];
			}
		}
	}

	return $filters;
}

/**
 * Get Dropdawn filters for the current component of the one passed in params
 *
 * @since 1.0.0
 *
 * @param string $context   'directory', 'user' or 'group'
 * @param string $component The BuddyPress component ID
 * @return array the dropdown filters
 */
function bp_nouveau_get_component_filters( $context = '', $component = '' ) {
	$filters = array();

	if ( empty( $context ) ) {
		if ( bp_is_user() ) {
			$context = 'user';
		} elseif ( bp_is_group() ) {
			$context = 'group';

		// Defaults to directory
		} else {
			$context = 'directory';
		}
	}

	if ( empty( $component ) ) {
		if ( 'directory' === $context || 'user' === $context ) {
			$component = bp_current_component();
		} elseif ( 'group' === $context && bp_is_group_activity() ) {
			$component = 'activity';
		}
	}

	if ( ! bp_is_active( $component ) ) {
		return $filters;
	}

	if ( 'members' === $component ) {
		$filters = bp_nouveau_get_members_filters( $context );
	} elseif ( 'activity' === $component ) {
		$filters = bp_nouveau_get_activity_filters();

		// Specific case for the activity dropdown
		$filters = array_merge( array( '-1' => __( '&mdash; Everything &mdash;', 'bp-nouveau' ) ), $filters );
	} elseif ( 'groups' === $component ) {
		$filters = bp_nouveau_get_groups_filters( $context );
	} elseif ( 'blogs' === $component ) {
		$filters = bp_nouveau_get_blogs_filters( $context );
	}

	return $filters;
}

/**
 * When previewing make sure to get the temporary setting of the customizer.
 * This is necessary when we need to get these very early.
 *
 * @since 1.0.0
 *
 * @param  string $option the index of the setting to get.
 * @param  mixed  $retval the value to use as default.
 * @return mixed          the value for the requested option.
 */
function bp_nouveau_get_temporary_setting( $option = '', $retval = false ) {
	if ( empty( $option ) || ! isset( $_POST['customized'] ) ) {
		return $retval;
	}

	$temporary_setting = json_decode( wp_unslash( $_POST['customized'] ), true );

	if ( isset( $temporary_setting['bp_nouveau_appearance[' . $option . ']'] ) ) {
		$retval = $temporary_setting['bp_nouveau_appearance[' . $option . ']'];
	}

	return $retval;
}

/**
 * Get the BP Nouveau Appearance settings.
 *
 * @since 1.0.0
 *
 * @param string $option Leave empty to get all settings, specify a value for a specific one.
 * @param mixed          An array of settings, the value of the requested setting.
 */
function bp_nouveau_get_appearance_settings( $option = '' ) {
	$default_args = array(
		'user_front_page'   => 1,
	);

	if ( bp_is_active( 'groups' ) ) {
		$default_args['group_front_page'] = 1;
	}

	$settings = bp_parse_args(
		bp_get_option( 'bp_nouveau_appearance', array() ),
		$default_args,
		'nouveau_appearance_settings'
	);

	if ( ! empty( $option ) ) {
		if ( isset( $settings[ $option ] ) ) {
			return $settings[ $option ];
		} else {
			return false;
		}
	}

	return $settings;
}

/**
 * Add a specific panel for the BP Nouveau Template Pack.
 *
 * @since 1.0.0
 *
 * @param WP_Customize_Manager $wp_customize WordPress customizer.
 */
function bp_nouveau_customize_register( WP_Customize_Manager $wp_customize ) {
	if ( ! bp_is_root_blog() ) {
		return;
	}

	$bp_nouveau_options = bp_nouveau_get_appearance_settings();

	$wp_customize->add_panel( 'bp_nouveau_panel', array(
		'description' => __( 'Customize the appearance of your BuddyPress Template pack.', 'bp-nouveau' ),
		'title'       => _x( 'BuddyPress Template Pack', 'Customizer Panel', 'bp-nouveau' ),
		'priority'    => 200,
	) );

	$sections = apply_filters( 'bp_nouveau_customizer_sections', array(
		'bp_nouveau_user_front_page' => array(
			'title'       => __( 'User\'s front page', 'bp-nouveau' ),
			'panel'       => 'bp_nouveau_panel',
			'priority'    => 10,
			'description' => __( 'Activate or deactivate the default front page for your users.', 'bp-nouveau' ),
		),
	) );

	// Add the sections to the customizer
	foreach ( $sections as $id_section => $section_args ) {
		$wp_customize->add_section( $id_section, $section_args );
	}

	$settings = apply_filters( 'bp_nouveau_customizer_settings', array(
		'bp_nouveau_appearance[user_front_page]' => array(
			'index'             => 'user_front_page',
			'capability'        => 'bp_moderate',
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
			'type'              => 'option',
		),
	) );

	// Add the settings
	foreach ( $settings as $id_setting => $setting_args ) {
		$args = array();

		if ( empty( $setting_args['index'] ) ) {
			continue;
		}

		$args = array_merge( $setting_args, array( 'default' => $bp_nouveau_options[ $setting_args['index'] ] ) );

		$wp_customize->add_setting( $id_setting, $args );
	}

	$controls = apply_filters( 'bp_nouveau_customizer_controls', array(
		'user_front_page' => array(
			'label'      => __( 'Enable default front page for user profiles.', 'bp-nouveau' ),
			'section'    => 'bp_nouveau_user_front_page',
			'settings'   => 'bp_nouveau_appearance[user_front_page]',
			'type'       => 'checkbox',
		),
	) );

	// Add the controls to the customizer's section
	foreach ( $controls as $id_control => $control_args ) {
		$wp_customize->add_control( $id_control, $control_args );
	}
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
