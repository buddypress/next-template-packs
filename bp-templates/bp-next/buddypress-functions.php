<?php
/**
 * Functions of BuddyPress's Next template pack.
 *
 * @since 1.0.0
 *
 * @package BP Next
 *
 * @buddypress-template-pack {
 * Template Pack ID:   next
 * Template Pack Name: BP Next
 * Version:            1.0.0
 * Description:        The next template pack for BuddyPress!
 * Text Domain:        bp-next
 * Domain Path:        /languages/
 * Author:             imath
 * Template Pack Link: http://imathi.eu
 * }}
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/** Theme Setup ***************************************************************/

if ( ! class_exists( 'BP_Next' ) ) :

/**
 * Loads BuddyPress Next Template pack functionality.
 *
 * See @link BP_Theme_Compat() for more.
 *
 * @since 1.0.0
 *
 * @package BP Next
 */
class BP_Next extends BP_Theme_Compat {

	/** Functions *************************************************************/

	/**
	 * The main BP Next Loader.
	 *
	 * @since 1.0.0
	 *
	 * @uses BP_Next::setup_globals()
	 * @uses BP_Next::setup_actions()
	 */
	public function __construct() {
		parent::start();

		// Add custom filters
		$this->setup_filters();
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

		if ( ! isset( $bp->theme_compat->packages['next'] ) ) {
			wp_die( __( 'Something is going wrong, please deactivate any plugin having an impact on the BP Theme Compat API', 'bp-next' ) );
		}

		foreach ( $bp->theme_compat->packages['next'] as $property => $value ) {
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
		if ( bp_is_active( 'messages' ) && ! is_active_widget( false, false, 'bp_messages_sitewide_notices_widget', true ) ) {
			add_action( 'wp_footer', array( $this, 'sitewide_notices' ), 9999 );
		}

		/** Ajax **************************************************************/

		$actions = array(

			// Directory filters.
			'activity_filter' => 'bp_legacy_theme_object_template_loader',
			'blogs_filter'    => 'bp_legacy_theme_object_template_loader',
			'forums_filter'   => 'bp_legacy_theme_object_template_loader',
			'groups_filter'   => 'bp_legacy_theme_object_template_loader',
			'members_filter'  => 'bp_legacy_theme_object_template_loader',
			'messages_filter' => 'bp_legacy_theme_messages_template_loader',
			'invite_filter'   => 'bp_legacy_theme_invite_template_loader',
			'requests_filter' => 'bp_legacy_theme_requests_template_loader',

			// Friends.
			'accept_friendship'     => 'bp_legacy_theme_ajax_accept_friendship',
			'reject_friendship' => 'bp_legacy_theme_ajax_reject_friendship',

			'friends_remove_friend'       => 'bp_next_ajax_addremove_friend',
			'friends_add_friend'          => 'bp_next_ajax_addremove_friend',
			'friends_withdraw_friendship' => 'bp_next_ajax_addremove_friend',


			// Activity.
			'activity_get_older_updates'  => 'bp_legacy_theme_activity_template_loader',
			'activity_mark_fav'           => 'bp_next_mark_activity_favorite',
			'activity_mark_unfav'         => 'bp_next_unmark_activity_favorite',
			'activity_clear_new_mentions' => 'bp_next_clear_new_mentions',
			'activity_widget_filter'      => 'bp_legacy_theme_activity_template_loader',
			'delete_activity'             => 'bp_next_delete_activity',
			'delete_activity_comment'     => 'bp_legacy_theme_delete_activity_comment',
			'get_single_activity_content' => 'bp_next_get_single_activity_content',
			'new_activity_comment'        => 'bp_next_new_activity_comment',
			'post_update'                 => 'bp_legacy_theme_post_update',
			'bp_spam_activity'            => 'bp_legacy_theme_spam_activity',
			'bp_spam_activity_comment'    => 'bp_legacy_theme_spam_activity',

			// Groups.
			'groups_invite_user'  => 'bp_legacy_theme_ajax_invite_user',
			'groups_join_group'   => 'bp_next_ajax_joinleave_group',
			'groups_leave_group'  => 'bp_next_ajax_joinleave_group',
			'request_membership'  => 'bp_next_ajax_joinleave_group',

			// Messages.
			'messages_autocomplete_results' => 'bp_legacy_theme_ajax_messages_autocomplete_results',
			'messages_close_notice'         => 'bp_legacy_theme_ajax_close_notice',
			'messages_delete'               => 'bp_legacy_theme_ajax_messages_delete',
			'messages_markread'             => 'bp_legacy_theme_ajax_message_markread',
			'messages_markunread'           => 'bp_legacy_theme_ajax_message_markunread',
			'messages_send_reply'           => 'bp_legacy_theme_ajax_messages_send_reply',
		);

		// Conditional actions.
		if ( bp_is_active( 'messages', 'star' ) ) {
			$actions['messages_star'] = 'bp_legacy_theme_ajax_messages_star_handler';
		}

		/**
		 * Register all of these AJAX handlers.
		 *
		 * The "wp_ajax_" action is used for logged in users, and "wp_ajax_nopriv_"
		 * executes for users that aren't logged in. This is for backpat with BP <1.6.
		 */
		foreach( $actions as $name => $function ) {
			add_action( 'wp_ajax_'        . $name, $function );
			add_action( 'wp_ajax_nopriv_' . $name, $function );
		}

		add_filter( 'bp_ajax_querystring', 'bp_next_ajax_querystring', 10, 2 );

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
		$css_dependencies = apply_filters( 'bp_next_css_dependencies', array( 'dashicons' ) );

		// Locate the BP stylesheet.
		$ltr = $this->locate_asset_in_stack( "buddypress{$min}.css", 'css', 'bp-next' );

		// LTR.
		if ( ! is_rtl() && isset( $ltr['location'], $ltr['handle'] ) ) {
			wp_enqueue_style( $ltr['handle'], $ltr['location'], $css_dependencies, $this->version, 'screen' );

			if ( $min ) {
				wp_style_add_data( $ltr['handle'], 'suffix', $min );
			}
		}

		// RTL.
		if ( is_rtl() ) {
			$rtl = $this->locate_asset_in_stack( "buddypress-rtl{$min}.css", 'css', 'bp-next-rtl' );

			if ( isset( $rtl['location'], $rtl['handle'] ) ) {
				$rtl['handle'] = str_replace( '-css', '-css-rtl', $rtl['handle'] );  // Backwards compatibility.
				wp_enqueue_style( $rtl['handle'], $rtl['location'], $css_dependencies, $this->version, 'screen' );

				if ( $min ) {
					wp_style_add_data( $rtl['handle'], 'suffix', $min );
				}
			}
		}
	}

	/**
	 * Register Template Pack JavaScript files
	 *
	 * @since 1.0.0
	 */
	public function register_scripts() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$main = $this->locate_asset_in_stack( "buddypress{$min}.js", 'js', 'bp-next' );

		if ( isset( $main['location'], $main['handle'] ) ) {
			wp_register_script( $main['handle'], $main['location'], bp_core_get_js_dependencies(), $this->version, true );
		}

		if ( bp_is_active( 'activity' ) && isset( $main['handle'] ) ) {
			$activity = $this->locate_asset_in_stack( "buddypress-activity{$min}.js", 'js', 'bp-next-activity' );

			if ( isset( $activity['location'], $activity['handle'] ) ) {
				$this->activity_handle = $activity['handle'];

				wp_register_script( $activity['handle'], $activity['location'], array( $main['handle'] ), $this->version, true );
			}
		}

		if ( bp_is_active( 'groups' ) && isset( $main['handle'] ) ) {
			$groups = $this->locate_asset_in_stack( "buddypress-group-invites{$min}.js", 'js', 'bp-next-group-invites' );

			if ( isset( $groups['location'], $groups['handle'] ) ) {
				$this->groups_handle = $groups['handle'];

				wp_register_script( $groups['handle'], $groups['location'], array( $main['handle'], 'json2', 'wp-backbone' ), $this->version, true );
			}
		}

		if ( ( bp_is_active( 'settings' ) || bp_get_signup_allowed() ) && isset( $main['handle'] ) ) {
			$password = $this->locate_asset_in_stack( "password-verify{$min}.js", 'js', 'bp-next-password-verify' );

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
		wp_enqueue_script( 'bp-next' );

		if ( bp_is_activity_component() || bp_is_group_activity() ) {
			wp_enqueue_script( 'bp-next-activity' );
		}

		if ( bp_is_register_page() || ( function_exists( 'bp_is_user_settings_general' ) && bp_is_user_settings_general() ) ) {
			wp_enqueue_script( 'bp-next-password-verify' );
		}

		if ( bp_is_group_invites() || ( bp_is_group_create() && bp_is_group_creation_step( 'group-invites' ) ) ) {
			wp_enqueue_script( 'bp-next-group-invites' );
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
			'accepted'            => __( 'Accepted', 'bp-next' ),
			'close'               => __( 'Close', 'bp-next' ),
			'comments'            => __( 'comments', 'bp-next' ),
			'leave_group_confirm' => __( 'Are you sure you want to leave this group?', 'bp-next' ),
			'my_favs'             => __( 'My Favorites', 'bp-next' ),
			'rejected'            => __( 'Rejected', 'bp-next' ),
			'show_all'            => __( 'Show all', 'bp-next' ),
			'show_all_comments'   => __( 'Show all comments for this thread', 'bp-next' ),
			'show_x_comments'     => __( 'Show all %d comments', 'bp-next' ),
			'unsaved_changes'     => __( 'Your profile has unsaved changes. If you leave the page, the changes will be lost.', 'bp-next' ),
			'view'                => __( 'View', 'bp-next' ),
			'object_nav_parent'   => '#buddypress',
			'time_since'        => array(
				'sometime'  => _x( 'sometime', 'javascript time since', 'bp-next' ),
				'now'       => _x( 'right now', 'javascript time since', 'bp-next' ),
				'ago'       => _x( '% ago', 'javascript time since', 'bp-next' ),
				'separator' => _x( ',', 'Separator in javascript time since', 'bp-next' ),
				'year'      => _x( '% year', 'javascript time since singular', 'bp-next' ),
				'years'     => _x( '% years', 'javascript time since plural', 'bp-next' ),
				'month'     => _x( '% month', 'javascript time since singular', 'bp-next' ),
				'months'    => _x( '% months', 'javascript time since plural', 'bp-next' ),
				'week'      => _x( '% week', 'javascript time since singular', 'bp-next' ),
				'weeks'     => _x( '% weeks', 'javascript time since plural', 'bp-next' ),
				'day'       => _x( '% day', 'javascript time since singular', 'bp-next' ),
				'days'      => _x( '% days', 'javascript time since plural', 'bp-next' ),
				'hour'      => _x( '% hour', 'javascript time since singular', 'bp-next' ),
				'hours'     => _x( '% hours', 'javascript time since plural', 'bp-next' ),
				'minute'    => _x( '% minute', 'javascript time since singular', 'bp-next' ),
				'minutes'   => _x( '% minutes', 'javascript time since plural', 'bp-next' ),
				'second'    => _x( '% second', 'javascript time since singular', 'bp-next' ),
				'seconds'   => _x( '% seconds', 'javascript time since plural', 'bp-next' ),
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
		if ( bp_next_is_object_nav_in_sidebar() ) {
			$params['object_nav_parent'] = '.buddypress_object_nav';
		}

		// Set the supported components
		$supported_objects = (array) apply_filters( 'bp_next_supported_components', bp_core_get_packaged_component_ids() );
		$object_nonces     = array();

		foreach ( $supported_objects as $key_object => $object ) {
			if ( ! bp_is_active( $object ) || 'forums' === $object ) {
				unset( $supported_objects[ $key_object ] );
				continue;
			}

			if ( 'groups' === $object ) {
				$supported_objects[] = 'group_members';
			}

			$object_nonces[ $object ] = wp_create_nonce( 'bp_next_' . $object );
		}

		// Add components & nonces
		$params['objects'] = $supported_objects;
		$params['nonces']  = $object_nonces;

		// Star private messages.
		if ( bp_is_active( 'messages', 'star' ) && bp_is_user_messages() ) {
			$params['messages'] = array(
				'strings' => array(
					'text_unstar'  => __( 'Unstar', 'bp-next' ),
					'text_star'    => __( 'Star', 'bp-next' ),
					'title_unstar' => __( 'Starred', 'bp-next' ),
					'title_star'   => __( 'Not starred', 'bp-next' ),
					'title_unstar_thread' => __( 'Remove all starred messages in this thread', 'bp-next' ),
					'title_star_thread'   => __( 'Star the first message in this thread', 'bp-next' ),
				),
				'is_single_thread' => (int) bp_is_messages_conversation(),
				'star_counter'     => 0,
				'unstar_counter'   => 0
			);
		}

		if ( bp_is_group_invites() || ( bp_is_group_create() && bp_is_group_creation_step( 'group-invites' ) ) ) {
			$show_pending = bp_group_has_invites( array( 'user_id' => 'any' ) ) && ! bp_is_group_create();

			// Init the Group invites nav
			$invites_nav = array(
				'members' => array( 'id' => 'members', 'caption' => __( 'All Members', 'bp-next' ), 'order' => 0 ),
				'invited' => array( 'id' => 'invited', 'caption' => __( 'Pending Invites', 'bp-next' ), 'order' => 90, 'hide' => (int) ! $show_pending ),
				'invites' => array( 'id' => 'invites', 'caption' => __( 'Send invites', 'bp-next' ), 'order' => 100, 'hide' => 1 ),
			);

			if ( bp_is_active( 'friends' ) ) {
				$invites_nav['friends'] = array( 'id' => 'friends', 'caption' => __( 'My friends', 'bp-next' ), 'order' => 5 );
			}

			$params['group_invites'] = array(
				'nav'                => bp_sort_by_key( $invites_nav, 'order', 'num' ),
				'loading'            => __( 'Loading members, please wait.', 'bp-next' ),
				'invites_form'       => __( 'Use the "Send" button to send your invite, or the "Cancel" button to abort.', 'bp-next' ),
				'invites_form_reset' => __( 'Invites cleared, please use one of the available tabs to select members to invite.', 'bp-next' ),
				'invites_sending'    => __( 'Sending the invites, please wait.', 'bp-next' ),
				'group_id'           => ! bp_get_current_group_id() ? bp_get_new_group_id() : bp_get_current_group_id(),
				'is_group_create'    => bp_is_group_create(),
				'nonces'             => array(
					'uninvite'     => wp_create_nonce( 'groups_invite_uninvite_user' ),
					'send_invites' => wp_create_nonce( 'groups_send_invites' )
				),
			);
		}

		/**
		 * Filters core JavaScript strings for internationalization before AJAX usage.
		 *
		 * @since 1.0.0
		 *
		 * @param array $value Array of key/value pairs for AJAX usage.
		 */
		wp_localize_script( 'bp-next', 'BP_Next', apply_filters( 'bp_core_get_js_strings', $params ) );
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
		if ( ! is_user_logged_in() )
			return;

		// Add a class to determine if the admin bar is on or not.
		$class = did_action( 'admin_bar_menu' ) ? 'admin-bar-on' : 'admin-bar-off';

		echo '<div id="sitewide-notice" class="' . $class . '">';
		bp_message_get_notices();
		echo '</div>';
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
new BP_Next();
endif;

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
 * This function looks scarier than it actually is. :)
 * Each object loop (activity/members/groups/blogs/forums) contains default
 * parameters to show specific information based on the page we are currently
 * looking at.
 *
 * The following function will take into account any cookies set in the JS and
 * allow us to override the parameters sent. That way we can change the results
 * returned without reloading the page.
 *
 * By using cookies we can also make sure that user settings are retained
 * across page loads.
 *
 * @param string $query_string Query string for the current request.
 * @param string $object       Object for cookie.
 * @return string Query string for the component loops
 * @since 1.2.0
 */
function bp_next_ajax_querystring( $query_string, $object ) {
	if ( empty( $object ) ) {
		return '';
	}

	// Default query
	$post_query = array(
		'filter'       => '',
		'scope'        => 'all',
		'page'         => 1,
		'search_terms' => '',
		'extras'       => '',
	);

	if ( ! empty( $_POST ) ) {
		$post_query = wp_parse_args( $_POST, $post_query );

		// Make sure to transport the scope, filter etc.. in HeartBeat Requests
		if ( ! empty( $post_query['data']['bp_heartbeat'] ) ) {
			$bp_heartbeat = $post_query['data']['bp_heartbeat'];

			// Remove heartbeat specific vars
			$post_query = array_diff_key(
				wp_parse_args( $bp_heartbeat, $post_query ),
				array(
					'data'      => false,
					'interval'  => false,
					'_nonce'    => false,
					'action'    => false,
					'screen_id' => false,
					'has_focus' => false,
				)
			);
		}
	}

	// Init the query string
	$qs = array();

	// Activity stream filtering on action.
	if ( ! empty( $post_query['filter'] ) && '-1' !== $post_query['filter'] ) {
		$qs[] = 'type='   . $post_query['filter'];
		$qs[] = 'action=' . $post_query['filter'];
	}

	if ( 'personal' === $post_query['scope'] ) {
		$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();
		$qs[] = 'user_id=' . $user_id;
	}

	// Activity stream scope only on activity directory.
	if ( 'all' !== $post_query['scope'] && ! bp_displayed_user_id() && ! bp_is_single_item() ) {
		$qs[] = 'scope=' . $post_query['scope'];
	}

	// If page have been passed via the AJAX post request, use those.
	if ( '-1' != $post_query['page'] ) {
		$qs[] = 'page=' . absint( $post_query['page'] );
	}

	// Excludes activity just posted and avoids duplicate ids.
	if ( ! empty( $post_query['exclude_just_posted'] ) ) {
		$just_posted = wp_parse_id_list( $post_query['exclude_just_posted'] );
		$qs[] = 'exclude=' . implode( ',', $just_posted );
	}

	// To get newest activities.
	if ( ! empty( $post_query['offset'] ) ) {
		$qs[] = 'offset=' . intval( $post_query['offset'] );
	}

	$object_search_text = bp_get_search_default_text( $object );
 	if ( ! empty( $post_query['search_terms'] ) && $object_search_text != $post_query['search_terms'] && 'false' != $post_query['search_terms'] && 'undefined' != $post_query['search_terms'] ) {
 		$qs[] = 'search_terms=' . urlencode( $_POST['search_terms'] );
 	}

 	// Now pass the querystring to override default values.
	$query_string = empty( $qs ) ? '' : join( '&', (array) $qs );

 	// List the variables for the filter
 	list( $filter, $scope, $page, $search_terms, $extras ) = array_values( $post_query );

	/**
	 * Filters the AJAX query string for the component loops.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query_string The query string we are working with.
	 * @param string $object       The type of page we are on.
	 * @param string $filter       The current object filter.
	 * @param string $scope        The current object scope.
	 * @param string $page         The current object page.
	 * @param string $search_terms The current object search terms.
	 * @param string $extras       The current object extras.
	 */
	return apply_filters( 'bp_next_ajax_querystring', $query_string, $object, $filter, $scope, $page, $search_terms, $extras );
}

/**
 * Load the template loop for the current object.
 *
 * @return string Prints template loop for the specified object
 * @since 1.2.0
 */
function bp_legacy_theme_object_template_loader() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error();
	}

	// Bail if no object passed.
	if ( empty( $_POST['object'] ) ) {
		wp_send_json_error();
	}

	// Sanitize the object.
	$object = sanitize_title( $_POST['object'] );

	// Bail if object is not an active component to prevent arbitrary file inclusion.
	if ( ! bp_is_active( $object ) ) {
		wp_send_json_error();
	}

	$result = array();

	if ( 'activity' === $object ) {
		$scope = '';
		if ( ! empty( $_POST['scope'] ) ) {
			$scope = $_POST['scope'];
		}

		// We need to calculate and return the feed URL for each scope.
		switch ( $scope ) {
			case 'friends':
				$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/friends/feed/';
				break;
			case 'groups':
				$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/groups/feed/';
				break;
			case 'favorites':
				$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/favorites/feed/';
				break;
			case 'mentions':
				$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/mentions/feed/';

				// Get user new mentions
				$new_mentions = bp_get_user_meta( bp_loggedin_user_id(), 'bp_new_mentions', true );

				// If we have some, include them into the returned json before deleting them
				if ( is_array( $new_mentions ) ) {
					$result['new_mentions'] = $new_mentions;

					// Clear new mentions
					bp_activity_clear_new_mentions( bp_loggedin_user_id() );
				}

				break;
			default:
				$feed_url = home_url( bp_get_activity_root_slug() . '/feed/' );
				break;
		}

		$result['feed_url'] = apply_filters( 'bp_legacy_theme_activity_feed_url', $feed_url, $scope );
	}

 	/**
	 * AJAX requests happen too early to be seen by bp_update_is_directory()
	 * so we do it manually here to ensure templates load with the correct
	 * context. Without this check, templates will load the 'single' version
	 * of themselves rather than the directory version.
	 */
	if ( ! bp_current_action() ) {
		bp_update_is_directory( true, bp_current_component() );
	}

	$template_part = $object . '/' . $object . '-loop';

	// The template part can be overridden by the calling JS function.
	if ( ! empty( $_POST['template'] ) ) {
		$template_part = sanitize_option( 'upload_path', $_POST['template'] );
	}

	ob_start();
	bp_get_template_part( $template_part );
	$result['contents'] = ob_get_contents();
	ob_end_clean();

	// Locate the object template.
	wp_send_json_success( $result );
}

/**
 * Load messages template loop when searched on the private message page
 *
 * @since 1.6.0
 *
 * @return string Prints template loop for the Messages component.
 */
function bp_legacy_theme_messages_template_loader() {
	bp_get_template_part( 'members/single/messages/messages-loop' );
	exit();
}

/**
 * Load group invitations loop to handle pagination requests sent via AJAX.
 *
 * @since 2.0.0
 */
function bp_legacy_theme_invite_template_loader() {
	bp_get_template_part( 'groups/single/invites-loop' );
	exit();
}

/**
 * Load group membership requests loop to handle pagination requests sent via AJAX.
 *
 * @since 2.0.0
 */
function bp_legacy_theme_requests_template_loader() {
	bp_get_template_part( 'groups/single/requests-loop' );
	exit();
}

/**
 * Load the activity loop template when activity is requested via AJAX.
 *
 * @return string JSON object containing 'contents' (output of the template loop
 * for the Activity component) and 'feed_url' (URL to the relevant RSS feed).
 *
 * @since 1.2.0
 */
function bp_legacy_theme_activity_template_loader() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error();
	}

