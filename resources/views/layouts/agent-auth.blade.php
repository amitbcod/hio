<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Agent Portal') | Holidays.io</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(90deg, #1b82c7 0%, #41b0aa 100%);
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }
        .agent-auth-wrap { display: grid; min-height: 100vh; place-items: center; padding: 32px 16px; }
        .agent-auth-card {
            display: flex;
            width: min(100%, 900px);
            overflow: hidden;
            border: 0;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,.12);
        }
        .agent-auth-form { flex: 1.15; min-width: 0; padding: 40px 36px; }
        .agent-auth-form h1 { margin-bottom: 24px; font-size: 1.5rem; font-weight: 700; text-align: center; text-transform: uppercase; }
        .agent-auth-brand {
            display: flex;
            flex: .85;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 32px;
            background: linear-gradient(135deg, #41b0aa 0%, #1b82c7 100%);
            color: #fff;
            text-align: center;
        }
        .agent-auth-brand .brand-icon { margin-bottom: 16px; font-size: 42px; }
        .agent-auth-brand h2 { font-size: 1.6rem; font-weight: 700; }
        .agent-auth-form .form-label { font-weight: 600; }
        .agent-auth-form .btn-primary { border-color: #41b0aa; background: #41b0aa; }
        .agent-auth-form .btn-primary:hover,
        .agent-auth-form .btn-primary:focus-visible { border-color: #1b82c7; background: #1b82c7; }
        .agent-auth-form a { color: #238c98; }
        @media (max-width: 767.98px) {
            .agent-auth-card { flex-direction: column-reverse; }
            .agent-auth-brand { padding: 24px 20px; }
            .agent-auth-brand .brand-icon { margin-bottom: 8px; font-size: 30px; }
            .agent-auth-brand h2 { font-size: 1.25rem; }
            .agent-auth-form { padding: 28px 22px; }
        }
    </style>
</head>
<body>
    <main class="agent-auth-wrap">
        <div class="agent-auth-card">
            <section class="agent-auth-form">
                @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-warning" role="alert">{{ session('error') }}</div>@endif
                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </section>
            <aside class="agent-auth-brand">
                <div class="brand-icon"><i class="fa-solid fa-earth-americas" aria-hidden="true"></i></div>
                <h2>Holidays.io</h2>
                <p class="mb-0">Agent Portal</p>
            </aside>
        </div>
    </main>
</body>
</html>
