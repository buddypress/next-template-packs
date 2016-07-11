<?php
/**
 * Adds a BuddyPress Admin Tab to manage BuddyPress template packs
 *
 *
 * @package   Next Template Packs
 * @author    The BuddyPress Community
 * @license   GPL-2.0+
 * @link      https://buddypress.org
 *
 * @buddypress-plugin
 * Plugin Name:       Next Template Packs
 * Plugin URI:        https://github.com/buddypress/next-template-packs
 * Description:       Adds a BuddyPress Admin Tab to manage BuddyPress template pack
 * Version:           1.0.0-alpha
 * Author:            The BuddyPress Community
 * Author URI:        https://buddypress.org
 * Text Domain:       next-template-packs
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages/
 * GitHub Plugin URI: https://github.com/buddypress/next-template-packs
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
	public static $bp_version_required = '2.7-alpha';

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

		/** Plugin config **********************************/
		$this->config = $this->network_check();
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
		if ( ! empty( $_POST['bp_tpl_pack']['dir'] ) ) {
			$tpl_dir = wp_unslash( $_POST['bp_tpl_pack']['dir'] );

			if ( ! is_dir( $tpl_dir ) || ! file_exists( $tpl_dir . '/buddypress-functions.php' ) ) {
				return false;
			}

			if ( 'bp-legacy' === wp_basename( $tpl_dir ) ) {
				$tp_data = array(
					'id'      => 'legacy',
				);

				bp_delete_option( '_next_template_packs_package_data' );
			} else {
				$tp_data = get_file_data( $tpl_dir . '/buddypress-functions.php', array(
					'id'                  => 'Template Pack ID',
					'name'                => 'Template Pack Name',
					'version'             => 'Version',
					'wp_required_version' => 'WP required version',
					'bp_required_version' => 'BP required version'
				) );

				if ( empty( $tp_data['id'] ) ) {
					return false;
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
		<a href="<?php echo esc_url( bp_get_admin_url( add_query_arg( array( 'page' => 'template-packs' ), 'admin.php' ) ) );?>" class="nav-tab <?php echo $class;?>"><?php esc_html_e( 'Template Packs', 'next-template-packs' );?></a>
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
			$tp_meta[] = sprintf( __( 'By %s', 'next-template-packs' ), esc_html( $tp->author ) );
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

	public static function get_template_pack_supports( $tp = null ) {
		if ( empty( $tp->supports ) ) {
			esc_html_e( 'No informations about supports provided.', 'next-template-packs' );
		}

		echo $tp->supports;
	}

	public static function translate_template_pack_data( $template_pack_data = null ) {
		if ( ! empty( $template_pack_data->text_domain ) ) {
			if ( ! empty( $template_pack_data->domain_path ) && ! empty( $template_pack_data->dir ) ) {
				$locale      = apply_filters( 'next_template_packs_locale', get_locale(), $template_pack_data->text_domain );
				$mofile      = $template_pack_data->dir . $template_pack_data->domain_path . sprintf( '%1$s-%2$s.mo', $template_pack_data->text_domain, $locale );
				$translation = load_textdomain( $template_pack_data->text_domain, $mofile );
			}

			if ( ! empty( $translation ) ) {
				foreach ( array( 'name', 'version', 'description', 'author', 'link', 'supports' ) as $field ) {
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

		$template_pack_data->description         = wptexturize( wp_kses( $template_pack_data->description, $allowed_tags ) );
		$template_pack_data->version             = wp_kses( $template_pack_data->version, $allowed_tags );

		if ( isset( $template_pack_data->wp_required_version ) ) {
			$template_pack_data->wp_required_version = esc_html( $template_pack_data->wp_required_version );
		}

		if ( isset( $template_pack_data->bp_required_version ) ) {
			$template_pack_data->bp_required_version = esc_html( $template_pack_data->bp_required_version );
		}

		$template_pack_data->link        = esc_url( $template_pack_data->link );
		$template_pack_data->supports    = esc_html( $template_pack_data->supports );

		return $template_pack_data;
	}

	public function check_versions( $versions = array() ) {
		$retval = true;
		if ( empty( $versions ) ) {
			return $retval;
		}

		if ( ! empty( $versions['bp_required_version'] ) && version_compare( bp_get_version(), $versions['bp_required_version'], '<' ) ) {
			$retval = false;
		}

		if ( ! empty( $versions['wp_required_version'] ) && version_compare( bp_get_major_wp_version(), $versions['wp_required_version'], '<' ) ) {
			$retval = false;
		}

		return $retval;
	}

	/**
	 * Display Admin
	 */
	public function admin_display() {
		$current_theme_package_id = bp_get_theme_package_id();
		?>

		<style type="text/css">
			.plugins tbody th.check-column input[type="radio"] {
				margin-top: 4px;
			}
		</style>

		<div class="wrap">
			<h1><?php esc_html_e( 'BuddyPress Settings', 'next-template-packs' ); ?></h1>
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
							'supports'    => 'activity, blogs, friends, groups, messages, notifications, retired-forums, settings, xprofile',
							'text_domain' => 'buddypress',
						);
					} else {
						$tp_headers = get_file_data( $template_pack->dir . '/buddypress-functions.php', array(
							'id'                  => 'Template Pack ID',
							'name'                => 'Template Pack Name',
							'version'             => 'Version',
							'wp_required_version' => 'WP required version',
							'bp_required_version' => 'BP required version',
							'description'         => 'Description',
							'author'              => 'Author',
							'link'                => 'Template Pack Link',
							'supports'            => 'Template Pack Supports',
							'text_domain'         => 'Text Domain',
							'domain_path'         => 'Domain Path'
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
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'template-packs' ), bp_get_admin_url( 'admin.php' ) ) ); ?>" class="current"><?php printf( _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', count( $template_packs ), 'plugins', 'next-template-packs' ), number_format_i18n( count( $template_packs ) ) ); ?></a> |
					</li>
				</ul>

				<table class="widefat fixed plugins" cellspacing="0">
					<thead>
						<tr>
							<td scope="col" id="cb" class="manage-column column-cb check-column">&nbsp;</td>
							<th scope="col" id="name" class="manage-column column-name" style="width: 190px;"><?php _e( 'Name', 'next-template-packs' ); ?></th>
							<th scope="col" id="description" class="manage-column column-description"><?php _e( 'Description', 'next-template-packs' ); ?></th>
							<th scope="col" id="supports" class="manage-column column-supports" style="width: 190px;"><?php _e( 'Supports', 'next-template-packs' ); ?></th>
						</tr>
					</thead>

					<tfoot>
						<tr>
							<td scope="col" class="manage-column column-cb check-column">&nbsp;</td>
							<th scope="col" class="manage-column column-name" style="width: 190px;"><?php _e( 'Name', 'next-template-packs' ); ?></th>
							<th scope="col" class="manage-column column-description"><?php _e( 'Description', 'next-template-packs' ); ?></th>
							<th scope="col" class="manage-column column-supports" style="width: 190px;"><?php _e( 'Supports', 'next-template-packs' ); ?></th>
						</tr>
					</tfoot>

					<tbody id="the-list">

						<?php if ( ! empty( $template_packs ) ) : ?>

							<?php foreach ( $template_packs as $tp_pack ) : ?>

								<?php
									$class               = ( $tp_pack->id === $current_theme_package_id ) ? 'active' : 'inactive';
									$config_is_supported = true;
									$versions             = array_intersect_key( (array) $tp_pack, array( 'wp_required_version' => '', 'bp_required_version' => '' ) );
									$config_is_supported = $this->check_versions( $versions );
								?>

								<tr id="<?php echo esc_attr( $tp_pack->id ); ?>" class="<?php echo esc_attr( $tp_pack->id ) . ' ' . esc_attr( $class ); ?>">
									<th scope="row" class="check-column">

										<?php if ( $config_is_supported ) : ?>
											<input type="radio" id="bp_tpl_pack-<?php echo esc_attr( $tp_pack->id ); ?>" name="bp_tpl_pack[dir]" value="<?php echo esc_attr( $tp_pack->dir );?>"<?php checked( ( $tp_pack->id === $current_theme_package_id ) ); ?> />
										<?php endif ;?>

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

										<?php if ( ! $config_is_supported ) : ?>
											<div class="attention">
												<?php
													$warnings = array();
													if ( ! empty( $tp_pack->wp_required_version ) ) {
														$warnings[] = sprintf( esc_html__( 'WordPress required version is %s.', 'next-template-packs' ), $tp_pack->wp_required_version );
													}

													if ( ! empty( $tp_pack->bp_required_version ) ) {
														$warnings[] = sprintf( esc_html__( 'BuddyPress required version is %s.', 'next-template-packs' ), $tp_pack->bp_required_version );
													}

													echo join( ' ', $warnings );
												;?>
											</div>
										<?php endif ; ?>
									</td>

									<td class="column-supports desc">
										<p class="description">
											<?php self::get_template_pack_supports( $tp_pack ) ;?>
										</p>
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

	public function register_theme_package() {
		if ( 'legacy' === bp_get_theme_package_id() ) {
			return;
		}

		$this->pack_data = bp_get_option( '_next_template_packs_package_data', array() );

		if ( ! empty( $this->pack_data['dir'] ) && is_dir( $this->pack_data['dir'] ) ) {
			bp_register_theme_package( $this->pack_data );
		}
	}

	/**
	 * Display a message to admin in case config is not as expected
	 */
	public function admin_warning() {
		$warnings = array();

		if( ! $this->version_check() ) {
			$warnings[] = sprintf( __( '%s requires at least version %s of BuddyPress.', 'next-template-packs' ), $this->name, self::$bp_version_required );
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

	/**
	 * Checks BuddyPress version
	 */
	public function version_check() {
		// taking no risk
		if ( ! function_exists( 'bp_get_version' ) ) {
			return false;
		}

		return version_compare( bp_get_version(), self::$bp_version_required, '>=' );
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
