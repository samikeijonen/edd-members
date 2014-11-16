module.exports = function(grunt) {

// Load multiple grunt tasks using globbing patterns
require('load-grunt-tasks')(grunt);

// Project configuration.
grunt.initConfig({
  pkg: grunt.file.readJSON('package.json'),

    makepot: {
      target: {
        options: {
          domainPath: '/languages/',           // Where to save the POT file.
          exclude: ['build/.*'],               // Exlude build folder.
          potFilename: 'edd-members.pot',      // Name of the POT file.
          type: 'wp-plugin',                   // Type of project (wp-plugin or wp-theme).
          updateTimestamp: false,              // Whether the POT-Creation-Date should be updated without other changes.
        }
      }
    },

    exec: {
      txpull: { // Pull Transifex translation - grunt exec:txpull
        cmd: 'tx pull -a --minimum-perc=100' // Change the percentage with --minimum-perc=yourvalue
      },
      txpush_s: { // Push pot to Transifex - grunt exec:txpush_s
        cmd: 'tx push -s'
      },
    },

	dirs: {
		lang: 'languages',
	},

    potomo: {
      dist: {
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
	},
	
	// Minify files
	uglify: {
		admin: {
			files: {
				'assets/js/admin.min.js': ['assets/js/admin.js']
			}
		}
	},

    // Clean up build directory
    clean: {
      main: ['build/<%= pkg.name %>']
    },

    // Copy the theme into the build directory
    copy: {
      main: {
        src:  [
          '**',
          '!node_modules/**',
          '!build/**',
          '!.git/**',
          '!Gruntfile.js',
          '!package.json',
          '!.gitignore',
          '!.gitmodules',
          '!.tx/**',
          '!**/Gruntfile.js',
          '!**/package.json',
          '!**/*~'
        ],
        dest: 'build/<%= pkg.name %>/'
      }
    },

    // Compress build directory into <name>.zip and <name>-<version>.zip
    compress: {
      main: {
        options: {
          mode: 'zip',
          archive: './build/<%= pkg.name %>_v<%= pkg.version %>.zip'
        },
        expand: true,
        cwd: 'build/<%= pkg.name %>/',
        src: ['**/*'],
        dest: '<%= pkg.name %>/'
      }
    },

});

// Default task.
grunt.registerTask( 'default', [ 'makepot' ], [ 'uglify' ] );

// Build task(s).
grunt.registerTask( 'build', [ 'clean', 'copy', 'compress' ] );

};