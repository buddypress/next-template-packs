<?php
/**
 * BP Nouveau Notifications
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Notifications Loader class
 *
 * @since 1.0.0
 */
class BP_Nouveau_Notifications {
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
		$dir = trailingslashit( $this->dir );

		require "{$dir}functions.php";
		require "{$dir}template-tags.php";
	}

	/**
	 * Register do_action() hooks
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {
		// Init notifications filters
		add_action( 'bp_init', 'bp_nouveau_notifications_init_filters', 20 );

		// Enqueue the scripts
		add_action( 'bp_nouveau_enqueue_scripts', 'bp_nouveau_notifications_enqueue_scripts' );

		$ajax_actions = array(
			array( 'notifications_filter' => array( 'function' => 'bp_nouveau_ajax_object_template_loader', 'nopriv' => false ) ),
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

		// Register notifications scripts
		add_filter( 'bp_nouveau_register_scripts', 'bp_nouveau_notifications_register_scripts', 10, 1 );

		// Add Dashicons to notification action links
		add_filter( 'bp_get_the_notification_mark_unread_link', 'bp_nouveau_notifications_mark_unread_link', 10, 1 );
		add_filter( 'bp_get_the_notification_mark_read_link',   'bp_nouveau_notifications_mark_read_link'  , 10, 1 );
		add_filter( 'bp_get_the_notification_delete_link',      'bp_nouveau_notifications_delete_link'     , 10, 1 );
	}
}

/**
 * Launch the Notifications loader class.
 *
 * @since 1.0.0
 */
function bp_nouveau_notifications( $bp_nouveau = null ) {
	if ( is_null( $bp_nouveau ) ) {
		return;
	}

	$bp_nouveau->notifications = new BP_Nouveau_Notifications();
}
add_action( 'bp_nouveau_includes', 'bp_nouveau_notifications', 10, 1 );
