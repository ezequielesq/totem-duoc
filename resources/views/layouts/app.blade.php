<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tótem Duoc UC')</title>
    @vite(['resources/css/styles.css'])
</head>
<body>
    @yield('content')
    @vite(['resources/js/script.js'])
    @yield('scripts')
</body>
</html>
