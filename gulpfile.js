const gulp = require('gulp');
const concat = require('gulp-concat');
const jest = require('gulp-jest').default;
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
                    'js/core.js',
                    'js/formDialog.js',
                    'js/inlineEdit.js',
                    'js//lineitems.js',
                    'js/listings.js',
                    'js/keyword.js',
                    'js/keywordFilter.js',
                    'js/resort.js'
                ],
                dir: 'js/pkg/',
                dst: 'littled.js'
            }
        },
        gallery: {
            concat: {
                options: {
                    separator: ''
                },
                src: [
                    'js/gallery.js'
                ],
                dir: 'js/pkg/',
                dst: 'gallery.js'
            }
        },
        tests: {
            src: [
                'js/tests'
            ]
        }
    },
    uglify: {
        src: [
            'js/pkg/*.js',
            '!js/pkg/*.min.*'
        ]
    }
};

/**
 *  combine all javascript into one file
 */
gulp.task('littled-js', function() {
    return gulp.src(paths.scripts.littled.concat.src)
        .pipe(concat(paths.scripts.littled.concat.dst))
        .pipe(gulp.dest(paths.scripts.littled.concat.dir));
});

gulp.task('gallery-js', function() {
    return gulp.src(paths.scripts.gallery.concat.src)
        .pipe(concat(paths.scripts.gallery.concat.dst))
        .pipe(gulp.dest(paths.scripts.gallery.concat.dir));
});

gulp.task('jest', function() {
    return gulp.src(paths.scripts.tests.src)
        .pipe(jest());
});

gulp.task('scripts', gulp.parallel('littled-js', 'gallery-js'), function() {

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
