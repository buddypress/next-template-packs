<?php
/**
 * Functions of BuddyPress's "Nouveau" template pack.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 *
 * @buddypress-template-pack {
 * Template Pack ID:       nouveau
 * Template Pack Name:     BP Nouveau
 * Version:                1.0.0
 * WP required version:    4.5
 * BP required version:    2.6.0-alpha
 * Description:            A new template pack for BuddyPress!
 * Text Domain:            bp-nouveau
 * Domain Path:            /languages/
 * Author:                 The BuddyPress community
 * Template Pack Link:     https://github.com/buddypress/next-template-packs/bp-templates/bp-nouveau
 * Template Pack Supports: activity, blogs, friends, groups, messages, notifications, settings, xprofile
 * }}
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/** Theme Setup ***************************************************************/

if ( ! class_exists( 'BP_Nouveau' ) ) :

/**
 * Loads BuddyPress Nouveau Template pack functionality.
 *
 * See @link BP_Theme_Compat() for more.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */
class BP_Nouveau extends BP_Theme_Compat {

	/** Functions *************************************************************/

	/**
	 * The main BP Nouveau Loader.
	 *
	 * @since 1.0.0
	 *
	 * @uses BP_Nouveau::setup_globals()
	 * @uses BP_Nouveau::setup_actions()
	 */
	public function __construct() {
		parent::start();

		// Include needed files
		$this->includes();

		// Add custom filters
		$this->setup_filters();
	}

	/**
	 * Includes!
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		require( trailingslashit( bp_get_theme_compat_dir() ) . 'includes/functions.php' );
		require( trailingslashit( bp_get_theme_compat_dir() ) . 'includes/ajax.php' );
	}

	/**
	 * Component global variables.
	 *
	 * You'll want to customize the values in here, so they match whatever your
	 * needs are.
	 *
	 * @since 1.0.0
	 */
	protected function setup_globals() {
		$bp = buddypress();

		if ( ! isset( $bp->theme_compat->packages['nouveau'] ) ) {
			wp_die( __( 'Something is going wrong, please deactivate any plugin having an impact on the BP Theme Compat API', 'bp-nouveau' ) );
		}

		foreach ( $bp->theme_compat->packages['nouveau'] as $property => $value ) {
			$this->{$property} = $value;
		}
	}

	/**
	 * Setup the theme hooks.
	 *
	 * @since 1.0.0
	 *
	 * @uses add_filter() To add various filters
	 * @uses add_action() To add various actions
	 */
	protected function setup_actions() {

		// Template Output.
		add_filter( 'bp_get_activity_action_pre_meta', array( $this, 'secondary_avatars' ), 10, 2 );

		// Filter BuddyPress template hierarchy and look for page templates.
		add_filter( 'bp_get_buddypress_template', array( $this, 'theme_compat_page_templates' ), 10, 1 );

		/** Scripts ***********************************************************/

		add_action( 'bp_enqueue_scripts', array( $this, 'register_scripts'  ), 2 ); // Register theme JS

		add_action( 'bp_enqueue_scripts', array( $this, 'enqueue_styles'   ) ); // Enqueue theme CSS
		add_action( 'bp_enqueue_scripts', array( $this, 'enqueue_scripts'  ) ); // Enqueue theme JS
		add_filter( 'bp_enqueue_scripts', array( $this, 'localize_scripts' ) ); // Enqueue theme script localization
		add_action( 'bp_head',            array( $this, 'head_scripts'     ) ); // Output some extra JS in the <head>.

		/** Body no-js Class **************************************************/

		add_filter( 'body_class', array( $this, 'add_nojs_body_class' ), 20, 1 );

		/** Buttons ***********************************************************/

		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			// Register buttons for the relevant component templates
			// Friends button.
			if ( bp_is_active( 'friends' ) )
				add_action( 'bp_member_header_actions',    'bp_add_friend_button',           5 );

			// Activity button.
			if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() )
				add_action( 'bp_member_header_actions',    'bp_send_public_message_button',  20 );

			// Messages button.
			if ( bp_is_active( 'messages' ) )
				add_action( 'bp_member_header_actions',    'bp_send_private_message_button', 20 );

			// Group buttons.
			if ( bp_is_active( 'groups' ) ) {
				add_action( 'bp_group_header_actions',          'bp_group_join_button',               5 );
				add_action( 'bp_group_header_actions',          'bp_group_new_topic_button',         20 );
				add_action( 'bp_directory_groups_actions',      'bp_group_join_button'                  );
				add_action( 'bp_groups_directory_group_filter', 'bp_legacy_theme_group_create_nav', 999 );
				add_action( 'bp_group_invites_item_action',     'bp_group_accept_invite_button',      5 );
				add_action( 'bp_group_invites_item_action',     'bp_group_reject_invite_button',     10 );
			}

