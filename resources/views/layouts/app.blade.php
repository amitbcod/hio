<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Holidayss.io</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    
<style>
     a.btn.btn-outline-light.btn-sm {
        border: 1px solid #000;
        color: #000;
        padding: 5px 25px;
        line-height: 1.6;
    }

.form-section {
    background: #fff;
    margin-top: 80px;
    padding: 40px 50px 50px 30px;
    border-radius: 10px;
    box-shadow: -7px 0px 18px 0px #dcd3b4;
    box-sizing: content-box;
}
    button.btn.btn-primary {
  background: #41b0aa;
    font-weight: 600 !important;
    border: none;
    width: 100px;
}

.main-setion {
    background: linear-gradient(
16deg, #fdda65 0%, #4aaee2 100%);
    min-height: 100vh;
}

.login-full-section h3 {
    font-weight: bold;
    margin-bottom: 24px;
    text-transform: uppercase;
    text-align: center;
}
.col-md-2.list-section {
    background: #fffffe;
    height: 100vh;
    margin: 0 !important;
    padding: 0;
    border-radius: 0 !important;
    border: none;
    display: none;
}
.col-md-2.show-section {
    background: #c6ac50;
    margin: 0 !important;
    border-radius: 0 !important;
    border: none;
    height: 100vh;
    padding: 0;
}

.list-group-item.active {
    background: #cabb85;
    border: none;
}

.list-group a {
    background: #c6ac50;
    color: #fff;
}

.list-group-item.list-group-item-action {
    border-radius: 0;
    background-color: #c6ac50;
    color: #fff !important;
    border-bottom: 1px solid #fff;
    padding-top: 16px;
    border-left: 0;
    border-right: 0;
}

.dash-section h3 {
font-weight: bold;
    padding-bottom: 15px;
}

button.btn.btn-outline-light.btn-sm {
    border: 1px solid red;
    color: red;
    padding: 5px 25px;
    line-height: 1.6;
}

.table th {
  font-weight: 600;
  color: #555;
}

.table td {
  vertical-align: middle;
}

.sidebar {
  background: #1e293b;
}

.sidebar a {
  color: #cbd5e1;
}

.col-md-3.net-section {
    padding: 0;
}

.dash-section {
    margin-top: 40px;
}

.col-md-9.my-pro {
    padding: 40px;
}
@media(max-width:768px){

       .col-md-2.show-section {
        height: inherit;
    }
    .table th,
  .table td {
    font-size: 12px;
    padding: 6px;
  }
}
</style>


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
