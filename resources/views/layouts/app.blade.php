<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Metal Cutting')</title>
    <!-- Styles -->
    @vite('resources/css/app.css')
    <!-- JavaScript -->
    <script type="module" src="{{ Vite::asset('resources/js/app.js') }}"></script>

</head>
<body>
    <div class="min-h-screen bg-slate-950 ">
        @yield('content')
    </div>

    <!-- Ajouter des scripts JavaScript ici -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
