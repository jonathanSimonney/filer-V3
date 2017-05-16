module.exports = function(grunt) {

    grunt.initConfig({
        sass: {                              // Nom de la tâche
            dist: {                            // Nom de la sous-tâche
                options: {                       // Options
                    style: 'expanded'
                },
                files: {                         // Liste des fichiers
                    'assets/style.css': 'assets/style.scss'
                }
            }
        },
        watch: {
            css: {
                files: '**/*.scss',
                tasks: ['sass'],
                options: {
                    livereload: true,
                },
            },
        },
    });

    // Import du package
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Redéfinition de la tâche `default` qui est la tâche lancée dès que vous lancez Grunt sans rien spécifier.
    // Note : ici, nous définissons sass comme une tâche à lancer si on lance la tâche `default`.
    grunt.registerTask('default', ['watch:css'])
};
