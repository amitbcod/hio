<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MPO Portal | Holidays.io')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/admin-mpo-theme.css') }}">
</head>
<body class="admin-mpo-auth">
    <header class="admin-mpo-auth-header">
        <a href="{{ route('frontend.home') }}" class="admin-mpo-brand">
            <img src="{{ asset('images/holidays-io-logo.png') }}" alt="Holidays.io" width="130">
        </a>
        <span>MPO Portal</span>
    </header>
    <main class="admin-mpo-auth-main">
        @yield('content')
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
