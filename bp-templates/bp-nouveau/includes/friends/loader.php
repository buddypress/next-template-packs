<?php
/**
 * BP Nouveau Friends
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BP_Nouveau_Friends' ) ) :
/**
 * Friends Loader class
 *
 * @since 1.0.0
 */
class BP_Nouveau_Friends {
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
		require( trailingslashit( $this->dir ) . 'ajax.php' );
	}

	/**
	 * Register do_action() hooks
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			add_action( 'bp_member_header_actions', 'bp_add_friend_button', 5 );
		}

		$ajax_actions = array(
			array( 'friends_remove_friend'       => array( 'function' => 'bp_nouveau_ajax_addremove_friend', 'nopriv' => false ) ),
			array( 'friends_add_friend'          => array( 'function' => 'bp_nouveau_ajax_addremove_friend', 'nopriv' => false ) ),
			array( 'friends_withdraw_friendship' => array( 'function' => 'bp_nouveau_ajax_addremove_friend', 'nopriv' => false ) ),

			/**
			 * @todo implement this action in buddypress-activity.js as it's missing
			 * right now
			 */
			array( 'accept_friendship' => array( 'function' => 'bp_legacy_theme_ajax_accept_friendship', 'nopriv' => false ) ),
			array( 'reject_friendship' => array( 'function' => 'bp_legacy_theme_ajax_reject_friendship', 'nopriv' => false ) ),
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
		$buttons = array(
			'friends_pending',
			'friends_awaiting_response',
			'friends_is_friend',
			'friends_not_friends',
		);

		foreach ( $buttons as $button ) {
			add_filter( 'bp_button_' . $button, 'bp_nouveau_ajax_button', 10, 4 );
		}
	}
}

endif;

/**
 * Launch the Friends loader class.
 *
 * @since 1.0.0
 */
function bp_nouveau_friends( $bp_nouveau = null ) {
	if ( is_null( $bp_nouveau) ) {
		return;
	}

	$bp_nouveau->activity = new BP_Nouveau_Friends();
}
add_action( 'bp_nouveau_includes', 'bp_nouveau_friends', 10, 1 );
