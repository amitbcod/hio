@extends('frontend.layout')

@section('title', __('traveler.trips.page_title'))
@section('meta_description', __('traveler.trips.meta_description'))

@section('content')
<section class="page-section traveler-trips-section">
    <div class="wrap">
        <div class="traveler-trips-card">
            <div class="traveler-trips-head">
                <h1>{{ __('traveler.trips.heading') }}</h1>
                <!-- <p>{{ __('traveler.trips.description') }}</p> -->
            </div>

            @if($ongoingTrips->count() > 0 || $pastTrips->count() > 0)
                <!-- Tab Navigation -->
                <div class="traveler-trips-tabs">
                    <button class="traveler-trips-tab-btn active" data-tab="ongoing">
                        {{ __('traveler.trips.ongoing_trips') }} {{ $ongoingTrips->count() > 0 ? '(' . $ongoingTrips->count() . ')' : '' }}
                    </button>
                    <button class="traveler-trips-tab-btn" data-tab="past">
                        {{ __('traveler.trips.past_trips') }} {{ $pastTrips->count() > 0 ? '(' . $pastTrips->count() . ')' : '' }}
                    </button>
                </div>

                <!-- Ongoing Trips Tab -->
                <div id="ongoing" class="traveler-trips-tab-content active">
                    @if($ongoingTrips->count() > 0)
                        <div class="traveler-trips-table-wrapper">
                            <table class="traveler-trips-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('traveler.trips.trip_label') }}</th>
                                        <th>{{ __('traveler.trips.service_type') }}</th>
                                        <th>{{ __('traveler.trips.dates') }}</th>
                                        <th>{{ __('traveler.trips.status') }}</th>
                                        <th>{{ __('traveler.trips.bookings') }}</th>
                                        <th>{{ __('traveler.trips.actions') }}</th>
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
                                            <td data-label="{{ __('traveler.trips.trip_label') }}">
                                                <div class="trip-name-cell">
                                                    <strong>#{{ $trip->id }}</strong>
                                                    <!-- <span>{{ __('traveler.trips.trip_label') }}</span> -->
                                                </div>
                                            </td>
                                            <td data-label="{{ __('traveler.trips.service_type') }}">
                                                <div class="service-type-badges">
                                                    @foreach($serviceTypes as $type)
                                                        <span class="service-badge">
                                                            @if($type === 'Accommodation')
                                                                {{ __('traveler.trips.service_type_accommodation') }}
                                                            @elseif($type === 'Activity')
                                                                {{ __('traveler.trips.service_type_activity') }}
                                                            @else
                                                                {{ __('traveler.trips.service_type_travel') }}
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                          <td data-label="{{ __('traveler.trips.dates') }}">
                                            {{ $trip->start_date ? $trip->start_date->format('d M Y') : __('traveler.trip_detail.not_set') }}

                                            @if($trip->end_date)
                                                - {{ $trip->end_date->format('d M Y') }}
                                            @endif
                                        </td>
                                            <td data-label="{{ __('traveler.trips.status') }}">
                                                <span class="trip-status trip-status--{{ $trip->status }}">{{ ucfirst($trip->status) }}</span>
                                            </td>
                                            <td data-label="{{ __('traveler.trips.bookings') }}" style="text-align: center;">{{ ($trip->accommodationBookings ? $trip->accommodationBookings->count() : 0) + ($trip->activityBookings ? $trip->activityBookings->count() : 0) }}</td>
                                            <td class="trip-actions-cell">
                                                <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.detail', ['otp' => $otp, 'trip' => $trip->id]) : route('traveler.trip.detail', $trip) }}" class="btn btn-primary">{{ __('traveler.trips.details') }}</a>
                                                <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.trip.download-invoice', ['otp' => $otp, 'trip' => $trip->id]) : route('traveler.trip.download-invoice', $trip) }}" class="btn btn-secondary" download>{{ __('traveler.trips.invoice') }}</a>
                                                @if(!isset($guestMode) || !$guestMode)
                                                    <form method="POST" action="{{ route('traveler.trip.add-service', $trip) }}" style="display:inline-block; margin-left: 0px;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-secondary a-font">{{ __('traveler.trips.add_service') }}</button>
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
                            <p>{{ __('traveler.trips.no_ongoing') }}</p>
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
                                        <th>{{ __('traveler.trips.trip_label') }}</th>
                                        <th>{{ __('traveler.trips.service_type') }}</th>
                                        <th>{{ __('traveler.trips.dates') }}</th>
                                        <th>{{ __('traveler.trips.status') }}</th>
                                        <th>{{ __('traveler.trips.bookings') }}</th>
                                        <th>{{ __('traveler.trips.actions') }}</th>
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
                                            <td data-label="{{ __('traveler.trips.trip_label') }}">
                                                <div class="trip-name-cell">
                                                    <strong> #{{ $trip->id }}</strong>
                                                    <!-- <span>{{ __('traveler.trips.trip_label') }}</span> -->
                                                </div>
                                            </td>
                                            <td data-label="{{ __('traveler.trips.service_type') }}">
                                                <div class="service-type-badges">
                                                    @foreach($serviceTypes as $type)
                                                        <span class="service-badge">
                                                            @if($type === 'Accommodation')
                                                                {{ __('traveler.trips.service_type_accommodation') }}
                                                            @elseif($type === 'Activity')
                                                                {{ __('traveler.trips.service_type_activity') }}
                                                            @else
                                                                {{ __('traveler.trips.service_type_travel') }}
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                           <td data-label="{{ __('traveler.trips.dates') }}">
                                                {{ $trip->start_date ? $trip->start_date->format('d M Y') : __('traveler.trip_detail.not_set') }}

                                                @if($trip->end_date)
                                                    - {{ $trip->end_date->format('d M Y') }}
                                                @endif
                                            </td>
                                            <td data-label="{{ __('traveler.trips.status') }}" align="center">
                                                <span class="trip-status trip-status--{{ $trip->status }}">{{ ucfirst($trip->status) }}</span>
                                            </td>
                                            <td data-label="{{ __('traveler.trips.bookings') }}" style="text-align: center;">
                                                {{ ($trip->accommodationBookings ? $trip->accommodationBookings->count() : 0) + ($trip->activityBookings ? $trip->activityBookings->count() : 0) }}
                                            </td>
                                            <td class="trip-actions-cell">
                                                <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.detail', ['otp' => $otp, 'trip' => $trip->id]) : route('traveler.trip.detail', $trip) }}" class="btn btn-primary">{{ __('traveler.trips.details') }}</a>
                                                <a href="{{ isset($guestMode) && $guestMode ? route('traveler.guest-trip.trip.download-invoice', ['otp' => $otp, 'trip' => $trip->id]) : route('traveler.trip.download-invoice', $trip) }}" class="btn btn-secondary" download>{{ __('traveler.trips.invoice') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="traveler-empty-state-tab">
                            <p>{{ __('traveler.trips.no_past') }}</p>
                        </div>
                    @endif
                </div>

            @else
                <div class="traveler-empty-state">
                    <p>{{ __('traveler.trips.no_trips') }}</p>
                    <a href="/" class="btn btn-primary">{{ __('traveler.trips.browse_accommodations') }}</a>
                </div>
            @endif
        </div>
    </div>
</section>

<section class="page-section traveler-trips-help-section">
    <div class="wrap">
        <div class="traveler-trips-help-card">
            <p>{{ __('traveler.trips.help_contact', ['email' => 'info@holidays.io']) }}</p>
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
        cursor: pointer;
    }

    @media (max-width: 900px) {
        .traveler-trips-table {
            /* min-width: 600px; */
            min-width: auto;
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

        .traveler-trips-table td.trip-actions-cell {
            /* flex-direction: column; */
            /* align-items: flex-start; */
            justify-content: center;
            flex-wrap: wrap;
        }

        .traveler-trips-table .trip-actions-cell form {
            flex: 1;
            flex-basis: 100%;
            text-align: center;
        }

        .traveler-trips-table td.trip-actions-cell::before {
            display:none;
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
