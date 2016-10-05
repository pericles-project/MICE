var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

// Disable source maps
elixir.config.sourcemaps = false;

elixir(function(mix) {
    // Copy assets
    mix.copy('vendor/twbs/bootstrap/dist/css/bootstrap.min.css', 'resources/assets/css/bootstrap.css')
      .copy('vendor/components/jquery/jquery.min.js', 'resources/assets/js/jquery.js')
      .copy('vendor/twbs/bootstrap/dist/js/bootstrap.min.js', 'resources/assets/js/bootstrap.js')
      .copy('vendor/mbostock/d3/d3.min.js', 'resources/assets/js/d3.js')

    // Compile Sass
    mix.sass(['main.scss'], 'resources/assets/css');

    // Minify
    mix.styles([
        'bootstrap.css',
        'ladda-themeless.min.css',
        'font-awesome.min.css',
        'theme.css',
        'app.css'
      ])
      .scripts([
        'jquery.js',
        'bootstrap.js',
        'd3.js',
        'd3-tip.js',
        'spin.min.js',
        'ladda.min.js',
        'graph.js',
        'app.js'
      ]);

    // Version
    mix.version([
      'css/all.css',
      'js/all.js'
    ]);
});
