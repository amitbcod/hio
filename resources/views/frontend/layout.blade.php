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
                <a href="{{ route('frontend.booking.cart') }}"><i class="fa-solid fa-cart-shopping"></i> Cart</a>
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
                    <h4>Guest Orders</h4>
                    <ul>
                        <li><a href="javascript:void(0);" onclick="openGuestAccessModal()">Access My Guest Booking</a></li>
                        <li>
                            @if(!auth('traveler')->check())
                                <a href="{{ route('traveler.login') }}">Traveller Login</a>
                            @endif
                        </li>
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

        <!-- ═════════════════════════════════════════════════════════
             Guest Access Modal
        ═════════════════════════════════════════════════════════ -->
        <div id="guestAccessModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
            <div class="modal-content" style="background: white; padding: 40px; border-radius: 8px; width: 90%; max-width: 450px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 20px;">Access Your Guest Booking</h2>
                    <button type="button" onclick="closeGuestAccessModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">×</button>
                </div>

                <div class="modal-body">
                    <p style="color: #666; margin-bottom: 20px;">Enter your email address to search for your guest bookings.</p>

                    <form id="guestAccessForm" style="display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <label for="guestEmail" style="display: block; margin-bottom: 5px; font-weight: 500;">Email Address</label>
                            <input type="email" id="guestEmail" name="email" placeholder="your@email.com" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                        </div>

                        <button type="submit" style="padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 14px;">
                            Find My Bookings
                        </button>
                    </form>

                    <p style="font-size: 12px; color: #999; margin-top: 15px; text-align: center;">
                        You'll receive an OTP via email to access your guest booking details.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('frontend/js/site.js') }}"></script>
    @stack('scripts')

    <script>
        function openGuestAccessModal() {
            const modal = document.getElementById('guestAccessModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeGuestAccessModal() {
            const modal = document.getElementById('guestAccessModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('guestAccessModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeGuestAccessModal();
                    }
                });

                // Handle form submission
                document.getElementById('guestAccessForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const email = document.getElementById('guestEmail').value;
                    if (email) {
                        // Redirect to guest order search page or send email
                        window.location.href = '/traveler/guest-order-search?email=' + encodeURIComponent(email);
                    }
                });
            }
        });
    </script>
</body>
</html>
