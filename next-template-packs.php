<?php
/**
 * Adds a BuddyPress Admin Tab to manage BuddyPress template pack
 *
 *
 * @package   Next Template Packs
 * @author    imath
 * @license   GPL-2.0+
 * @link      http://imathi.eu
 *
 * @buddypress-plugin
 * Plugin Name:       Next Template Packs
 * Plugin URI:        https://github.com/imath/next-template-packs
 * Description:       Adds a BuddyPress Admin Tab to manage BuddyPress template pack
 * Version:           1.0.0-alpha
 * Author:            imath
 * Author URI:        http://imathi.eu
 * Text Domain:       next-template-packs
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages/
 * GitHub Plugin URI: https://github.com/imath/next-template-packs
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Next_Template_Packs' ) ) :
/**
 * Main Class
 *
 * @since 1.0.0
 */
class Next_Template_Packs {
	/**
	 * Instance of this class.
	 */
	protected static $instance = null;

	/**
	 * BuddyPress db version
	 */
	public static $bp_db_version_required = 10000;

	/**
	 * Initialize the plugin
	 */
	private function __construct() {
		$this->setup_globals();
		$this->setup_hooks();
	}

	/**
	 * Return an instance of this class.
	 */
	public static function start() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Sets some globals for the plugin
	 */
	private function setup_globals() {
		/** Plugin globals ********************************************/
		$this->version       = '1.0.0-alpha';
		$this->domain        = 'next-template-packs';
		$this->name          = 'Next Template Packs';
		$this->file          = __FILE__;
		$this->basename      = plugin_basename( $this->file );
		$this->plugin_dir    = plugin_dir_path( $this->file );
		$this->plugin_url    = plugin_dir_url( $this->file );
		$this->lang_dir      = trailingslashit( $this->plugin_dir . 'languages' );
		$this->templates_dir = $this->plugin_dir . 'bp-templates';
		$this->templates_url = $this->plugin_url . 'bp-templates';
		$this->plugin_js     = trailingslashit( $this->plugin_url . 'js' );
		$this->plugin_css    = trailingslashit( $this->plugin_url . 'css' );

		/** Plugin config **********************************/
		$this->config = $this->network_check();
	}

	/**
	 * Checks BuddyPress version
	 */
	public function version_check() {
		// taking no risk
		if ( ! function_exists( 'bp_get_db_version' ) ) {
			return false;
		}

		return self::$bp_db_version_required <= bp_get_db_version();
	}

	/**
	 * Checks if current blog is the one where BuddyPress is activated
	 */
	public function network_check() {
		/*
		 * network_active : this plugin is activated on the network
		 * network_status : BuddyPress & this plugin share the same network status
		 */
		$config = array( 'network_active' => false, 'network_status' => true );
		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );

		// No Network plugins
		if ( empty( $network_plugins ) ) {
			return $config;
		}

		$check = array( buddypress()->basename, $this->basename );
		$network_active = array_diff( $check, array_keys( $network_plugins ) );

		if ( count( $network_active ) == 1 )
			$config['network_status'] = false;

		$config['network_active'] = isset( $network_plugins[ $this->basename ] );