	$scope = '';
	if ( ! empty( $_POST['scope'] ) ) {
		$scope = $_POST['scope'];
	}

	// We need to calculate and return the feed URL for each scope.
	switch ( $scope ) {
		case 'friends':
			$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/friends/feed/';
			break;
		case 'groups':
			$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/groups/feed/';
			break;
		case 'favorites':
			$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/favorites/feed/';
			break;
		case 'mentions':
			$feed_url = bp_loggedin_user_domain() . bp_get_activity_slug() . '/mentions/feed/';
			bp_activity_clear_new_mentions( bp_loggedin_user_id() );
			break;
		default:
			$feed_url = home_url( bp_get_activity_root_slug() . '/feed/' );
			break;
	}

	$result = array();

	// Buffer the loop in the template to a var for JS to spit out.
	ob_start();
	bp_get_template_part( 'activity/activity-loop' );
	$result['contents'] = ob_get_contents();

	/**
	 * Filters the feed URL for when activity is requested via AJAX.
	 *
	 * @since 1.7.0
	 *
	 * @param string $feed_url URL for the feed to be used.
	 * @param string $scope    Scope for the activity request.
	 */
	$result['feed_url'] = apply_filters( 'bp_legacy_theme_activity_feed_url', $feed_url, $scope );
	ob_end_clean();

