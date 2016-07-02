<?php

/**
 * How to
 *
 * 1/ Define the WP_DEVELOP_DIR env var from your ~/.bash_profile file eg:
 * export WP_DEVELOP_DIR="/Users/path/to/wordpress"
 *
 * 2/ run the command
 * php bp-makepot.php bp-templatepack /absolute/path/to/template/pack/folder /absolute/path/to/template/pack/folder/tempate-pack-name.pot
 *
 * For the included BP Nouveau Template pack, the command is
 * php bp-makepot.php bp-templatepack ~/pathtoplugin/next-template-packs/bp-templates/bp-nouveau ~/pathtoplugin/next-template-packs/bp-templates/bp-nouveau/languages/bp-nouveau.pot
 */

require_once getenv( 'WP_DEVELOP_DIR' ) . '/tools/i18n/not-gettexted.php';
require_once dirname( __FILE__ ) . '/pot-ext-meta.php';
require_once getenv( 'WP_DEVELOP_DIR' ) . '/tools/i18n/extract.php';

if ( !defined( 'STDERR' ) ) {
	define( 'STDERR', fopen( 'php://stderr', 'w' ) );
}


class BPMakePOT {
	private $max_header_lines = 30;

	public $projects = array(
		'bp-templatepack-manager',
		'bp-templatepack',
		'wp-plugin',
	);

	public $rules = array(
		'_' => array('string'),
		'__' => array('string'),
		'_e' => array('string'),
		'_c' => array('string'),
		'_n' => array('singular', 'plural'),
		'_n_noop' => array('singular', 'plural'),
		'_nc' => array('singular', 'plural'),
		'__ngettext' => array('singular', 'plural'),
		'__ngettext_noop' => array('singular', 'plural'),
		'_x' => array('string', 'context'),
		'_ex' => array('string', 'context'),
		'_nx' => array('singular', 'plural', null, 'context'),
		'_nx_noop' => array('singular', 'plural', 'context'),
		'_n_js' => array('singular', 'plural'),
		'_nx_js' => array('singular', 'plural', 'context'),
		'esc_attr__' => array('string'),
		'esc_html__' => array('string'),
		'esc_attr_e' => array('string'),
		'esc_html_e' => array('string'),
		'esc_attr_x' => array('string', 'context'),
		'esc_html_x' => array('string', 'context'),
		'comments_number_link' => array('string', 'singular', 'plural'),
	);

	private $ms_files = array(
		'ms-.*', '.*/ms-.*', '.*/my-.*', 'wp-activate\.php', 'wp-signup\.php',
		'wp-admin/network\.php', 'wp-admin/network/.*\.php', 'wp-admin/includes/ms\.php',
		'wp-admin/includes/class-wp-ms.*', 'wp-admin/includes/network\.php',
	);

	private $temp_files = array();

	public $meta = array(
		'default' => array(
			'from-code' => 'utf-8',
			'msgid-bugs-address' => 'https://make.wordpress.org/polyglots/',
			'language' => 'php',
			'add-comments' => 'translators',
			'comments' => "Copyright (C) {year} {package-name}\nThis file is distributed under the same license as the {package-name} package.",
		),
		'bp-templatepack-manager' => array(
			'description' => 'Translation of the WordPress plugin {name} {version} by {author}',
			'msgid-bugs-address' => 'https://github.com/buddypress/next-template-packs/issues',
			'copyright-holder' => '{author}',
			'package-name' => '{name}',
			'package-version' => '{version}',
		),
		'bp-templatepack' => array(
			'description'      => 'Translation of the BuddyPress Template Pack {name} {version} by {author}',
			'copyright-holder' => '{author}',
			'package-name'     => '{name}',
			'package-version'  => '{version}',
		),
		'wp-plugin' => array(
			'description' => 'Translation of the WordPress plugin {name} {version} by {author}',
			'msgid-bugs-address' => 'https://wordpress.org/support/plugin/{slug}',
			'copyright-holder' => '{author}',
			'package-name' => '{name}',
			'package-version' => '{version}',
		),
	);

	public function __construct($deprecated = true) {
		$this->extractor = new StringExtractor( $this->rules );
	}

	public function __destruct() {
		foreach ( $this->temp_files as $temp_file )
			unlink( $temp_file );
	}

	private function tempnam( $file ) {
		$tempnam = tempnam( sys_get_temp_dir(), $file );
		$this->temp_files[] = $tempnam;
		return $tempnam;
	}

	private function realpath_missing($path) {
		return realpath(dirname($path)).DIRECTORY_SEPARATOR.basename($path);
	}

