<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Contas') }}</title>
    @vite(['resources/css/app.css', 'resources/js/main.js'])
    <script>
        window.AppConfig = {
            locale: '{{ app()->getLocale() }}',
            currency: '{{ config('app.currency', 'BLR') }}',
            fallbackLocale: '{{ config('app.fallback_locale') }}'
        };
    </script>
</head>
<body>
<div id="app"></div>
</body>
</html>
