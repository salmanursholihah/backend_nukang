<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>@yield('title', 'Error') &mdash; Nukang Admin</title>

    <link rel="stylesheet" href="{{ asset('assets/library/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">

    @stack('style')
</head>

<body>
    <div id="app">
        <section class="section">
            <div class="container mt-5">

                @yield('main')

                @include('components.error-footer')

            </div>
        </section>
    </div>

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
