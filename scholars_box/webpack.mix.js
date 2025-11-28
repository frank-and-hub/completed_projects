const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/system/admin.js', 'public/js/system/admin.js')
    .js('resources/js/system/dt.js', 'public/js/system/dt.js')

    .css('resources/css/system/dt.css', 'public/css/system/dt.css')
    .sass('resources/sass/app.scss', 'public/css')


    .autoload({
        jquery: ['$', 'window.jQuery', "jQuery", "window.$", "jquery", "window.jquery"],
        validate: 'jquery-validation',
        DataTable: 'datatables.net-bs'
    })

    .sourceMaps();