@extends('frontend.layout')

@section('title', $accommodation['title'] . ' | Stay | Holidays.io')
@section('meta_description', $accommodation['excerpt'])

@section('content')
    @php
        $booking = $accommodation['booking'] ?? [
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'check_in_display' => now()->format('d-m-Y'),
            'check_out_display' => now()->addDays(2)->format('d-m-Y'),
            'adults' => 2,
            'children' => 0,
            'nights' => 2,
            'total_guests' => 2,
        ];

        $availableRooms = $accommodation['available_rooms'] ?? [];
        $roomCatalog = collect($accommodation['room_catalog'] ?? [])->keyBy('room_id');
        $amenityList = $accommodation['amenity_list'] ?? [];
        $ratingSummary = $ratingSummary ?? ['score' => null, 'score_display' => null, 'count' => 0];
        $similarAccommodations = $similarAccommodations ?? [];
        $gallery = $accommodation['gallery'] ?? [];
        $mainImage = $gallery[0] ?? $accommodation['image'];
        $startingRate = $accommodation['starting_rate'] ?? null;
        $startingCurrency = $availableRooms[0]['currency'] ?? 'MUR';
        $detailQuery = http_build_query([
            'check_in' => $booking['check_in'],
            'check_out' => $booking['check_out'],
            'adults' => $booking['adults'],
            'children' => $booking['children'],
        ]);
    @endphp

    <section class="page-hero">
        <div class="page-hero-media">
            <img src="{{ $mainImage }}" alt="{{ $accommodation['title'] }}">
        </div>
        <div class="wrap page-hero-content">
            <div class="breadcrumbs">
                <a href="{{ url('/') }}">Home</a>
                <span>/</span>
                <a href="{{ url('/#stays-section') }}">Stays</a>
                <span>/</span>
                <span>{{ $accommodation['title'] }}</span>
            </div>

            <div class="hero-meta-row">
                <span class="hero-chip">{{ $accommodation['property_type'] ?: 'Accommodation' }}</span>
                @if(!empty($accommodation['booking_confirmation_type']))
                    <span class="hero-chip">{{ $accommodation['booking_confirmation_type'] }} booking</span>
                @endif
                @if(!empty($ratingSummary['score_display']))
                    <span class="hero-chip">{{ $ratingSummary['score_display'] }}/5 · {{ number_format((int) ($ratingSummary['count'] ?? 0)) }} reviews</span>
                @endif
            </div>

            <h1>{{ $accommodation['title'] }}</h1>
            <p>
                {{ $accommodation['address'] ?: $accommodation['location'] }}
                @if($startingRate)
                    • From {{ $startingCurrency }} {{ number_format((float) $startingRate, 2) }} / night
                @endif
            </p>
        </div>
    </section>

    <section class="page-section detail-page-shell">
        <div class="wrap">
            <div class="detail-top-grid">
                <div class="detail-gallery-card">
                    <img src="{{ $mainImage }}" alt="{{ $accommodation['title'] }}" id="detailMainImage" class="detail-main-image">

                    @if(count($gallery) > 1)
                        <div class="detail-thumbs-row">
                            @foreach($gallery as $index => $image)
                                <button type="button" class="detail-thumb {{ $index === 0 ? 'is-active' : '' }}" data-gallery-image="{{ $image }}">
                                    <img src="{{ $image }}" alt="{{ $accommodation['title'] }} thumbnail {{ $index + 1 }}">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <aside class="detail-booking-card">
                    <form method="GET" action="{{ route('frontend.accommodations.show', $accommodation['id']) }}" class="booking-form-grid">
                        <div class="booking-field">
                            <label>Check-in</label>
                            <input type="date" name="check_in" value="{{ $booking['check_in'] }}" class="booking-input">
                        </div>
                        <div class="booking-field">
                            <label>Check-out</label>
                            <input type="date" name="check_out" value="{{ $booking['check_out'] }}" class="booking-input">
                        </div>
                        <div class="booking-field">
                            <label>Adults</label>
                            <input type="number" name="adults" min="1" value="{{ $booking['adults'] }}" class="booking-input">
                        </div>
                        <div class="booking-field">
                            <label>Children</label>
                            <input type="number" name="children" min="0" value="{{ $booking['children'] }}" class="booking-input">
                        </div>

                        <button type="submit" class="btn-primary booking-btn">Update Search</button>
                    </form>

                    <div class="booking-summary-line">
                        <span>{{ $booking['adults'] }} Adults{{ $booking['children'] > 0 ? ', ' . $booking['children'] . ' Children' : '' }}</span>
                        <strong>{{ max(1, (int) ($booking['nights'] ?? 1)) }} Night{{ ((int) ($booking['nights'] ?? 1)) !== 1 ? 's' : '' }}</strong>
                    </div>
                    @if($startingRate)
                        <div class="booking-starting-rate">
                            <span>Starting from</span>
                            <strong>{{ $startingCurrency }} {{ number_format((float) $startingRate, 2) }}</strong>
                        </div>
                    @endif
                    <div class="booking-quick-links">
                        <a href="#room-options">View room options</a>
                        @if(!empty($accommodation['map_link']))
                            <a href="{{ $accommodation['map_link'] }}" target="_blank" rel="noopener">Get directions</a>
                        @endif
                        @if(!empty($accommodation['contact_phone']))
                            <a href="tel:{{ preg_replace('/\s+/', '', $accommodation['contact_phone']) }}">Call hotel</a>
                        @endif
                    </div>
                </aside>
            </div>

            <nav class="detail-anchor-nav">
                <a href="#room-options">Room Options</a>
                <a href="#amenities">Amenities</a>
                <a href="#reviews">Reviews</a>
                <a href="#policies">Policies</a>
                <a href="#location-map">Location</a>
                <a href="#similar-properties">Similar Properties</a>
            </nav>

            <div class="detail-section-card" id="room-options">
                <h2>Room Options</h2>
                @if(empty($availableRooms))
                    <p class="detail-empty">No room availability is currently configured for the selected dates.</p>
                @else
                    <div class="room-option-list">
                        @foreach($availableRooms as $room)
                            @php
                                $roomDetail = $roomCatalog->get((int) ($room['room_id'] ?? 0));
                                $nights = max(1, (int) ($booking['nights'] ?? 1));
                            @endphp
                            <div class="room-option-item">
                                <div class="room-option-left">
                                    <h3>{{ $room['room_name'] }}</h3>
                                    <p class="room-option-sub">
                                        {{ $room['room_type'] ?: ($roomDetail['room_type'] ?? 'Room') }}
                                        @if(!empty($roomDetail['size_sqm'])) • {{ rtrim(rtrim(number_format((float) $roomDetail['size_sqm'], 2, '.', ''), '0'), '.') }} sqm @endif
                                        @if(!empty($roomDetail['view'])) • {{ $roomDetail['view'] }} view @endif
                                    </p>

                                    @if(!empty($roomDetail['description']))
                                        <p class="room-option-desc">{{ \Illuminate\Support\Str::limit($roomDetail['description'], 180) }}</p>
                                    @endif

                                    <div class="room-option-meta">
                                        @if(!empty($roomDetail['capacity']))
                                            <span>Up to {{ $roomDetail['capacity'] }} guests</span>
                                        @endif
                                        @if(!empty($room['quantity']))
                                            <span>{{ $room['quantity'] }} room{{ (int)$room['quantity'] !== 1 ? 's' : '' }} left</span>
                                        @endif
                                        @if(!empty($roomDetail['smoking']))
                                            <span>{{ $roomDetail['smoking'] }}</span>
                                        @endif
                                        @if(!empty($roomDetail['is_accessible']) || !empty($roomDetail['accessibility']))
                                            <span>Accessible</span>
                                        @endif
                                    </div>

                                    @if(!empty($roomDetail['amenities']))
                                        <div class="room-amenity-chips">
                                            @foreach(array_slice($roomDetail['amenities'], 0, 5) as $amenity)
                                                <span>{{ $amenity }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="room-option-right">
                                    <div class="room-price">
                                        @if($room['total_price'] !== null)
                                            <strong>{{ $room['currency'] }} {{ number_format((float) $room['total_price'], 2) }}</strong>
                                            <small>{{ $nights }} night{{ $nights !== 1 ? 's' : '' }} total</small>
                                        @else
                                            <strong>On request</strong>
                                        @endif
                                    </div>

                                    @if($room['total_price'] !== null)
                                        <form method="POST" action="{{ route('frontend.booking.cart.add') }}" class="room-booking-form">
                                            @csrf
                                            <input type="hidden" name="type" value="accommodation">
                                            <input type="hidden" name="accommodation_id" value="{{ $accommodation['id'] }}">
                                            <input type="hidden" name="room_id" value="{{ $room['room_id'] ?? '' }}">
                                            <input type="hidden" name="room_name" value="{{ $room['room_name'] }}">
                                            <input type="hidden" name="title" value="{{ $accommodation['title'] }}">
                                            <input type="hidden" name="image" value="{{ $accommodation['image'] }}">
                                            <input type="hidden" name="check_in" value="{{ $booking['check_in'] }}">
                                            <input type="hidden" name="check_out" value="{{ $booking['check_out'] }}">
                                            <input type="hidden" name="nights" value="{{ $nights }}">
                                            <input type="hidden" name="adults" value="{{ $booking['adults'] }}">
                                            <input type="hidden" name="children" value="{{ $booking['children'] }}">
                                            <input type="hidden" name="nightly_price" value="{{ $room['nightly_price'] ?? $room['total_price'] }}">
                                            <input type="hidden" name="total_price" value="{{ $room['total_price'] }}">
                                            <input type="hidden" name="currency" value="{{ $room['currency'] }}">
                                            <button type="submit" class="btn-primary room-book-btn">Select Room</button>
                                        </form>
                                    @else
                                        <a href="tel:+23052511153" class="room-request-link">Request quote</a>
                                    @endif

                                    <a href="#policies" class="room-policy-link">View cancellation details &amp; policies</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="detail-section-card" id="amenities">
                <h2>Amenities</h2>
                @if(empty($amenityList))
                    <p class="detail-empty">Amenities have not been configured yet for this property.</p>
                @else
                    <div class="amenity-chip-grid">
                        @foreach($amenityList as $amenity)
                            <span>{{ $amenity }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="detail-section-card" id="reviews">
                <h2>Guest Reviews &amp; Rating</h2>
                @if(!empty($ratingSummary['score_display']))
                    <div class="rating-overview">
                        <div class="rating-score">{{ $ratingSummary['score_display'] }}</div>
                        <div>
                            <strong>Very Good</strong>
                            <p>{{ number_format((int) ($ratingSummary['count'] ?? 0)) }} review{{ (int) ($ratingSummary['count'] ?? 0) !== 1 ? 's' : '' }} from operator profile data.</p>
                        </div>
                    </div>
                @else
                    <p class="detail-empty">No guest reviews available yet for this property.</p>
                @endif
                <div class="detail-text">{{ $accommodation['description_text'] ?: $accommodation['excerpt'] }}</div>
            </div>

            <div class="detail-section-card" id="policies">
                <h2>Property Policies</h2>

                <div class="policy-top-grid">
                    <div>
                        <h3>Check-in / Check-out</h3>
                        <p>
                            Check-in: {{ $accommodation['checkin_time'] ?: 'As per booking confirmation' }}
                            <br>
                            Check-out: {{ $accommodation['checkout_time'] ?: 'As per booking confirmation' }}
                        </p>
                    </div>
                    <div>
                        <h3>Booking Type</h3>
                        <p>{{ $accommodation['booking_confirmation_type'] ?: 'On Request' }}</p>
                    </div>
                </div>

                @if(!empty($accommodation['policy_highlights']))
                    <ul class="policy-highlights">
                        @foreach($accommodation['policy_highlights'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @endif

                <div class="policy-block">
                    <h3>Booking Notes</h3>
                    <div class="detail-text">{{ $accommodation['booking_notes_text'] ?: 'Booking notes will be shared by the operator during confirmation.' }}</div>
                </div>

                <div class="policy-block">
                    <h3>Checkout Policy</h3>
                    <div class="detail-text">{{ $accommodation['checkout_policy_text'] ?: 'Checkout policy is not available yet.' }}</div>
                </div>

                <div class="policy-block">
                    <h3>Terms &amp; Conditions</h3>
                    <div class="detail-text">{{ $accommodation['terms_conditions_text'] ?: 'Terms and conditions are not available yet.' }}</div>
                </div>
            </div>

            <div class="detail-section-card" id="location-map">
                <h2>Location</h2>
                <p class="detail-text">{{ $accommodation['address'] ?: $accommodation['location'] }}</p>
                @if(!empty($accommodation['map_embed_url']))
                    <div class="location-map">
                        <iframe
                            src="{{ $accommodation['map_embed_url'] }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="{{ $accommodation['title'] }} map">
                        </iframe>
                    </div>
                @endif
                <div class="location-quick-links">
                    @if(!empty($accommodation['map_link']))
                        <a href="{{ $accommodation['map_link'] }}" target="_blank" rel="noopener">Get directions</a>
                    @endif
                    @if(!empty($accommodation['contact_phone']))
                        <a href="tel:{{ preg_replace('/\s+/', '', $accommodation['contact_phone']) }}">Call hotel</a>
                    @endif
                    @if(!empty($accommodation['contact_email']))
                        <a href="mailto:{{ $accommodation['contact_email'] }}">Email hotel</a>
                    @endif
                </div>
            </div>

            <div class="detail-section-card" id="similar-properties">
                <h2>Similar Properties</h2>
                @if(empty($similarAccommodations))
                    <p class="detail-empty">No similar properties available at the moment.</p>
                @else
                    <div class="similar-grid">
                        @foreach($similarAccommodations as $item)
                            <article class="similar-card">
                                <a href="{{ $item['url'] . ($detailQuery ? ('?' . $detailQuery) : '') }}" class="similar-image-link">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                                </a>
                                <div class="similar-body">
                                    <h3>
                                        <a href="{{ $item['url'] . ($detailQuery ? ('?' . $detailQuery) : '') }}">{{ $item['title'] }}</a>
                                    </h3>
                                    <p>{{ $item['location'] }}</p>
                                    <div class="similar-footer">
                                        <strong>
                                            @if(!empty($item['starting_rate']))
                                                MUR {{ number_format((float) $item['starting_rate'], 2) }} / night
                                            @else
                                                On request
                                            @endif
                                        </strong>
                                        <a href="{{ $item['url'] . ($detailQuery ? ('?' . $detailQuery) : '') }}" class="btn-secondary">View</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(!empty($gallery))
                <div class="detail-section-card">
                    <h2>Photo Gallery</h2>
                    <div class="gallery-grid detail-gallery-grid">
                        @foreach($gallery as $image)
                            <img src="{{ $image }}" alt="{{ $accommodation['title'] }} gallery image">
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .hero-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }

        .hero-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.32);
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .detail-page-shell {
            padding-top: 38px;
        }

        .detail-top-grid {
            display: grid;
            grid-template-columns: 1.65fr 0.9fr;
            gap: 22px;
            align-items: start;
        }

        .detail-gallery-card,
        .detail-booking-card,
        .detail-section-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 22px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        }

        .detail-gallery-card {
            padding: 16px;
        }

        .detail-main-image {
            width: 100%;
            aspect-ratio: 16 / 8.5;
            object-fit: cover;
            border-radius: 16px;
        }

        .detail-thumbs-row {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .detail-thumb {
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 0;
            background: transparent;
            overflow: hidden;
            cursor: pointer;
            line-height: 0;
        }

        .detail-thumb.is-active {
            border-color: var(--brand);
        }

        .detail-thumb img {
            width: 100%;
            height: 70px;
            object-fit: cover;
            border-radius: 10px;
        }

        .detail-booking-card {
            padding: 20px;
            position: sticky;
            top: 96px;
        }

        .booking-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .booking-field {
            display: grid;
            gap: 6px;
        }

        .booking-field label {
            font-size: 11px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 700;
        }

        .booking-input {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 0 12px;
            font: inherit;
            color: var(--ink);
            background: var(--card);
        }

        .booking-btn {
            grid-column: 1 / -1;
            border: 0;
            cursor: pointer;
            margin-top: 4px;
        }

        .booking-summary-line,
        .booking-starting-rate {
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
            font-size: 14px;
            color: var(--muted);
        }

        .booking-summary-line strong,
        .booking-starting-rate strong {
            color: var(--ink);
            font-size: 18px;
            font-weight: 800;
        }

        .booking-quick-links {
            margin-top: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .booking-quick-links a {
            font-size: 12px;
            color: var(--brand-dark);
            font-weight: 700;
        }

        .detail-anchor-nav {
            margin-top: 18px;
            margin-bottom: 18px;
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 2px;
        }

        .detail-anchor-nav a {
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: var(--card);
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 700;
            color: var(--ink);
        }

        .detail-section-card {
            padding: 24px;
            margin-bottom: 16px;
        }

        .detail-section-card h2 {
            margin: 0 0 16px;
            font-family: 'Roboto Slab', Georgia, serif;
            font-size: 28px;
        }

        .detail-empty {
            margin: 0;
            color: var(--muted);
            line-height: 1.8;
        }

        .room-option-list {
            display: grid;
            gap: 14px;
        }

        .room-option-item {
            display: grid;
            grid-template-columns: 1.7fr 0.9fr;
            gap: 20px;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.82);
        }

        .room-option-left h3 {
            margin: 0;
            font-size: 20px;
            font-family: 'Roboto Slab', Georgia, serif;
            color: var(--ink);
        }

        .room-option-sub {
            margin: 6px 0 8px;
            color: var(--muted);
            font-size: 13px;
        }

        .room-option-desc {
            margin: 0 0 10px;
            color: var(--ink);
            line-height: 1.7;
            font-size: 14px;
        }

        .room-option-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .room-option-meta span {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid var(--line);
            padding: 5px 10px;
            font-size: 12px;
            color: var(--muted);
            font-weight: 700;
        }

        .room-amenity-chips {
            margin-top: 10px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .room-amenity-chips span {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: rgba(65, 175, 170, 0.12);
            border: 1px solid rgba(65, 175, 170, 0.24);
            color: var(--brand-dark);
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 700;
        }

        .room-option-right {
            display: grid;
            justify-items: end;
            align-content: center;
            gap: 10px;
            text-align: right;
        }

        .room-price strong {
            display: block;
            font-size: 24px;
            color: var(--ink);
            font-weight: 800;
        }

        .room-price small {
            color: var(--muted);
            font-size: 12px;
        }

        .room-book-btn {
            border: 0;
            cursor: pointer;
            min-width: 170px;
        }

        .room-request-link,
        .room-policy-link {
            font-size: 12px;
            color: var(--brand-dark);
            font-weight: 700;
        }

        .amenity-chip-grid {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .amenity-chip-grid span {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 13px;
            color: var(--ink);
            font-weight: 700;
            background: rgba(255, 255, 255, 0.7);
        }

        .rating-overview {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 14px;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 14px;
            margin-bottom: 14px;
        }

        .rating-score {
            width: 74px;
            height: 74px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(65, 175, 170, 0.14);
            color: var(--brand-dark);
            font-size: 28px;
            font-weight: 800;
            font-family: 'Roboto Slab', Georgia, serif;
        }

        .rating-overview strong {
            display: block;
            color: var(--ink);
            font-size: 18px;
            margin-bottom: 2px;
        }

        .rating-overview p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .policy-top-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }

        .policy-top-grid h3,
        .policy-block h3 {
            margin: 0 0 6px;
            font-size: 16px;
            color: var(--ink);
        }

        .policy-top-grid p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
            font-size: 14px;
        }

        .policy-highlights {
            margin: 0 0 14px;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 8px;
        }

        .policy-highlights li {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 8px 12px;
            font-size: 13px;
            color: var(--ink);
            background: rgba(255, 255, 255, 0.82);
        }

        .policy-block {
            padding-top: 10px;
            margin-top: 10px;
            border-top: 1px solid var(--line);
        }

        .detail-text {
            color: var(--muted);
            line-height: 1.9;
            white-space: pre-line;
            margin: 0;
        }

        .location-map iframe {
            width: 100%;
            min-height: 320px;
            border: 0;
            border-radius: 16px;
            margin-top: 12px;
        }

        .location-quick-links {
            margin-top: 12px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .location-quick-links a {
            font-size: 13px;
            color: var(--brand-dark);
            font-weight: 700;
        }

        .similar-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .similar-card {
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
            background: var(--card);
            display: grid;
        }

        .similar-image-link img {
            width: 100%;
            height: 170px;
            object-fit: cover;
        }

        .similar-body {
            padding: 12px;
            display: grid;
            gap: 8px;
        }

        .similar-body h3 {
            margin: 0;
            font-size: 17px;
            color: var(--ink);
            font-family: 'Roboto Slab', Georgia, serif;
        }

        .similar-body p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .similar-footer {
            margin-top: 2px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
        }

        .similar-footer strong {
            color: var(--ink);
            font-size: 14px;
        }

        .similar-footer .btn-secondary {
            padding: 8px 14px;
            border: 1px solid var(--line);
        }

        .detail-gallery-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        @media (max-width: 1120px) {
            .detail-top-grid {
                grid-template-columns: 1fr;
            }

            .detail-booking-card {
                position: static;
            }

            .similar-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .detail-gallery-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 860px) {
            .room-option-item {
                grid-template-columns: 1fr;
            }

            .room-option-right {
                justify-items: start;
                text-align: left;
            }

            .policy-top-grid {
                grid-template-columns: 1fr;
            }

            .similar-grid {
                grid-template-columns: 1fr;
            }

            .detail-gallery-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .detail-thumbs-row {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .booking-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const mainImage = document.getElementById('detailMainImage');
            const thumbButtons = document.querySelectorAll('[data-gallery-image]');

            if (!mainImage || !thumbButtons.length) {
                return;
            }

            thumbButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const image = button.getAttribute('data-gallery-image');
                    if (!image) {
                        return;
                    }

                    mainImage.src = image;
                    thumbButtons.forEach((item) => item.classList.remove('is-active'));
                    button.classList.add('is-active');
                });
            });
        })();
    </script>
@endpush
