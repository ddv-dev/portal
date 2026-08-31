{{-- resources/views/layouts/app.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Корпоративный портал')</title>

    @vite(['resources/css/app.css'])
    @stack('styles')
</head>

<body>

    @include('partials.header')

    <main class="section">
        <div class="container">
            @yield('content')
        </div>
    </main>
    @vite(['resources/js/app.js'])
</body>

</html>