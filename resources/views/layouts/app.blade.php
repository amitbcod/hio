<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Holidayss.io</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/app-style.css') }}">
   
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-0">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="#"><img src="{{ asset('images/holidays-io-logo.png') }}" alt="Logo" width="130px"></a>
            @auth
                @if((auth()->user()->is_owner ?? '') === 'yes')
                    <div class="ms-auto">
                        <div class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="ownerSettingsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear-fill"></i> Settings
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="ownerSettingsDropdown">
                                <li><a class="dropdown-item" href="{{ route('operator.manage.operators.index') }}">Manage Operators</a></li>
                            </ul>
                        </div>
                    </div>
                @endif
            @endauth
            <div>
                @auth
                    <span class="me-3">{{ auth()->user()->email }}</span>
                    <form action="{{ route('operator.logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    @hasSection('progressbar')
                        @yield('progressbar')
                    @endif
                </div>
            </div>
            <div class="row">
                @yield('content')
            </div>
        </div>
    </main>
    <!-- jQuery and Bootstrap Bundle (for modal support) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