	wp_send_json_success( $result );
}

/**
 * Processes Activity updates received via a POST request.
 *
 * @return string HTML
 * @since 1.2.0
 */
function bp_legacy_theme_post_update() {
	$bp = buddypress();

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce.
	check_admin_referer( 'post_update', '_wpnonce_post_update' );

	if ( ! is_user_logged_in() )
		exit( '-1' );

	if ( empty( $_POST['content'] ) )
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . __( 'Please enter some content to post.', 'bp-next' ) . '</p></div>' );

	$activity_id = 0;
	$item_id     = 0;
	$object      = '';


	// Try to get the item id from posted variables.
	if ( ! empty( $_POST['item_id'] ) ) {
		$item_id = (int) $_POST['item_id'];
	}

	// Try to get the object from posted variables.
	if ( ! empty( $_POST['object'] ) ) {
		$object  = sanitize_key( $_POST['object'] );

	// If the object is not set and we're in a group, set the item id and the object
	} elseif ( bp_is_group() ) {
		$item_id = bp_get_current_group_id();
		$object = 'groups';
	}

	if ( ! $object && bp_is_active( 'activity' ) ) {
		$activity_id = bp_activity_post_update( array( 'content' => $_POST['content'] ) );

	} elseif ( 'groups' === $object ) {
		if ( $item_id && bp_is_active( 'groups' ) )
			$activity_id = groups_post_update( array( 'content' => $_POST['content'], 'group_id' => $item_id ) );

	} else {

		/** This filter is documented in bp-activity/bp-activity-actions.php */
		$activity_id = apply_filters( 'bp_activity_custom_update', false, $object, $item_id, $_POST['content'] );
	}

	if ( empty( $activity_id ) )
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . __( 'There was a problem posting your update. Please try again.', 'bp-next' ) . '</p></div>' );

	$last_recorded = ! empty( $_POST['since'] ) ? date( 'Y-m-d H:i:s', intval( $_POST['since'] ) ) : 0;
	if ( $last_recorded ) {
		$activity_args = array( 'since' => $last_recorded );
		$bp->activity->last_recorded = $last_recorded;
		add_filter( 'bp_get_activity_css_class', 'bp_activity_newest_class', 10, 1 );
	} else {
		$activity_args = array( 'include' => $activity_id );
	}

	if ( bp_has_activities ( $activity_args ) ) {
		while ( bp_activities() ) {
			bp_the_activity();
			bp_get_template_part( 'activity/entry' );
		}
	}

	if ( ! empty( $last_recorded ) ) {
		remove_filter( 'bp_get_activity_css_class', 'bp_activity_newest_class', 10, 1 );
	}

	exit;
}