			// Blog button.
			if ( bp_is_active( 'blogs' ) ) {
				add_action( 'bp_directory_blogs_actions',    'bp_blogs_visit_blog_button'           );
				add_action( 'bp_blogs_directory_blog_types', 'bp_legacy_theme_blog_create_nav', 999 );
			}
		}

		/** Notices ***********************************************************/

		// Only hook the 'sitewide_notices' overlay if the Sitewide
		// Notices widget is not in use (to avoid duplicate content).
		if ( bp_is_active( 'messages' ) ) {
			add_action( 'template_notices', array( $this, 'sitewide_notices' ), 9999 );
		}

		/** Ajax **************************************************************/

		$actions = array(

			// Directory filters.
			'activity_filter' => 'bp_nouveau_ajax_object_template_loader',
			'blogs_filter'    => 'bp_nouveau_ajax_object_template_loader',
			'groups_filter'   => 'bp_nouveau_ajax_object_template_loader',
			'members_filter'  => 'bp_nouveau_ajax_object_template_loader',

			/**
			 * @todo check if we still use these 3 actions, else remove it
			 * and the corresponding functions
			 */
			'messages_filter' => 'bp_legacy_theme_messages_template_loader',
			'invite_filter'   => 'bp_legacy_theme_invite_template_loader',
			'requests_filter' => 'bp_legacy_theme_requests_template_loader',

			/**
			 * @todo check if we still use these 2 actions, else remove it
			 * and the corresponding functions
			 */
			'accept_friendship' => 'bp_legacy_theme_ajax_accept_friendship',
			'reject_friendship' => 'bp_legacy_theme_ajax_reject_friendship',

			// Friends.
			'friends_remove_friend'       => 'bp_nouveau_ajax_addremove_friend',
			'friends_add_friend'          => 'bp_nouveau_ajax_addremove_friend',
			'friends_withdraw_friendship' => 'bp_nouveau_ajax_addremove_friend',


			// Activity.
			'activity_get_older_updates'      => 'bp_nouveau_ajax_activity_template_loader',
			'activity_mark_fav'               => 'bp_nouveau_ajax_mark_activity_favorite',
			'activity_mark_unfav'             => 'bp_nouveau_ajax_unmark_activity_favorite',
			'activity_clear_new_mentions'     => 'bp_nouveau_ajax_clear_new_mentions',
			'activity_widget_filter'          => 'bp_nouveau_ajax_activity_template_loader',
			'delete_activity'                 => 'bp_nouveau_ajax_delete_activity',

			/**
			 * @todo implement this action in buddypress-activity.js as it's missing
			 * right now
			 */
			'delete_activity_comment'         => 'bp_nouveau_ajax_delete_activity_comment',

			'get_single_activity_content'     => 'bp_nouveau_ajax_get_single_activity_content',
			'new_activity_comment'            => 'bp_nouveau_ajax_new_activity_comment',
			'bp_nouveau_get_activity_objects' => 'bp_nouveau_ajax_get_activity_objects',
			'post_update'                     => 'bp_nouveau_ajax_post_update',

			/**
			 * @todo implement these 2 actions in buddypress-activity.js as they're missing
			 * right now
			 */
			'bp_spam_activity'                => 'bp_nouveau_ajax_spam_activity',
			'bp_spam_activity_comment'        => 'bp_nouveau_ajax_spam_activity',

			// Groups.
			'groups_join_group'    => 'bp_nouveau_ajax_joinleave_group',
			'groups_leave_group'   => 'bp_nouveau_ajax_joinleave_group',
			'groups_accept_invite' => 'bp_nouveau_ajax_joinleave_group',
			'groups_reject_invite' => 'bp_nouveau_ajax_joinleave_group',
			'request_membership'   => 'bp_nouveau_ajax_joinleave_group',

			/**
			 * @todo check if we still use these 2 actions, else remove it
			 * and the corresponding functions
			 */
			'messages_markread'   => 'bp_legacy_theme_ajax_message_markread',
			'messages_markunread' => 'bp_legacy_theme_ajax_message_markunread',
		);

		/**
		 * Register all of these AJAX handlers.
		 *
		 * The "wp_ajax_" action is used for logged in users, and "wp_ajax_nopriv_"
		 * executes for users that aren't logged in. This is for backpat with BP <1.6.
		 *
		 * @todo not all actions should be nopriv!
		 */
		foreach( $actions as $name => $function ) {
			add_action( 'wp_ajax_'        . $name, $function );
			add_action( 'wp_ajax_nopriv_' . $name, $function );
		}

		add_filter( 'bp_ajax_querystring', 'bp_nouveau_ajax_querystring', 10, 2 );

		/** Override **********************************************************/

		/**
		 * Fires after all of the BuddyPress theme compat actions have been added.
		 *
		 * @since 1.7.0
		 *
		 * @param BP_Legacy $this Current BP_Legacy instance.
		 */
		do_action_ref_array( 'bp_theme_compat_actions', array( &$this ) );
	}

	protected function setup_filters() {
		$buttons = array();

		if ( bp_is_active( 'groups' ) ) {
			$buttons = array(
				'groups_leave_group',
				'groups_join_group',
				'groups_accept_invite',
				'groups_reject_invite',
				'groups_membership_requested',
				'groups_request_membership',
			);
		}

		if ( bp_is_active( 'friends' ) ) {
			$buttons = array_merge( $buttons, array(
				'friends_pending',
				'friends_awaiting_response',
				'friends_is_friend',
				'friends_not_friends',
			) );
		}

		if ( ! empty( $buttons ) ) {
			foreach ( $buttons as $button ) {
				add_filter( 'bp_button_' . $button, array( $this, 'ajax_button' ), 10, 4 );
			}
		}
	}

	/**
	 * Load the theme CSS
	 *
	 * @since 1.7.0
	 * @since 2.3.0 Support custom CSS file named after the current theme or parent theme.
	 *
	 * @uses wp_enqueue_style() To enqueue the styles
	 */
	public function enqueue_styles() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$css_dependencies = apply_filters( 'bp_nouveau_css_dependencies', array( 'dashicons' ) );

		// Locate the BP stylesheet.
		$ltr = $this->locate_asset_in_stack( "buddypress{$min}.css", 'css', 'bp-nouveau' );

		// LTR.
		if ( ! is_rtl() && isset( $ltr['location'], $ltr['handle'] ) ) {
			wp_enqueue_style( $ltr['handle'], $ltr['location'], $css_dependencies, $this->version, 'screen' );

			if ( $min ) {
				wp_style_add_data( $ltr['handle'], 'suffix', $min );
			}
		}

		// RTL.
		if ( is_rtl() ) {
			$rtl = $this->locate_asset_in_stack( "buddypress-rtl{$min}.css", 'css', 'bp-nouveau-rtl' );

			if ( isset( $rtl['location'], $rtl['handle'] ) ) {
				$rtl['handle'] = str_replace( '-css', '-css-rtl', $rtl['handle'] );  // Backwards compatibility.
				wp_enqueue_style( $rtl['handle'], $rtl['location'], $css_dependencies, $this->version, 'screen' );

				if ( $min ) {
					wp_style_add_data( $rtl['handle'], 'suffix', $min );
				}
			}
		}

		if ( bp_is_user_messages() ) {
			wp_enqueue_style( 'bp-nouveau-at-message', buddypress()->plugin_url . "bp-activity/css/mentions{$min}.css", array(), bp_get_version() );
		}
	}

	/**
	 * Register Template Pack JavaScript files
	 *
	 * @since 1.0.0
	 */
	public function register_scripts() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$main = $this->locate_asset_in_stack( "buddypress{$min}.js", 'js', 'bp-nouveau' );

		if ( isset( $main['location'], $main['handle'] ) ) {
			wp_register_script( $main['handle'], $main['location'], bp_core_get_js_dependencies(), $this->version, true );
		}

		if ( bp_is_active( 'activity' ) && isset( $main['handle'] ) ) {
			$activity = $this->locate_asset_in_stack( "buddypress-activity{$min}.js", 'js', 'bp-nouveau-activity' );

			if ( isset( $activity['location'], $activity['handle'] ) ) {
				$this->activity_handle = $activity['handle'];

				wp_register_script( $activity['handle'], $activity['location'], array( $main['handle'] ), $this->version, true );
				wp_register_script( 'bp-nouveau-activity-post-form', trailingslashit( bp_get_theme_compat_url() ) . "js/buddypress-activity-post-form{$min}.js", array( $main['handle'], 'json2', 'wp-backbone' ), $this->version, true );
			}
		}

		if ( bp_is_active( 'groups' ) && isset( $main['handle'] ) ) {
			$groups = $this->locate_asset_in_stack( "buddypress-group-invites{$min}.js", 'js', 'bp-nouveau-group-invites' );

			if ( isset( $groups['location'], $groups['handle'] ) ) {
				$this->groups_handle = $groups['handle'];

				wp_register_script( $groups['handle'], $groups['location'], array( $main['handle'], 'json2', 'wp-backbone' ), $this->version, true );
			}
		}

		if ( bp_is_active( 'messages' ) && isset( $main['handle'] ) ) {
			$messages = $this->locate_asset_in_stack( "buddypress-messages{$min}.js", 'js', 'bp-nouveau-messages' );

			if ( isset( $messages['location'], $messages['handle'] ) ) {
				$this->message_handle = $messages['handle'];

				// Use the activity mentions script
				wp_register_script( $messages['handle'] . '-at', buddypress()->plugin_url . "bp-activity/js/mentions{$min}.js", array( 'jquery', 'jquery-atwho' ), bp_get_version(), true );
				wp_register_script( $messages['handle'], $messages['location'], array( $main['handle'], 'json2', 'wp-backbone', $messages['handle'] . '-at' ), $this->version, true );
			}

			// Remove deprecated scripts
			remove_action( 'bp_enqueue_scripts', 'messages_add_autocomplete_js' );
		}

		if ( ( bp_is_active( 'settings' ) || bp_get_signup_allowed() ) && isset( $main['handle'] ) ) {
			$password = $this->locate_asset_in_stack( "password-verify{$min}.js", 'js', 'bp-nouveau-password-verify' );

			if ( isset( $password['location'], $password['handle'] ) ) {
				$this->password_handle = $password['handle'];

				wp_register_script( $password['handle'], $password['location'], array( $main['handle'], 'password-strength-meter' ), $this->version, true );
			}
		}
	}

	/**
	 * Enqueue the required JavaScript files
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		// Always enqueue the common javascript file
		wp_enqueue_script( 'bp-nouveau' );

		if ( bp_is_activity_component() || bp_is_group_activity() ) {
			wp_enqueue_script( 'bp-nouveau-activity' );
		}

		if ( bp_is_register_page() || ( function_exists( 'bp_is_user_settings_general' ) && bp_is_user_settings_general() ) ) {
			wp_enqueue_script( 'bp-nouveau-password-verify' );
		}

		if ( bp_is_group_invites() || ( bp_is_group_create() && bp_is_group_creation_step( 'group-invites' ) ) ) {
			wp_enqueue_script( 'bp-nouveau-group-invites' );
		}

		if ( bp_is_user_messages() ) {
			wp_enqueue_script( 'bp-nouveau-messages' );

			add_filter( 'tiny_mce_before_init', 'bp_nouveau_messages_at_on_tinymce_init', 10, 2 );
		}

		// Maybe enqueue comment reply JS.
		if ( is_singular() && bp_is_blog_page() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	/**
	 * Get the URL and handle of a web-accessible CSS or JS asset
	 *
	 * We provide two levels of customizability with respect to where CSS
	 * and JS files can be stored: (1) the child theme/parent theme/theme
	 * compat hierarchy, and (2) the "template stack" of /buddypress/css/,
	 * /community/css/, and /css/. In this way, CSS and JS assets can be
	 * overloaded, and default versions provided, in exactly the same way
	 * as corresponding PHP templates.
	 *
	 * We are duplicating some of the logic that is currently found in
	 * bp_locate_template() and the _template_stack() functions. Those
	 * functions were built with PHP templates in mind, and will require
	 * refactoring in order to provide "stack" functionality for assets
	 * that must be accessible both using file_exists() (the file path)
	 * and at a public URI.
	 *
	 * This method is marked private, with the understanding that the
	 * implementation is subject to change or removal in an upcoming
	 * release, in favor of a unified _template_stack() system. Plugin
	 * and theme authors should not attempt to use what follows.
	 *
	 * @since 1.8.0
	 * @param string $file A filename like buddypress.css.
	 * @param string $type Optional. Either "js" or "css" (the default).
	 * @param string $script_handle Optional. If set, used as the script name in `wp_enqueue_script`.
	 * @return array An array of data for the wp_enqueue_* function:
	 *   'handle' (eg 'bp-child-css') and a 'location' (the URI of the
	 *   asset)
	 */
	private function locate_asset_in_stack( $file, $type = 'css', $script_handle = '' ) {
		$locations = array();

		// Ensure the assets can be located when running from /src/.
		if ( defined( 'BP_SOURCE_SUBDIRECTORY' ) && BP_SOURCE_SUBDIRECTORY === 'src' ) {
			$file = str_replace( '.min', '', $file );
		}

		// No need to check child if template == stylesheet.
		if ( is_child_theme() ) {
			$locations['bp-child'] = array(
				'dir'  => get_stylesheet_directory(),
				'uri'  => get_stylesheet_directory_uri(),
				'file' => str_replace( '.min', '', $file ),
			);
		}

		$locations['bp-parent'] = array(
			'dir'  => get_template_directory(),
			'uri'  => get_template_directory_uri(),
			'file' => str_replace( '.min', '', $file ),
		);

		$locations['bp-legacy'] = array(
			'dir'  => bp_get_theme_compat_dir(),
			'uri'  => bp_get_theme_compat_url(),
			'file' => $file,
		);

		// Subdirectories within the top-level $locations directories.
		$subdirs = array(
			'buddypress/' . $type,
			'community/' . $type,
			$type,
		);

		$retval = array();

		foreach ( $locations as $location_type => $location ) {
			foreach ( $subdirs as $subdir ) {
				if ( file_exists( trailingslashit( $location['dir'] ) . trailingslashit( $subdir ) . $location['file'] ) ) {
					$retval['location'] = trailingslashit( $location['uri'] ) . trailingslashit( $subdir ) . $location['file'];
					$retval['handle']   = ( $script_handle ) ? $script_handle : "{$location_type}-{$type}";

					break 2;
				}
			}
		}

		return $retval;
	}

	/**
	 * Put some scripts in the header, like AJAX url for wp-lists.
	 *
	 * @since 1.7.0
	 */
	public function head_scripts() {
	?>

		<script type="text/javascript">
			/* <![CDATA[ */
			var ajaxurl = '<?php echo bp_core_ajax_url(); ?>';
			/* ]]> */
		</script>

	<?php
	}

	/**
	 * Adds the no-js class to the body tag.
	 *
	 * This function ensures that the <body> element will have the 'no-js' class by default. If you're
	 * using JavaScript for some visual functionality in your theme, and you want to provide noscript
	 * support, apply those styles to body.no-js.
	 *
	 * The no-js class is removed by the JavaScript created in buddypress.js.
	 *
	 * @since 1.7.0
	 *
	 * @param array $classes Array of classes to append to body tag.
	 * @return array $classes
	 */
	public function add_nojs_body_class( $classes ) {
		if ( ! in_array( 'no-js', $classes ) )
			$classes[] = 'no-js';

		return array_unique( $classes );
	}

	/**
	 * Load localizations for topic script.
	 *
	 * These localizations require information that may not be loaded even by init.
	 *
	 * @since 1.7.0
	 */
	public function localize_scripts() {
		// First global params
		$params = array(
			'accepted'            => __( 'Accepted', 'bp-nouveau' ),
			'close'               => __( 'Close', 'bp-nouveau' ),
			'comments'            => __( 'comments', 'bp-nouveau' ),
			'leave_group_confirm' => __( 'Are you sure you want to leave this group?', 'bp-nouveau' ),
			'my_favs'             => __( 'My Favorites', 'bp-nouveau' ),
			'rejected'            => __( 'Rejected', 'bp-nouveau' ),
			'show_all'            => __( 'Show all', 'bp-nouveau' ),
			'show_all_comments'   => __( 'Show all comments for this thread', 'bp-nouveau' ),
			'show_x_comments'     => __( 'Show all %d comments', 'bp-nouveau' ),
			'unsaved_changes'     => __( 'Your profile has unsaved changes. If you leave the page, the changes will be lost.', 'bp-nouveau' ),
			'view'                => __( 'View', 'bp-nouveau' ),
			'object_nav_parent'   => '#buddypress',
			'time_since'        => array(
				'sometime'  => _x( 'sometime', 'javascript time since', 'bp-nouveau' ),
				'now'       => _x( 'right now', 'javascript time since', 'bp-nouveau' ),
				'ago'       => _x( '% ago', 'javascript time since', 'bp-nouveau' ),
				'separator' => _x( ',', 'Separator in javascript time since', 'bp-nouveau' ),
				'year'      => _x( '% year', 'javascript time since singular', 'bp-nouveau' ),
				'years'     => _x( '% years', 'javascript time since plural', 'bp-nouveau' ),
				'month'     => _x( '% month', 'javascript time since singular', 'bp-nouveau' ),
				'months'    => _x( '% months', 'javascript time since plural', 'bp-nouveau' ),
				'week'      => _x( '% week', 'javascript time since singular', 'bp-nouveau' ),
				'weeks'     => _x( '% weeks', 'javascript time since plural', 'bp-nouveau' ),
				'day'       => _x( '% day', 'javascript time since singular', 'bp-nouveau' ),
				'days'      => _x( '% days', 'javascript time since plural', 'bp-nouveau' ),
				'hour'      => _x( '% hour', 'javascript time since singular', 'bp-nouveau' ),
				'hours'     => _x( '% hours', 'javascript time since plural', 'bp-nouveau' ),
				'minute'    => _x( '% minute', 'javascript time since singular', 'bp-nouveau' ),
				'minutes'   => _x( '% minutes', 'javascript time since plural', 'bp-nouveau' ),
				'second'    => _x( '% second', 'javascript time since singular', 'bp-nouveau' ),
				'seconds'   => _x( '% seconds', 'javascript time since plural', 'bp-nouveau' ),
				'time_chunks' => array(
					'a_year'   => YEAR_IN_SECONDS,
					'b_month'  => 30 * DAY_IN_SECONDS,
					'c_week'   => WEEK_IN_SECONDS,
					'd_day'    => DAY_IN_SECONDS,
					'e_hour'   => HOUR_IN_SECONDS,
					'f_minute' => MINUTE_IN_SECONDS,
					'g_second' => 1,
				),
			),
			'search_icon' => '&#xf179;'
		);

		// If the Object/Item nav are in the sidebar
		if ( bp_nouveau_is_object_nav_in_sidebar() ) {
			$params['object_nav_parent'] = '.buddypress_object_nav';
		}

		// Set the supported components
		$supported_objects = (array) apply_filters( 'bp_nouveau_supported_components', bp_core_get_packaged_component_ids() );
		$object_nonces     = array();

		foreach ( $supported_objects as $key_object => $object ) {
			if ( ! bp_is_active( $object ) || 'forums' === $object ) {
				unset( $supported_objects[ $key_object ] );
				continue;
			}

			if ( 'groups' === $object ) {
				$supported_objects[] = 'group_members';
			}

			$object_nonces[ $object ] = wp_create_nonce( 'bp_nouveau_' . $object );
		}

		// Add components & nonces
		$params['objects'] = $supported_objects;
		$params['nonces']  = $object_nonces;

		if ( bp_is_user_messages() ) {
			$params['messages'] = array(
				'errors' => array(
					'send_to'         => __( 'Please add at least a user to send the message to, using their @username.', 'bp-nouveau' ),
					'subject'         => __( 'Please add a subject to your message.', 'bp-nouveau' ),
					'message_content' => __( 'Please add some content to your message.', 'bp-nouveau' ),
				),
				'nonces' => array(
					'send' => wp_create_nonce( 'messages_send_message' ),
				),
				'loading' => __( 'Loading messages, please wait.', 'bp-nouveau' ),
				'bulk_actions' => bp_nouveau_messages_get_bulk_actions(),
			);

			// Star private messages.
			if ( bp_is_active( 'messages', 'star' ) ) {
				$params['messages'] = array_merge( $params['messages'], array(
					'strings' => array(
						'text_unstar'  => __( 'Unstar', 'bp-nouveau' ),
						'text_star'    => __( 'Star', 'bp-nouveau' ),
						'title_unstar' => __( 'Starred', 'bp-nouveau' ),
						'title_star'   => __( 'Not starred', 'bp-nouveau' ),
						'title_unstar_thread' => __( 'Remove all starred messages in this thread', 'bp-nouveau' ),
						'title_star_thread'   => __( 'Star the first message in this thread', 'bp-nouveau' ),
					),
					'is_single_thread' => (int) bp_is_messages_conversation(),
					'star_counter'     => 0,
					'unstar_counter'   => 0
				) );
			}
		}

		if ( bp_is_group_invites() || ( bp_is_group_create() && bp_is_group_creation_step( 'group-invites' ) ) ) {
			$show_pending = bp_group_has_invites( array( 'user_id' => 'any' ) ) && ! bp_is_group_create();

			// Init the Group invites nav
			$invites_nav = array(
				'members' => array( 'id' => 'members', 'caption' => __( 'All Members', 'bp-nouveau' ), 'order' => 0 ),
				'invited' => array( 'id' => 'invited', 'caption' => __( 'Pending Invites', 'bp-nouveau' ), 'order' => 90, 'hide' => (int) ! $show_pending ),
				'invites' => array( 'id' => 'invites', 'caption' => __( 'Send invites', 'bp-nouveau' ), 'order' => 100, 'hide' => 1 ),
			);

			if ( bp_is_active( 'friends' ) ) {
				$invites_nav['friends'] = array( 'id' => 'friends', 'caption' => __( 'My friends', 'bp-nouveau' ), 'order' => 5 );
			}

			$params['group_invites'] = array(
				'nav'                => bp_sort_by_key( $invites_nav, 'order', 'num' ),
				'loading'            => __( 'Loading members, please wait.', 'bp-nouveau' ),
				'invites_form'       => __( 'Use the "Send" button to send your invite, or the "Cancel" button to abort.', 'bp-nouveau' ),
				'invites_form_reset' => __( 'Invites cleared, please use one of the available tabs to select members to invite.', 'bp-nouveau' ),
				'invites_sending'    => __( 'Sending the invites, please wait.', 'bp-nouveau' ),
				'group_id'           => ! bp_get_current_group_id() ? bp_get_new_group_id() : bp_get_current_group_id(),
				'is_group_create'    => bp_is_group_create(),
				'nonces'             => array(
					'uninvite'     => wp_create_nonce( 'groups_invite_uninvite_user' ),
					'send_invites' => wp_create_nonce( 'groups_send_invites' )
				),
			);
		}

		if ( bp_is_current_component( 'activity' ) || bp_is_group_activity() ) {
			$activity_params = array(
				'user_id'     => bp_loggedin_user_id(),
				'object'      => 'user',
				'backcompat'  => (bool) has_action( 'bp_activity_post_form_options' ),
				'post_nonce'  => wp_create_nonce( 'post_update', '_wpnonce_post_update' ),
			);

			$user_displayname = bp_get_loggedin_user_fullname();

			if ( buddypress()->avatar->show_avatars ) {
				$width  = bp_core_avatar_thumb_width();
				$height = bp_core_avatar_thumb_height();
				$activity_params = array_merge( $activity_params, array(
					'avatar_url'    => bp_get_loggedin_user_avatar( array(
						'width'  => $width,
						'height' => $height,
						'html'   => false,
					) ),
					'avatar_width'  => $width,
					'avatar_height' => $height,
					'avatar_alt'    => sprintf( __( 'Profile photo of %s', 'bp-nouveau' ), $user_displayname ),
					'user_domain'   => bp_loggedin_user_domain()
				) );
			}

			/**
			 * Filter here to include specific Action buttons.
			 *
			 * @param array $value The array containing the button params. Must look like:
			 * array( 'buttonid' => array(
			 *  'id'      => 'buttonid',                            // Id for your action
			 *  'caption' => __( 'Button caption', 'text-domain' ),
			 *  'icon'    => 'dashicons-*',                         // The dashicon to use
			 *  'order'   => 0,
			 *  'handle'  => 'button-script-handle',                // The handle of the registered script to enqueue
			 * );
			 */
			$activity_buttons = apply_filters( 'bp_nouveau_activity_buttons', array() );

			if ( ! empty( $activity_buttons ) ) {
				// Sort buttons
				$activity_params['buttons'] = bp_sort_by_key( $activity_buttons, 'order', 'num' );

				// Enqueue Buttons scripts and styles
				foreach ( $activity_params['buttons'] as $key_button => $buttons ) {
					if ( empty( $buttons['handle'] ) ) {
						continue;
					}

					// Enqueue the button style if registered
					if ( wp_style_is( $buttons['handle'], 'registered' ) ) {
						wp_enqueue_style( $buttons['handle'] );
					}

					// Enqueue the button script if registered
					if ( wp_script_is( $buttons['handle'], 'registered' ) ) {
						wp_enqueue_script( $buttons['handle'] );
					}

					// Finally remove the handle parameter
					unset( $activity_params['buttons'][ $key_button ]['handle'] );
				}
			}

			// Activity Objects
			if ( ! bp_is_single_item() && ! bp_is_user() ) {
				$activity_objects = array(
					'profile' => array(
						'text'                     => __( 'Post in: Profile', 'bp-nouveau' ),
						'autocomplete_placeholder' => '',
						'priority'                 => 5,
					),
				);

				// the groups component is active & the current user is at least a member of 1 group
				if ( bp_is_active( 'groups' ) && bp_has_groups( array( 'user_id' => bp_loggedin_user_id(), 'max' => 1 ) ) ) {
					$activity_objects['group'] = array(
						'text'                     => __( 'Post in: Group', 'bp-nouveau' ),
						'autocomplete_placeholder' => __( 'Start typing the group name...', 'bp-nouveau' ),
						'priority'                 => 10,
					);
				}

				$activity_params['objects'] = apply_filters( 'bp_nouveau_activity_objects', $activity_objects );
			}

			$activity_strings = array(
				'whatsnewPlaceholder' => sprintf( __( "What's new, %s?", 'bp-nouveau' ), bp_get_user_firstname( $user_displayname ) ),
				'whatsnewLabel'       => __( 'Post what\'s new', 'bp-nouveau' ),
				'whatsnewpostinLabel' => __( 'Post in', 'bp-nouveau' ),
			);

			if ( bp_is_group() ) {
				$activity_params = array_merge( $activity_params,
					array( 'object' => 'group', 'item_id' => bp_get_current_group_id() )
				);
			}

			$params['activity'] = array(
				'params'  => $activity_params,
				'strings' => $activity_strings,
			);
		}

		/**
		 * Filters core JavaScript strings for internationalization before AJAX usage.
		 *
		 * @since 1.0.0
		 *
		 * @param array $value Array of key/value pairs for AJAX usage.
		 */
		wp_localize_script( 'bp-nouveau', 'BP_Nouveau', apply_filters( 'bp_core_get_js_strings', $params ) );
	}

	/**
	 * Outputs sitewide notices markup in the footer.
	 *
	 * @since 1.7.0
	 *
	 * @see https://buddypress.trac.wordpress.org/ticket/4802
	 */
	public function sitewide_notices() {
		// Do not show notices if user is not logged in.
		if ( ! is_user_logged_in() || ! bp_is_user() ) {
			return;
		}

		$notice = BP_Messages_Notice::get_active();

		if ( empty( $notice ) ) {
			return false;
		}

		$user_id = bp_loggedin_user_id();

		$closed_notices = bp_get_user_meta( $user_id, 'closed_notices', true );

		if ( empty( $closed_notices ) ) {
			$closed_notices = array();
		}

		if ( is_array( $closed_notices ) ) {
			if ( ! in_array( $notice->id, $closed_notices ) && $notice->id ) {
				?>
				<div class="clear"></div>
				<div class="bp-feedback info" rel="n-<?php echo esc_attr( $notice->id ); ?>">
					<strong><?php echo stripslashes( wp_filter_kses( $notice->subject ) ) ?></strong><br />
					<?php echo stripslashes( wp_filter_kses( $notice->message) ) ?>
				</div>
				<?php

				// Add the notice to closed ones
				$closed_notices[] = (int) $notice->id;
				bp_update_user_meta( $user_id, 'closed_notices', $closed_notices );
			}
		}
	}

	/**
	 * Add secondary avatar image to this activity stream's record, if supported.
	 *
	 * @since 1.7.0
	 *
	 * @param string               $action   The text of this activity.
	 * @param BP_Activity_Activity $activity Activity object.
	 * @return string
	 */
	function secondary_avatars( $action, $activity ) {
		switch ( $activity->component ) {
			case 'groups' :
			case 'friends' :
				// Only insert avatar if one exists.
				if ( $secondary_avatar = bp_get_activity_secondary_avatar() ) {
					$reverse_content = strrev( $action );
					$position        = strpos( $reverse_content, 'a<' );
					$action          = substr_replace( $action, $secondary_avatar, -$position - 2, 0 );
				}
				break;
		}

		return $action;
	}

	/**
	 * Filter the default theme compatibility root template hierarchy, and prepend
	 * a page template to the front if it's set.
	 *
	 * @see https://buddypress.trac.wordpress.org/ticket/6065
	 *
	 * @since 2.2.0
	 *
	 * @param  array $templates Array of templates.
	 * @uses   apply_filters() call 'bp_legacy_theme_compat_page_templates_directory_only' and return false
	 *                         to use the defined page template for component's directory and its single items
	 * @return array
	 */
	public function theme_compat_page_templates( $templates = array() ) {

		/**
		 * Filters whether or not we are looking at a directory to determine if to return early.
		 *
		 * @since 2.2.0
		 *
		 * @param bool $value Whether or not we are viewing a directory.
		 */
		if ( true === (bool) apply_filters( 'bp_legacy_theme_compat_page_templates_directory_only', ! bp_is_directory() ) ) {
			return $templates;
		}

		// No page ID yet.
		$page_id = 0;

		// Get the WordPress Page ID for the current view.
		foreach ( (array) buddypress()->pages as $component => $bp_page ) {

			// Handles the majority of components.
			if ( bp_is_current_component( $component ) ) {
				$page_id = (int) $bp_page->id;
			}

			// Stop if not on a user page.
			if ( ! bp_is_user() && ! empty( $page_id ) ) {
				break;
			}

			// The Members component requires an explicit check due to overlapping components.
			if ( bp_is_user() && ( 'members' === $component ) ) {
				$page_id = (int) $bp_page->id;
				break;
			}
		}

		// Bail if no directory page set.
		if ( 0 === $page_id ) {
			return $templates;
		}

		// Check for page template.
		$page_template = get_page_template_slug( $page_id );

		// Add it to the beginning of the templates array so it takes precedence
		// over the default hierarchy.
		if ( ! empty( $page_template ) ) {

			/**
			 * Check for existence of template before adding it to template
			 * stack to avoid accidentally including an unintended file.
			 *
			 * @see: https://buddypress.trac.wordpress.org/ticket/6190
			 */
			if ( '' !== locate_template( $page_template ) ) {
				array_unshift( $templates, $page_template );
			}
		}

		return $templates;
	}

	public function ajax_button( $output ='', $button = null, $before ='', $after = '' ) {
		if ( empty( $button->component ) ) {
			return $output;
		}

		// Add span bp-screen-reader-text class
		return $before . '<a'. $button->link_href . $button->link_title . $button->link_id . $button->link_rel . $button->link_class . ' data-bp-btn-action="' . $button->id . '">' . $button->link_text . '</a>' . $after;
	}



}
new BP_Nouveau();
endif;


