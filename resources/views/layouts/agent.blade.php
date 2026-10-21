@php
    $agentPanelUser = auth('agent')->user() ?? auth('agent_staff')->user();
    $agentPanelAccount = $agentPanelUser instanceof \App\Models\AgentStaffUser
        ? $agentPanelUser->agent
        : $agentPanelUser;
    $agentPanelName = $agentPanelUser instanceof \App\Models\AgentStaffUser
        ? $agentPanelUser->full_name
        : ($agentPanelAccount?->contact_person ?? $agentPanelAccount?->email);
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Agent Panel | Holidays.io') - Holidays.io</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: #f3f4f6;
            color: #111827;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .agent-shell { display: flex; min-height: 100vh; }
        .agent-sidebar {
            display: flex;
            flex: 0 0 280px;
            flex-direction: column;
            width: 280px;
            padding: 18px;
            background-color: #0b1220;
            background-image: linear-gradient(rgba(11, 18, 32, .78), rgba(11, 18, 32, .78)), url('https://hio.whuso.in/images/sidebar-bkg.jpg');
            background-position: center;
            background-size: cover;
            color: #fff;
        }
        .agent-brand {
            display: block;
            margin: 0 0 6px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            text-decoration: none;
        }
        .agent-brand-subtitle { color: rgba(255,255,255,.75); font-size: 13px; }
        .agent-nav { display: grid; gap: 4px; margin-top: 24px; }
        .agent-nav-label {
            margin: 16px 10px 6px;
            color: rgba(255,255,255,.58);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .agent-nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 40px;
            padding: 9px 11px;
            border-radius: 5px;
            color: rgba(255,255,255,.82);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color .15s ease, color .15s ease;
        }
        .agent-nav-link i { width: 17px; text-align: center; color: rgba(255,255,255,.68); }
        .agent-nav-link:hover, .agent-nav-link:focus-visible {
            color: #fff;
            background: rgba(255,255,255,.1);
        }
        .agent-nav-link.active { color: #fff; background: rgba(255,255,255,.18); }
        .agent-nav-link.active i { color: #fff; }
        .agent-sidebar-footer { margin-top: auto; padding-top: 18px; }
        .agent-sidebar-footer form { margin: 0; }
        .agent-logout {
            width: 100%;
            padding: 9px 11px;
            border: 0;
            border-radius: 5px;
            background: transparent;
            color: #fff;
            font-size: 13px;
            text-align: left;
        }
        .agent-logout:hover, .agent-logout:focus-visible { background: rgba(255,255,255,.1); }
        .agent-main { flex: 1; min-width: 0; padding: 22px; }
        .agent-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }
        .agent-topbar h1 { margin: 0; font-size: 1.35rem; font-weight: 700; }
        .agent-topbar-user { font-size: 13px; font-weight: 700; text-align: right; }
        .agent-topbar-agency { display: block; color: #6b7280; font-size: 12px; font-weight: 400; }
        .agent-mobile-menu { display: none; }
        .agent-breadcrumb { margin: -8px 0 20px; color: #6b7280; font-size: 12px; }
        .agent-breadcrumb a { color: #1b82c7; text-decoration: none; }
        .agent-breadcrumb a:hover { color: #145f91; text-decoration: underline; }
        .agent-content > h1:first-child { margin-bottom: 1rem !important; }
        .agent-content .card { border-color: #e5e7eb; border-radius: 8px; }
        .agent-content .card-header { background: #fff; font-weight: 700; }
        .agent-content .table thead th { color: #374151; font-size: 12px; font-weight: 700; }
        .agent-content .form-label { font-weight: 600; }
        @media (max-width: 767.98px) {
            .agent-shell { display: block; }
            .agent-sidebar { display: none; width: 100%; padding: 14px 16px; }
            .agent-sidebar.is-open { display: flex; }
            .agent-nav { margin-top: 14px; }
            .agent-main { padding: 16px; }
            .agent-mobile-menu {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 38px;
                height: 36px;
                border: 1px solid #d1d5db;
                border-radius: 5px;
                background: #fff;
                color: #111827;
            }
            .agent-topbar { align-items: flex-start; }
            .agent-topbar-user { max-width: 45%; overflow-wrap: anywhere; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="agent-shell">
        <aside class="agent-sidebar" id="agent-sidebar">
            <div>
                <a class="agent-brand" href="{{ route('agent.dashboard') }}">Holidays.io</a>
                <div class="agent-brand-subtitle">{{ $agentPanelAccount?->agency_name ?? 'Agent Panel' }}</div>
            </div>

            @include('agent.partials.sidebar')

            <div class="agent-sidebar-footer">
                <form method="POST" action="{{ route('agent.logout') }}">
                    @csrf
                    <button class="agent-logout" type="submit"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</button>
                </form>
            </div>
        </aside>

        <main class="agent-main">
            <header class="agent-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="agent-mobile-menu" type="button" aria-label="Toggle navigation" aria-controls="agent-sidebar" aria-expanded="false">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h1>@yield('title', 'Agent Dashboard')</h1>
                </div>
                <div class="agent-topbar-user">
                    {{ $agentPanelName ?? 'Agent' }}
                    <span class="agent-topbar-agency">{{ $agentPanelAccount?->agency_name ?? 'Agent Panel' }}</span>
                </div>
            </header>

            <nav class="agent-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('agent.dashboard') }}">Agent Panel</a>
                <span class="mx-1">/</span>
                <span aria-current="page">@yield('title', 'Dashboard')</span>
            </nav>

            <div class="agent-content">
                @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
                @if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
                @if(session('error'))<div class="alert alert-warning" role="alert">{{ session('error') }}</div>@endif
                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        document.querySelector('.agent-mobile-menu')?.addEventListener('click', function () {
            const sidebar = document.getElementById('agent-sidebar');
            const isOpen = sidebar.classList.toggle('is-open');
            this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    </script>
    @stack('scripts')
</body>
</html>
