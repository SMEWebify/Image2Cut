<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="description" content="Image2Cut permet de transformer facilement vos images en fichiers DXF et GCode gratuits, prêts pour la découpe punch ou laser.">

        <title>Image2Cut - Génération gratuite de fichiers DXF et GCode pour découpe</title>

        <!-- Styles -->
        @vite('resources/css/app.css')
        <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">

        <!-- JavaScript -->
        <script type="module" src="{{ Vite::asset('resources/js/app.js') }}"></script>

    </head>
    <body style=" background: linear-gradient(135deg, #0d0d0d 0%, #1e293b 100%);">
        <div >
            @yield('content')
        </div>
    </body>
</html>
