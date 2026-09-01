<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Holidayss.io</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/app-style.css') }}">

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-0 p-2">
        <button class="hamburger" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/operator/accommodation') }}">
              
                <img src="{{ asset('images/holidays-io-logo.png') }}" alt="Logo" width="130px">
              <!-- <i class="fa-solid fa-house me-2" aria-hidden="true"></i> -->
            </a>
            @auth
                @if((auth()->user()->is_owner ?? '') === 'yes')
                    <div class="ms-auto">

                    </div>
                @endif
            @endauth
            
            <div>
                @auth
                    <span class="me-3 fw-bold">{{ auth()->user()->email }}</span>
                    <form action="{{ route('operator.logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Logout</button>
                    </form>
                @endauth
            </div>
            <div class="dropdown">
                <a href="#" class="nav-link dropdown-toggle" id="ownerSettingsDropdown" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fa-solid fa-gear"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="ownerSettingsDropdown">
                    <li><a class="dropdown-item" href="{{ route('operator.manage.operators.index') }}">Manage
                            Operators</a></li>
                </ul>
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