/**
 * Posts new Activity comments received via a POST request.
 *
 * @since 1.2.0
 *
 * @global BP_Activity_Template $activities_template
 *
 * @return string HTML
 */
function bp_next_new_activity_comment() {
	global $activities_template;
	$bp = buddypress();

	$response = array(
		'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was an error posting your reply. Please try again.', 'bp-next' )
		)
	);

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( $response );
	}

	// Nonce check!
	if ( empty( $_POST['_wpnonce_new_activity_comment'] ) || ! wp_verify_nonce( $_POST['_wpnonce_new_activity_comment'], 'new_activity_comment' ) ) {
		wp_send_json_error( $response );
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['content'] ) ) {
		wp_send_json_error( array( 'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'Please do not leave the comment area blank.', 'bp-next' )
		) ) );
	}

	if ( empty( $_POST['form_id'] ) || empty( $_POST['comment_id'] ) || ! is_numeric( $_POST['form_id'] ) || ! is_numeric( $_POST['comment_id'] ) ) {
		wp_send_json_error( $response );
	}

	$comment_id = bp_activity_new_comment( array(
		'activity_id' => $_POST['form_id'],
		'content'     => $_POST['content'],
		'parent_id'   => $_POST['comment_id'],
	) );

	if ( ! $comment_id ) {
		if ( ! empty( $bp->activity->errors['new_comment'] ) && is_wp_error( $bp->activity->errors['new_comment'] ) ) {
			$response = array( 'feedback' => sprintf(
				'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
				esc_html( $bp->activity->errors['new_comment']->get_error_message() )
			) );
			unset( $bp->activity->errors['new_comment'] );
		}

		wp_send_json_error( $response );
	}

	// Load the new activity item into the $activities_template global.
	bp_has_activities( array(
		'display_comments' => 'stream',
		'hide_spam'        => false,
		'show_hidden'      => true,
		'include'          => $comment_id,
	) );

	// Swap the current comment with the activity item we just loaded.
	if ( isset( $activities_template->activities[0] ) ) {
		$activities_template->activity = new stdClass();
		$activities_template->activity->id = $activities_template->activities[0]->item_id;
		$activities_template->activity->current_comment = $activities_template->activities[0];

		// Because the whole tree has not been loaded, we manually
		// determine depth.
		$depth = 1;
		$parent_id = (int) $activities_template->activities[0]->secondary_item_id;
		while ( $parent_id !== (int) $activities_template->activities[0]->item_id ) {
			$depth++;
			$p_obj = new BP_Activity_Activity( $parent_id );
			$parent_id = (int) $p_obj->secondary_item_id;
		}
		$activities_template->activity->current_comment->depth = $depth;
	}

	ob_start();
	// Get activity comment template part.
	bp_get_template_part( 'activity/comment' );
	$response = array( 'contents' => ob_get_contents() );
	ob_end_clean();

	unset( $activities_template );

	wp_send_json_success( $response );
}

