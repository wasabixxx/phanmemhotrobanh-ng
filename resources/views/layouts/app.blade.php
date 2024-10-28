<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"> <!-- CSS -->
    <title>@yield('title')</title>
</head>
<body>
    <header>
        <nav>
            <!-- Navigation links -->
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <!-- Footer content -->
    </footer>

    <script src="{{ asset('js/app.js') }}"></script> <!-- JavaScript -->
</body>
</html>
