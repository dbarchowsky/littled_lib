const gulp = require('gulp');
const concat = require('gulp-concat');
const rename = require('gulp-rename');
const terser = require('gulp-terser');
const pump = require('pump');

const paths = {
    scripts: {
        littled: {
            concat: {
                options: {
                    separator: ''
                },
                src: [
                    'scripts/core.js',
                    'scripts/formDialog.js',
                    'scripts/inlineEdit.js',
                    'scripts//lineitems.js',
                    'scripts/listings.js',
                    'scripts/keyword.js',
                    'scripts/keywordFilter.js',
                    'scripts/resort.js'
                ],
                dir: 'scripts/pkg/',
                dst: 'littled.js'
            }
        },
        gallery: {
            concat: {
                options: {
                    separator: ''
                },
                src: [
                    'scripts/gallery.js'
                ],
                dir: 'scripts/pkg/',
                dst: 'gallery.js'
            }
        },
    },
    uglify: {
        src: [
            'scripts/pkg/*.js',
            '!scripts/pkg/*.min.*'
        ]
    }
};

/**
 *  combine all javascript into one file
 */
gulp.task('littled-scripts', function() {
    return gulp.src(paths.scripts.littled.concat.src)
        .pipe(concat(paths.scripts.littled.concat.dst))
        .pipe(gulp.dest(paths.scripts.littled.concat.dir));
});

gulp.task('gallery-scripts', function() {
    return gulp.src(paths.scripts.gallery.concat.src)
        .pipe(concat(paths.scripts.gallery.concat.dst))
        .pipe(gulp.dest(paths.scripts.gallery.concat.dir));
});

gulp.task('scripts', gulp.parallel('littled-scripts', 'gallery-scripts'), function() {

});

/**
 *  minify local javascript libraries
 */
gulp.task('uglify', function(cb) {
    pump([
        gulp.src(paths.uglify.src, {base: '.'}),
        terser(),
        rename({suffix: '.min'}),
        gulp.dest(function(file) {
            return file.base;
        })
        ],
        cb
    );
});

gulp.task('watch', function() {
    gulp.watch(paths.scripts.littled.concat.src, ['scripts']);
    gulp.watch(paths.uglify.src, ['uglify']);
});

gulp.task('build',
    gulp.series('scripts', 'uglify')
);

gulp.task('default',
    gulp.series('scripts', 'uglify', 'watch')
);
