const gulp = require('gulp');
const concat = require('gulp-concat');
const rename = require('gulp-rename');
const uglify = require('gulp-uglify');

const paths = {
    scripts: {
        littled: {
            concat: {
                options: {
                    separator: ''
                },
                src: [
                    'scripts/littled/core.js',
                    'scripts/littled/formDialog.js',
                    'scripts/littled/inlineEdit.js',
                    'scripts/littled/lineitems.js',
                    'scripts/littled/listings.js',
                    'scripts/littled/keyword.js',
                    'scripts/littled//keywordFilter.js',
                    'scripts/littled/resort.js'
                ],
                dir: 'scripts/littled/pkg/',
                dst: 'littled.js'
            }
        },
        gallery: {
            concat: {
                options: {
                    separator: ''
                },
                src: [
                    'scripts/littled/gallery.js'
                ],
                dir: 'scripts/littled/pkg/',
                dst: 'gallery.js'
            }
        },
    },
    uglify: {
        src: [
            'scripts/littled/pkg/*.js',
            '!scripts/littled/pkg/*.min.*'
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
gulp.task('uglify', function() {
    return gulp.src(paths.uglify.src, {base: '.'})
        .pipe(uglify())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest(function(file) {
            return file.base;
        }));
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
