<?php
/**
 * BP Nouveau Members
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BP_Nouveau_Members' ) ) :
/**
 * Members Loader class
 *
 * @since 1.0.0
 */
class BP_Nouveau_Members {
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
		require( trailingslashit( $this->dir ) . 'template-tags.php' );
	}

	/**
	 * Register do_action() hooks
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {
		$ajax_actions = array(
			array( 'members_filter' => array( 'function' => 'bp_nouveau_ajax_object_template_loader', 'nopriv' => true ) ),
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
		add_filter( 'bp_directory_members_search_form', 'bp_nouveau_members_directory_search_form', 10, 1 );
	}
}

endif;

/**
 * Launch the Members loader class.
 *
 * @since 1.0.0
 */
function bp_nouveau_members( $bp_nouveau = null ) {
	if ( is_null( $bp_nouveau ) ) {
		return;
	}

	$bp_nouveau->members = new BP_Nouveau_Members();
}
add_action( 'bp_nouveau_includes', 'bp_nouveau_members', 5, 1 );
