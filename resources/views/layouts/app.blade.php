<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title.' - ' : '' }}{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    @include('layouts.navigation')

    @isset($header)
        <div class="bg-white border-bottom">
            <div class="container py-3">
                {{ $header }}
            </div>
        </div>
    @endisset

    <main class="py-4">
        {{ $slot }}
    </main>

    <footer class="border-top bg-white py-3 mt-4">
        <div class="container small text-muted">
            &copy; {{ date('Y') }} {{ config('app.name') }}
        </div>
    </footer>
</body>
</html>
