module.exports = function(grunt) {

	grunt.initConfig({


		// Pospojuj JS
		concat: {
			js: {
				options: {
					separator: ';'
				},
				src: [

				
				// Retina.js
				'bower_components/retina.js/dist/retina.js',
				

				// Swiper.js
				'bower_components/swiper/dist/js/swiper.jquery.js',
				

				// Fancybox.js
				'../www/assets/standalone-components/fancybox/source/jquery.fancybox.js',


				// FastCLick
				'bower_components/fastclick/lib/fastclick.js',


				// Unveil.js
				'bower_components/unveil/jquery.unveil.js',


				// FadeSLideShow
				'js/fadeSlideShow.js',


				// Nette forms
				'js/netteForms.js',
				

				// Main.js
				'js/main.js'


				],
				dest: '../www/assets/js/main.min.js'
			}
		},


		// Minifikuj JS
		uglify: {
			options: {
				mangle: false
			},
			js: {
				files: {
					'../www/assets/js/main.min.js': ['../www/assets/js/main.min.js']
				}
			}
		},


		// Kompiluj a minifikuj less
		less: {
			style: {
				files: {
					"../www/assets/css/style.min.css": "less/style.less"
				},
				options: {
					compress: true,
					relativeUrls: true,
					yuicompress: true,

					sourceMap: false, // Nezapomenout přeponout i v Autoprefixeru!
					sourceMapFilename: '../www/assets/css/style.min.css.map', // where file is generated and located
					sourceMapURL: 'style.min.css.map', // the complete url and filename put in the compiled css file
					sourceMapBasepath: '', // Sets sourcemap base path, defaults to current working directory.
					sourceMapRootpath: '../' // adds this path onto the sourcemap filename and less file paths
				}
			},
			admin: {
				files: {
					"../www/assets/css/style-admin.min.css": "less/style-admin.less"
				},
				options: {
					compress: true,
					relativeUrls: true,
					yuicompress: true,

					sourceMap: false, // Nezapomenout přeponout i v Autoprefixeru!
					sourceMapFilename: '../www/assets/css/style-admin.min.css.map', // where file is generated and located
					sourceMapURL: 'style-admin.min.css.map', // the complete url and filename put in the compiled css file
					sourceMapBasepath: '', // Sets sourcemap base path, defaults to current working directory.
					sourceMapRootpath: '../' // adds this path onto the sourcemap filename and less file paths
				}
			}
		},


		// Autoprefixuj
		autoprefixer: {
			options: {
				map: false
			},
			file: {
				src: '../www/assets/css/style.min.css',
				dest: '../www/assets/css/style.min.css'
			}
		},


		// Zkopíruj potřebné dependency komponenty z bower adresáře, aby šly použít mimo tento adresář
		bowercopy: {
			options: {
				
			},
			jquery: {
				files: {
					'../www/assets/js/jquery.min.js': 'jquery/dist/jquery.min.js'
				}
			},
			html5shiv: {
				files: {
					'../www/assets/js/html5shiv.min.js': 'html5shiv/dist/html5shiv.min.js'
				}
			},
			css3mediaqueries: {
				files: {
					'../www/assets/js/css3-mediaqueries.js': 'css3-mediaqueries-js/css3-mediaqueries.js'
				}
			},
			fontawesome: {
				files: {
					'../www/assets/font/fontawesome/': 'fontawesome/fonts',
				}
			},
			tinymce: {
				files: {
					'../www/assets/standalone-components/tinymce/': 'tinymce-dist',
				}
			},
			fancybox: {
				files: {
					'../www/assets/standalone-components/fancybox/': 'fancybox',
				}
			},
		},


		// Vytvoř sprite obrázků
		sprite:{
			all: {
				src: 'images/*.png',
				cssFormat: 'less',
				dest: '../www/assets/images/smith-sprite.png',
				destCss: 'css/sprite.css'
			}
		},


		// Notifikace úspěchu a failů pro parádu
		notify: {
			upozorni_js: {
				options: {
					title: 'Kombinace a minifikace JS se povedla!',  // optional
					message: 'Jsi šikovný borec, jen tak dál!' //required
				}
			},
			upozorni_less: {
				options: {
					title: 'LESS se zkompilovalo na výbornou!',  // optional
					message: 'Jsi šikovný borec, jen tak dál!' //required
				}
			}
			/*deploy_dev: {
				options: {
					title: 'Deploy na dev se podařil!',  // Volitelný
					message: 'Jsi šikovný borec, jen tak dál!' // Povinný
				}
			},
			deploy_live: {
				options: {
					title: 'Deploy na live se podařil!',  // Volitelný
					message: 'Jsi šikovný borec, jen tak dál!' // Povinný
				}
			}*/
		},


		// Sleduj a konej
		watch: {
			configFiles: {
				files: ['gruntfile.js'] // Při aktualizace gruntfile.js jej znovu načti (netřeba watch exitovat a znovu spouštět)
			},
			js: {
				files: ['js/*.js'],
				tasks: ['concat:js', 'uglify:js', 'notify:upozorni_js'],
				options: {
					spawn: false,
					livereload: true
				}
			},
			less: {
				files: ['less/**/*.less'],
				tasks: ['less:style', 'less:admin', 'autoprefixer:file', 'notify:upozorni_less'],
				options: {
					spawn: false,
					livereload: true
				}
			}
		},

		// Hashres
		hashres: {
			options: {
				encoding: 'utf8',
				fileNameFormat: '${name}.${ext}?${hash}',
				renameFiles: false
			},
			prod: {
				options: {
				},
				src: ['../www/assets/css/style.min.css', '../www/assets/css/style-admin.min.css', '../www/assets/js/main.min.js'],
				dest: ['../app/FrontModule/templates/@layout.latte', '../app/AdminModule/templates/@layout.latte', '../app/AdminModule/templates/Sign/in.latte'],
			}
		},

		// Deploy
		/*ftpush: {
			live: {
				auth: {
					host: 'ftp.domain.com',
					port: 21,
					authKey: 'key1'
				},
				src: '../../../',
				dest: '/www',
				exclusions: [
					'composer.json',
					'composer.lock',
					'Feeio.sublime-project',
					'Feeio.sublime-workspace',
					'license.md',
					'readme.md',
					'sftp-config-alt.json',
					'sftp-config.json'
				],
				simple: false,
				useList: false
			},
			dev: {
				auth: {
					host: 'ftp.feeio.com',
					port: 21,
					authKey: 'key1'
				},
				src: '../',
				dest: '/dev',
				exclusions: [
					'www-dev',
					'composer.json',
					'composer.lock',
					'Feeio.sublime-project',
					'Feeio.sublime-workspace',
					'license.md',
					'readme.md',
					'sftp-config-alt.json',
					'sftp-config.json'
				],
				simple: false,
				useList: false
			}
		}*/


	});


	// Naloaduj tasky
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-autoprefixer');
	grunt.loadNpmTasks('grunt-notify');
	grunt.loadNpmTasks('grunt-hashres');
	grunt.loadNpmTasks('grunt-bowercopy');
	grunt.loadNpmTasks('grunt-spritesmith');

	//grunt.loadNpmTasks('grunt-ftpush');


	// Registruj spouštějící úlohy pro terminál - pro defaultní stačí volat "grunt"
	grunt.registerTask('default', [ 'watch' ]);
	
	//grunt.registerTask('deploy:live', [ 'ftpush:live', 'notify:deploy_live' ]);
	//grunt.registerTask('deploy:dev', [ 'ftpush:dev', 'notify:deploy_dev' ]);


};