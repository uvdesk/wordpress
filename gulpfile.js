// Just Change folder Name in 'Path';
//Install all @required packages

const themePath   = './assets/';
const gulp        = require( 'gulp' );
const less        = require( 'gulp-less' );
const cleanCSS    = require( 'gulp-clean-css' );
const rename      = require( 'gulp-rename' );
const terser      = require('gulp-terser');


/* Task to compile less */
const compileLess = () => {
  return gulp.src( themePath + 'less/*.less' )
    .pipe( less() )
    .pipe(gulp.dest( themePath + 'build/css/' ) );
};


/*Minify CSS*/
const minifyCss = () => {
  return gulp.src( themePath + 'build/css/*.css' )
    .pipe(cleanCSS( { compatibility: 'ie8' } ) )
    .pipe(rename({
        suffix: '.min'
    }))
    .pipe(gulp.dest( themePath + 'dist/css/' ) );
};



/* Minify JS*/
const minifyJs = () => {
    return gulp.src(themePath + 'build/js/*.js')
        .pipe(terser())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest(themePath + 'dist/js/'));
}

/*Watch JS*/
const watchJs = () => {
    gulp.watch(themePath + 'build/js/*.js', minifyJs );
};

/*Watch LESS*/
const watchLess = () => {
  gulp.watch(themePath + 'less/**/*.less' , compileLess );
};

/*Watch CSS*/
const watchCss = () => {
  gulp.watch(themePath + 'build/css/*.css' , minifyCss );
};

/* Task when running `gulp` from terminal */
const build = gulp.parallel( watchLess, watchCss, watchJs );

/* Tasks when running `gulp` from terminal */
exports.compileLess = compileLess;
exports.minifyCss = minifyCss;
exports.minifyJs = minifyJs;
exports.watchLess = watchLess;
exports.watchCss = watchCss;
exports.watchJs = watchJs;

gulp.task( 'default', build );
