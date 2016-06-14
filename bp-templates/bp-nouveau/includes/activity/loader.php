<?php
/**
 * BP Nouveau Activity
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BP_Nouveau_Activity' ) ) :
/**
 * Activity Loader class
 *
 * @since 1.0.0
 */
class BP_Nouveau_Activity {
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
		require( trailingslashit( $this->dir ) . 'ajax.php'          );
	}

	/**
	 * Register do_action() hooks
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			if ( bp_activity_do_mentions() ) {
				add_action( 'bp_member_header_actions', 'bp_send_public_message_button',  20 );
			}
		}

		$ajax_actions = array(
			array( 'activity_filter'                 => array( 'function' => 'bp_nouveau_ajax_object_template_loader',      'nopriv' => true ) ),
			array( 'get_single_activity_content'     => array( 'function' => 'bp_nouveau_ajax_get_single_activity_content', 'nopriv' => true ) ),
			array( 'activity_mark_fav'               => array( 'function' => 'bp_nouveau_ajax_mark_activity_favorite',      'nopriv' => false ) ),
			array( 'activity_mark_unfav'             => array( 'function' => 'bp_nouveau_ajax_unmark_activity_favorite',    'nopriv' => false ) ),
			array( 'activity_clear_new_mentions'     => array( 'function' => 'bp_nouveau_ajax_clear_new_mentions',          'nopriv' => false ) ),
			array( 'delete_activity'                 => array( 'function' => 'bp_nouveau_ajax_delete_activity',             'nopriv' => false ) ),
			array( 'new_activity_comment'            => array( 'function' => 'bp_nouveau_ajax_new_activity_comment',        'nopriv' => false ) ),
			array( 'bp_nouveau_get_activity_objects' => array( 'function' => 'bp_nouveau_ajax_get_activity_objects',        'nopriv' => false ) ),
			array( 'post_update'                     => array( 'function' => 'bp_nouveau_ajax_post_update',                 'nopriv' => false ) ),

			/**
			 * @todo implement this action in buddypress-activity.js as it's missing
			 * right now
			 */
			array( 'delete_activity_comment'         => array( 'function' => 'bp_nouveau_ajax_delete_activity_comment',     'nopriv' => false ) ),
			array( 'bp_spam_activity'                => array( 'function' => 'bp_nouveau_ajax_spam_activity',               'nopriv' => false ) ),
			array( 'bp_spam_activity_comment'        => array( 'function' => 'bp_nouveau_ajax_spam_activity',               'nopriv' => false ) ),
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
		add_filter( 'bp_get_activity_action_pre_meta', 'bp_nouveau_activity_secondary_avatars',  10, 2 );
		add_filter( 'bp_get_activity_css_class',       'bp_nouveau_activity_scope_newest_class', 10, 1 );
		add_filter( 'bp_activity_time_since',          'bp_nouveau_activity_time_since',         10, 2 );
		add_filter( 'bp_activity_allowed_tags',        'bp_nouveau_activity_allowed_tags',       10, 1 );
		add_filter( 'bp_get_activity_delete_link',     'bp_nouveau_get_activity_delete_link',    10, 1 );
	}
}

endif;

/**
 * Launch the Activity loader class.
 *
 * @since 1.0.0
 */
function bp_nouveau_activity( $bp_nouveau = null ) {
	if ( is_null( $bp_nouveau) ) {
		return;
	}

	$bp_nouveau->activity = new BP_Nouveau_Activity();
}
add_action( 'bp_nouveau_includes', 'bp_nouveau_activity', 10, 1 );
