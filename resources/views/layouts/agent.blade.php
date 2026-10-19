<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Agent Panel | Holidays.io')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f7fa; }
        .agent-header { background: #0e2a56; color: #fff; }
        .agent-header a { color: #fff; }
        .agent-nav a { text-decoration: none; }
    </style>
</head>
<body>
    <header class="agent-header py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ route('agent.dashboard') }}" class="fw-bold text-decoration-none">Holidays.io · Agent Panel</a>
            <div class="d-flex align-items-center gap-3">
                <span>{{ auth('agent')->user()?->email ?? auth('agent_staff')->user()?->email }}</span>
                <form method="POST" action="{{ route('agent.logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Logout</button></form>
            </div>
        </div>
    </header>
    <div class="container py-4">
        <div class="row g-4">
            <aside class="col-lg-3 agent-nav">
                <nav class="list-group">
                    <a class="list-group-item list-group-item-action" href="{{ route('agent.dashboard') }}">Dashboard</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('agent.profile') }}">Profile</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('agent.compliance') }}">Compliance</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('agent.users') }}">Users</a>
                    <a class="list-group-item list-group-item-action" href="{{ route('agent.operations') }}">Operations</a>
                </nav>
            </aside>
            <main class="col-lg-9">
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-warning">{{ session('error') }}</div>@endif
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
