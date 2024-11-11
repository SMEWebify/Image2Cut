<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Styles -->
        @vite('resources/css/app.css')
        <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">


        <!-- JavaScript -->
        <script type="module" src="{{ Vite::asset('resources/js/app.js') }}"></script>
        <script type="module" src="{{ Vite::asset('node_modules/lodash/lodash.min.js') }}"></script>
        <script type="module" src="{{ Vite::asset('node_modules/dropzone/dist/dropzone-min.js') }}"></script>

        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1279966095557282"
     crossorigin="anonymous"></script>

    </head>
    <body class=" bg-slate-500 ">
        <div >
            @yield('content')
        </div>
    </body>
</html>
