/* jshint node:true */
/* global module */
module.exports = function(grunt) {
	var WORKING_DIR = 'bp-templates/',

		BP_NTP_CSS = [
			'**/*.css'
		],

		BP_NTP_EXCLUDED_CSS = [
			'!**/*-rtl.css'
		],

		BP_NTP_JS = [
			'**/*.js'
		],

		stylelintConfigCss  = require('stylelint-config-wordpress/index.js'),
		stylelintConfigScss = require('stylelint-config-wordpress/scss.js');

	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

	grunt.initConfig( {
		pkg: grunt.file.readJSON('package.json'),

		checkDependencies: {
			options: {
				packageManager: 'npm'
			},
			src: {}
		},
		jshint: {
			options: grunt.file.readJSON( '.jshintrc' ),
			grunt: {
				src: ['Gruntfile.js']
			},
			core: {
				expand: true,
				cwd: WORKING_DIR,
				src: BP_NTP_JS
			}
		},
		rtlcss: {
			options: {
				opts: {
					processUrls: false,
					autoRename: false,
					clean: true
				},
				saveUnmodified: false
			},
			buildrtl: {
				expand: true,
				cwd: WORKING_DIR,
				dest: WORKING_DIR,
				extDot: 'last',
				ext: '-rtl.css',
				src: BP_NTP_CSS.concat( BP_NTP_EXCLUDED_CSS )
			}
		},
		sass: {
			dist: {
				options: {
					outputStyle: 'expanded',
					unixNewlines: true,
					indentType: 'tab',
					indentWidth: '1',
					indentedSyntax: false
				},
				cwd: WORKING_DIR,
				extDot: 'last',
				expand: true,
				ext: '.css',
				flatten: true,
				src: ['bp-nouveau/sass/buddypress.scss'],
				dest: WORKING_DIR + 'bp-nouveau/css/'
			}
		},
		stylelint: {
			css: {
				options: {
					config: stylelintConfigCss,
					format: 'css'
				},
				expand: true,
				cwd: WORKING_DIR,
				src: [
					'bp-nouveau/css/*.css',
					'!bp-nouveau/css/buddypress.css',
					'!bp-nouveau/css/buddypress-rtl.css'
				]
			//ignoreFiles: 'bp-nouveau/css/*-rtl.css'
			},
			scss: {
				options: {
					config: stylelintConfigScss,
					format: 'scss'
				},
				expand: true,
				cwd: WORKING_DIR,
				src: [
					'bp-nouveau/sass/buddypress.scss',
					'common-styles/*.scss',
					'!common-styles/_bp-mixins.scss',
					'!common-styles/_bp-variables.scss'
				]
			}
		},
		watch: {
			config: {
				files: 'Gruntfile.js',
				tasks: 'jshint:grunt'
			},
			sass: {
				files: [
					'bp-templates/bp-nouveau/sass/buddypress.scss',
					'bp-templates/bp-nouveau/common-styles/*.scss',
					'bp-templates/bp-nouveau/sass/*.scss'
					],
				tasks: 'sass'
			}
		}
	});

	// Lint CSS & JavaScript
	grunt.registerTask( 'lint', ['stylelint', 'jshint' ] );

	// Build CSS & JavaScript
	grunt.registerTask( 'build', [ 'sass', 'rtlcss' ] );

	// Default task(s).
	grunt.registerTask( 'default', 'Runs the default Grunt tasks', [ 'checkDependencies', 'lint', 'build' ] );

	// Travis CI Tasks.
	grunt.registerTask( 'travis:grunt', 'Runs the Grunt build tasks.', [ 'lint', 'build' ] );
	grunt.registerTask( 'travis:phpunit', 'Runs the PHPUnit tasks.',[ 'build', 'phpunit' ] );
};
