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
 * BP required version:    2.6.0-rc1
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
	/**
	 * Instance of this class.
	 */
	protected static $instance = null;

	/** Functions *************************************************************/

	/**
	 * Return the instance of this class.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * The BP Nouveau constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::start();

		// Include needed files
		$this->includes();
	}

	/**
	 * BP Nouveau global variables.
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

		// Includes dir
		$this->includes_dir = trailingslashit( $this->dir ) . 'includes';

		// Set the Directory Nav
		$this->directory_nav = new BP_Core_Nav();
	}

	/**
	 * Includes!
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		require( trailingslashit( $this->includes_dir ) . 'functions.php'     );
		require( trailingslashit( $this->includes_dir ) . 'classes.php'       );
		require( trailingslashit( $this->includes_dir ) . 'template-tags.php' );
		require( trailingslashit( $this->includes_dir ) . 'ajax.php'          );

		foreach ( bp_core_get_packaged_component_ids() as $component ) {
			$component_loader = trailingslashit( $this->includes_dir ) . $component . '/loader.php';

			// Only load files for active components.
			if ( ! bp_is_active( $component ) || ! file_exists( $component_loader ) ) {
				continue;
			}

			require( $component_loader );
		}

		do_action_ref_array( 'bp_nouveau_includes', array( &$this ) );
	}

	/**
	 * Setup the Template Pack common actions.
	 *
	 * @since 1.0.0
	 */
	protected function setup_actions() {
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

		/** Ajax **************************************************************/

		$actions = array(
			/**
			 * @todo check if we still use these 2 actions, else remove it
			 * and the corresponding functions
			 */
			'invite_filter'   => 'bp_legacy_theme_invite_template_loader',
			'requests_filter' => 'bp_legacy_theme_requests_template_loader',
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

		// Register directory nav items
		add_action( 'bp_screens', array( $this, 'setup_directory_nav' ), 15 );

		// Feedbacks for developers
		if ( WP_DEBUG ) {
			add_action( 'wp_footer', array( $this, 'developer_feedbacks' ), 0 );
		}

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
		$min = bp_core_get_minified_asset_suffix();

		$scripts = apply_filters( 'bp_nouveau_register_scripts', array(
			'bp-nouveau' => array(
				'file' => 'js/buddypress%s.js', 'dependencies' => bp_core_get_js_dependencies(), 'version' => $this->version, 'footer' => true,
			),
		) );

		if ( $scripts ) {

			// Add The password verify if needed.
			if ( bp_is_active( 'settings' ) || bp_get_signup_allowed() ) {
				$scripts['bp-nouveau-password-verify'] = array(
					'file' => 'js/password-verify%s.js', 'dependencies' => array( 'bp-nouveau', 'password-strength-meter' ), 'footer' => true,
				);
			}

			foreach ( $scripts as $handle => $script ) {
				if ( ! isset( $script['file'] ) ) {
					continue;
				}

				// Eventually use the minified version.
				$file = sprintf( $script['file'], $min );

				// Locate the asset if needed.
				if ( false === strpos( $script['file'], '://' ) ) {
					$asset = bp_locate_template_asset( $file );

					if ( empty( $asset['uri'] ) || false === strpos( $asset['uri'], '://' ) ) {
						continue;
					}

					$file = $asset['uri'];
				}

				$data = wp_parse_args( $script, array(
					'dependencies' => array(),
					'version'      => $this->version,
					'footer'       => false,
				) );

				wp_register_script( $handle, $file, $data['dependencies'], $data['version'], $data['footer'] );
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

		// Maybe enqueue Password Verify
		if ( bp_is_register_page() || ( function_exists( 'bp_is_user_settings_general' ) && bp_is_user_settings_general() ) ) {
			wp_enqueue_script( 'bp-nouveau-password-verify' );
		}

		// Maybe enqueue comment reply JS.
		if ( is_singular() && bp_is_blog_page() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		/**
		 * Let specific scripts to components to be enqueued
		 */
		do_action( 'bp_nouveau_enqueue_scripts' );
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

	/**
	 * Define the directory nav items
	 *
	 * @since 1.0.0
	 */
	public function setup_directory_nav() {
		$nav_items = array();

		if ( bp_is_members_directory() ) {
			$nav_items = bp_nouveau_get_members_directory_nav_items();
		} elseif ( bp_is_activity_directory() ) {
			$nav_items = bp_nouveau_get_activity_directory_nav_items();
		} elseif ( bp_is_groups_directory() ) {
			$nav_items = bp_nouveau_get_groups_directory_nav_items();
		} elseif ( bp_is_blogs_directory() ) {
			$nav_items = bp_nouveau_get_blogs_directory_nav_items();
		}

		if ( empty( $nav_items ) ) {
			return;
		}

		foreach ( $nav_items as $nav_item ) {
			if ( empty( $nav_item['component'] ) || $nav_item['component'] !== bp_current_component() ) {
				continue;
			}

			// Define the primary nav for the current component's directory
			$this->directory_nav->add_nav( $nav_item );
		}
	}

	/**
	 * Inform developers about the Legacy hooks
	 * we are not using.
	 *
	 * @since  1.0.0
	 *
	 * @return string HTML Output
	 */
	public function developer_feedbacks() {
		$forsaken_hooks = bp_nouveau_get_forsaken_hooks();
		$notices        = array();

		foreach ( $forsaken_hooks as $hook => $feedback ) {
			if ( 'action' === $feedback['hook_type'] ) {
				if ( ! has_action( $hook ) ) {
					continue;
				}

			} elseif ( 'filter' === $feedback['hook_type'] ) {
				if ( ! has_filter( $hook ) ) {
					continue;
				}
			}

			$notices[] = sprintf( '<div class="bp-feedback %1$s"><p>%2$s</p></div>', $feedback['message_type'], esc_html( $feedback['message'] ) );
		}

		if ( empty( $notices ) ) {
			return;
		}

		?>
		<div id="developer-feedbacks">
			<?php foreach ( $notices as $notice ) {
				echo $notice;
			} ;?>
		</div>
		<?php
	}
}
endif;

/**
 * Get a unique instance of BP Nouveau
 *
 * @since  1.0.0
 *
 * @return BP_Nouveau the main instance of the class
 */
function bp_nouveau() {
	return BP_Nouveau::get_instance();
}

/**
 * Launch BP Nouveau!
 */
bp_nouveau();