/**
 * Directory pagination
 */
	function bp_pagination( $position = null ) {

	$component = bp_current_component();
	$screen = ( bp_is_user() )? 'user' : 'dir';
	switch( $component ) {

		case 'blogs' :

			$bp_pag_count = bp_get_blogs_pagination_count();
			$bp_pag_links = bp_get_blogs_pagination_links();

		break;

		case 'members' || 'friends' :

			$bp_pag_count =	bp_get_members_pagination_count();
			$bp_pag_links = bp_get_members_pagination_links();

		break;

		case 'groups' :

			$bp_pag_count =	bp_get_groups_pagination_count();
			$bp_pag_links = bp_get_groups_pagination_links();

		break;
	}
?>

	<div class="pagination <?php echo $position; ?>" data-bp-nav="pagination">

		<?php if( $bp_pag_count ) : ?>
		<div class="pag-count <?php echo $component; ?>-<?php echo $screen; ?>-count-<?php echo $position; ?>">

			<p class="pag-data">
				<?php echo $bp_pag_count; ?>
			</p>

		</div>
		<?php endif; ?>

		<?php if( $bp_pag_links ) : ?>
		<div class="pagination-links <?php echo $component; ?>-<?php echo $screen; ?>-links-<?php echo $position; ?>">

			<p class="pag-data">
				<?php echo $bp_pag_links; ?>
			</p>

		</div>
		<?php endif; ?>

	</div>

	<?php

	return;

	}

