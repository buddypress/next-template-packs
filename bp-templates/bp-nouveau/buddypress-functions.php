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
 * BP required version:    2.7-alpha
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

		// Setup features support
		$this->setup_support();
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
	 * Setup the Template Pack features support.
	 *
	 * @since 1.0.0
	 */
	private function setup_support() {
		$width         = 1300;
		$top_offset    = 150;
		$avatar_height = apply_filters( 'bp_core_avatar_full_height', $top_offset );

		if ( ! empty( $GLOBALS['content_width'] ) ) {
			$width = $GLOBALS['content_width'];
		}

		if ( $avatar_height > $top_offset ) {
			$top_offset = $avatar_height;
		}

		bp_set_theme_compat_feature( $this->id, array(
			'name'     => 'cover_image',
			'settings' => array(
				'components'   => array( 'xprofile', 'groups' ),
				'width'        => $width,
				'height'       => $top_offset + round( $avatar_height / 2 ),
				'callback'     => 'bp_nouveau_theme_cover_image',
				'theme_handle' => 'bp-nouveau',
			),
		) );
	}

	/**
	 * Setup the Template Pack common actions.
	 *
	 * @since 1.0.0
	 */
	protected function setup_actions() {
		// Filter BuddyPress template hierarchy and look for page templates.
		add_filter( 'bp_get_buddypress_template', array( $this, 'theme_compat_page_templates' ), 10, 1 );

		// We'll handle the display of template notices in BP Nouveau
		remove_action( 'template_notices', 'bp_core_render_message' );

		/** Scripts ***********************************************************/

		add_action( 'bp_enqueue_scripts', array( $this, 'register_scripts'  ), 2 ); // Register theme JS

		add_action( 'bp_enqueue_scripts', array( $this, 'enqueue_styles'   ) ); // Enqueue theme CSS
		add_action( 'bp_enqueue_scripts', array( $this, 'enqueue_scripts'  ) ); // Enqueue theme JS
		add_filter( 'bp_enqueue_scripts', array( $this, 'localize_scripts' ) ); // Enqueue theme script localization

		/** Body no-js Class **************************************************/

		add_filter( 'body_class', array( $this, 'add_nojs_body_class' ), 20, 1 );

		// Ajax querystring
		add_filter( 'bp_ajax_querystring', 'bp_nouveau_ajax_querystring', 10, 2 );

		// Register directory nav items
		add_action( 'bp_screens', array( $this, 'setup_directory_nav' ), 15 );

		// BP Nouveau Customizer panel.
		add_action( 'bp_customize_register', 'bp_nouveau_customize_register' );

		// Enqueue scripts for the BP Nouveau customizer panel.
		add_action( 'customize_controls_enqueue_scripts', 'bp_nouveau_customizer_enqueue_scripts' );

		// Register the Default front pages Dynamic Sidebars
		add_action( 'widgets_init', 'bp_nouveau_register_sidebars', 11 );

		// Register the Primary Object nav widget
		add_action( 'bp_widgets_init', array( 'BP_Nouveau_Object_Nav_Widget', 'register_widget' ) );

		/** Override **********************************************************/

		/**
		 * Fires after all of the BuddyPress theme compat actions have been added.
		 *
		 * @since 1.0.0
		 *
		 * @param BP_Nouveau $this Current BP_Nouveau instance.
		 */
		do_action_ref_array( 'bp_theme_compat_actions', array( &$this ) );
	}

	/**
	 * Enqueue the template pack css files
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		$min = bp_core_get_minified_asset_suffix();
		$rtl = '';

		if ( is_rtl() ) {
			$rtl = '-rtl';
		}

		$css_dependencies = apply_filters( 'bp_nouveau_css_dependencies', array( 'dashicons' ) );

		$styles = apply_filters( 'bp_nouveau_enqueue_styles', array(
			'bp-nouveau' => array(
				'file' => 'css/buddypress%1$s%2$s.css', 'dependencies' => $css_dependencies, 'version' => $this->version,
			),
		) );

		if ( $styles ) {

			foreach ( $styles as $handle => $style ) {
				if ( ! isset( $style['file'] ) ) {
					continue;
				}

				// Eventually use the rtl/minified version.
				$file = sprintf( $style['file'], $rtl, $min );

				// Locate the asset if needed.
				if ( false === strpos( $style['file'], '://' ) ) {
					$asset = bp_locate_template_asset( $file );

					if ( empty( $asset['uri'] ) || false === strpos( $asset['uri'], '://' ) ) {
						continue;
					}

					$file = $asset['uri'];
				}

				$data = wp_parse_args( $style, array(
					'dependencies' => array(),
					'version'      => $this->version,
					'type'         => 'screen',
				) );

				wp_enqueue_style( $handle, $file, $data['dependencies'], $data['version'], $data['type'] );

				if ( $min ) {
					wp_style_add_data( $handle, 'suffix', $min );
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
	 * Adds the no-js class to the body tag.
	 *
	 * This function ensures that the <body> element will have the 'no-js' class by default. If you're
	 * using JavaScript for some visual functionality in your theme, and you want to provide noscript
	 * support, apply those styles to body.no-js.
	 *
	 * The no-js class is removed by the JavaScript created in buddypress.js.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 */
	public function localize_scripts() {
		// First global params
		$params = array(
			'ajaxurl'             => bp_core_ajax_url(),
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
				$supported_objects = array_merge( $supported_objects, array( 'group_members', 'group_requests' ) );
			}

			$object_nonces[ $object ] = wp_create_nonce( 'bp_nouveau_' . $object );
		}

		// Add components & nonces
		$params['objects'] = $supported_objects;
		$params['nonces']  = $object_nonces;

		// Add Warnings if any
		$params['warnings'] = $this->developer_feedbacks();

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
	 * @since 1.0.0
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
	 * we are not using. This will be output as
	 * warnings inside the Browser console to avoid
	 * messing with the page display.
	 *
	 * @since  1.0.0
	 *
	 * @return string HTML Output
	 */
	public function developer_feedbacks() {
		$notices = array();

		// If debug is not on, stop!
		if ( ! WP_DEBUG ) {
			return;
		}

		// Get The forsaken hooks.
		$forsaken_hooks = bp_nouveau_get_forsaken_hooks();

		// Loop to check if deprecated hooks are used.
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

			$notices[] = $feedback['message'];
		}

		return $notices;
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
