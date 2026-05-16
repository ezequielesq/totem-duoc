<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tótem Duoc UC')</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/styles.css'])
</head>
<body class="duoc-bg-gris-carbon">
    @yield('content')
    @vite(['resources/js/script.js'])
    @yield('scripts')
</body>
</html>