	private function xgettext($project, $dir, $output_file, $placeholders = array(), $excludes = array(), $includes = array()) {
		$meta = array_merge( $this->meta['default'], $this->meta[$project] );
		$placeholders = array_merge( $meta, $placeholders );
		$meta['output'] = $this->realpath_missing( $output_file );
		$placeholders['year'] = date( 'Y' );
		$placeholder_keys = array_map( create_function( '$x', 'return "{".$x."}";' ), array_keys( $placeholders ) );
		$placeholder_values = array_values( $placeholders );
		foreach($meta as $key => $value) {
			$meta[$key] = str_replace($placeholder_keys, $placeholder_values, $value);
		}

		$originals = $this->extractor->extract_from_directory( $dir, $excludes, $includes );
		$pot = new PO;
		$pot->entries = $originals->entries;

		$pot->set_header( 'Project-Id-Version', $meta['package-name'].' '.$meta['package-version'] );
		$pot->set_header( 'Report-Msgid-Bugs-To', $meta['msgid-bugs-address'] );
		$pot->set_header( 'POT-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
		$pot->set_header( 'MIME-Version', '1.0' );
		$pot->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
		$pot->set_header( 'Content-Transfer-Encoding', '8bit' );
		$pot->set_header( 'PO-Revision-Date', date( 'Y') . '-MO-DA HO:MI+ZONE' );
		$pot->set_header( 'Last-Translator', 'FULL NAME <EMAIL@ADDRESS>' );
		$pot->set_header( 'Language-Team', 'LANGUAGE <LL@li.org>' );
		$pot->set_comment_before_headers( $meta['comments'] );
		$pot->export_to_file( $output_file );
		return true;
	}

	public function bp_templatepack_manager( $dir, $output, $slug = null ) {
		return $this->wp_plugin( $dir, $output, $slug, array(
			'project' => 'bp-templatepack-manager', 'output' => $output,
			'includes' => array(),
			'excludes' => array( 'bp-templates/.*', 'node_modules/.*', 'tools/.*', 'tests/.*' )
		) );
	}

	function bp_templatepack($dir, $output, $slug = null) {
		$placeholders = array();
		// guess plugin slug
		if (is_null($slug)) {
			$slug = $this->guess_plugin_slug($dir);
		}

		$plugins_dir = @opendir( $dir );
		$plugin_files = array();
		if ( $plugins_dir ) {
			while ( ( $file = readdir( $plugins_dir ) ) !== false ) {
				if ( '.' === substr( $file, 0, 1 ) ) {
					continue;
				}

				if ( '.php' === substr( $file, -4 ) ) {
					$plugin_files[] = $file;
				}
			}
			closedir( $plugins_dir );
		}

		if ( empty( $plugin_files ) ) {
			return false;
		}

		$main_file = '';
		foreach ( $plugin_files as $plugin_file ) {
			if ( ! is_readable( "$dir/$plugin_file" ) ) {
				continue;
			}

			$source = $this->get_first_lines( "$dir/$plugin_file", $this->max_header_lines );

			// Stop when we find a file with a plugin name header in it.
			if ( $this->get_addon_header( 'Template Pack ID', $source ) != false ) {
				$main_file = "$dir/$plugin_file";
				break;
			}
		}

		if ( empty( $main_file ) ) {
			return false;
		}

		$placeholders['version'] = $this->get_addon_header('Version', $source);
		$placeholders['author'] = $this->get_addon_header('Author', $source);
		$placeholders['name'] = $this->get_addon_header('Template Pack Name', $source);
		$placeholders['supports'] = $this->get_addon_header('Template Pack Supports', $source);
		$placeholders['slug'] = $slug;

		$output = is_null($output)? "$slug.pot" : $output;
		$res = $this->xgettext('bp-templatepack', $dir, $output, $placeholders);
		if (!$res) return false;
		$potextmeta = new PotExtMeta;
		$res = $potextmeta->append($main_file, $output);
		/* Adding non-gettexted strings can repeat some phrases */
		$output_shell = escapeshellarg($output);
		system("msguniq $output_shell -o $output_shell");
		return $res;
	}

	public function get_first_lines($filename, $lines = 30) {
		$extf = fopen($filename, 'r');
		if (!$extf) return false;
		$first_lines = '';
		foreach(range(1, $lines) as $x) {
			$line = fgets($extf);
			if (feof($extf)) break;
			if (false === $line) {
				return false;
			}
			$first_lines .= $line;
		}

		// PHP will close file handle, but we are good citizens.
		fclose( $extf );

		// Make sure we catch CR-only line endings.
		$first_lines = str_replace( "\r", "\n", $first_lines );

		return $first_lines;
	}

	public function get_addon_header($header, &$source) {
		/*
		 * A few things this needs to handle:
		 * - 'Header: Value\n'
		 * - '// Header: Value'
		 * - '/* Header: Value * /'
		 * - '<?php // Header: Value ?>'
		 * - '<?php /* Header: Value * / $foo='bar'; ?>'
		 */
		if ( preg_match( '/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . preg_quote( $header, '/' ) . ':(.*)$/mi', $source, $matches ) ) {
			return $this->_cleanup_header_comment( $matches[1] );
		} else {
			return false;
		}
	}

	/**
	 * Removes any trailing closing comment / PHP tags from the header value
	 */
	private function _cleanup_header_comment( $str ) {
		return trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $str ) );
	}

