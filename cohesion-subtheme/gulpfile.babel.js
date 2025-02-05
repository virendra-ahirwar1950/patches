import gulp from 'gulp';
import sass from 'gulp-sass';
import sourcemaps from 'gulp-sourcemaps';
import livereload from 'gulp-livereload';
import rename from 'gulp-rename';

const sassInput = './sass/custom.scss',
    cssOutput = './css';

gulp.task('sass', () => {
    return gulp
        .src(sassInput)
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(sass({errLogToConsole: true}).on('error', sass.logError))
        .pipe(rename('custom.css'))
        .pipe(gulp.dest(cssOutput))
        .pipe(livereload());
});

gulp.task('watch', () => {
    livereload.listen();
    gulp.watch('./sass/**/*.scss', ['sass'])
});

gulp.task('default', ['sass', 'watch']);