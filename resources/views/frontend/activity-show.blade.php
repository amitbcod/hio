@extends('frontend.layout')

@section('title', $activity['title'] . ' | Activity | Holidays.io')
@section('meta_description', $activity['excerpt'])

@section('content')
    @php
        $booking = $activity['booking'] ?? [
            'activity_date' => now()->toDateString(),
            'activity_date_display' => now()->format('d-m-Y'),
            'participants' => 1,
        ];
        $availableRooms = $activity['available_rooms'] ?? [];
    @endphp

    <section class="page-hero">
        <div class="page-hero-media">
            <img src="{{ $activity['image'] }}" alt="{{ $activity['title'] }}">
        </div>
        <div class="wrap page-hero-content">
            <div class="breadcrumbs">
                <a href="{{ url('/') }}">Home</a>
                <span>/</span>
                <a href="{{ url('/#activities-section') }}">Activities</a>
                <span>/</span>
                <span>{{ $activity['title'] }}</span>
            </div>
            <h1>{{ $activity['title'] }}</h1>
            <p>{{ $activity['excerpt'] }}</p>
        </div>
    </section>

    <section class="page-section">
        <div class="wrap detail-shell detail-shell--booking">
            <aside class="detail-side booking-side">
                <form method="GET" action="{{ route('frontend.activities.show', $activity['id']) }}" class="booking-form">
                    <div class="side-list">
                        <div class="side-item">
                            <span>Select Date</span>
                            <strong>{{ $booking['activity_date_display'] }}</strong>
                            <input type="date" name="activity_date" value="{{ $booking['activity_date'] }}" class="booking-input" min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="side-item">
                            <span>Participants</span>
                            <strong>{{ $booking['participants'] }}</strong>
                            <input type="number" name="participants" min="1" value="{{ $booking['participants'] }}" class="booking-input">
                        </div>
                        <div class="side-item">
                            <button type="submit" class="btn-primary booking-btn">Check Rates</button>
                            <p class="booking-note">Book for more than 20 people, please contact us directly.</p>
                            <p class="booking-note">On Request Booking (we will get back within 24 hours)</p>
                        </div>
                    </div>
                </form>
            </aside>

            <div class="detail-main">
                <div class="detail-card">
                    <h2>Overview</h2>
                    <div class="detail-text">{!! $activity['overview_text'] ?: $activity['excerpt'] !!}</div>
                </div>

                <div class="detail-card">
                    <h2>Available Options</h2>
                    @if(empty($availableRooms))
                        <div class="detail-text">No availability is currently configured for the selected dates.</div>
                    @else
                        <div class="availability-table-wrap">
                            <table class="availability-table">
                                <thead>
                                    <tr>
                                        <th>Option / Variant</th>
                                        <th>Qty</th>
                                        <th>Total Price</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($availableRooms as $room)
                                        <tr>
                                            <td>{{ $room['room_name'] }}</td>
                                            <td>{{ $room['quantity'] }}</td>
                                            <td>
                                                @if($room['total_price'] !== null)
                                                    <strong>{{ $room['currency'] }} {{ number_format((float) $room['total_price'], 2) }}</strong>
                                                @else
                                                    On request
                                                @endif
                                            </td>
                                            <td>
                                                @if($room['total_price'] !== null)
                                                    <form method="POST" action="{{ route('frontend.booking.cart.add') }}">
                                                        @csrf
                                                        <input type="hidden" name="type" value="activity">
                                                        <input type="hidden" name="activity_id" value="{{ $activity['id'] }}">
                                                        <input type="hidden" name="variant_id" value="{{ $room['room_id'] ?? '' }}">
                                                        <input type="hidden" name="variant_name" value="{{ $room['room_name'] }}">
                                                        <input type="hidden" name="title" value="{{ $activity['title'] }}">
                                                        <input type="hidden" name="image" value="{{ $activity['image'] }}">
                                                        <input type="hidden" name="activity_date" value="{{ $booking['activity_date'] }}">
                                                        <input type="hidden" name="participants" value="{{ $booking['participants'] }}">
                                                        <input type="hidden" name="total_price" value="{{ $room['total_price'] }}">
                                                        <input type="hidden" name="currency" value="{{ $room['currency'] }}">
                                                        <button type="submit" class="btn-book-now">Book Now</button>
                                                    </form>
                                                @else
                                                    <a href="tel:+23052511153" class="btn-book-now btn-book-now--outline">Request</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="detail-card">
                    <h2>Location And Map</h2>
                    <div class="detail-text">
                        {!! $activity['location'] !!}
                        @if(!empty($activity['meeting_point']))