		return $config;
	}

	/**
	 * Set hooks
	 */
	private function setup_hooks() {
		// This plugin && BuddyPress share the same config & BuddyPress version is ok
		if ( $this->version_check() && $this->config['network_status'] ) {
			// Page
			add_action( bp_core_admin_hook(), array( $this, 'admin_menu' )       );

			add_action( 'admin_head',         array( $this, 'admin_head' ),  999 );

			// Admin Tab
			add_action( 'bp_admin_tabs',      array( $this, 'admin_tab'  ), 1000 );

			add_action( 'bp_register_theme_packages', array( $this, 'register_theme_package' ) );

			add_action( 'bp_setup_theme', array( $this, 'load_features' ) );

		} else {
			add_action( $this->config['network_active'] ? 'network_admin_notices' : 'admin_notices', array( $this, 'admin_warning' ) );
		}

		// loads the languages..
		add_action( 'bp_init', array( $this, 'load_textdomain' ), 5 );

	}

	/**
	 * Set the plugin's page
	 */
	public function admin_menu() {
		$this->page  = bp_core_do_network_admin()  ? 'settings.php' : 'options-general.php';

		$hook = add_submenu_page(
			$this->page,
			__( 'Template Packs', 'next-template-packs' ),
			__( 'Templates Packs', 'next-template-packs' ),
			'manage_options',
			'template-packs',
			array( $this, 'admin_display' )
		);

		add_action( "load-$hook",       array( $this, 'admin_load'       ) );
		add_action( "admin_head-$hook", array( $this, 'modify_highlight' ) );
	}

	/**
	 * Hide submenu
	 */
	public function admin_head() {
		if ( empty( $this->page ) ) {
			return;
		}

		remove_submenu_page( $this->page, 'template-packs' );
	}

	/**
	 * Hook bp_admin_enqueue_scripts when on plugin's page
	 */
	public function admin_load() {
		add_action( 'bp_admin_enqueue_scripts', array( $this, 'inline_style' ) );

		if ( ! empty( $_POST['bp_tpl_pack']['dir'] ) ) {
			$tpl_dir = wp_unslash( $_POST['bp_tpl_pack']['dir'] );

			if ( ! is_dir( $tpl_dir ) || ! file_exists( $tpl_dir . '/buddypress-functions.php' ) ) {
				var_dump( $tpl_dir );
				wp_die();
			}

			if ( 'bp-legacy' === wp_basename( $tpl_dir ) ) {
				$tp_data = array(
					'id'      => 'legacy',
				);

				bp_delete_option( '_next_template_packs_package_data' );
			} else {
				$tp_data = get_file_data( $tpl_dir . '/buddypress-functions.php', array(
					'id'          => 'Template Pack ID',
					'name'        => 'Template Pack Name',
					'version'     => 'Version',
				) );

				if ( empty( $tp_data['id'] ) ) {
					wp_die();
				}

				if ( ! empty( $tp_data['support'] ) ) {
					$tp_data['support'] = array_map( 'trim', explode( ',', $tp_data['support'] ) );
				}

				$tp_data['dir'] = $tpl_dir;

				$tp_locations = apply_filters( 'next_template_packs_locations', array(
					'wp-content'          => WP_CONTENT_DIR . '/bp-templates',
					'next-template-packs' => $this->templates_dir,
					'buddypress'          => buddypress()->plugin_dir . 'bp-templates',
				) );

				$tp_urls = apply_filters( 'next_template_packs_url', array(
					'wp-content'          => content_url() . '/bp-templates',
					'next-template-packs' => $this->templates_url,
					'buddypress'          => buddypress()->plugin_url . 'bp-templates',
				) );

				$tp_data['url'] = trailingslashit( $tp_urls[ array_search( dirname( $tpl_dir ), $tp_locations ) ] ) . wp_basename( $tpl_dir );

				bp_update_option( '_next_template_packs_package_data', $tp_data );
			}

			bp_update_option( '_bp_theme_package_id', $tp_data['id'] );
		}
	}

	/**
	 * Add inline style
	 */
	public function inline_style() {
		wp_add_inline_style( 'bp-admin-common-css', '
			#bp-template-checker-outdated a,
			#bp-template-checker-outdated a:hover {
				text-decoration:none;
				border:none;
				color:#555;
			}

			#bp-template-checker-outdated tr.attention {
			 	color:#0073aa;
			 }
		' );
	}

	/**
	 * Modify highlighted menu
	 */
	public function modify_highlight() {
		global $plugin_page, $submenu_file;

		// This tweaks the Settings subnav menu to show only one BuddyPress menu item
		if ( $plugin_page == 'template-packs') {
			$submenu_file = 'bp-components';
		}
	}

	/**
	 * Add Admin tab
	 */
	public function admin_tab() {
		$class = false;

		if ( strpos( get_current_screen()->id, 'template-packs' ) !== false ) {
			$class = "nav-tab-active";
		}
		?>
		<a href="<?php echo esc_url( bp_get_admin_url( add_query_arg( array( 'page' => 'template-packs' ), 'admin.php' ) ) );?>" class="nav-tab <?php echo $class;?>" style="margin-left:-6px"><?php esc_html_e( 'Template Packs', 'next-template-packs' );?></a>
		<?php
	}

	public static function scandir( $directory ) {
		return scandir( $directory );
	}

	public static function get_template_pack_meta( $tp = null ) {
		$tp_meta = array();
		if ( ! empty( $tp->version ) ) {
			$tp_meta[] = sprintf( __( 'Version %s', 'next-template-packs' ), esc_html( $tp->version ) );
		}

		if ( ! empty( $tp->author ) ) {
			$tp_meta[] = sprintf( __( 'By %s' ), esc_html( $tp->author ) );
		}

		if ( ! empty( $tp->link ) ) {
			$tp_meta[] = sprintf( '<a href="%s">%s</a>',
				esc_url( $tp->link ),
				__( 'Visit Template Pack site', 'next-template-packs' )
			);
		}

		if ( empty( $tp_meta ) ) {
			return;
		}

		echo implode( ' | ', $tp_meta );
	}

	public static function translate_template_pack_data( $template_pack_data = null ) {
		if ( ! empty( $template_pack_data->text_domain ) ) {
			if ( ! empty( $template_pack_data->domain_path ) && ! empty( $template_pack_data->dir ) ) {
				$locale      = apply_filters( 'next_template_packs_locale', get_locale(), $template_pack_data->text_domain );
				$mofile      = $template_pack_data->dir . $template_pack_data->domain_path . sprintf( '%1$s-%2$s.mo', $template_pack_data->text_domain, $locale );
				$translation = load_textdomain( $template_pack_data->text_domain, $mofile );
			}

			if ( ! empty( $translation ) ) {
				foreach ( array( 'name', 'version', 'description', 'author', 'link' ) as $field ) {
					$template_pack_data->{$field} = translate( $template_pack_data->{$field}, $template_pack_data->text_domain );
				}
			}
		}

		// Sanitize fields
		$allowed_tags = $allowed_tags_in_links = array(
			'abbr'    => array( 'title' => true ),
			'acronym' => array( 'title' => true ),
			'code'    => true,
			'em'      => true,
			'strong'  => true,
		);
		$allowed_tags['a'] = array( 'href' => true, 'title' => true );

		// Sanitize
		$template_pack_data->name        = wp_kses( $template_pack_data->name,   $allowed_tags_in_links );
		$template_pack_data->author      = wp_kses( $template_pack_data->author, $allowed_tags );

		$template_pack_data->description = wptexturize( wp_kses( $template_pack_data->description, $allowed_tags ) );
		$template_pack_data->version     = wp_kses( $template_pack_data->version,     $allowed_tags );

		$template_pack_data->link        = esc_url( $template_pack_data->link );

		return $template_pack_data;
	}

	/**
	 * Display Admin
	 */
	public function admin_display() {
		$current_theme_package_id = bp_get_theme_package_id();
		?>
		<div class="wrap">
			<h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs( __( 'Templates', 'next-template-packs' ) ); ?></h2>

			<form action="" method="post" id="bp-admin-template-pack-form">
			<?php
			$tp_locations = apply_filters( 'next_template_packs_locations', array(
				'wp-content'          => WP_CONTENT_DIR . '/bp-templates',
				'next-template-packs' => $this->templates_dir,
				'buddypress'          => buddypress()->plugin_dir . 'bp-templates',
			) );

			$tp_urls = apply_filters( 'next_template_packs_url', array(
				'wp-content'          => content_url() . '/bp-templates',
				'next-template-packs' => $this->templates_url,
				'buddypress'          => buddypress()->plugin_url . 'bp-templates',
			) );

			$template_packs = array();

			foreach ( $tp_locations as $k_location => $tp_location ) {
				if ( ! is_dir( $tp_location ) ) {
					continue;
				}

				$template_pack_dirs = self::scandir( $tp_location );

				foreach ( $template_pack_dirs as $template_pack_dir ) {
					if ( 0 === strpos( $template_pack_dir, '.' ) ) {
						continue;
					}

					$tpdir      = new stdClass();
					$tpdir->dir = $tp_location . '/' . $template_pack_dir;
					$tpdir->url = trailingslashit( $tp_urls[ $k_location ] ) . wp_basename( $tpdir->dir );

					$template_packs[ $template_pack_dir ] = $tpdir;
				}
			}

			foreach ( $template_packs as $key => $template_pack ) {

				$tp_content = self::scandir( $template_pack->dir );

				if ( ! array_search( 'buddypress-functions.php', $tp_content ) ) {
					unset( $template_packs[ $key ] );
				} else {
					// Find the template pack information
					if ( 'bp-legacy' === $key ) {
						$tp_headers = array(
							'id'          => 'legacy',
							'name'        => 'BuddyPress Legacy',
							'version'     => bp_get_version(),
							'description' => 'The BuddyPress legacy template pack',
							'author'      => 'The BuddyPress community',
							'link'        => 'http://buddypress.org',
							'text_domain' => 'buddypress',
						);
					} else {
						$tp_headers = get_file_data( $template_pack->dir . '/buddypress-functions.php', array(
							'id'          => 'Template Pack ID',
							'name'        => 'Template Pack Name',
							'version'     => 'Version',
							'description' => 'Description',
							'author'      => 'Author',
							'link'        => 'Template Pack Link',
							'text_domain' => 'Text Domain',
							'domain_path' => 'Domain Path'
						) );
					}

					if ( empty( $tp_headers ) ) {
						unset( $template_packs[ $key ] );
					} else {
						// Add the header information to the template pack
						foreach ( $tp_headers as $k_header => $v_header ) {
							$template_packs[ $key ]->{$k_header} = $v_header;
						}

						$template_packs[ $key ] = self::translate_template_pack_data( $template_packs[ $key ] );
					}
				}
			}
			?>
				<ul class="subsubsub">
					<li>
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'template-packs' ), bp_get_admin_url( 'admin.php' ) ) ); ?>" class="current"><?php printf( _nx( 'All <span class="count">(%s)</span>',      'All <span class="count">(%s)</span>',      count( $template_packs ),         'plugins', 'buddypress' ), number_format_i18n( count( $template_packs ) ) ); ?></a> |
					</li>
				</ul>

				<table class="widefat fixed plugins" cellspacing="0">
					<thead>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb check-column">&nbsp;</th>
							<th scope="col" id="name" class="manage-column column-name" style="width: 190px;"><?php _e( 'Name', 'next-template-packs' ); ?></th>
							<th scope="col" id="description" class="manage-column column-description"><?php _e( 'Description', 'next-template-packs' ); ?></th>
						</tr>
					</thead>

					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-cb check-column">&nbsp;</th>
							<th scope="col" class="manage-column column-name" style="width: 190px;"><?php _e( 'Name', 'next-template-packs' ); ?></th>
							<th scope="col" class="manage-column column-description"><?php _e( 'Description', 'next-template-packs' ); ?></th>
						</tr>
					</tfoot>

					<tbody id="the-list">

						<?php if ( ! empty( $template_packs ) ) : ?>

							<?php foreach ( $template_packs as $tp_pack ) : ?>

								<?php  $class = ( $tp_pack->id === $current_theme_package_id ) ? 'active' : 'inactive'; ?>

								<tr id="<?php echo esc_attr( $tp_pack->id ); ?>" class="<?php echo esc_attr( $tp_pack->id ) . ' ' . esc_attr( $class ); ?>">
									<th scope="row">

										<input type="radio" id="bp_tpl_pack-<?php echo esc_attr( $tp_pack->id ); ?>" name="bp_tpl_pack[dir]" value="<?php echo esc_attr( $tp_pack->dir );?>"<?php checked( ( $tp_pack->id === $current_theme_package_id ) ); ?> />

									</th>
									<td class="plugin-title" style="width: 190px;">
										<span></span>
										<label for="bp_tpl_pack-<?php echo esc_attr( $tp_pack->id ); ?>">
											<strong><?php echo $tp_pack->name; ?></strong>
										</label>

										<div class="row-actions-visible">

										</div>
									</td>

									<td class="column-description desc">
										<div class="plugin-description">
											<p><?php echo $tp_pack->description; ?></p>
										</div>
										<div class="active second plugin-version-author-uri">
											<?php self::get_template_pack_meta( $tp_pack ) ; ?>
										</div>
									</td>
								</tr>

							<?php endforeach ?>

						<?php else : ?>

							<tr class="no-items">
								<td class="colspanchange" colspan="3"><?php esc_html_e( 'No template packs found ???', 'next-template-packs' ); ?></td>
							</tr>

						<?php endif; ?>

					</tbody>
				</table>

				<p class="submit clear">
					<input class="button-primary" type="submit" name="bp_tpl_pack[submit]" id="bp_tpl_pack-submit" value="<?php esc_attr_e( 'Save Settings', 'next-template-packs' ) ?>"/>
				</p>

				<?php wp_nonce_field( 'next-template-packs-setup' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Display a message to admin in case config is not as expected
	 */
	public function admin_warning() {
		$warnings = array();

		if( ! $this->version_check() ) {
			$warnings[] = sprintf( __( '%s requires at least version %s of BuddyPress.', 'next-template-packs' ), $this->name, '2.4.0-alpha' );
		}

		if ( bp_core_do_network_admin() && ! is_plugin_active_for_network( $this->basename ) ) {
			$warnings[] = sprintf( __( '%s and BuddyPress need to share the same network configuration.', 'next-template-packs' ), $this->name );
		}

		if ( ! empty( $warnings ) ) :
		?>
		<div id="message" class="error">
			<?php foreach ( $warnings as $warning ) : ?>
				<p><?php echo esc_html( $warning ) ; ?>
			<?php endforeach ; ?>
		</div>
		<?php
		endif;
	}

	public function register_theme_package() {
		if ( 'legacy' === bp_get_theme_package_id() ) {
			return;
		}

		$this->pack_data = bp_get_option( '_next_template_packs_package_data', array() );

		if ( empty( $this->pack_data ) ) {
			// User legacy ???
			wp_die( 'oops' );
		}

		bp_register_theme_package( $this->pack_data );
	}

	public function load_features() {
		if ( ! empty( $this->pack_data ) && file_exists( $this->pack_data['dir'] . '/bp-custom.php' ) ) {
			include( $this->pack_data['dir'] . '/bp-custom.php' );
		}
	}

	/**
	 * Loads the translation files
	 */
	public function load_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/next-template-packs/' . $mofile;

		// Look in global /wp-content/languages/next-template-packs folder
		load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/next-template-packs/languages/ folder
		load_textdomain( $this->domain, $mofile_local );
	}

}

endif;

// Let's start !
function next_template_packs() {
	return Next_Template_Packs::start();
}
add_action( 'bp_include', 'next_template_packs', 9 );
