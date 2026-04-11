<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'Nukang Admin')</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('assets/library/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom Style -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">

    @stack('style')
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">

            @auth
                @include('components.header')
                @include('components.sidebar')
            @endauth

            <!-- Main Content -->
            <div class="main-content">
                @yield('main')
            </div>

            @auth
                @include('components.footer')
            @endauth

        </div>
    </div>

    <!-- JS -->
    <script src="{{ asset('assets/library/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/library/popper.js/dist/umd/popper.js') }}"></script>
    <script src="{{ asset('assets/library/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/library/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('assets/library/moment/min/moment.min.js') }}"></script>

    <script src="{{ asset('assets/js/stisla.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    @stack('scripts')
</body>

</html>