{!! $activity['meeting_point'] !!}
                        @endif
                    </div>
                    @if(!empty($activity['map_embed_url']))
                        <div class="location-map">
                            <iframe
                                src="{{ $activity['map_embed_url'] }}"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="{{ $activity['title'] }} map">
                            </iframe>
                        </div>
                    @endif
                </div>

                <div class="detail-card">
                    <h2>Booking Notes</h2>
                    <div class="detail-text">{!! $activity['booking_notes_text'] ?: 'Booking notes will be shared by the operator during confirmation.' !!}</div>
                </div>

                <div class="detail-card">
                    <h2>Checkout Policy</h2>
                    <div class="detail-text">{!! $activity['checkout_policy_text'] ?: 'Checkout policy is not available yet.' !!}</div>
                </div>

                <div class="detail-card">
                    <h2>Terms And Conditions</h2>
                    <div class="detail-text">{!! $activity['terms_conditions_text'] ?: 'Terms and conditions are not available yet.' !!}</div>
                </div>

                @if(!empty($activity['included_text']))
                    <div class="detail-card">
                        <h2>What’s Included</h2>
                        <div class="detail-text">{!! $activity['included_text'] !!}</div>
                    </div>
                @endif

                @if(!empty($activity['itinerary_text']))
                    <div class="detail-card">
                        <h2>Itinerary</h2>
                        <div class="detail-text">{!! $activity['itinerary_text'] !!}</div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if(!empty($activity['gallery']))
        <section class="page-section" style="padding-top:0;">
            <div class="wrap">
                <div class="section-header">
                    <div>
                        <h2>Gallery</h2>
                        <p>Images uploaded by the operator for this activity.</p>
                    </div>
                </div>
                <div class="gallery-grid">
                    @foreach($activity['gallery'] as $image)
                        <img src="{{ $image }}" alt="{{ $activity['title'] }} gallery image">
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

@push('styles')
    <style>
        .detail-shell--booking {
            grid-template-columns: 0.95fr 1.65fr;
            gap: 24px;
        }

        .detail-main {
            display: grid;
            gap: 24px;
        }

        .booking-input {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 0 12px;
            font: inherit;
            color: var(--ink);
            background: #fff;
        }

        .booking-btn {
            width: 100%;
            border: 0;
            cursor: pointer;
            margin-top: 4px;
        }

        .booking-note {
            margin: 12px 0 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .availability-table-wrap {
            overflow-x: auto;
        }

        .availability-table {
            width: 100%;
            border-collapse: collapse;
        }

        .availability-table th,
        .availability-table td {
            border-bottom: 1px solid var(--line);
            text-align: left;
            padding: 12px 8px;
            font-size: 14px;
        }

        .availability-table th {
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
            font-size: 12px;
        }

        .btn-book-now {
            display: inline-block;
            padding: 8px 18px;
            background: #1a1a2e;
            color: #fff;
            border: 2px solid #1a1a2e;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            transition: background 0.2s, color 0.2s;
        }
        .btn-book-now:hover { background: #16213e; border-color: #16213e; }
        .btn-book-now--outline {
            background: transparent;
            color: #1a1a2e;
        }
        .btn-book-now--outline:hover { background: #1a1a2e; color: #fff; }

        .location-map iframe {
            width: 100%;
            min-height: 320px;
            border: 0;
            border-radius: 16px;
            margin-top: 14px;
        }

        @media (max-width: 1080px) {
            .detail-shell--booking {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush
