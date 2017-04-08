/* jshint node:true */
/* global module */
module.exports = function(grunt) {

		var WORKING_DIR = 'bp-templates/';
		var PATH_TO_SCSS = WORKING_DIR + 'bp-nouveau/sass/';
		var PATH_TO_CSS  = WORKING_DIR + 'bp-nouveau/css/';
		var PATH_TO_JS   = WORKING_DIR + 'bp-nouveau/js/';

		grunt.initConfig({
				pkg: grunt.file.readJSON('package.json'),

				files: {
						rtl:     PATH_TO_CSS   +  'buddypress-rtl.css',
						rtl:     PATH_TO_CSS   +  'embeds-activity-rtl.css',
						css:     PATH_TO_CSS   +  'buddypress.css',
						css:     PATH_TO_CSS   +  'embeds-activity.css',
						scss:    PATH_TO_SCSS  +  'buddypress.scss',
						js:      PATH_TO_JS    +  'buddypress.js'
				},

				// https://github.com/sass/node-sass  - config
				sass: {                              // Task
						dist: {                            // Target
								options: { // Target options
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

				rtlcss: {
						options: {
						//map: {inline: false},
										opts: {
												clean: true,
												processUrls: false,
												autoRename: false
										},
								saveUnmodified: true,
								sourcemap: 'none'
						},

						buildrtl: {

								core: {
								expand: true,
								cwd: WORKING_DIR,
								dest: 'bp-nouveau/css/',
								ext: '-rtl.css',
								src: '<%= files.css %>'
								},
								files:  {
												'<%= files.rtl %>' : '<%= files.css %>'
								}

						}
				},

				stylelint: {
					css: {
						options: {
							configFile: '.stylelintrc',
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
							configFile: '.stylelintrc',
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
								src: ['*.js']

							// file: 'members-list-module/members-list-module.js'

						}
				},
				watch: {
						//scripts: {
						//		files: ['assets/js/*.js'],
						//		tasks: ['concat'],
						//},
						sass: {
								files: [
										'bp-templates/bp-nouveau/sass/buddypress.scss',
										'bp-templates/bp-nouveau/common-styles/*.scss',
										'bp-templates/bp-nouveau/sass/*.scss',
										'Gruntfile.js',
										],
								//options: {
										// Reload reloads the watch config for any file change
										// not required if wanting to reload on gruntfile changes
										// only as it's a default to  reload if gruntfile changes
										//reload: true
								//},
								tasks: 'sass'
						}
						// uncomment to let 'watch' run on less files.
					/* less: {
								files: [
										MODULE_DIR + MODULE_NAME + preprocext,
										],
								tasks: 'less'
						} */
				}
});

		//grunt.loadNpmTasks('grunt-check-dependencies');

		grunt.loadNpmTasks('grunt-contrib-watch');
		//grunt.loadNpmTasks('grunt-contrib-sass');
		grunt.loadNpmTasks('grunt-sass');
		//grunt.loadNpmTasks('grunt-contrib-less');
		//grunt.loadNpmTasks('grunt-scss-lint');
		grunt.loadNpmTasks('grunt-rtlcss');
		grunt.loadNpmTasks('grunt-contrib-jshint');
		grunt.loadNpmTasks('grunt-postcss');
		grunt.loadNpmTasks('grunt-stylelint');

		// Lint & Build rtl css
		grunt.registerTask('commit', ['jshint', 'stylelint', 'rtlcss']);

		// Default task(s).
		// ?
		grunt.registerTask('default', ['uglify', 'postcss']);

};
