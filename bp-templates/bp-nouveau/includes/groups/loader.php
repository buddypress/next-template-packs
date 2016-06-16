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
		$this->dir = dirname( __FILE__ );
	}

	/**
	 * Include needed files
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		require( trailingslashit( $this->dir ) . 'functions.php'     );
		require( trailingslashit( $this->dir ) . 'classes.php'       );
		require( trailingslashit( $this->dir ) . 'ajax.php'          );
	}

	/**
	 * Register do_action() hooks
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			add_action( 'bp_group_header_actions',          'bp_group_join_button',           5 );
			add_action( 'bp_group_header_actions',          'bp_group_new_topic_button',     20 );
			add_action( 'bp_directory_groups_actions',      'bp_group_join_button'              );
			add_action( 'bp_group_invites_item_action',     'bp_group_accept_invite_button',  5 );
			add_action( 'bp_group_invites_item_action',     'bp_group_reject_invite_button', 10 );
			add_action( 'groups_setup_nav',                 'bp_nouveau_group_setup_nav'        );
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
			array( 'request_membership'                 => array( 'function' => 'bp_nouveau_ajax_joinleave_group',        'nopriv' => false ) ),
			array( 'groups_get_group_potential_invites' => array( 'function' => 'bp_nouveau_ajax_get_users_to_invite',    'nopriv' => false ) ),
			array( 'groups_send_group_invites'          => array( 'function' => 'bp_nouveau_ajax_send_group_invites',     'nopriv' => false ) ),
			array( 'groups_delete_group_invite'         => array( 'function' => 'bp_nouveau_ajax_remove_group_invite',    'nopriv' => false ) ),
			array( 'groups_delete_group_invite'         => array( 'function' => 'bp_nouveau_ajax_remove_group_invite',    'nopriv' => false ) ),
		);

		foreach ( $ajax_actions as $ajax_action ) {
			$action = key( $ajax_action );

			add_action( 'wp_ajax_' . $action, $ajax_action[ $action ]['function'] );

			if ( ! empty( $ajax_action[ $action ]['nopriv'] ) ) {
				add_action( 'wp_ajax_nopriv_' . $action, $ajax_action[ $action ]['function'] );
			}
		}
	}

	/**
	 * Register add_filter() hooks
	 *
	 * @since 1.0.0
	 */
	private function setup_filters() {
		// Register groups scripts
		add_filter( 'bp_nouveau_register_scripts', 'bp_nouveau_groups_register_scripts', 10, 1 );

		add_filter( 'groups_create_group_steps', 'bp_nouveau_group_invites_create_steps', 10, 1 );

		$buttons = array(
			'groups_leave_group',
			'groups_join_group',
			'groups_accept_invite',
			'groups_reject_invite',
			'groups_membership_requested',
			'groups_request_membership',
		);

		foreach ( $buttons as $button ) {
			add_filter( 'bp_button_' . $button, 'bp_nouveau_ajax_button', 10, 4 );
		}
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
