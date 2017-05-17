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
		$this->dir = trailingslashit( dirname( __FILE__ ) );
	}

	/**
	 * Include needed files
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		require $this->dir . 'functions.php';
		require $this->dir . 'template-tags.php';
		require $this->dir . 'widgets.php';

		// Test suite requires the AJAX functions early.
		if ( function_exists( 'tests_add_filter' ) ) {
			require $this->dir . 'ajax.php';

		// Load AJAX code only on AJAX requests.
		} else {
			add_action( 'admin_init', function() {
				// AJAX condtion.
				if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX &&
					// Check to see if action is activity-specific.
					( false !== strpos( $_REQUEST['action'], 'activity' ) || ( 'post_update' === $_REQUEST['action'] ) )
				) {
					require $this->dir . 'ajax.php';
				}
			} );
		}
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

		/**
		 * Avoid BuddyPress to trigger a forsaken action notice.
		 * We'll generate the button inside our bp_nouveau_get_activity_entry_buttons()
		 * function.
		 */
		$bp = buddypress();

		if ( bp_is_akismet_active() && isset( $bp->activity->akismet ) ) {
			remove_action( 'bp_activity_entry_meta',      array( $bp->activity->akismet, 'add_activity_spam_button'         ) );
			remove_action( 'bp_activity_comment_options', array( $bp->activity->akismet, 'add_activity_comment_spam_button' ) );
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
