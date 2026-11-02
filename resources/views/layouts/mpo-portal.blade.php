@php($mpoPortalAccount = request()->attributes->get('mpo_admin'))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MPO Portal | Holidays.io')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/admin-mpo-theme.css') }}">
    @stack('styles')
</head>
<body class="admin-mpo-layout">
    <header class="admin-mpo-header">
        <button type="button" class="admin-mpo-menu-toggle" data-admin-mpo-sidebar-toggle aria-controls="mpo-sidebar" aria-expanded="false">
            Menu
        </button>
        <a class="admin-mpo-brand" href="{{ route('mpo.profile') }}">
            <img src="{{ asset('images/holidays-io-logo.png') }}" alt="Holidays.io" width="130">
        </a>
        <div class="admin-mpo-account">
            <span class="admin-mpo-account-label">{{ $mpoPortalAccount?->email }}</span>
            <form action="{{ route('mpo.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">Logout</button>
            </form>
        </div>
    </header>
    <div class="admin-mpo-shell">
        <aside class="admin-mpo-sidebar" id="mpo-sidebar" aria-label="MPO navigation">
            @include('mpo.partials.sidebar', [
                'mpoAdmin' => $mpoPortalAccount,
                'mpoStaffUser' => request()->attributes->get('mpo_staff_user'),
            ])
        </aside>
        <main class="admin-mpo-main">
            <div class="admin-mpo-content">
                <h1 class="h3 mb-4">@yield('title', 'MPO Portal')</h1>
                @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
                @if(session('warning'))<div class="alert alert-warning" role="alert">{{ session('warning') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
    <script src="{{ asset('frontend/js/admin-mpo.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
