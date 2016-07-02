/* jshint node:true */
/* global module */
module.exports = function( grunt ) {
	require( 'matchdep' ).filterDev( ['grunt-*', '!grunt-legacy-util'] ).forEach( grunt.loadNpmTasks );
	grunt.util = require( 'grunt-legacy-util' );

	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		checktextdomain: {
			options: {
				correct_domain: false,
				text_domain: grunt.option( 'template_pack' ) ? grunt.option( 'template_pack' ) : 'next-template-packs',
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'_n:1,2,4d',
					'_ex:1,2c,3d',
					'_nx:1,2,4c,5d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: grunt.option( 'template_pack' ) ? [ '**/' + grunt.option( 'template_pack' ) + '/**/*.php', '!**/testcases/*'] : ['**/*.php', '!**/node_modules/**', '!**/tests/**', '!**/tools/**', '!**/bp-templates/**'],
				expand: true
			}
		},
		exec: {
			generate_template_pack_pot: {
				command: 'php tools/i18n/bp-makepot.php bp-templatepack bp-templates/' + grunt.option( 'template_pack' ) + ' bp-templates/' + grunt.option( 'template_pack' ) + '/languages/' + grunt.option( 'template_pack' ) + '.pot',
				stdout: false
			},
			generate_manager_pot: {
				command: 'php tools/i18n/bp-makepot.php bp-templatepack-manager ../next-template-packs languages/next-template-packs.pot',
				stdout: false
			},
		},
	} );

	/**
	 * Register tasks.
	 */
	grunt.registerTask( 'makepot', 'Generating the pot file...', function( n ) {
		grunt.task.run( 'checktextdomain' );

		if ( grunt.option( 'template_pack' ) ) {
			grunt.task.run( 'exec:generate_template_pack_pot' );
		} else {
			grunt.task.run( 'exec:generate_manager_pot' );
		}
	} );
};