/**
 * Deletes an Activity item received via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function bp_next_delete_activity() {
	$response = array(
		'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem when deleting. Please try again.', 'bp-next' )
		)
	);

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( $response );
	}

	// Nonce check!
	if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'bp_activity_delete_link' ) ) {
		wp_send_json_error( $response );
	}

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) ) {
		wp_send_json_error( $response );
	}

	$activity = new BP_Activity_Activity( (int) $_POST['id'] );

	// Check access.
	if ( ! bp_activity_user_can_delete( $activity ) ) {
		wp_send_json_error( $response );
	}

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_before_action_delete_activity', $activity->id, $activity->user_id );

	if ( ! bp_activity_delete( array( 'id' => $activity->id, 'user_id' => $activity->user_id ) ) ) {
		wp_send_json_error( $response );
	}

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_action_delete_activity', $activity->id, $activity->user_id );

	wp_send_json_success( array( 'deleted' => $activity->id ) );
}

/**
 * Deletes an Activity comment received via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function bp_legacy_theme_delete_activity_comment() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check the nonce.
	check_admin_referer( 'bp_activity_delete_link' );

	if ( ! is_user_logged_in() )
		exit( '-1' );

	$comment = new BP_Activity_Activity( $_POST['id'] );

	// Check access.
	if ( ! bp_current_user_can( 'bp_moderate' ) && $comment->user_id != bp_loggedin_user_id() )
		exit( '-1' );

	if ( empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) )
		exit( '-1' );

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_before_action_delete_activity', $_POST['id'], $comment->user_id );

	if ( ! bp_activity_delete_comment( $comment->item_id, $comment->id ) )
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . __( 'There was a problem when deleting. Please try again.', 'bp-next' ) . '</p></div>' );

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_action_delete_activity', $_POST['id'], $comment->user_id );
	exit;
}

/**
 * AJAX spam an activity item or comment.
 *
 * @since 1.6.0
 *
 * @return mixed String on error, void on success.
 */
function bp_legacy_theme_spam_activity() {
	$bp = buddypress();

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Check that user is logged in, Activity Streams are enabled, and Akismet is present.
	if ( ! is_user_logged_in() || ! bp_is_active( 'activity' ) || empty( $bp->activity->akismet ) )
		exit( '-1' );

	// Check an item ID was passed.
	if ( empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) )
		exit( '-1' );

	// Is the current user allowed to spam items?
	if ( ! bp_activity_user_can_mark_spam() )
		exit( '-1' );

	// Load up the activity item.
	$activity = new BP_Activity_Activity( (int) $_POST['id'] );
	if ( empty( $activity->component ) )
		exit( '-1' );

	// Check nonce.
	check_admin_referer( 'bp_activity_akismet_spam_' . $activity->id );

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_before_action_spam_activity', $activity->id, $activity );

	// Mark as spam.
	bp_activity_mark_as_spam( $activity );
	$activity->save();

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_action_spam_activity', $activity->id, $activity->user_id );
	exit;
}

/**
 * Mark an activity as a favourite via a POST request.
 *
 * @since 1.2.0
 *
 * @return string HTML
 */
function bp_next_mark_activity_favorite() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error();
	}

	// Nonce check!
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_next_activity' ) ) {
		wp_send_json_error();
	}

	if ( bp_activity_add_user_favorite( $_POST['id'] ) ) {
		$response = array( 'content' => __( 'Remove Favorite', 'bp-next' ) );

		if ( ! bp_is_user() ) {
			$fav_count = (int) bp_get_total_favorite_count_for_user( bp_loggedin_user_id() );

			if ( 1 === $fav_count ) {
				$response['directory_tab'] = '<li id="activity-favorites" data-bp-scope="favorites" data-bp-object="activity">
					<a href="' . bp_loggedin_user_domain() . bp_get_activity_slug() . '/favorites/" title="' . esc_attr__( "The activity I've marked as a favorite.", 'bp-next' ) . '">
						' . esc_html__( 'My Favorites', 'bp-next' ) . '
					</a>
				</li>';
			} else {
				$response['fav_count'] = $fav_count;
			}
		}

		wp_send_json_success( $response );
	} else {
		wp_send_json_error();
	}
}

/**
 * Un-favourite an activity via a POST request.
 *
 * @since 1.2.0
 *
 * @return string HTML
 */
function bp_next_unmark_activity_favorite() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error();
	}

	// Nonce check!
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_next_activity' ) ) {
		wp_send_json_error();
	}

	if ( bp_activity_remove_user_favorite( $_POST['id'] ) ) {
		$response = array( 'content' => __( 'Favorite', 'bp-next' ) );

		$fav_count = (int) bp_get_total_favorite_count_for_user( bp_loggedin_user_id() );

		if ( 0 === $fav_count ) {
			$response['no_favorite'] = '<li id="activity-stream-message" class="info">
				<p>' . __( 'Sorry, there was no activity found. Please try a different filter.', 'bp-next' ) . '</p>
			</li>';
		} else {
			$response['fav_count'] = $fav_count;
		}

		wp_send_json_success( $response );
	} else {
		wp_send_json_error();
	}
}

function bp_next_clear_new_mentions() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error();
	}

	// Nonce check!
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_next_activity' ) ) {
		wp_send_json_error();
	}

	bp_activity_clear_new_mentions( bp_loggedin_user_id() );
	wp_send_json_success();
}

/**
 * Fetches an activity's full, non-excerpted content via a POST request.
 * Used for the 'Read More' link on long activity items.
 *
 * @since 1.0.0
 *
 * @return string HTML
 */
function bp_next_get_single_activity_content() {
	$response = array(
		'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem displaying the content. Please try again.', 'bp-next' )
		)
	);

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( $response );
	}

	// Nonce check!
	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'bp_next_activity' ) ) {
		wp_send_json_error( $response );
	}

	$activity_array = bp_activity_get_specific( array(
		'activity_ids'     => $_POST['id'],
		'display_comments' => 'stream'
	) );

	if ( empty( $activity_array['activities'][0] ) ) {
		wp_send_json_error( $response );
	}

	$activity = $activity_array['activities'][0];

	/**
	 * Fires before the return of an activity's full, non-excerpted content via a POST request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $activity Activity content. Passed by reference.
	 */
	do_action_ref_array( 'bp_next_get_single_activity_content', array( &$activity ) );

	// Activity content retrieved through AJAX should run through normal filters, but not be truncated.
	remove_filter( 'bp_get_activity_content_body', 'bp_activity_truncate_entry', 5 );

	/** This filter is documented in bp-activity/bp-activity-template.php */
	$content = apply_filters( 'bp_get_activity_content_body', $activity->content );

	wp_send_json_success( array( 'contents' => $content ) );
}

/**
 * Invites a friend to join a group via a POST request.
 *
 * @since 1.2.0
 *
 * @todo Audit return types
 */
