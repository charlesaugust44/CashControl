<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? config('app.name', 'Cash Control') }}</title>
    <script>
        (function() {
            var t = localStorage.getItem('cashcontrol-theme');
            if (t !== 'light' && t !== 'dark') {
                t = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/theme.js'])
    @stack('styles')
</head>
<body>
<div class="d-flex overflow-hidden">
    @include('components.sidebar')
    <div class="d-flex flex-column overflow-hidden vh-100 vw-100">
        @include('components.header', ['pageTitle' => $pageTitle ?? null, 'headerOptions' => $headerOptions ?? []])
        @include('components.breadcrumbs', ['breadcrumbs' => $breadcrumbs ?? []])
        <main>
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
