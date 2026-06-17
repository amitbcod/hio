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
            'infants' => 0,
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
        $startingCurrency = $availableRooms[0]['currency'] ?? 'USD';
        $detailQuery = http_build_query([
            'check_in' => $booking['check_in'],
            'check_out' => $booking['check_out'],
            'adults' => $booking['adults'],
            'children' => $booking['children'],
            'infants' => $booking['infants'],
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
                    @php $accommodationStars = (int) round($ratingSummary['score_display']); @endphp
                    <span class="hero-chip" aria-label="{{ $accommodationStars }} star rating">{!! str_repeat('<i class="fa-solid fa-star"></i>', max(1, min(5, $accommodationStars))) !!}</span>
                @endif
            </div>

            <h1>
                {{ $accommodation['title'] }}
                @if(!empty($accommodation['rating_display']))
                    @php $titleAccommodationStars = (int) round($accommodation['rating_display']); @endphp
                    <span class="detail-title-rating" aria-label="{{ $titleAccommodationStars }} star rating">{!! str_repeat('<i class="fa-solid fa-star"></i>', max(1, min(5, $titleAccommodationStars))) !!}</span>
                @endif
            </h1>
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
                        <div class="booking-field">
                            <label>Infants</label>
                            <input type="number" name="infants" min="0" value="{{ $booking['infants'] }}" class="booking-input">
                        </div>

                        <button type="submit" class="btn-primary booking-btn">Update Search</button>
                    </form>

                    <div class="booking-summary-line">
                        <span>
                            {{ $booking['adults'] }} Adults
                            {{ $booking['children'] > 0 ? ', ' . $booking['children'] . ' Children' : '' }}
                            {{ $booking['infants'] > 0 ? ', ' . $booking['infants'] . ' Infants' : '' }}
                        </span>
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
                        @php
                            // Group rooms by room_id to show each room once with multiple pricing options
                            $roomsByRoomId = collect($availableRooms)->groupBy('room_id');
                        @endphp
                        @foreach($roomsByRoomId as $roomId => $roomVariants)
                            @php
                                $roomDetail = $roomCatalog->get((int) $roomId);
                                $nights = max(1, (int) ($booking['nights'] ?? 1));
                                $firstVariant = $roomVariants->first();
                            @endphp
                            <div class="room-option-item"
                                data-room-adults="{{ (int) ($roomDetail['capacity'] ?? 0) }}"
                                data-room-children="{{ (int) ($roomDetail['children_capacity'] ?? 0) }}"
                                data-room-infants="{{ (int) ($roomDetail['infant_capacity'] ?? 0) }}"
                                data-room-max-persons="{{ (int) ($roomDetail['max_person_capacity'] ?? ((int) ($roomDetail['capacity'] ?? 0) + (int) ($roomDetail['children_capacity'] ?? 0) + max(0, ((int) ($roomDetail['infant_capacity'] ?? 0) - 1)))) }}">
                                <div class="room-option-left">
                                    <h3>{{ $firstVariant['room_name'] }}</h3>
                                    <p class="room-option-sub">
                                        {{ $firstVariant['room_type'] ?: ($roomDetail['room_type'] ?? 'Room') }}
                                        @if(!empty($roomDetail['size_sqm'])) • {{ rtrim(rtrim(number_format((float) $roomDetail['size_sqm'], 2, '.', ''), '0'), '.') }} sqm @endif
                                        @if(!empty($roomDetail['view'])) • {{ $roomDetail['view'] }} view @endif
                                    </p>

                                    @if(!empty($roomDetail['description']))
                                        <p class="room-option-desc">{{ \Illuminate\Support\Str::limit($roomDetail['description'], 180) }}</p>
                                    @endif

                                    <div class="room-option-meta">
                                        @if(!empty($firstVariant['quantity']))
                                            <span>{{ $firstVariant['quantity'] }} room{{ (int)$firstVariant['quantity'] !== 1 ? 's' : '' }} left</span>
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
                                    <div class="room-capacity-warning" style="display:none; margin-bottom: 12px; color: #b43434; font-weight: 700;"></div>
                                    {{-- Show all pricing plan options for this room --}}
                                    @foreach($roomVariants as $room)
                                        <div class="room-price" style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #eee;">
                                            @if($room['total_price'] !== null)
                                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                                    <strong>{{ $room['currency'] }} {{ number_format((float) $room['total_price'], 2) }}</strong>
                                                    @if(!empty($room['plan_label']))
                                                        <span style="background: #f0f7f7; color: #19b5b5; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">{{ $room['plan_label'] }}</span>
                                                    @endif
                                                </div>
                                                <small>{{ $nights }} night{{ $nights !== 1 ? 's' : '' }} total</small>
                                                @if(!empty($room['pricing_setting']))
                                                    <small style="display: block; color: #666; margin-top: 4px; font-size: 11px;">{{ $room['pricing_setting'] }}</small>
                                                @endif
                                            @else
                                                <strong>On request</strong>
                                            @endif
                                        </div>

                                        @if($room['total_price'] !== null)
                                            <form method="POST" action="{{ route('frontend.booking.cart.add') }}" class="room-booking-form" style="margin-bottom: 8px;">
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
                                                <input type="hidden" name="infants" value="{{ $booking['infants'] }}">
                                                <input type="hidden" name="rooms" value="{{ request()->query('rooms', 1) }}">
                                                <input type="hidden" name="nightly_price" value="{{ $room['nightly_price'] ?? $room['total_price'] }}">
                                                <input type="hidden" name="total_price" value="{{ $room['total_price'] }}">
                                                <input type="hidden" name="currency" value="{{ $room['currency'] }}">
                                                <input type="hidden" name="pricing_setting" value="{{ $room['pricing_setting'] ?? 'Per Room/Night' }}">
                                                <input type="hidden" name="plan_label" value="{{ $room['plan_label'] ?? '' }}">
                                                <button type="submit" class="btn-primary room-book-btn">{{ !empty($room['plan_label']) ? 'Select (' . $room['plan_label'] . ')' : 'Select Room' }}</button>
                                            </form>
                                        @endif
                                    @endforeach

                                    @if($firstVariant['total_price'] === null)
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
                    @php $summaryStars = (int) round($ratingSummary['score_display']); @endphp
                    <div class="rating-overview">
                        <div class="rating-score" aria-label="{{ $summaryStars }} star rating">{!! str_repeat('<i class="fa-solid fa-star"></i>', max(1, min(5, $summaryStars))) !!}</div>
                        <div>
                            <strong>Very Good</strong>
                            <p>{{ number_format((int) ($ratingSummary['count'] ?? 0)) }} review{{ (int) ($ratingSummary['count'] ?? 0) !== 1 ? 's' : '' }} from operator profile data.</p>
                        </div>
                    </div>
                @elseif(empty($approvedAccommodationReviews) || count($approvedAccommodationReviews) === 0)
                    <p class="detail-empty">No guest reviews available yet for this property.</p>
                @endif

                @if($approvedAccommodationReviews && count($approvedAccommodationReviews) > 0)
                    <div class="review-listing" style="margin-top: 30px;">
                        <h3>Guest Reviews</h3>
                        @foreach($approvedAccommodationReviews as $review)
                            @php
                                $travelerName = optional($review->parentReview->trip->traveler)->full_name 
                                    ?? optional($review->parentReview->trip->traveler)->email 
                                    ?? 'Guest';
                                $criteria = is_array($review->criteria) ? $review->criteria : [];
                                $numericRatings = collect($criteria)->filter(fn($value) => is_numeric($value) && $value !== null && $value !== '');
                                $avgRating = $numericRatings->count() ? number_format($numericRatings->avg(), 1) : null;
                            @endphp
                            <div class="review-card">
                                <div class="review-card__header">
                                    <div>
                                        <h4>{{ $travelerName }}</h4>
                                        <p>{{ $review->parentReview->created_at->format('M d, Y') }}</p>
                                    </div>
                                    @if($avgRating)
                                        @php $reviewStars = (int) round($avgRating); @endphp
                                        <div class="review-card__score">
                                            <span class="review-score-badge" aria-label="{{ $reviewStars }} star rating">{!! str_repeat('<i class="fa-solid fa-star"></i>', max(1, min(5, $reviewStars))) !!}</span>
                                        </div>
                                    @endif
                                </div>

                                @if(count($criteria) > 0)
                                    <div class="review-card__criteria-grid">
                                        @foreach($criteria as $key => $value)
                                            @if($value !== null && $value !== '')
                                                <div class="review-card__criteria-item">
                                                    <span>{{ ucwords(str_replace(['_','-'], ' ', $key)) }}</span>
                                                    <strong>{{ is_numeric($value) ? $value . '/5' : $value }}</strong>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                @if($review->review)
                                    <p class="review-card__text">{{ $review->review }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
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
                                                USD {{ number_format((float) $item['starting_rate'], 2) }} / night
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

        .detail-title-rating {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-left: 12px;
            font-size: 1rem;
            color: #1f2937;
            font-weight: 600;
        }

        .detail-title-rating i {
            color: #f59e0b;
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
            color: #008dd7;
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

        .review-card {
            border: 1px solid rgba(25, 181, 85, 0.14);
            background: #ffffff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 18px 45px rgba(16, 34, 71, 0.06);
            margin-bottom: 18px;
        }

        .review-card__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .review-card__header h4 {
            margin: 0 0 6px;
            font-size: 18px;
            color: var(--ink);
        }

        .review-card__header p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .review-card__score {
            text-align: right;
        }

        .review-score-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #155724;
            background: #e6ffed;
            border-radius: 999px;
            padding: 10px 14px;
            font-weight: 700;
            font-size: 14px;
        }

        .review-card__criteria-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .review-card__criteria-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border-radius: 14px;
            background: #f8fbf9;
            color: var(--ink);
            font-size: 14px;
        }

        .review-card__criteria-item span {
            color: #4b5563;
            font-weight: 600;
        }

        .review-card__text {
            margin: 0;
            color: #334155;
            line-height: 1.85;
            font-size: 15px;
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

        // Scroll to room-options when search is updated and validate room availability on the detail page
        (() => {
            const roomOptionsSection = document.getElementById('room-options');
            const bookingForm = document.querySelector('.booking-form-grid');
            const adultsInput = document.querySelector('input[name="adults"]');
            const childrenInput = document.querySelector('input[name="children"]');
            const infantsInput = document.querySelector('input[name="infants"]');
            const roomItems = Array.from(document.querySelectorAll('.room-option-item'));

            const parseCount = (input, fallback = 0) => {
                if (!input) {
                    return fallback;
                }
                const value = parseInt(input.value, 10);
                return Number.isNaN(value) ? fallback : value;
            };

            const updateRoomCapacityWarnings = () => {
                const adults = Math.max(1, parseCount(adultsInput, 1));
                const children = Math.max(0, parseCount(childrenInput, 0));
                const infants = Math.max(0, parseCount(infantsInput, 0));
                const effectiveGuests = adults + children + Math.max(0, infants - 1);

                roomItems.forEach((item) => {
                    const roomAdults = parseInt(item.dataset.roomAdults, 10) || 0;
                    const roomChildren = parseInt(item.dataset.roomChildren, 10) || 0;
                    const roomInfants = parseInt(item.dataset.roomInfants, 10) || 0;
                    const roomMaxPersons = parseInt(item.dataset.roomMaxPersons, 10) || 0;
                    const warning = item.querySelector('.room-capacity-warning');
                    const bookButtons = Array.from(item.querySelectorAll('button[type="submit"]'));

                    const isValid = roomAdults >= adults
                        && roomChildren >= children
                        && roomInfants >= infants
                        && roomMaxPersons >= effectiveGuests;

                    if (!isValid) {
                        if (warning) {
                            warning.textContent = 'This room cannot accommodate your selected number of guests. Please update your search or choose another room.';
                            warning.style.display = 'block';
                        }
                        bookButtons.forEach((button) => {
                            button.disabled = true;
                            button.classList.add('disabled');
                        });
                    } else {
                        if (warning) {
                            warning.style.display = 'none';
                            warning.textContent = '';
                        }
                        bookButtons.forEach((button) => {
                            button.disabled = false;
                            button.classList.remove('disabled');
                        });
                    }
                });
            };

            if (roomOptionsSection && sessionStorage.getItem('scrollToRoomOptions')) {
                sessionStorage.removeItem('scrollToRoomOptions');
                setTimeout(() => {
                    roomOptionsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            }

            if (bookingForm) {
                bookingForm.addEventListener('submit', (e) => {
                    sessionStorage.setItem('scrollToRoomOptions', 'true');
                });
            }

            if (adultsInput || childrenInput || infantsInput) {
                const inputs = [adultsInput, childrenInput, infantsInput].filter(Boolean);
                inputs.forEach((input) => input.addEventListener('input', updateRoomCapacityWarnings));
                updateRoomCapacityWarnings();
            }
        })();
    </script>
@endpush
