@extends('frontend.layout')

@section('title', 'My Trips | Holidays.io')
@section('meta_description', 'Manage your travel trips on Holidays.io.')

@section('content')
<section class="page-section traveler-trips-section">
    <div class="wrap">
        <div class="traveler-trips-card">
            <div class="traveler-trips-head">
                <h1>My Trips</h1>
                <!-- <p>View and manage your holiday trips in a simple table format.</p> -->
            </div>

            @if($ongoingTrips->count() > 0 || $pastTrips->count() > 0)
                <!-- Tab Navigation -->
                <div class="traveler-trips-tabs">
                    <button class="traveler-trips-tab-btn active" data-tab="ongoing">
                        Ongoing Trips {{ $ongoingTrips->count() > 0 ? '(' . $ongoingTrips->count() . ')' : '' }}
                    </button>
                    <button class="traveler-trips-tab-btn" data-tab="past">
                        Past Trips {{ $pastTrips->count() > 0 ? '(' . $pastTrips->count() . ')' : '' }}
                    </button>
                </div>

                <!-- Ongoing Trips Tab -->
                <div id="ongoing" class="traveler-trips-tab-content active">
                    @if($ongoingTrips->count() > 0)
                        <div class="traveler-trips-table-wrapper">
                            <table class="traveler-trips-table">
                                <thead>
                                    <tr>
                                        <th>Trip</th>
                                        <th>Service Type</th>
                                        <th>Dates</th>
                                        <th>Status</th>
                                        <th>Bookings</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ongoingTrips as $trip)
                                        @php
                                            $serviceTypes = collect();
                                            
                                            if ($trip->accommodationBookings && $trip->accommodationBookings->isNotEmpty()) {
                                                $serviceTypes->push('Accommodation');
                                            }
                                            
                                            if ($trip->activityBookings && $trip->activityBookings->isNotEmpty()) {
                                                $serviceTypes->push('Activity');
                                            }
                                            
                                            if ($serviceTypes->isEmpty()) {
                                                $serviceTypes->push('Travel');
                                            }
                                            
                                            $serviceTypes = $serviceTypes->unique();
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="trip-name-cell">
                                                    <strong>Trip #00{{ $trip->id }}</strong>
                                                    <span>Trip</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="service-type-badges">
                                                    @foreach($serviceTypes as $type)
                                                        <span class="service-badge">{{ $type }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                {{ $trip->start_date ? $trip->start_date->format('d M Y') : 'N/A' }}
                                                -
                                                {{ $trip->end_date ? $trip->end_date->format('d M Y') : 'N/A' }}
                                            </td>
                                            <td>
                                                <span class="trip-status trip-status--{{ $trip->status }}">{{ ucfirst($trip->status) }}</span>
                                            </td>
                                            <td>{{ ($trip->accommodationBookings ? $trip->accommodationBookings->count() : 0) + ($trip->activityBookings ? $trip->activityBookings->count() : 0) }}</td>
                                            <td class="trip-actions-cell">
                                                <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.detail', ['otp' => $otp, 'trip' => $trip->id]) : route('traveler.trip.detail', $trip) }}" class="btn btn-primary">Details</a>
                                                <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.trip.download-invoice', ['otp' => $otp, 'trip' => $trip->id]) : route('traveler.trip.download-invoice', $trip) }}" class="btn btn-secondary" download>Invoice</a>
                                                @if(!isset($guestMode) || !$guestMode)
                                                    <form method="POST" action="{{ route('traveler.trip.add-service', $trip) }}" style="display:inline-block; margin-left: 0px;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-secondary a-font">Add Service</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="traveler-empty-state-tab">
                            <p>No ongoing trips. Your next adventure awaits!</p>
                        </div>
                    @endif
                </div>

                <!-- Past Trips Tab -->
                <div id="past" class="traveler-trips-tab-content">
                    @if($pastTrips->count() > 0)
                        <div class="traveler-trips-table-wrapper">
                            <table class="traveler-trips-table">
                                <thead>
                                    <tr>
                                        <th>Trip</th>
                                        <th>Service Type</th>
                                        <th>Dates</th>
                                        <th>Status</th>
                                        <th>Bookings</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pastTrips as $trip)
                                        @php
                                            $serviceTypes = collect();
                                            
                                            if ($trip->accommodationBookings && $trip->accommodationBookings->isNotEmpty()) {
                                                $serviceTypes->push('Accommodation');
                                            }
                                            
                                            if ($trip->activityBookings && $trip->activityBookings->isNotEmpty()) {
                                                $serviceTypes->push('Activity');
                                            }
                                            
                                            if ($serviceTypes->isEmpty()) {
                                                $serviceTypes->push('Travel');
                                            }
                                            
                                            $serviceTypes = $serviceTypes->unique();
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="trip-name-cell">
                                                    <strong>Trip #00{{ $trip->id }}</strong>
                                                    <!-- <span>Trip</span> -->
                                                </div>
                                            </td>
                                            <td>
                                                <div class="service-type-badges">
                                                    @foreach($serviceTypes as $type)
                                                        <span class="service-badge">{{ $type }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                {{ $trip->start_date ? $trip->start_date->format('d M Y') : 'N/A' }}
                                                -
                                                {{ $trip->end_date ? $trip->end_date->format('d M Y') : 'N/A' }}
                                            </td>
                                            <td>
                                                <span class="trip-status trip-status--{{ $trip->status }}">{{ ucfirst($trip->status) }}</span>
                                            </td>
                                            <td>{{ ($trip->accommodationBookings ? $trip->accommodationBookings->count() : 0) + ($trip->activityBookings ? $trip->activityBookings->count() : 0) }}</td>
                                            <td class="trip-actions-cell">
                                                <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.detail', ['otp' => $otp, 'trip' => $trip->id]) : route('traveler.trip.detail', $trip) }}" class="btn btn-primary">Details</a>
                                                <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.trip.download-invoice', ['otp' => $otp, 'trip' => $trip->id]) : route('traveler.trip.download-invoice', $trip) }}" class="btn btn-secondary" download>Invoice</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="traveler-empty-state-tab">
                            <p>No past trips yet. Start booking to create your travel history!</p>
                        </div>
                    @endif
                </div>

            @else
                <div class="traveler-empty-state">
                    <p>No trips found. Start planning your holiday!</p>
                    <a href="/" class="btn btn-primary">Browse Accommodations</a>
                </div>
            @endif
        </div>
    </div>
</section>

<section class="page-section traveler-trips-help-section">
    <div class="wrap">
        <div class="traveler-trips-help-card">
            <p>If you have any questions, concerns, or require assistance, please contact us at <a href="mailto:info@holidays.io">info@holidays.io</a>.</p>
        </div>
    </div>
</section>

<style>
    .a-font {
        font-size: 16px;
        font-family: 'Open Sans', Arial, sans-serif;
    }
    
    .traveler-trips-help-card {
        max-width: 880px;
        margin: 0 auto 30px;
        padding: 18px 22px;
        background: #f9fbff;
        border: 1px solid #dce7f5;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(17, 74, 128, 0.04);
        color: #1f3f66;
        font-size: 0.98rem;
        line-height: 1.75;
        text-align: center;
    }

    .traveler-trips-help-card a {
        color: #1659c2;
        text-decoration: none;
        font-weight: 700;
    }

    .traveler-trips-help-card a:hover {
        text-decoration: underline;
    }

    .traveler-trips-tabs {
        display: flex;
        gap: 0;
        border-bottom: 2px solid #e0e0e0;
        margin-bottom: 0;
        background: #fff;
        border-radius: 10px 10px 0 0;
    }

    .traveler-trips-tab-btn {
        padding: 14px 24px;
        background: #f5f5f5;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #666;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .traveler-trips-tab-btn:first-child {
        border-radius: 10px 0 0 0;
    }

    .traveler-trips-tab-btn:hover {
        background: #f0f0f0;
        color: #333;
    }

    .traveler-trips-tab-btn.active {
        background: #fff;
        color: var(--brand);
        border-bottom-color: var(--brand);
    }

    .traveler-trips-tab-content {
        display: none;
    }

    .traveler-trips-tab-content.active {
        display: block;
    }

    .traveler-empty-state-tab {
        padding: 40px 20px;
        text-align: center;
        background: #f9f9f9;
        border-radius: 0 0 10px 10px;
        color: #666;
        font-size: 16px;
    }

    .traveler-trips-table-wrapper {
        overflow-x: auto;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
    }

    .traveler-trips-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 720px;
    }

    .traveler-trips-table th,
    .traveler-trips-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f0f0f0;
        text-align: left;
        vertical-align: middle;
    }

    .traveler-trips-table th {
        background: #faf7f2;
        color: #333;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        font-size: 0.85rem;
    }

    .trip-name-cell {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .trip-name-cell span {
        color: #777;
        font-size: 0.95rem;
    }

    .service-type-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .service-badge {
        display: inline-block;
        padding: 6px 10px;
        background: #fff4e5;
        color: #bf6d14;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .trip-status {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .trip-status--planned {
        background: #e8f4ff;
        color: #1565c0;
    }

    .trip-status--active {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .trip-status--completed {
        background: #f1f8e9;
        color: #558b2f;
    }

    .trip-status--cancelled {
        background: #ffebee;
        color: #c62828;
    }

    .trip-actions-cell {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
    }

    .trip-actions-cell a, .trip-actions-cell button {
        padding: 8px 10px !important;
        font-size: 15px;
    }

    @media (max-width: 900px) {
        .traveler-trips-table {
            min-width: 600px;
        }
    }

    @media (max-width: 700px) {
        .traveler-trips-table,
        .traveler-trips-table thead,
        .traveler-trips-table tbody,
        .traveler-trips-table th,
        .traveler-trips-table td,
        .traveler-trips-table tr {
            display: block;
        }

        .traveler-trips-table thead {
            float: left;
        }

        .traveler-trips-table tbody {
            width: auto;
            position: relative;
            overflow-x: auto;
        }

        .traveler-trips-table tr {
            margin-bottom: 16px;
        }

        .traveler-trips-table th {
            display: none;
        }

        .traveler-trips-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 10px;
            border: none;
            border-bottom: 1px solid #f0f0f0;
        }

        .traveler-trips-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #555;
            margin-right: 8px;
        }

        .trip-actions-cell {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.traveler-trips-tab-btn');
        const tabContents = document.querySelectorAll('.traveler-trips-tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');

                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // Add active class to clicked button and corresponding content
                this.classList.add('active');
                document.getElementById(tabName).classList.add('active');
            });
        });
    });
</script>
@endsection
