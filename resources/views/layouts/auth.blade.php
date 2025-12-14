<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Административная панель - {{ config('app.name', 'kotiksCMS') }}</title>

    <!-- Icons -->
    <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
    <link rel="shortcut icon" href="/assets/admin/media/favicons/favicon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/admin/media/favicons/favicon-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/admin/media/favicons/apple-touch-icon-180x180.png">
    <!-- END Icons -->

    <!-- Dashmix framework -->
    <link rel="stylesheet" id="css-main" href="/assets/admin/css/dashmix.min.css">
</head>

<body>
    <div id="app">
        @yield('content')
    </div>

    <script src="/assets/admin/js/dashmix.app.min.js"></script>

    <!-- jQuery (required for jQuery Validation plugin) -->
    <script src="/assets/admin/js/lib/jquery.min.js"></script>

    <!-- Page JS Plugins -->
    <script src="/assets/admin/js/plugins/jquery-validation/jquery.validate.min.js"></script>

    <!-- Page JS Code -->
    <script src="/assets/admin/js/pages/op_auth_signup.min.js"></script>
</body>
</html>