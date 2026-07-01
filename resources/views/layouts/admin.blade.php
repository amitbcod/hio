<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
     <link rel="stylesheet" href="{{ asset('frontend/css/admin-style.css') }}">
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-light mb-0">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- <a class="navbar-brand" href="#">Admin Portal</a> -->
            <a class="navbar-brand" href="#"><img src="https://hio.whuso.in/public/images/holidays-io-logo.png"
                    alt="Logo" width="130px"></a>
            <div>
                @if(session('admin_id'))
                    @php $admin = \App\Models\AdminUser::find(session('admin_id')); @endphp
                    <span class="me-3">{{ $admin->email ?? 'Admin' }}</span>
                    <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                    </form>
                @else
                    <a href="{{ route('admin.login') }}" class="btn btn-outline-light btn-sm">Login</a>
                @endif
            </div>
        </div>
    </nav>
    <main>
        <div class="container-fluid main-setion">
            <div class="row">
                <div class="col-md-2 list-section">
                    @include('admin._sidebar')
                </div>
                <div class="col-md-10 login-full-section">
                    @yield('content')
                </div>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>