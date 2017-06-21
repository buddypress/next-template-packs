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

add_action( 'bp_register_theme_packages', function() {
	bp_register_theme_package( array(
		'id'      => 'nouveau',
		'name'    => __( 'BuddyPress Nouveau', 'buddypress' ),
		'version' => bp_get_version(),
		'dir'     => plugin_dir_path( __FILE__ ) . 'bp-templates/bp-nouveau/',
		'url'     => plugin_dir_url( __FILE__ ) . 'bp-templates/bp-nouveau/',
	) );
} );
