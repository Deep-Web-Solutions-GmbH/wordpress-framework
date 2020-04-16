module.exports = function( grunt ) {
    'use strict';

    // Load all grunt tasks matching the `grunt-*` pattern
    require( 'load-grunt-tasks' )( grunt );

    // Show elapsed time
    require( 'time-grunt' )( grunt );

    // Project configuration
    grunt.initConfig({
        package: grunt.file.readJSON( 'package.json' ),
        dirs: {
            lang: 'src/languages'
        },
        jshint: {
            files: ['Gruntfile.js', 'src/**/*.js', 'test/**/*.js'],
            options: {
                globals: {
                    jQuery: true
                }
            }
        },
        watch: {
            files: ['<%= jshint.files %>'],
            tasks: ['jshint']
        },
        makepot: {
            framework: {
                options: {
                    cwd: 'src',
                    domainPath: 'languages',
                    exclude: [],
                    potFilename: 'dws-wp-framework.pot',
                    mainFile: 'index.php',
                    potHeaders: {
                        'report-msgid-bugs-to': 'https://github.com/Deep-Web-Solutions-GmbH/wordpress-framework-core/issues',
                        'project-id-version': '<%= package.title %> <%= package.version %>'
                    },
                    processPot: function( pot ) {
                        delete pot.headers['x-generator'];
                        return pot;
                    },
                    type: 'wp-plugin',
                    updateTimestamp: false,
                    updatePoFiles: true
                }
            }
        },
        potomo: {
            framework: {
                options: {
                    poDel: false
                },
                files: [{
                    expand: true,
                    cwd: '<%= dirs.lang %>',
                    src: ['*.po'],
                    dest: '<%= dirs.lang %>',
                    ext: '.mo',
                    nonull: true
                }]
            }
        }
    });

    grunt.registerTask( 'default', ['jshint', 'makepot', 'potomo'] );
}