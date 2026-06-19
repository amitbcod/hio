@extends('frontend.layout')

@section('title', 'Trip Details | Holidays.io')

@section('meta_description', 'View your trip details on Holidays.io.')

@section('content')
    <section class="page-section traveler-trip-detail-section">
        <div class="wrap">
            <!-- Header -->
            <div class="trip-detail-header-section">
                <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.show', ['otp' => $otp]) : route('traveler.trips') }}"
                    class="btn btn-secondary-outline">&larr; Back to Trips</a>
                <div class="trip-id">
                    <h1>Trip ID: <strong>#00{{ $trip->id }}</strong></h1>
                    <!-- <p style="color: #666; font-size: 1rem; margin: 5px 0; padding: 12px 16px; background: #fff3e0; border-left: 4px solid #ff9500; display: inline-block; border-radius: 4px;">Trip ID: <strong>#{{ $trip->id }}</strong></p> -->

                    @php
                        $tripHasEnded = $tripEndDate && \Carbon\Carbon::parse($tripEndDate)->isPast();
                        $traveler = auth('traveler')->user();
                        $canLeaveFeedback = $traveler && $trip->traveler_account_id === $traveler->id && $tripHasEnded;
                    @endphp

                    @if($canLeaveFeedback)
                        <div style="margin-top: 15px;">
                            <a href="{{ route('frontend.feedback.show', ['trip' => $trip->id]) }}" class="btn btn-primary"
                                style="background: #ff9500; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block; font-weight: 600;">
                                ⭐ Share Your Feedback
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Trip Summary Cards -->
            <div class="trip-summary-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
                <div class="summary-card">
                    <label>Status</label>
                    <p>
                        <span class="trip-status trip-status--{{ $trip->status }}"
                            style="display: inline-block; padding: 6px 12px; border-radius: 4px; background: {{ $trip->status === 'planned' ? '#e3f2fd' : ($trip->status === 'active' ? '#e8f5e9' : '#f3e5f5') }}; color: {{ $trip->status === 'planned' ? '#1976d2' : ($trip->status === 'active' ? '#388e3c' : '#7b1fa2') }};">
                            {{ ucfirst($trip->status) }}
                        </span>
                    </p>
                </div>

                <div class="summary-card">
                    <label><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>
                        Start Date</label>
                    <p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>
                        {{ $tripStartDate ? \Carbon\Carbon::parse($tripStartDate)->format('d M Y') : 'Not set' }}
                    </p>
                </div>

                <div class="summary-card">
                    <label><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg> End Date</label>
                    <p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>
                        {{ $tripEndDate ? \Carbon\Carbon::parse($tripEndDate)->format('d M Y') : 'Not set' }}
                    </p>
                </div>
            </div>

            <!-- New Accommodation Bookings Section -->
            <style>
                .booking-card {
                    width:100%;
                    background: #fff;
                    border-radius: 12px;
                    padding: 25px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
                    margin-bottom: 20px;
                }

                .booking-header {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    margin-bottom: 20px;
                    position: relative;
                }

                .header-icon {
                    width: 55px;
                    height: 55px;
                    border-radius: 50%;
                    background: #f7efe0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #1f2a44;
                }

                .booking-header h2 {
                    font-size: 28px;
                    font-weight: 700;
                    margin-bottom: 5px;
                    margin-top: 0;
                    margin-left: 20px;
                    border-bottom: 3px solid #e7a628;
                    display: block;
                    flex: 1;
                    padding-bottom: 10px;
                }

                .booking-content {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    margin-top: 25px;
                }

                .left-section {
                    display: flex;
                    gap: 20px;
                }

                .property-img {
                    width: 180px;
                    height: 145px;
                    border-radius: 10px;
                    object-fit: cover;
                }

                .property-info h3 {
                    font-size: 22px;
                    margin-bottom: 6px;
                    margin-top: 0;
                    color:#333;
                }

                .property-info .type {
                    color: #333;
                    font-size: 22px;
                    margin-bottom: 28px;
                }

                .ref-label {
                    color: #777;
                    margin-bottom: 5px;
                }

                .ref-no {
                    color: #f39c12;
                    font-weight: 700;
                    font-size: 20px;
                }

                .status {
                    background: #e8f7e9;
                    color: #3d9a4f;
                    padding: 10px 20px;
                    border-radius: 8px;
                    font-weight: 600;
                }

                .booking-details {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    margin-top: 25px;
                    border-top: 1px solid #eee;
                    padding-top: 20px;
                }

                .detail-item {
                    padding: 0 15px;
                    border-right: 1px solid #e5e5e5;
                }

                .detail-item:last-child {
                    border-right: none;
                }

                .detail-title {
                    display: flex;
                    align-items: start;
                    gap: 10px;
                    color: #666;
                    margin-bottom: 10px;
                }

                .detail-title i {
                    font-size:20px
                }

                .detail-value {
                    font-weight: 600;
                    color: #1e293b;
                    line-height: 1.6;
                    margin-top: 7px;
                }

                .actions {
                    margin-top: 25px;
                    display: flex;
                    gap: 15px;
                }

                .btn {
                    flex: 1;
                    padding: 15px;
                    border-radius: 8px;
                    font-size: 18px;
                    font-weight: 600;
                    cursor: pointer;
                    border: none;
                }

                .btn-manage {
                    background: #fff;
                    border: 1px solid #ddd;
                    color: #e69b18;
                }

                .btn-download {
                    background: #0057d9;
                    color: #fff;
                    flex-basis: 20%;
                }

                @media(max-width:767px) {

                    .booking-content {
                        flex-direction: column;
                        gap: 15px;
                    }

                    .left-section {
                        flex-direction: column;
                    }

                    .booking-details {
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                    }

                    .detail-item {
                        border-right: none;
                    }

                    .actions {
                        flex-direction: column;
                    }

                    .property-info h3 {
                        font-size: 24px;
                    }

                    .property-info .type {
                        font-size: 18px;
                    }

                    .booking-header h2 {
                        font-size: 22px;
                    }
                }

                @media (max-width:440px){
                    .property-img {
                        width: 100%;
                        height: auto;
                    }
                }
            </style>

            <div class="booking-card" style="display:none">
                <div class="booking-header">
                    <div class="header-icon">
                        <i class="fa-solid fa-bed"></i>
                    </div>
                    <h2>Accommodation Bookings</h2>
                </div>

                <div class="booking-content">
                    <div class="left-section">
                        <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600" alt="Villa"
                            class="property-img">
                        <div class="property-info">
                            <h3>Sandy Cove Villa</h3>
                            <div class="type">Duplex - Two bedrooms</div>

                            <div class="ref-label">Booking Ref</div>
                            <div class="ref-no">ACC-100108-20260619-2</div>
                        </div>
                    </div>
                    <div class="status">
                        Confirmed
                    </div>
                </div>
                <div class="booking-details">
                    <div class="detail-item">
                        <div class="detail-title">
                            <i class="fa-regular fa-calendar"></i>
                            <span>
                                Check-in
                                <div class="detail-value">19 Jun 2026</div>
                            </span>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-title">
                            <i class="fa-regular fa-calendar"></i>
                            <span>
                                Check-out
                                <div class="detail-value">21 Jun 2026</div>
                            </span>
                        </div>
                        
                    </div>

                    <div class="detail-item">
                        <div class="detail-title">
                            <i class="fa-solid fa-users"></i>
                            <span>
                                Guests
                                <div class="detail-value">
                                    2 Booked<br>
                                    0 Added
                                </div>
                            </span>
                        </div>
                        
                    </div>

                    <div class="detail-item">
                        <div class="detail-title">
                            <i class="fa-regular fa-credit-card"></i>
                            <span>
                                Amount
                                <div class="detail-value">
                                    USD 185.40
                                </div>
                            </span>
                        </div>
                        
                    </div>

                </div>

                <div class="actions">
                    <button class="btn btn-manage">
                        <i class="fa-solid fa-gear"></i>
                        Manage
                    </button>

                    <button class="btn btn-download">
                        <i class="fa-solid fa-download"></i>
                        Download Voucher
                    </button>
                </div>
            </div>


            <!-- New activity bookings Section -->

            

            <style>

            .activity-card{
                width:100%;
                background:#fff;
                border:1px solid #e5e5e5;
                border-radius:12px;
                overflow:hidden;
                margin-bottom:20px
            }

            .activity-card-body{
                padding:20px;
            }

            /* Content */

            .booking-content{
                display:flex;
                justify-content:space-between;
                gap:20px;
            }

            .left-section{
                display:flex;
                gap:20px;
                flex:1;
            }

            .activity-img{
                width:110px;
                height:120px;
                border-radius:8px;
                object-fit:cover;
            }

            .activity-info h3{
                font-size:22px;
                margin-bottom:6px;
                color:#333;
                margin-top:0;
            }

            .activity-info .subtitle{
                font-size:16px;
                font-weight:600;
                margin-bottom:20px;
            }

            /* Details */

            .details{
                display:flex;
                gap:30px;
            }

            .detail-item{
                min-width:130px;
                padding-right:25px;
                border-right:1px solid #e5e5e5;
            }
 
            .detail-item:last-child{
                border-right:none;
            }

            .detail-value{
                font-size:16px;
                font-weight:600;
                color:#111827;
                line-height:1.5;
            }

            .detail-small{
                color:#777;
                font-size:14px;
                margin-top:4px;
            }

            /* Right Side */

            .right-section{
                text-align:right;
                min-width:150px;
            }

            .status{
                display:inline-block;
                background:#e8f7e8;
                color:#2e8b3c;
                padding:10px 16px;
                border-radius:8px;
                font-weight:600;
                margin-bottom:20px;
            }

            .manage-link{
                color:#e49a18;
                font-weight:600;
                font-size:16px;
                text-decoration:none;
                display:block;
                margin-bottom:30px;
            }

            .amount-label{
                color:#777;
                margin-bottom:8px;
            }

            .amount{
                font-size:18px;
                font-weight:700;
            }

            /* Time Slot Box */

            .timeslot-box{
                margin-top:25px;
                border:1px solid #d7e5f7;
                background:#f7fbff;
                border-radius:10px;
                padding:20px;
                display:flex;
                justify-content:space-between;
                align-items:center;
            }

            .timeslot-title{
                color:#0058c8;
                font-weight:700;
                margin-bottom:12px;
                display: flex;
            }

            .timeslot-title i{
                margin-right:8px;
            }

            .timeslot-box p{
                color:#222;
                    margin: 5px 0;
            }

            .timeslot-box strong{
                font-weight:700;
            }

            .kayak-icon{
                font-size:55px;
                color:#c8ddf6;
            }

            /* Footer */

            .activity-footer{
                border-top:1px solid #ececec;
                padding:16px;
                text-align:center;
            }

            .activity-footer a{
                color:#0058c8;
                font-weight:600;
                text-decoration:none;
                font-size:18px;
            }

            .activity-footer i{
                margin-left:8px;
            }

            @media (min-width:768px) and (max-width:900px){
                .activity-card .detail-item {
                    min-width: 190px;
                }

                .activity-card .booking-details {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    margin-top: 25px;
                    border-top: 1px solid #eee;
                    padding-top: 20px;
                }
            }

            /* Responsive */

            @media(max-width:767px){

                .booking-content{
                    flex-direction:column;
                }

                .left-section{
                    flex-direction:column;
                }

                .details{
                    flex-wrap:wrap;
                }

                .right-section{
                    text-align:left;
                }

                .timeslot-box{
                    flex-direction:column;
                    align-items:flex-start;
                    gap:15px;
                }
            }
            </style>
            <div class="activity-card" style="display:none">

                <div class="activity-card-body">

                    <div class="booking-header">
                        <div class="header-icon">
                            <i class="fa-solid fa-person-hiking"></i>
                        </div>
                        <h2>Activity Bookings</h2>
                    </div>

                    <div class="booking-content">

                        <div class="left-section">

                            <img
                                src="https://images.unsplash.com/photo-1528127269322-539801943592?w=500"
                                class="property-img"
                                alt="Sea Kayaking">

                            <div>

                                <div class="activity-info">
                                    <h3>Sea Kayaking Ile D'Ambre</h3>
                                    <div class="subtitle">
                                        Sea Kayak Morning Session
                                    </div>
                                </div>

                                <div class="booking-details">

                                    <div class="detail-item">
                                        <div class="detail-title">
                                            <i class="fa-regular fa-calendar"></i>
                                            <span>Activity Date
                                                <div class="detail-value">
                                                    19 Jun 2026
                                                </div>
                                            </span>
                                        </div>
                                        
                                    </div>

                                    <div class="detail-item">
                                        <div class="detail-title">
                                            <i class="fa-solid fa-users"></i>
                                            <span>Participants
                                                <div class="detail-value">
                                                    1 Booked<br>
                                                    0 Added
                                                </div>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="detail-item">
                                        <div class="detail-title">
                                            <i class="fa-regular fa-clock"></i>
                                            <span>Time Slot
                                                <div class="detail-value">
                                                    08:45 - 12:00
                                                </div>
                                                <div class="detail-small">
                                                    Duration: 3h 15m
                                                </div>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="detail-item">
                                        <div class="detail-title">
                                            <span>Amount
                                                <div class="amount">
                                                    USD 60.00
                                                </div>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="right-section">

                            <div class="status">
                                Confirmed
                            </div>

                            <a href="#" class="manage-link">
                                Manage
                                <i class="fa-solid fa-angle-right"></i>
                            </a>
                        </div>

                    </div>

                    <div class="timeslot-box">

                        <div>
                            <div class="timeslot-title">
                                <i class="fa-solid fa-circle-info"></i>
                                <span>Time Slot Details
                                <p>
                                    <strong>8:45 AM</strong> - Meet & briefing at the beach
                                </p>

                                <p>
                                    <strong>12:00 PM</strong> - Activity ends
                                </p></span>
                            </div>
                        </div>
                        <div class="kayak-icon">
                            <i class="fa-solid fa-water"></i>
                        </div>

                    </div>

                </div>

                <div class="activity-footer">
                    <a href="#">
                        View All Activities (1)
                        <i class="fa-solid fa-angle-down"></i>
                    </a>
                </div>

            </div>









            <!-- Accommodation Bookings Section -->
            @if($accommodationBookings->count() > 0)
                <div class="bookings-section"
                    style="margin-bottom: 40px; background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 25px;">
                    <h3 style="font-size: 1.3rem; margin-bottom: 20px; border-bottom: 2px solid #ff9500; padding-bottom: 10px;">
                        Accommodation Bookings</h3>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                            <thead>
                                <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                                    <th style="padding: 12px; text-align: left; font-weight: 600;">Booking Ref</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600;">Accommodation</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600;">Room</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600;">Check-in</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600;">Check-out</th>
                                    <th style="padding: 12px; text-align: center; font-weight: 600;">Guests</th>
                                    <th style="padding: 12px; text-align: right; font-weight: 600;">Amount</th>
                                    <th style="padding: 12px; text-align: center; font-weight: 600;">Status</th>
                                    <th style="padding: 12px; text-align: center; font-weight: 600;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accommodationBookings as $booking)
                                    <tr style="border-bottom: 1px solid #e0e0e0; transition: background 0.2s;">
                                        <td style="padding: 12px; font-weight: 600; color: #ff9500;">
                                            {{ $booking->booking_reference }}</td>
                                        <td style="padding: 12px;">
                                            {{ $booking->accommodation ? $booking->accommodation->property_name : 'N/A' }}
                                        </td>
                                        <td style="padding: 12px;">
                                            {{ $booking->room ? $booking->room->room_name : 'N/A' }}
                                        </td>
                                        <td style="padding: 12px;">
                                            {{ $booking->check_in_date->format('d M Y') }}
                                        </td>
                                        <td style="padding: 12px;">
                                            {{ $booking->check_out_date->format('d M Y') }}
                                        </td>
                                        <td style="padding: 12px; text-align: center;">
                                            @php
                                                $bookedCount = ($booking->adults ?? 0) + ($booking->children ?? 0);
                                                $addedCount = $booking->guests->count();
                                            @endphp
                                            <div style="font-weight: 600;">Booked: {{ $bookedCount }}</div>
                                            <div style="margin-bottom: 8px;">Added: {{ $addedCount }}</div>

                                        </td>
                                        <td style="padding: 12px; text-align: right; font-weight: 600;">
                                            {{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}
                                        </td>
                                        <td style="padding: 12px; text-align: center;">
                                            <span
                                                style="display: inline-block; padding: 4px 10px; background: {{ $booking->booking_status === 'Confirmed' ? '#e8f5e9' : ($booking->booking_status === 'Pending' ? '#fff3e0' : '#ffebee') }}; color: {{ $booking->booking_status === 'Confirmed' ? '#2e7d32' : ($booking->booking_status === 'Pending' ? '#e65100' : '#c62828') }}; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                                                {{ $booking->booking_status }}
                                            </span>
                                        </td>
                                        <td
                                            style="padding: 12px; text-align: center; display:flex; justify-content:center; gap: 8px; flex-wrap:wrap;">
                                            <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.trip.booking.manage-guests', ['otp' => $otp, 'trip' => $trip->id, 'booking' => $booking->id]) : route('traveler.trip.booking.manage-guests', ['trip' => $trip->id, 'booking' => $booking->id]) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                style="margin-top: 5px;font-weight: 600; color: #ff9500;">Manage</a>
                                            <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.trip.booking.download-voucher', ['otp' => $otp, 'trip' => $trip->id, 'booking' => $booking->id]) : route('traveler.trip.booking.download-voucher', ['trip' => $trip->id, 'booking' => $booking->id]) }}"
                                                class="btn btn-sm btn-secondary" style="margin-top: 5px;font-weight: 600;">Download
                                                Voucher</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Activity Bookings Section -->
            @if($activityBookings->count() > 0)
                <div class="bookings-section"
                    style="margin-bottom: 40px; background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 25px;">
                    <h3 style="font-size: 1.3rem; margin-bottom: 20px; border-bottom: 2px solid #ff9500; padding-bottom: 10px;">
                        Activity Bookings</h3>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                            <thead>
                                <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                                    <th style="padding: 12px; text-align: left; font-weight: 600;">Booking Ref</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600;">Activity</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600;">Variant</th>
                                    <th style="padding: 12px; text-align: left; font-weight: 600;">Activity Date</th>
                                    <th style="padding: 12px; text-align: center; font-weight: 600;">Participants</th>
                                    <th style="padding: 12px; text-align: right; font-weight: 600;">Amount</th>
                                    <th style="padding: 12px; text-align: center; font-weight: 600;">Status</th>
                                    <th style="padding: 12px; text-align: center; font-weight: 600;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activityBookings as $booking)
                                    <tr style="border-bottom: 1px solid #e0e0e0; transition: background 0.2s;">
                                        <td style="padding: 12px; font-weight: 600; color: #ff9500;">
                                            {{ $booking->booking_reference }}</td>
                                        <td style="padding: 12px;">
                                            {{ $booking->activity ? $booking->activity->activity_name : 'N/A' }}
                                        </td>
                                        <td style="padding: 12px;">
                                            {{ $booking->variant_name ?? 'Standard' }}
                                        </td>
                                        <td style="padding: 12px;">
                                            {{ $booking->activity_date->format('d M Y') }}
                                        </td>
                                        <td style="padding: 12px; text-align: center;">
                                            @php
                                                $bookedCount = ($booking->adults ?? 0) + ($booking->children ?? 0);
                                                $addedCount = $booking->guests->count();
                                            @endphp
                                            <div style="font-weight: 600;">Booked: {{ $bookedCount }}</div>
                                            <div style="margin-bottom: 8px;">Added: {{ $addedCount }}</div>
                                            @if($booking->participant_time_slots)
                                                @php
                                                    $timeSlotsCount = count(array_filter($booking->participant_time_slots));
                                                @endphp
                                                <div style="font-size: 0.8rem; color: #666;">Time slots:
                                                    {{ $timeSlotsCount }}/{{ $addedCount }}</div>
                                            @endif
                                        </td>
                                        <td style="padding: 12px; text-align: right; font-weight: 600;">
                                            {{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}
                                        </td>
                                        <td style="padding: 12px; text-align: center;">
                                            <span
                                                style="display: inline-block; padding: 4px 10px; background: {{ $booking->booking_status === 'Confirmed' ? '#e8f5e9' : ($booking->booking_status === 'Pending' ? '#fff3e0' : '#ffebee') }}; color: {{ $booking->booking_status === 'Confirmed' ? '#2e7d32' : ($booking->booking_status === 'Pending' ? '#e65100' : '#c62828') }}; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                                                {{ $booking->booking_status }}
                                            </span>
                                        </td>
                                        <td
                                            style="padding: 12px; text-align: center; display:flex; justify-content:center; gap: 8px; flex-wrap:wrap;">
                                            <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.trip.booking.manage-guests', ['otp' => $otp, 'trip' => $trip->id, 'booking' => $booking->id]) : route('traveler.trip.booking.manage-guests', ['trip' => $trip->id, 'booking' => $booking->id]) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                style="margin-top: 5px;font-weight: 600; color: #ff9500;">Manage</a>
                                            @if($booking->guests->count() > 0)
                                                <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.trip.booking.manage-guests', ['otp' => $otp, 'trip' => $trip->id, 'booking' => $booking->id]) : route('traveler.trip.booking.manage-guests', ['trip' => $trip->id, 'booking' => $booking->id]) }}#download-voucher-section"
                                                    class="btn btn-sm btn-secondary" style="margin-top: 5px;font-weight: 600;">Download
                                                    Voucher</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- No Bookings Message -->
            @if($accommodationBookings->count() === 0 && $activityBookings->count() === 0)
                <div style="background: #f9f9f9; padding: 30px; border-radius: 8px; text-align: center; margin-bottom: 30px;">
                    <p style="color: #999; font-size: 1.1rem; margin: 0;">No bookings added to this trip yet.</p>
                </div>
            @endif

            @if((!isset($guestMode) || !$guestMode) && !in_array($trip->status, ['completed', 'cancelled']))
                <!-- Add Services Section -->
                <div class="trip-actions-section"
                    style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 25px; margin-top: 30px;">
                    <h3 style="font-size: 1.3rem; margin-bottom: 20px; border-bottom: 2px solid #ff9500; padding-bottom: 10px;">
                        Add More Services</h3>
                    <p style="color: #666; margin-bottom: 20px;">Expand your trip by adding more accommodations or activities.
                    </p>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <form method="POST" action="{{ route('traveler.trip.add-service', $trip) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary" name="service_type" value="accommodation"
                                style="padding: 12px 24px; background: #ff9500; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.3s;">
                                + Add Accommodation
                            </button>
                        </form>
                        <form method="POST" action="{{ route('traveler.trip.add-service', $trip) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary" name="service_type" value="activity"
                                style="padding: 12px 24px; background: #2196F3; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.3s;">
                                + Add Activity
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Back to Trips -->
            <div style="margin-top: 30px; text-align: center;">
                <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.show', ['otp' => $otp]) : route('traveler.trips') }}"
                    class="btn btn-secondary"
                    style="padding: 12px 30px; background: #f5f5f5; color: #333; border: 1px solid #ddd; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block; transition: background 0.3s;">
                    &larr; Back to All Trips
                </a>
            </div>
        </div>
    </section>

    <style>
        .wrap {
            /* max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px; */
        }

        .page-section {
            padding: 40px 0;
            background: #f9f9f9;
        }

        @media (max-width: 768px) {
            .trip-summary-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }

            table {
                font-size: 0.85rem !important;
            }

            table th,
            table td {
                padding: 8px !important;
            }
        }

        @media (max-width: 480px) {
            .trip-summary-grid {
                grid-template-columns: 1fr !important;
            }

            .trip-detail-header-section h1 {
                font-size: 1.8rem !important;
            }
        }
    </style>
@endsection