function bp_legacy_theme_ajax_invite_user() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	check_ajax_referer( 'groups_invite_uninvite_user' );

	if ( ! $_POST['friend_id'] || ! $_POST['friend_action'] || ! $_POST['group_id'] )
		return;

	if ( ! bp_groups_user_can_send_invites( $_POST['group_id'] ) )
		return;

	if ( ! friends_check_friendship( bp_loggedin_user_id(), $_POST['friend_id'] ) )
		return;

	$group_id = (int) $_POST['group_id'];
	$friend_id = (int) $_POST['friend_id'];

	if ( 'invite' == $_POST['friend_action'] ) {
		$group = groups_get_group( $group_id );

		// Users who have previously requested membership do not need
		// another invitation created for them.
		if ( BP_Groups_Member::check_for_membership_request( $friend_id, $group_id ) ) {
			$user_status = 'is_pending';

		// Create the user invitation.
		} elseif ( groups_invite_user( array( 'user_id' => $friend_id, 'group_id' => $group_id ) ) ) {
			$user_status = 'is_invited';

		// Miscellaneous failure.
		} else {
			return;
		}

		$user = new BP_Core_User( $friend_id );

		$uninvite_url = bp_is_current_action( 'create' )
			? bp_get_groups_directory_permalink() . 'create/step/group-invites/?user_id=' . $friend_id
			: bp_get_group_permalink( $group )    . 'send-invites/remove/' . $friend_id;

		echo '<li id="uid-' . esc_attr( $user->id ) . '">';
		echo $user->avatar_thumb;
		echo '<h4>' . $user->user_link . '</h4>';
		echo '<span class="activity">' . esc_attr( $user->last_active ) . '</span>';
		echo '<div class="action">
				<a class="button remove" href="' . wp_nonce_url( $uninvite_url, 'groups_invite_uninvite_user' ) . '" id="uid-' . esc_attr( $user->id ) . '">' . __( 'Remove Invite', 'bp-next' ) . '</a>
			  </div>';

		if ( 'is_pending' == $user_status ) {
			echo '<p class="description">' . sprintf( __( '%s has previously requested to join this group. Sending an invitation will automatically add the member to the group.', 'bp-next' ), $user->user_link ) . '</p>';
		}

		echo '</li>';
		exit;

	} elseif ( 'uninvite' == $_POST['friend_action'] ) {
		// Users who have previously requested membership should not
		// have their requests deleted on the "uninvite" action.
		if ( BP_Groups_Member::check_for_membership_request( $friend_id, $group_id ) ) {
			return;
		}

		// Remove the unsent invitation.
		if ( ! groups_uninvite_user( $friend_id, $group_id ) ) {
			return;
		}

		exit;

	} else {
		return;
	}
}

/**
 * Friend/un-friend a user via a POST request.
 *
 * @since 1.0.0
 *
 * @return string HTML
 */
function bp_next_ajax_addremove_friend() {
	$response = array(
		'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'bp-next' )
		)
	);

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['nonce'] ) || empty( $_POST['item_id'] ) || ! bp_is_active( 'friends' ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce
	$nonce = $_POST['nonce'];
	$check = 'bp_next_friends';

	// Use a specific one for actions needed it
	if ( ! empty( $_POST['_wpnonce'] ) && ! empty( $_POST['action'] ) ) {
		$nonce = $_POST['_wpnonce'];
		$check = $_POST['action'];
	}

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	// Cast fid as an integer.
	$friend_id = (int) $_POST['item_id'];

	// Trying to cancel friendship.
	if ( 'is_friend' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $friend_id ) ) {
		if ( ! friends_remove_friend( bp_loggedin_user_id(), $friend_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
				esc_html__( 'Friendship could not be canceled.', 'bp-next' )
			);

			wp_send_json_error( $response );
		} else {
			wp_send_json_success( array( 'contents' => bp_get_add_friend_button( $friend_id ) ) );
		}

	// Trying to request friendship.
	} elseif ( 'not_friends' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $friend_id ) ) {
		if ( ! friends_add_friend( bp_loggedin_user_id(), $friend_id ) ) {
			$response['feedback'] = sprintf(
				'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
				esc_html__( 'Friendship could not be requested.', 'bp-next' )
			);

			wp_send_json_error( $response );
		} else {
			wp_send_json_success( array( 'contents' => bp_get_add_friend_button( $friend_id ) ) );
		}

	// Trying to cancel pending request.
	} elseif ( 'pending' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $friend_id ) ) {
		if ( friends_withdraw_friendship( bp_loggedin_user_id(), $friend_id ) ) {
			wp_send_json_success( array( 'contents' => bp_get_add_friend_button( $friend_id ) ) );
		} else {
			$response['feedback'] = sprintf(
				'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
				esc_html__( 'Friendship request could not be cancelled.', 'bp-next' )
			);

			wp_send_json_error( $response );
		}

	// Request already pending.
	} else {
		$response['feedback'] = sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'Request Pending', 'bp-next' )
		);

		wp_send_json_error( $response );
	}
}

/**
 * Accept a user friendship request via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function bp_legacy_theme_ajax_accept_friendship() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	check_admin_referer( 'friends_accept_friendship' );

	if ( ! friends_accept_friendship( (int) $_POST['id'] ) )
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem accepting that request. Please try again.', 'bp-next' ) . '</p></div>';

	exit;
}

/**
 * Reject a user friendship request via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function bp_legacy_theme_ajax_reject_friendship() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	check_admin_referer( 'friends_reject_friendship' );

	if ( ! friends_reject_friendship( (int) $_POST['id'] ) )
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem rejecting that request. Please try again.', 'bp-next' ) . '</p></div>';

	exit;
}

/**
 * Join or leave a group when clicking the "join/leave" button via a POST request.
 *
 * @since 1.0.0
 *
 * @return string HTML
 */
