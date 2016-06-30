<?php
/**
 * BP Nouveau Groups
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BP_Nouveau_Groups' ) ) :
/**
 * Groups Loader class
 *
 * @since 1.0.0
 */
class BP_Nouveau_Groups {
	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();

		// Setup list of add_action() hooks
		$this->setup_actions();

		// Setup list of add_filter() hooks
		$this->setup_filters();
	}

	/**
	 * Globals
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {
		$this->dir                   = dirname( __FILE__ );
		$this->is_group_home_sidebar = false;
	}

	/**
	 * Include needed files
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		require( trailingslashit( $this->dir ) . 'functions.php'     );
		require( trailingslashit( $this->dir ) . 'classes.php'       );
		require( trailingslashit( $this->dir ) . 'template-tags.php' );
		require( trailingslashit( $this->dir ) . 'ajax.php'          );
	}

	/**
	 * Register do_action() hooks
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			add_action( 'groups_setup_nav', 'bp_nouveau_group_setup_nav' );
		}

		// Enqueue the scripts
		add_action( 'bp_nouveau_enqueue_scripts', 'bp_nouveau_groups_enqueue_scripts' );

		// Avoid Notices for BuddyPress Legacy Backcompat
		remove_action( 'bp_groups_directory_group_filter', 'bp_group_backcompat_create_nav_item', 1000 );

		$ajax_actions = array(
			array( 'groups_filter'                      => array( 'function' => 'bp_nouveau_ajax_object_template_loader', 'nopriv' => true ) ),
			array( 'groups_join_group'                  => array( 'function' => 'bp_nouveau_ajax_joinleave_group',        'nopriv' => false ) ),
			array( 'groups_leave_group'                 => array( 'function' => 'bp_nouveau_ajax_joinleave_group',        'nopriv' => false ) ),
			array( 'groups_accept_invite'               => array( 'function' => 'bp_nouveau_ajax_joinleave_group',        'nopriv' => false ) ),
			array( 'groups_reject_invite'               => array( 'function' => 'bp_nouveau_ajax_joinleave_group',        'nopriv' => false ) ),
			array( 'groups_request_membership'          => array( 'function' => 'bp_nouveau_ajax_joinleave_group',        'nopriv' => false ) ),
			array( 'groups_get_group_potential_invites' => array( 'function' => 'bp_nouveau_ajax_get_users_to_invite',    'nopriv' => false ) ),
			array( 'groups_send_group_invites'          => array( 'function' => 'bp_nouveau_ajax_send_group_invites',     'nopriv' => false ) ),
			array( 'groups_delete_group_invite'         => array( 'function' => 'bp_nouveau_ajax_remove_group_invite',    'nopriv' => false ) ),
		);

		foreach ( $ajax_actions as $ajax_action ) {
			$action = key( $ajax_action );

			add_action( 'wp_ajax_' . $action, $ajax_action[ $action ]['function'] );

			if ( ! empty( $ajax_action[ $action ]['nopriv'] ) ) {
				add_action( 'wp_ajax_nopriv_' . $action, $ajax_action[ $action ]['function'] );
			}
		}

		// Actions to check wether we are in the Group's default front page sidebar
		add_action( 'dynamic_sidebar_before', array( $this, 'group_home_sidebar_set'   ), 10, 1 );
		add_action( 'dynamic_sidebar_after',  array( $this, 'group_home_sidebar_unset' ), 10, 1 );
	}

	/**
	 * Register add_filter() hooks
	 *
	 * @since 1.0.0
	 */
	private function setup_filters() {
		// Register groups scripts
		add_filter( 'bp_nouveau_register_scripts', 'bp_nouveau_groups_register_scripts', 10, 1 );

		// Localize Scripts
		add_filter( 'bp_core_get_js_strings', 'bp_nouveau_groups_localize_scripts', 10, 1 );

		add_filter( 'groups_create_group_steps', 'bp_nouveau_group_invites_create_steps', 10, 1 );

		$buttons = array(
			'groups_leave_group',
			'groups_join_group',
			'groups_accept_invite',
			'groups_reject_invite',
			'groups_membership_requested',
			'groups_request_membership',
			'groups_group_membership',
		);

		foreach ( $buttons as $button ) {
			add_filter( 'bp_button_' . $button, 'bp_nouveau_ajax_button', 10, 4 );
		}

		// Add sections in the BP Template Pack panel of the customizer.
		add_filter( 'bp_nouveau_customizer_sections', 'bp_nouveau_groups_customizer_sections', 10, 1 );

		// Add settings into the Groups sections of the customizer.
		add_filter( 'bp_nouveau_customizer_settings', 'bp_nouveau_groups_customizer_settings', 10, 1 );

		// Add controls into the Groups sections of the customizer.
		add_filter( 'bp_nouveau_customizer_controls', 'bp_nouveau_groups_customizer_controls', 10, 1 );

		// Add the group's default front template to hieararchy if user enabled it (Enabled by default).
		add_filter( 'bp_groups_get_front_template', 'bp_nouveau_group_reset_front_template', 10, 1 );
	}

	/**
	 * Add filters to be sure the (BuddyPress) widgets display will be consistent
	 * with the current group's default front page.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $sidebar_index The Sidebar identifier.
	 */
	public function group_home_sidebar_set( $sidebar_index = '' ) {
		if ( 'sidebar-buddypress-groups' !== $sidebar_index ) {
			return;
		}

		$this->is_group_home_sidebar = true;

		// Add needed filters.
		bp_nouveau_groups_add_home_widget_filters();
	}

	/**
	 * Remove filters to be sure the (BuddyPress) widgets display will no more take
	 * the current group displayed in account.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $sidebar_index The Sidebar identifier.
	 */
	public function group_home_sidebar_unset( $sidebar_index = '' ) {
		if ( 'sidebar-buddypress-groups' !== $sidebar_index ) {
			return;
		}

		$this->is_group_home_sidebar = false;

		// Remove no more needed filters.
		bp_nouveau_groups_remove_home_widget_filters();
	}
}

endif;

/**
 * Launch the Groups loader class.
 *
 * @since 1.0.0
 */
function bp_nouveau_groups( $bp_nouveau = null ) {
	if ( is_null( $bp_nouveau ) ) {
		return;
	}

	$bp_nouveau->groups = new BP_Nouveau_Groups();
}
add_action( 'bp_nouveau_includes', 'bp_nouveau_groups', 10, 1 );
