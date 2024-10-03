/**
 * gulp.js template, this file goes with package.json in same folder
 * run npm install in this directory
 * commands you can run:
 * gulp, gulp sass, gulp watch, gulp clearcache, gulp reload, gulp sassdoc
 */

var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var sassdoc = require('sassdoc');
var browserSync = require('browser-sync');
var shell = require('gulp-shell');
var phpcs = require('gulp-phpcs');
var eslint = require('gulp-eslint');
var phplint = require('gulp-phplint');
var sassLint = require('gulp-sass-lint');
var css_destination = './css';

// Load in configuration.  You don't have to use this,
// but it makes it easier to update tasks in the future
// if paths aren't scattered in the gulpfile.
var config = require('./gulpconfig');

var sassOptions = {
  errLogToConsole: true,
  outputStyle: 'expanded'
};

/**
 * SASS compile
 * based on this plus additions: https://www.sitepoint.com/simple-gulpy-workflow-sass/
 */
gulp.task('sass', function () {
  return gulp
    .src(config.sass_source)
    .pipe(sourcemaps.init())
    .pipe(sass(sassOptions).on('error', sass.logError))
    .pipe(sourcemaps.write())
    .pipe(autoprefixer())
    .pipe(gulp.dest(config.css_destination));
});

/**
 * watch task, keeps running, looks for sass changes and compiles if found
 */
gulp.task('watch', function() {
  return gulp
  // Watch the sass files for change,
  // and run `sass` task when something happens
    .watch(config.sass_source, ['sass'])
    // When there is a change,
    // log a message in the console
    .on('change', function(event) {
      console.log('File ' + event.path + ' was ' + event.type + ', running tasks...');
    });
});

/**
 * clear cache, Drupal only
 */
gulp.task('clearcache', function() {
  return shell.task([
    'drush cc all'
  ]);
});

/**
 * auto reload browser
 */
gulp.task('reload', function () {
  return gulp
    browserSync.reload({stream:true}); // reload the stream
});

/**
 * SASS docs, you may or may not want/need
 * more: http://sassdoc.com/gulp/
 */
gulp.task('sassdoc', function () {
  return gulp
    .src(config.sass_source)
    .pipe(sassdoc())
    .resume();
});

/**
 * Check tasks
 *
 * Add steps here to run during checking phase of the app.
 * Check steps should not require a database to function.
 */
gulp.task('check', ['check:phplint', 'check:phpcs', 'check:eslint', 'check:sasslint']);
gulp.task('check:phplint', function () {
  return gulp.src(config.phpCheck)
    .pipe(phplint('', {notify: false, skipPassedFiles: true}))
    .pipe(phplint.reporter('fail'));
});
gulp.task('check:phpcs', function () {
  return gulp.src(config.phpCheck)
    .pipe(phpcs({
      // these paths are assuming that composer files are outside the drupal root
      bin: '../vendor/bin/phpcs',
      standard: '../vendor/drupal/coder/coder_sniffer/Drupal'
    }))
    .pipe(phpcs.reporter('log'))
    .pipe(phpcs.reporter('fail'));
});
gulp.task('check:eslint', function () {
  return gulp
    .src(config.jsCheck)
    .pipe(eslint())
    .pipe(eslint.format())
    .pipe(eslint.failAfterError());
});
gulp.task('check:sasslint', function() {
  return gulp.src(config.sass_source)
    .pipe(sassLint({rules: config.sass_rules}))
    .pipe(sassLint.format())
    .pipe(sassLint.failOnError())
});

/**
 * define what runs by default with gulp command
 */
gulp.task('default', ['sass', 'watch', 'clearcache', 'reload' /*, possible other tasks... */]);