function bp_next_ajax_joinleave_group() {
	$response = array(
		'feedback' => sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'There was a problem performing this action. Please try again.', 'bp-next' )
		)
	);

	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		wp_send_json_error( $response );
	}

	if ( empty( $_POST['nonce'] ) || empty( $_POST['item_id'] )  || ! bp_is_active( 'groups' ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce
	$nonce = $_POST['nonce'];
	$check = 'bp_next_groups';

	// Use a specific one for actions needed it
	if ( ! empty( $_POST['_wpnonce'] ) && ! empty( $_POST['action'] ) ) {
		$nonce = $_POST['_wpnonce'];
		$check = $_POST['action'];
	}

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	// Cast gid as integer.
	$group_id = (int) $_POST['item_id'];

	if ( groups_is_user_banned( bp_loggedin_user_id(), $group_id ) ) {
		$response['feedback'] = sprintf(
			'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
			esc_html__( 'You cannot join this group.', 'bp-next' )
		);

		wp_send_json_error( $response );
	}

	// Validate and get the group
	$group = groups_get_group( array( 'group_id' => $group_id ) );

	if ( empty( $group->id ) ) {
		wp_send_json_error( $response );
	}

	if ( ! groups_is_user_member( bp_loggedin_user_id(), $group->id ) ) {
		if ( 'public' == $group->status ) {

			if ( ! groups_join_group( $group->id ) ) {
				$response['feedback'] = sprintf(
					'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
					esc_html__( 'Error joining group', 'bp-next' )
				);

				wp_send_json_error( $response );
			} else {
				// User is now a member of the group
				$group->is_member = '1';

				wp_send_json_success( array( 'contents' => bp_get_group_join_button( $group ) ) );
			}

		} elseif ( 'private' == $group->status ) {

			// If the user has already been invited, then this is
			// an Accept Invitation button.
			if ( groups_check_user_has_invite( bp_loggedin_user_id(), $group->id ) ) {

				if ( ! groups_accept_invite( bp_loggedin_user_id(), $group->id ) ) {
					$response['feedback'] = sprintf(
						'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
						esc_html__( 'Error requesting membership', 'bp-next' )
					);

					wp_send_json_error( $response );
				} else {
					// User is now a member of the group
					$group->is_member = '1';

					wp_send_json_success( array( 'contents' => bp_get_group_join_button( $group ) ) );
				}

			// Otherwise, it's a Request Membership button.
			} else {

				if ( ! groups_send_membership_request( bp_loggedin_user_id(), $group->id ) ) {
					$response['feedback'] = sprintf(
						'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
						esc_html__( 'Error requesting membership', 'bp-next' )
					);

					wp_send_json_error( $response );
				} else {
					// Request is pending
					$group->is_pending = '1';

					wp_send_json_success( array( 'contents' => bp_get_group_join_button( $group ) ) );
				}
			}
		}

	} else {

		if ( ! groups_leave_group( $group->id ) ) {
			$response['feedback'] = sprintf(
				'<div class="feedback error bp-ajax-message"><p>%s</p></div>',
				esc_html__( 'Error leaving group', 'bp-next' )
			);

			wp_send_json_error( $response );
		} else {
			// User is no more a member of the group
			$group->is_member = '0';

			wp_send_json_success( array( 'contents' => bp_get_group_join_button( $group ) ) );
		}
	}
}

/**
 * Close and keep closed site wide notices from an admin in the sidebar, via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function bp_legacy_theme_ajax_close_notice() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( ! isset( $_POST['notice_id'] ) ) {
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem closing the notice.', 'bp-next' ) . '</p></div>';

	} else {
		$user_id      = get_current_user_id();
		$notice_ids   = bp_get_user_meta( $user_id, 'closed_notices', true );
		$notice_ids[] = (int) $_POST['notice_id'];

		bp_update_user_meta( $user_id, 'closed_notices', $notice_ids );
	}

	exit;
}

/**
 * Send a private message reply to a thread via a POST request.
 *
 * @since 1.2.0
 *
 * @return string HTML
 */
function bp_legacy_theme_ajax_messages_send_reply() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	check_ajax_referer( 'messages_send_message' );

	$result = messages_new_message( array( 'thread_id' => (int) $_REQUEST['thread_id'], 'content' => $_REQUEST['content'] ) );

	if ( !empty( $result ) ) {

		// Pretend we're in the message loop.
		global $thread_template;

		bp_thread_has_messages( array( 'thread_id' => (int) $_REQUEST['thread_id'] ) );

		// Set the current message to the 2nd last.
		$thread_template->message = end( $thread_template->thread->messages );
		$thread_template->message = prev( $thread_template->thread->messages );

		// Set current message to current key.
		$thread_template->current_message = key( $thread_template->thread->messages );

		// Now manually iterate message like we're in the loop.
		bp_thread_the_message();

		// Manually call oEmbed
		// this is needed because we're not at the beginning of the loop.
		bp_messages_embed();

		// Add new-message css class.
		add_filter( 'bp_get_the_thread_message_css_class', create_function( '$retval', '
			$retval[] = "new-message";
			return $retval;
		' ) );

		// Output single message template part.
		bp_get_template_part( 'members/single/messages/message' );

		// Clean up the loop.
		bp_thread_messages();

	} else {
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem sending that reply. Please try again.', 'bp-next' ) . '</p></div>';
	}

	exit;
}

/**
 * Mark a private message as unread in your inbox via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function bp_legacy_theme_ajax_message_markunread() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( ! isset($_POST['thread_ids']) ) {
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem marking messages as unread.', 'bp-next' ) . '</p></div>';

	} else {
		$thread_ids = explode( ',', $_POST['thread_ids'] );

		for ( $i = 0, $count = count( $thread_ids ); $i < $count; ++$i ) {
			BP_Messages_Thread::mark_as_unread( (int) $thread_ids[$i] );
		}
	}

	exit;
}

/**
 * Mark a private message as read in your inbox via a POST request.
 *
 * @since 1.2.0
 *
 * @return mixed String on error, void on success.
 */
function bp_legacy_theme_ajax_message_markread() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( ! isset($_POST['thread_ids']) ) {
		echo "-1<div id='message' class='error'><p>" . __('There was a problem marking messages as read.', 'bp-next' ) . '</p></div>';

	} else {
		$thread_ids = explode( ',', $_POST['thread_ids'] );

		for ( $i = 0, $count = count( $thread_ids ); $i < $count; ++$i ) {
			BP_Messages_Thread::mark_as_read( (int) $thread_ids[$i] );
		}
	}

	exit;
}

/**
 * Delete a private message(s) in your inbox via a POST request.
 *
 * @since 1.2.0
 *
 * @return string HTML
 */
function bp_legacy_theme_ajax_messages_delete() {
	// Bail if not a POST action.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	if ( ! isset($_POST['thread_ids']) ) {
		echo "-1<div id='message' class='error'><p>" . __( 'There was a problem deleting messages.', 'bp-next' ) . '</p></div>';

	} else {
		$thread_ids = wp_parse_id_list( $_POST['thread_ids'] );
		messages_delete_thread( $thread_ids );

		_e( 'Messages deleted.', 'bp-next' );
	}

	exit;
}

/**
 * AJAX handler for autocomplete.
 *
 * Displays friends only, unless BP_MESSAGES_AUTOCOMPLETE_ALL is defined.
 *
 * @since 1.2.0
 */
function bp_legacy_theme_ajax_messages_autocomplete_results() {

	/**
	 * Filters the max results default value for ajax messages autocomplete results.
	 *
	 * @since 1.5.0
	 *
	 * @param int $value Max results for autocomplete. Default 10.
	 */
	$limit = isset( $_GET['limit'] ) ? absint( $_GET['limit'] )          : (int) apply_filters( 'bp_autocomplete_max_results', 10 );
	$term  = isset( $_GET['q'] )     ? sanitize_text_field( $_GET['q'] ) : '';

	// Include everyone in the autocomplete, or just friends?
	if ( bp_is_current_component( bp_get_messages_slug() ) ) {
		$only_friends = ( buddypress()->messages->autocomplete_all === false );
	} else {
		$only_friends = true;
	}

	$suggestions = bp_core_get_suggestions( array(
		'limit'        => $limit,
		'only_friends' => $only_friends,
		'term'         => $term,
		'type'         => 'members',
	) );

	if ( $suggestions && ! is_wp_error( $suggestions ) ) {
		foreach ( $suggestions as $user ) {

			// Note that the final line break acts as a delimiter for the
			// autocomplete JavaScript and thus should not be removed.
			printf( '<span id="%s" href="#"></span><img src="%s" style="width: 15px"> &nbsp; %s (%s)' . "\n",
				esc_attr( 'link-' . $user->ID ),
				esc_url( $user->image ),
				esc_html( $user->name ),
				esc_html( $user->ID )
			);
		}
	}

	exit;
}

/**
 * AJAX callback to set a message's star status.
 *
 * @since 2.3.0
 */