	private function guess_plugin_slug($dir) {
		if ('trunk' == basename($dir)) {
			$slug = basename(dirname($dir));
		} elseif (in_array(basename(dirname($dir)), array('branches', 'tags'))) {
			$slug = basename(dirname(dirname($dir)));
		} else {
			$slug = basename($dir);
		}
		return $slug;
	}

	public function wp_plugin( $dir, $output, $slug = null, $args = array() ) {
		$defaults = array(
			'excludes' => array(),
			'includes' => array(),
		);
		$args = array_merge( $defaults, $args );
		$placeholders = array();
		// guess plugin slug
		if (is_null($slug)) {
			$slug = $this->guess_plugin_slug($dir);
		}

		$plugins_dir = @opendir( $dir );
		$plugin_files = array();
		if ( $plugins_dir ) {
			while ( ( $file = readdir( $plugins_dir ) ) !== false ) {
				if ( '.' === substr( $file, 0, 1 ) ) {
					continue;
				}

				if ( '.php' === substr( $file, -4 ) ) {
					$plugin_files[] = $file;
				}
			}
			closedir( $plugins_dir );
		}

		if ( empty( $plugin_files ) ) {
			return false;
		}

		$main_file = '';
		foreach ( $plugin_files as $plugin_file ) {
			if ( ! is_readable( "$dir/$plugin_file" ) ) {
				continue;
			}

			$source = $this->get_first_lines( "$dir/$plugin_file", $this->max_header_lines );

			// Stop when we find a file with a plugin name header in it.
			if ( $this->get_addon_header( 'Plugin Name', $source ) != false ) {
				$main_file = "$dir/$plugin_file";
				break;
			}
		}

		if ( empty( $main_file ) ) {
			return false;
		}

		$placeholders['version'] = $this->get_addon_header('Version', $source);
		$placeholders['author'] = $this->get_addon_header('Author', $source);
		$placeholders['name'] = $this->get_addon_header('Plugin Name', $source);
		$placeholders['slug'] = $slug;

		$output = is_null($output)? "$slug.pot" : $output;
		$res = $this->xgettext( 'wp-plugin', $dir, $output, $placeholders, $args['excludes'], $args['includes'] );
		if (!$res) return false;
		$potextmeta = new PotExtMeta;
		$res = $potextmeta->append($main_file, $output);
		/* Adding non-gettexted strings can repeat some phrases */
		$output_shell = escapeshellarg($output);
		system("msguniq $output_shell -o $output_shell");
		return $res;
	}
}

// run the CLI only if the file
// wasn't included
$included_files = get_included_files();
if ($included_files[0] == __FILE__) {
	$makepot = new BPMakePOT;

	if ((3 == count($argv) || 4 == count($argv)) && in_array($method = str_replace('-', '_', $argv[1]), get_class_methods($makepot))) {
		if ( isset( $argv[3] ) ) {
			$folder = realpath( str_replace( '/' . basename( $argv[3] ), '', $argv[3] ) );
			$file   = $folder . '/' . basename( $argv[3] );
		}

		$res = call_user_func(array($makepot, $method), realpath($argv[2]), isset($file)? $file : null);
		if (false === $res) {
			fwrite(STDERR, "Couldn't generate POT file!\n");
		} else {
			fwrite(STDERR, "POT file successfuly generated!\n");
		}
	} else {
		$usage  = "Usage: php makepot.php PROJECT DIRECTORY [OUTPUT]\n\n";
		$usage .= "Generate POT file from the files in DIRECTORY [OUTPUT]\n";
		$usage .= "Available projects: ".implode(', ', $makepot->projects)."\n";
		fwrite(STDERR, $usage);
		exit(1);
	}
}
