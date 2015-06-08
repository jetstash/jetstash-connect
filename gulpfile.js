var gulp        = require('gulp');
var concat      = require('gulp-concat');
var uglify      = require('gulp-uglify');
var notify      = require('gulp-notify');
var browserSync = require('browser-sync').create();

gulp.task('admin', function() {
  return gulp.src('js/admin/*.js')
    .pipe(concat('jetstash-admin.js'))
    .pipe(uglify())
    .pipe(gulp.dest('js'))
    .pipe(notify({ message: "Completed minifying admin javascript"}));
});

gulp.task('app', function() {
  return gulp.src('js/app/*.js')
    .pipe(concat('jetstash-app.js'))
    .pipe(uglify())
    .pipe(gulp.dest('js'))
    .pipe(notify({ message: "Completed minifying app javascript"}));
});

gulp.task('default', ['admin', 'app']);