function bp_legacy_theme_ajax_messages_star_handler() {
	if ( false === bp_is_active( 'messages', 'star' ) || empty( $_POST['message_id'] ) ) {
		return;
	}

	// Check nonce.
	check_ajax_referer( 'bp-messages-star-' . (int) $_POST['message_id'], 'nonce' );

	// Check capability.
	if ( ! is_user_logged_in() || ! bp_core_can_edit_settings() ) {
		return;
	}

	if ( true === bp_messages_star_set_action( array(
		'action'     => $_POST['star_status'],
		'message_id' => (int) $_POST['message_id'],
		'bulk'       => ! empty( $_POST['bulk'] ) ? true : false
	 ) ) ) {
		echo '1';
		die();
	}

	echo '-1';
	die();
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

function bp_next_get_users_to_invite() {
	$bp = buddypress();

	$response = array(
		'feedback' => esc_html__( 'There was a problem performing this action. Please try again.', 'bp-next' ),
		'type'     => 'error',
	);

	if ( empty( $_POST['nonce'] ) ) {
		wp_send_json_error( $response );
	}

	// Use default nonce
	$nonce = $_POST['nonce'];
	$check = 'bp_next_groups';

	// Use a specific one for actions needed it
	if ( ! empty( $_POST['_wpnonce'] ) && ! empty( $_POST['action'] ) ) {
		$nonce = $_POST['_wpnonce'];
		$check = $_POST['action'];
	}

	// Nonce check!
	if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $check ) ) {
		wp_send_json_error( $response );
	}

	$request = wp_parse_args( $_POST, array(
		'scope' => 'members',
	) );

	$bp->groups->invites_scope = 'members';
	$message = __( 'You can invite members using the + button, a new nav will appear to let you send your invites', 'bp-next' );

	if ( 'friends' === $request['scope'] ) {
		$request['user_id'] = bp_loggedin_user_id();
		$bp->groups->invites_scope = 'friends';
		$message = __( 'You can invite friends using the + button, a new nav will appear to let you send your invites', 'bp-next' );
	}

	if ( 'invited' === $request['scope'] ) {

		if ( ! bp_group_has_invites( array( 'user_id' => 'any' ) ) ) {
			wp_send_json_error( array(
				'feedback' => __( 'No pending invites found.', 'bp-next' ),
				'type'     => 'info',
			) );
		}

		$request['is_confirmed'] = false;
		$bp->groups->invites_scope = 'invited';
		$message = __( 'You can view all the group\'s pending invites from this screen.', 'bp-next' );
	}

	$potential_invites = bp_next_get_group_potential_invites( $request );

	if ( empty( $potential_invites->users ) ) {
		$error = array(
			'feedback' => __( 'No members were found, try another filter.', 'bp-next' ),
			'type'     => 'info',
		);

		if ( 'friends' === $bp->groups->invites_scope ) {
			$error = array(
				'feedback' => __( 'All your friends are already members of this group or already received an invite to join this group.', 'bp-next' ),
				'type'     => 'info',
			);

			if ( 0 === (int) bp_get_total_friend_count( bp_loggedin_user_id() ) ) {
				$error = array(
					'feedback' => __( 'You have no friends!', 'bp-next' ),
					'type'     => 'info',
				);
			}

		}

		unset( $bp->groups->invites_scope );

		wp_send_json_error( $error );
	}

	$potential_invites->users = array_map( 'bp_next_prepare_group_potential_invites_for_js', array_values( $potential_invites->users ) );
	$potential_invites->users = array_filter( $potential_invites->users );

	// Set a message to explain use of the current scope
	$potential_invites->feedback = $message;

	unset( $bp->groups->invites_scope );

	wp_send_json_success( $potential_invites );
}
add_action( 'wp_ajax_groups_get_group_potential_invites', 'bp_next_get_users_to_invite' );

function bp_next_groups_invites_custom_message( $message = '' ) {
	if ( empty( $message ) ) {
		return $message;
	}

	$bp = buddypress();

	if ( empty( $bp->groups->invites_message ) ) {
		return $message;
	}

	$message = str_replace( '---------------------', "
---------------------\n
" . $bp->groups->invites_message . "\n
---------------------
	", $message );

	return $message;
}

function bp_next_send_group_invites() {
	$bp = buddypress();

	$response = array(
		'feedback' => __( 'Invites could not be sent, please try again.', 'bp-next' ),
	);

	// Verify nonce
	if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'groups_send_invites' ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Invites could not be sent, please try again.', 'bp-next' ),
			'type'     => 'error',
		) );
	}

	$group_id = bp_get_current_group_id();

	if ( bp_is_group_create() && ! empty( $_POST['group_id'] ) ) {
		$group_id = (int) $_POST['group_id'];
	}

	if ( ! bp_groups_user_can_send_invites( $group_id ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'You are not allowed to send invites for this group.', 'bp-next' ),
			'type'     => 'error',
		) );
	}

	if ( empty( $_POST['users'] ) ) {
		wp_send_json_error( $response );
	}

	// For feedback
	$invited = array();

	foreach ( (array) $_POST['users'] as $user_id ) {
		$invited[ $user_id ] = groups_invite_user( array( 'user_id' => $user_id, 'group_id' => $group_id ) );
	}

	if ( ! empty( $_POST['message'] ) ) {
		$bp->groups->invites_message = wp_kses( wp_unslash( $_POST['message'] ), array() );

		add_filter( 'groups_notification_group_invites_message', 'bp_next_groups_invites_custom_message', 10, 1 );
	}

	// Send the invites.
	groups_send_invites( bp_loggedin_user_id(), $group_id );

	if ( ! empty( $_POST['message'] ) ) {
		unset( $bp->groups->invites_message );

		remove_filter( 'groups_notification_group_invites_message', 'bp_next_groups_invites_custom_message', 10, 1 );
	}

	if ( array_search( false, $invited ) ) {
		$errors = array_keys( $invited, false );

		wp_send_json_error( array(
			'feedback' => sprintf( __( 'Invites failed for %d user(s).', 'bp-next' ), count( $errors ) ),
			'users'    => $errors,
			'type'     => 'error',
		) );
	}

	wp_send_json_success( array(
		'feedback' => __( 'Invites sent.', 'bp-next' )
	) );
}
add_action( 'wp_ajax_groups_send_group_invites', 'bp_next_send_group_invites' );

function bp_next_remove_group_invite() {
	$user_id  = $_POST['user'];
	$group_id = bp_get_current_group_id();

	// Verify nonce
	if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'groups_invite_uninvite_user' ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Invites could not be removed, please try again.', 'bp-next' ),
		) );
	}

	if ( BP_Groups_Member::check_for_membership_request( $user_id, $group_id ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Too late, the user is now a member of the group.', 'bp-next' ),
			'code'     => 1,
		) );
	}

	// Remove the unsent invitation.
	if ( ! groups_uninvite_user( $user_id, $group_id ) ) {
		wp_send_json_error( array(
			'feedback' => __( 'Removing the invite for the user failed.', 'bp-next' ),
			'code'     => 0,
		) );
	}

	wp_send_json_success( array(
		'feedback'    => __( 'No more pending invites for the group.', 'bp-next' ),
		'has_invites' => bp_group_has_invites( array( 'user_id' => 'any' ) ),
	) );
}
add_action( 'wp_ajax_groups_delete_group_invite', 'bp_next_remove_group_invite' );
