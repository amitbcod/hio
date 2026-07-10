<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Operator Dashboard') - Holidays.io</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/app.css">
    @stack('styles')
    <style>
        body { font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background:#f3f4f6; color:#111827; }
        .operator-shell { display: flex; min-height: 100vh; }
        .operator-sidebar { width: 280px; background: #0b1220; color: #fff; padding: 18px; }
        .operator-main { flex: 1; padding: 22px; }
        .operator-topbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; }
        .operator-topbar .user { font-weight:700; }
        a.sidebar-link { color: #fff; text-decoration:none; display:block; padding:6px 0; }
    </style>
</head>
<body>
    <div class="operator-shell">
        <aside class="operator-sidebar">
            <div style="margin-bottom:18px;">
                <h2 style="margin:0 0 6px 0; font-size:18px;">Operator</h2>
                <div style="font-size:13px; color: rgba(255,255,255,0.75);">Dashboard</div>
            </div>

            @include('operator.management._sidebar')

            <div style="margin-top:20px; font-size:13px;">
                <form id="logoutForm" method="POST" action="{{ route('operator.logout') }}">
                    @csrf
                    <button type="submit" style="background:transparent;border:0;color:#fff;padding:8px 0;cursor:pointer;">Logout</button>
                </form>
            </div>
        </aside>

        <main class="operator-main">
            <div class="operator-topbar">
                <div>
                    <h3 style="margin:0;">@yield('title')</h3>
                </div>
                <div class="user">
                    @php $op = Auth::guard('operator')->user() ?? Auth::guard('operator_staff')->user(); @endphp
                    @if($op)
                        <span>{{ $op->name ?? $op->email ?? 'Operator' }}</span>
                    @endif
                </div>
            </div>

            @yield('content')
        </main>
    </div>

    <script src="/js/app.js"></script>
    @stack('scripts')
</body>
</html>