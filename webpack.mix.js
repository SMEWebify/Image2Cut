mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),
    ])
    .copy('node_modules/lodash/lodash.min.js', 'public/js')
    .copy('node_modules/dropzone/dist/dropzone-min.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');