/**
 * Add the Create a Group button to the Groups directory title.
 *
 * The bp-legacy puts the Create a Group button into the page title, to mimic
 * the behavior of bp-default.
 *
 * @since 2.0.0
 * @todo Deprecate
 *
 * @param string $title Groups directory title.
 * @return string
 */
function bp_legacy_theme_group_create_button( $title ) {
	return $title . ' ' . bp_get_group_create_button();
}

/**
 * Add the Create a Group nav to the Groups directory navigation.
 *
 * The bp-legacy puts the Create a Group nav at the last position of
 * the Groups directory navigation.
 *
 * @since 2.2.0
 *
 * @uses   bp_group_create_nav_item() to output the create a Group nav item.
 */
function bp_legacy_theme_group_create_nav() {
	bp_group_create_nav_item();
}

/**
 * Add the Create a Site button to the Sites directory title.
 *
 * The bp-legacy puts the Create a Site button into the page title, to mimic
 * the behavior of bp-default.
 *
 * @since 2.0.0
 * @todo Deprecate
 *
 * @param string $title Sites directory title.
 * @return string
 */
function bp_legacy_theme_blog_create_button( $title ) {
	return $title . ' ' . bp_get_blog_create_button();
}

/**
 * Add the Create a Site nav to the Sites directory navigation.
 *
 * The bp-legacy puts the Create a Site nav at the last position of
 * the Sites directory navigation.
 *
 * @since 2.2.0
 *
 * @uses   bp_blog_create_nav_item() to output the Create a Site nav item
 */
function bp_legacy_theme_blog_create_nav() {
	bp_blog_create_nav_item();
}

/**
 * BP Legacy's callback for the cover image feature.
 *
 * @since  2.4.0
 *
 * @param  array $params the current component's feature parameters.
 * @return array          an array to inform about the css handle to attach the css rules to
 */
function bp_legacy_theme_cover_image( $params = array() ) {
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

function bp_nouveau_before_activity_post_form() {
	// Enqueue needed script.
	if ( bp_nouveau_current_user_can( 'publish_activity' ) ) {
		wp_enqueue_script( 'bp-nouveau-activity-post-form' );
	}

	do_action( 'bp_before_activity_post_form' );
}

function bp_nouveau_after_activity_post_form() {
	bp_get_template_part( 'assets/activity/form' );

	do_action( 'bp_after_activity_post_form' );
}
