<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-base-url" content="{{ url('/') }}">
    <meta name="description" content="@yield('meta_description', 'Dynamic Holidays.io homepage powered by live accommodation and activity data.')">
    <title>@yield('title', 'Holidays.io')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&family=Roboto+Slab:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/site.css') }}">
    <style>
        .header-home-icon {
            margin-right: 10px;
            font-size: 24px;
            color: #16213e;
            vertical-align: middle;
        }

        .header-cart-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            margin-left: 6px;
            padding: 0 6px;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            background: #ff5a5f;
            border-radius: 999px;
        }
        .mini-cart-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 9998;
            display: none;
            justify-content: flex-end;
            align-items: stretch;
            overflow: hidden;

            /* opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease; */
        }

        .mini-cart-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .mini-cart-panel {
            width: min(420px, 100%);
            background: #fff;
            box-shadow: -4px 0 30px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;

            /* transform: translateX(100%);
            transition: transform 0.35s ease; */
        }

        .mini-cart-overlay.show .mini-cart-panel {
            transform: translateX(0);
        }

        .mini-cart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px;
            border-bottom: 1px solid #eee;
            background: #fafafa;
        }
        .mini-cart-header h2 {
            margin: 0;
            font-size: 18px;
            line-height: 1.2;
        }
        .mini-cart-close {
            border: none;
            background: none;
            font-size: 24px;
            cursor: pointer;
            color: #333;
        }
        .mini-cart-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .mini-cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #f1f1f1;
            align-items: start;
        }
        .mini-cart-item:last-child {
            border-bottom: none;
        }
        .mini-cart-item-image {
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: relative;
        }
        .mini-cart-item-image img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            background: #f5f5f5;
        }
        .mini-cart-item-image-label {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            color: #111;
            line-height: 1.3;
        }
        .mini-cart-item-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: flex-start;
        }
        .mini-cart-item-meta {
            font-size: 13px;
            color: #666;
            line-height: 1.4;
        }
        .mini-cart-item-price {
            text-align: right;
            font-size: 15px;
            font-weight: 700;
            color: #111;
            min-width: 76px;
        }
        .mini-cart-item-delete {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 28px;
            height: 28px;
            padding: 0;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }
        .mini-cart-item-delete:hover {
            background: rgba(0, 0, 0, 0.8);
        }
        .mini-cart-item-title {
            font-size: 13px;
            font-weight: 700;
            color: #111;
            margin-bottom: 4px;
        }
        .mini-cart-item-date {
            font-size: 12px;
            color: #666;
            margin-bottom: 4px;
        }
        .mini-cart-item-guests {
            font-size: 12px;
            color: #666;
            margin-bottom: 4px;
        }
        .mini-cart-summary {
            padding: 16px 24px;
            border-top: 1px solid #eee;
            background: #f9f9f9;
        }
        .mini-cart-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .mini-cart-summary-row.total {
            font-weight: 700;
            margin-top: 8px;
        }
        .mini-cart-actions {
            display: flex;
            gap: 12px;
            padding: 18px 24px 24px;
            background: #fff;
            border-top: 1px solid #eee;
        }
        .mini-cart-actions .mini-cart-link,
        .mini-cart-actions .mini-cart-checkout-btn {
            flex: 1;
            text-align: center;
            padding: 12px 0;
            border-radius: 8px;
            text-decoration: none;
        }
        .mini-cart-actions .mini-cart-link {
            color: #333;
            background: #f2f2f2;
            border: 1px solid #ddd;
        }
        .mini-cart-actions .mini-cart-checkout-btn {
            color: #fff;
            background: #16213e;
            border: none;
        }
        .mini-cart-empty {
            color: #555;
            font-size: 14px;
            padding: 30px 0;
            text-align: center;
        }
        .mini-cart-message {
            padding: 14px 16px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.4;
            display: none;
        }
        .mini-cart-message.success {
            background: #e6ffed;
            color: #116530;
            border: 1px solid #b9f2c2;
        }
        .mini-cart-message.error {
            background: #ffe8e8;
            color: #9f1c21;
            border: 1px solid #f5c2c2;
        }
    </style>
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
                <!-- <i class="fa-solid fa-house header-home-icon" aria-hidden="true"></i> -->
                <img src="{{ asset('images/holidays-io-logo.png') }}" alt="Holidays.io logo">
                <!-- <div>
                    <small>Your local connection</small>
                    <strong>Holidays<span>.io</span></strong>
                </div> -->
            </a>
            <div class="mobile-menu-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </div>
            <nav class="main-nav">
                <a href="{{ url('/') }}" class="is-active">Home</a>
                <a href="{{ url('/#accommodations-section') }}">Accommodation</a>
                <a href="{{ url('/#activities-section') }}">Activities</a>
                <a href="{{ url('/#discover-mauritius') }}">Discover Mauritius</a>
                <a href="{{ url('/operator/accommodation') }}">Operator</a>
                <a href="{{ route('frontend.booking.cart') }}" id="headerCartToggle"><i class="fa-solid fa-cart-shopping"></i> Cart <span id="headerCartCount" class="header-cart-badge">{{ count(session('booking_cart', [])) }}</span></a>
            </nav>
        </div>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const menuIcon = document.querySelector('.mobile-menu-icon');
        const nav = document.querySelector('.main-nav');

        menuIcon.addEventListener('click', function () {
            nav.classList.toggle('active');
        });
    });
    </script>
    

    @php
        $sharedCartToken = session('booking_shared_cart_token');
        $sharedCartUrl = $sharedCartToken ? route('frontend.booking.shared', $sharedCartToken) : null;
        $isOperatorOrAdmin = auth('operator')->check() || session('admin_id');
    @endphp

    @if($sharedCartToken && $sharedCartUrl && $isOperatorOrAdmin)
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div class="wrap" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                <div>
                    <p style="margin: 0 0 4px 0; font-size: 13px; opacity: 0.9;">🔗 Building Shared Cart</p>
                    <p style="margin: 0; font-size: 14px; font-weight: 600;">Add items below, then copy & send this link to your customer</p>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; min-width: 200px;">
                    <input type="text" readonly value="{{ $sharedCartUrl }}" id="sharedCartUrlInput" 
                        style="flex: 1; padding: 8px 12px; border: none; border-radius: 4px; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    <button type="button" onclick="copySharedCartUrl(this)" 
                        style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 8px 14px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s; white-space: nowrap;"
                        onmouseover="this.style.background='rgba(255,255,255,0.3)'" 
                        onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                        📋 Copy
                    </button>
                </div>
            </div>
        </div>
        <script>
            function copySharedCartUrl(btn) {
                const input = document.getElementById('sharedCartUrlInput');
                const url = input.value;
                navigator.clipboard.writeText(url).then(() => {
                    const originalText = btn.textContent;
                    btn.textContent = '✓ Copied!';
                    btn.style.background = 'rgba(76, 175, 80, 0.3)';
                    setTimeout(() => {
                        btn.textContent = originalText;
                        btn.style.background = 'rgba(255,255,255,0.2)';
                    }, 2000);
                }).catch((err) => {
                    console.error('Copy failed:', err);
                    alert('Failed to copy. Please copy manually from the input field.');
                });
            }
        </script>
    @endif

    <div id="miniCartOverlay" class="mini-cart-overlay" aria-hidden="true">
        <div class="mini-cart-panel" role="dialog" aria-modal="true" aria-labelledby="miniCartTitle">
            <div class="mini-cart-header">
                <div>
                    <h2 id="miniCartTitle">Booking Cart</h2>
                    <p id="miniCartCountText" style="margin: 6px 0 0; color: #666; font-size: 13px;">0 items in cart</p>
                </div>
                <button id="closeMiniCartBtn" type="button" class="mini-cart-close" aria-label="Close cart">×</button>
            </div>
            <div class="mini-cart-body">
                <div id="miniCartMessage" class="mini-cart-message"></div>
                <div id="miniCartItems"></div>
                <div id="miniCartEmpty" class="mini-cart-empty" style="display:none;">Your cart is empty. Add a booking to see it here.</div>
            </div>
            <div class="mini-cart-summary" id="miniCartSummary">
                <div class="mini-cart-summary-row"><span>Subtotal</span><span id="miniCartSubtotal">USD 0.00</span></div>
                <div class="mini-cart-summary-row"><span>Discount</span><span id="miniCartDiscount">USD 0.00</span></div>
                <div class="mini-cart-summary-row"><span>Tax / Fees</span><span id="miniCartTaxFees">USD 0.00</span></div>
                <div class="mini-cart-summary-row total"><span>Total</span><span id="miniCartTotal">USD 0.00</span></div>
            </div>
            <div class="mini-cart-actions">
                <a href="{{ route('frontend.booking.cart') }}" class="mini-cart-link">View Cart</a>
                <a href="{{ auth('traveler')->check() ? route('frontend.booking.checkout') : route('frontend.booking.guest-checkout') }}" class="mini-cart-checkout-btn">Proceed to Checkout</a>
            </div>
        </div>
    </div>

    @yield('content')

    <footer class="site-footer">
        <div class="wrap">
            <div class="site-footer-grid">
                <div>
                    <h4>Holidays.io </h4>
                    <p>Wishes you all great Holidays and awesome Adventures</p>
                    <!-- <p>
                        This homepage now reads live activity and accommodation content entered by operators,
                        while keeping your operator and admin panels unchanged.
                    </p> -->
                    <ul class="social-footer">
                        <li><a href="#"><i class="fa-brands fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fa-brands fa-youtube"></i></a></li>
                        <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                        <li><a href="#"><i class="fa-brands fa-x-twitter"></i></a></li>
                    </ul>
                </div>

                <div>
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="{{ url('/#accommodations-section') }}">Accommodation</a></li>
                        <li><a href="{{ url('/#activities-section') }}">Activities</a></li>
                        <li><a href="{{ url('/#discover-mauritius') }}">Discover Mauritius
