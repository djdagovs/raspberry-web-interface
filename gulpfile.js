"use strict";

var gulp = require('gulp'),
    gutil = require('gulp-util'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    livereload = require('gulp-livereload'),
    uglify = require('gulp-uglify'),
    concat = require('gulp-concat');

var stylesheets = [
    'web/assets/css/**/*.css'
];

var scripts = {
    app: [
        'web/assets/js/**/*.js',
    ],
    vendor: [
        'web/bower_components/jquery/dist/jquery.min.js',
        'web/bower_components/bootstrap-sass/assets/javascripts/bootstrap.min.js',
        'web/bower_components/jasny-bootstrap/js/offcanvas.js'
    ],
};

gulp.task('twig', function() {
    return gulp.src('app/Resources/views/**/*.html.twig')
        .pipe(livereload());
});

gulp.task('sass', function() {
    return gulp.src('web/assets/sass/**/*.{sass,scss}')
        .pipe(sass({outputStyle: 'compressed'}).on('error', function(error) {
            gutil.log(error);
            this.emit('end');
        }))
        .pipe(autoprefixer())
        .pipe(gulp.dest('web/assets/css'))
        .pipe(livereload());
});

gulp.task('css', ['sass'], function() {
    return gulp.src(stylesheets)
        .pipe(concat('app.min.css'))
        .pipe(gulp.dest('web/build/css'));
});

gulp.task('scripts_app', function() {
    return gulp.src(scripts.app)
        .pipe(uglify().on('error', function(error) {
            gutil.log(error);
            this.emit('end');
        }))
        .pipe(concat('app.min.js'))
        .pipe(gulp.dest('web/build/js'))
        .pipe(livereload());
});

gulp.task('scripts_vendor', function() {
    return gulp.src(scripts.vendor)
        .pipe(uglify().on('error', function(error) {
            gutil.log(error);
            this.emit('end');
        }))
        .pipe(concat('vendor.min.js'))
        .pipe(gulp.dest('web/build/js'))
        .pipe(livereload());
});

gulp.task('watch', function() {
    livereload.listen({
        host: 'localhost'
    });

    // Watch Twig files
    gulp.watch('app/Resources/views/**/*.html.twig', ['twig']);

    // Watch SASS and SCSS files
    gulp.watch('web/assets/sass/**/*.{sass,scss}', ['css']);

    // Watch JS files
    gulp.watch('web/assets/js/*.js', ['scripts_app']);
});

gulp.task('vendor', ['scripts_vendor'])

gulp.task('default', ['watch']);
