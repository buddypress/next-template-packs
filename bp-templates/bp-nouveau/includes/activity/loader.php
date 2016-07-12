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
		$dir = trailingslashit( $this->dir );

		require "{$dir}functions.php";
		require "{$dir}template-tags.php";
		require "{$dir}ajax.php";
		require "{$dir}widgets.php";
	}

	/**
	 * Register do_action() hooks
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// Enqueue the scripts
		add_action( 'bp_nouveau_enqueue_scripts', 'bp_nouveau_activity_enqueue_scripts' );

		// Register the Activity Widget.
		add_action( 'bp_widgets_init', array( 'BP_Latest_Activities', 'register_widget' ) );

		// Register the Activity Notifications filters
		add_action( 'bp_nouveau_notifications_init_filters', 'bp_nouveau_activity_notification_filters' );

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

		// Register activity scripts
		add_filter( 'bp_nouveau_register_scripts', 'bp_nouveau_activity_register_scripts', 10, 1 );

		// Localize Scripts
		add_filter( 'bp_core_get_js_strings', 'bp_nouveau_activity_localize_scripts', 10, 1 );

		add_filter( 'bp_get_activity_action_pre_meta', 'bp_nouveau_activity_secondary_avatars',  10, 2 );
		add_filter( 'bp_get_activity_css_class',       'bp_nouveau_activity_scope_newest_class', 10, 1 );
		add_filter( 'bp_activity_time_since',          'bp_nouveau_activity_time_since',         10, 2 );
		add_filter( 'bp_activity_allowed_tags',        'bp_nouveau_activity_allowed_tags',       10, 1 );
	}
}

endif;

/**
 * Launch the Activity loader class.
 *
 * @since 1.0.0
 */
function bp_nouveau_activity( $bp_nouveau = null ) {

	if ( is_null( $bp_nouveau ) ) {
		return;
	}

	$bp_nouveau->activity = new BP_Nouveau_Activity();
}
add_action( 'bp_nouveau_includes', 'bp_nouveau_activity', 10, 1 );