</a></li>
                        <li><a href="{{ url('/operator/accommodation') }}">Operator</a></li>
                        
                    </ul>
                </div>
                <div>
                    <h4>Links</h4>
                    <ul>
                        <li><a href="{{ url('/#accommodations-section') }}">About Us</a></li>
                        <li><a href="{{ url('/#activities-section') }}">Contact</a></li>
                        <li><a href="{{ url('/#discover-mauritius') }}">Travels Blogs</a></li>
                        <li><a href="{{ url('#') }}">Terms of Use</a></li>
                        <li><a href="{{ url('#') }}">Privacy Statement</a></li>
                        <li><a href="{{ url('#') }}">Conditions</a></li>
                    </ul>
                </div>

                <div>
                    <h4>Contacts</h4>
                    <ul class="contact-footer">
                        <li><i class="fa-solid fa-location-dot"></i><span>123, lorem ipsum, city, conutry 200002</span></li>
                        <li><a href="#"><i class="fa-solid fa-phone"></i><span>+00 1234567890</span></a></li>
                        <li><a href="mailto:info@holidays.io"><i class="fa-solid fa-envelope"></i><span>info@holidays.io</span></a></li>
                    </ul>
                </div>

                <!-- <div>
                    <h4>Guest Orders</h4>
                    <ul>
                        <li><a href="javascript:void(0);" onclick="openGuestAccessModal()">Access My Guest Booking</a></li>
                        <li>
                            @if(!auth('traveler')->check())
                                <a href="{{ route('traveler.login') }}">Traveller Login</a>
                            @endif
                        </li>
                    </ul>
                </div> -->
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
                <span>© {{ now()->year }} Holidays.io All rights reserved. </span>
                <span>No part of this site may be reproduced without our written permission</span>
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
