<?php

require_once getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/functions.php';

function _bootstrap_template_pack() {

	if ( ! defined( 'BP_TESTS_DIR' ) ) {
		define( 'BP_TESTS_DIR', dirname( __FILE__ ) . '/../../../buddypress/tests/phpunit' );
	}

	if ( ! file_exists( BP_TESTS_DIR . '/bootstrap.php' ) )  {
		die( 'The BuddyPress Test suite could not be found' );
	}

	// Make sure BP is installed and loaded first
	require BP_TESTS_DIR . '/includes/loader.php';

	if ( ! defined( 'BP_TEMPLATE_PACK' ) || ! BP_TEMPLATE_PACK ) {
		echo "Data for your template pack are not set." . PHP_EOL;
	}

	echo "To test your own template pack, use -c path/toyour/phpunit.xml configuration file." . PHP_EOL;

	// load WP Idea Stream
	require dirname( __FILE__ ) . '/../../next-template-packs.php';
}
tests_add_filter( 'muplugins_loaded', '_bootstrap_template_pack' );

function _set_theme_package_id( $theme_package_id ) {
	if ( ! defined( 'BP_TEMPLATE_PACK' ) ) {
		return $theme_package_id;
	}

	$template_pack_data = explode( ';', BP_TEMPLATE_PACK );

	return reset( $template_pack_data );
}
tests_add_filter( 'bp_get_theme_package_id', '_set_theme_package_id' );

function _set_template_pack( $option ) {
	if ( ! defined( 'BP_TEMPLATE_PACK' ) ) {
		return $option;
	}

	$template_pack_data = explode( ';', BP_TEMPLATE_PACK );

	$path           = next_template_packs()->templates_dir;
	$symlinked_path = dirname( dirname( $path ) );
	$url            = str_replace( $symlinked_path, '', next_template_packs()->templates_url );

	return array(
		'id'      => $template_pack_data[0],
		'name'    => $template_pack_data[1],
		'version' => $template_pack_data[2],
		'dir'     => trailingslashit( $path ) . $template_pack_data[3],
		'url'     => trailingslashit( $url ) . $template_pack_data[3],
	);
}
tests_add_filter( 'pre_option__next_template_packs_package_data', '_set_template_pack' );

require getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit/includes/bootstrap.php';
require BP_TESTS_DIR . '/includes/testcase.php';

// include our testcase
require( 'testcase.php' );
