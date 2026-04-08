<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Dynamic Holidays.io homepage powered by live accommodation and activity data.')">
    <title>@yield('title', 'Holidays.io')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/site.css') }}">
    @stack('styles')
</head>
<body>
    <div class="top-bar">
        <div class="wrap top-bar-inner">
            <div class="top-meta">
                <span><i class="fa-solid fa-phone"></i> +230 52 51 11 53</span>
                <span><i class="fa-solid fa-globe"></i> Your Local Connection - Mauritius</span>
            </div>
            <div class="top-links">
                @if(auth('traveler')->check())
                    <a href="{{ route('traveler.profile') }}">My Profile</a>
                    <form method="POST" action="{{ route('traveler.logout') }}" class="top-inline-form">
                        @csrf
                        <button type="submit" class="top-link-button">Traveller Logout</button>
                    </form> 
                @else
                 <a href="{{ route('traveler.login') }}">Traveller Login</a>
                    <a href="{{ route('traveler.register') }}">Traveller Register</a> 
                @endif
                <!-- <a href="{{ route('operator.login') }}">Operator Login</a>
                <a href="{{ route('operator.register') }}">Operator Register</a> -->
            </div>
        </div>
    </div>

    <header class="site-header">
        <div class="wrap site-header-inner">
            <a href="{{ url('/') }}" class="brand">
                <img src="{{ asset('images/holidays-io-logo.png') }}" alt="Holidays.io logo">
                <!-- <div>
                    <small>Your local connection</small>
                    <strong>Holidays<span>.io</span></strong>
                </div> -->
            </a>

            <nav class="main-nav">
                <a href="{{ url('/') }}" class="is-active">Home</a>
                <a href="{{ url('/#accommodations-section') }}">Accommodation</a>
                <a href="{{ url('/#activities-section') }}">Activities</a>
                <a href="{{ url('/#discover-mauritius') }}">Discover Mauritius</a>
                <a href="{{ route('frontend.booking.checkout') }}"><i class="fa-solid fa-check-to-slot"></i> Checkout</a>
            </nav>
        </div>
    </header>

    @yield('content')

    <footer class="site-footer">
        <div class="wrap">
            <div class="site-footer-grid">
                <div>
                    <h4>Holidays.io </h4>
                    <!-- <p>
                        This homepage now reads live activity and accommodation content entered by operators,
                        while keeping your operator and admin panels unchanged.
                    </p> -->
                </div>
                <div>
                    <h4>Browse</h4>
                    <ul>
                        <li><a href="{{ url('/#activities-section') }}">Activities</a></li>
                        <li><a href="{{ url('/#accommodations-section') }}">Holiday Rentals</a></li>
                        <li><a href="{{ url('/#discover-mauritius') }}">Hotels</a></li>
                    </ul>
                </div>
                <div>
                           @if(auth('traveler')->check())
                    <h4>Traveler</h4>
                    <ul>
                         @if(auth('traveler')->check())
                    
                        @else
                        <li>
                        <a href="{{ route('traveler.login') }}">Traveller Login</a></li>
                        <li>
                            <a href="{{ route('traveler.register') }}">Traveller Register</a> </li>
                        @endif
                        
                    </ul>
                     @endif
                </div>
            </div>
            <div class="site-footer-bottom">
                <span>© {{ now()->year }} Holidays.io</span>
                <!-- <span>Dynamic public frontend powered by Laravel</span> -->
            </div>
        </div>
    </footer>

    <script src="{{ asset('frontend/js/site.js') }}"></script>
    @stack('scripts')
</body>
</html>
