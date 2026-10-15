<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Portal | Holidays.io')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/admin-mpo-theme.css') }}">
    @stack('styles')
</head>
<body class="admin-mpo-layout{{ request()->routeIs('admin.login') ? ' admin-mpo-layout--auth' : '' }}">
    <header class="admin-mpo-header">
        <button type="button" class="admin-mpo-menu-toggle" data-admin-mpo-sidebar-toggle aria-controls="admin-mpo-sidebar" aria-expanded="false">
            Menu
        </button>
        <a class="admin-mpo-brand" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('images/holidays-io-logo.png') }}" alt="Holidays.io" width="130">
        </a>
        <div class="admin-mpo-account">
            <span class="admin-mpo-account-label">
                @if(session('admin_id'))
                    @php $admin = \App\Models\AdminUser::find(session('admin_id')); @endphp
                    {{ $admin->email ?? 'Admin' }}
                @else
                    Admin Portal
                @endif
            </span>
            @if(session('admin_id'))
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">Logout</button>
                </form>
            @else
                <a href="{{ route('admin.login') }}" class="btn btn-outline-primary btn-sm">Login</a>
            @endif
        </div>
    </header>
    <div class="admin-mpo-shell">
        <aside class="admin-mpo-sidebar" id="admin-mpo-sidebar" aria-label="Admin and MPO navigation">
            @if(isset($sidebar) && view()->exists($sidebar))
                @include($sidebar)
            @else
                @include('admin._sidebar')
            @endif
        </aside>
        <main class="admin-mpo-main">
            <div class="admin-mpo-content">
                @yield('content')
            </div>
        </main>
    </div>
    <script src="{{ asset('frontend/js/admin-mpo.js') }}" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>