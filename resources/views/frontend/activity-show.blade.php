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
            <h1>
                {{ $activity['title'] }}
                @if(!empty($activity['rating_display']))
                    @php $activityDetailStars = (int) round($activity['rating_display']); @endphp
                    <span class="detail-title-rating" aria-label="{{ $activityDetailStars }} star rating">{!! str_repeat('<i class="fa-solid fa-star"></i>', max(1, min(5, $activityDetailStars))) !!}</span>
                @endif
            </h1>
            <p>{{ $activity['excerpt'] }}</p>
        </div>
    </section>

    <section class="page-section detail-page-shell">
        <div class="wrap">
            <div class="detail-top-grid">
                <div class="detail-gallery-card">
                    @php
                        $gallery = $activity['gallery'] ?? [];
                        $mainImage = $gallery[0] ?? $activity['image'];
                    @endphp
                    <img src="{{ $mainImage }}" alt="{{ $activity['title'] }}" id="detailMainImage" class="detail-main-image">

                    @if(count($gallery) > 1)
                        <div class="detail-thumbs-row">
                            @foreach($gallery as $index => $image)
                                <button type="button" class="detail-thumb {{ $index === 0 ? 'is-active' : '' }}" data-gallery-image="{{ $image }}">
                                    <img src="{{ $image }}" alt="{{ $activity['title'] }} thumbnail {{ $index + 1 }}">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <aside class="detail-booking-card">
                    <form method="GET" action="{{ route('frontend.activities.show', $activity['id']) }}" class="booking-form-grid">
                        <div class="booking-field">
                            <label>Activity Date</label>
                            <input type="date" name="activity_date" value="{{ $booking['activity_date'] }}" class="booking-input" min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="booking-field" style="{{ !($activity['allow_adults'] ?? true) ? 'opacity:0.5;pointer-events:none;' : '' }}">
                            <label>Adults{{ !($activity['allow_adults'] ?? true) ? ' (Not Allowed)' : '' }}</label>
                            <input type="number" name="adults" min="1" value="{{ ($activity['allow_adults'] ?? true) ? ($activity['booking']['adults'] ?? 1) : 0 }}" class="booking-input" {{ !($activity['allow_adults'] ?? true) ? 'disabled' : '' }}>
                        </div>
                        <div class="booking-field" style="{{ !($activity['allow_children'] ?? true) ? 'opacity:0.5;pointer-events:none;' : '' }}">
                            <label>Children{{ !($activity['allow_children'] ?? true) ? ' (Not Allowed)' : '' }}</label>
                            <input type="number" name="children" min="0" value="{{ ($activity['allow_children'] ?? true) ? ($activity['booking']['children'] ?? 0) : 0 }}" class="booking-input" {{ !($activity['allow_children'] ?? true) ? 'disabled' : '' }}>
                        </div>
                        <div class="booking-field" style="{{ !($activity['allow_infants'] ?? true) ? 'opacity:0.5;pointer-events:none;' : '' }}">
                            <label>Infants{{ !($activity['allow_infants'] ?? true) ? ' (Not Allowed)' : '' }}</label>
                            <input type="number" name="infants" min="0" value="{{ ($activity['allow_infants'] ?? true) ? ($activity['booking']['infants'] ?? 0) : 0 }}" class="booking-input" {{ !($activity['allow_infants'] ?? true) ? 'disabled' : '' }}>
                        </div>

                        <button type="submit" class="btn-primary booking-btn">Check Rates</button>
                        @if(empty($activity['time_slots']))
                            <p class="booking-note">This activity requires a time slot and none are available for the selected date.</p>
                        @endif
                        <p class="booking-note">Book for more than 20 people, please contact us directly.</p>
                        <p class="booking-note">On Request Booking (we will get back within 24 hours)</p>
                    </form>

                    @if(!empty($availableRooms))
                        <div id="available-options-section" class="available-options-section">
                            <h3>Available Options</h3>
                            <div class="availability-table-wrap">
                                <table class="availability-table">
                                    <thead>
                                        <tr>
                                            <th>Option / Variant</th>
                                            <th>Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($availableRooms as $room)
                                            <tr>
                                                <td>
                                                    <div class="activity-option-card">
                                                        <div class="activity-option-card__header">
                                                            <strong>{{ $room['room_name'] }}</strong>
                                                        </div>

                                                        @if($room['rate_specificity'] === 'Per Equipment')
                                                            <div class="activity-option-card__equipment">
                                                                Equipment Rate: <strong>{{ $room['currency'] }} {{ number_format((float) $room['equipment_rate'] ?? 0, 2) }}</strong>
                                                            </div>
                                                        @else
                                                            <div class="activity-option-person-grid">
                                                                @if($activity['allow_adults'] ?? true)
                                                                    <div class="person-block" style="border: 1px solid #006400;">
                                                                        <div class="person-block__count">{{ $activity['booking']['adults'] }}</div>
                                                                        <div class="person-block__label">Adult</div>
                                                                        <div class="person-block__rate">{{ $room['currency'] }} {{ number_format((float) $room['adult_rate'] ?? 0, 2) }}/Adult</div>
                                                                    </div>
                                                                @else
                                                                    <div class="person-block" style="opacity:0.4;pointer-events:none; border: 1px solid #a9a9a9;">
                                                                        <div class="person-block__count">0</div>
                                                                        <div class="person-block__label">Adult</div>
                                                                        <div class="person-block__rate" style="color:#999;">Not Allowed</div>
                                                                    </div>
                                                                @endif
                                                                @if($activity['allow_children'] ?? true)
                                                                    <div class="person-block" style="border: 1px solid #006400;">
                                                                        <div class="person-block__count">{{ $activity['booking']['children'] }}</div>
                                                                        <div class="person-block__label">Children</div>
                                                                        <div class="person-block__rate">{{ $room['currency'] }} {{ number_format((float) $room['children_rate'] ?? ($room['adult_rate'] ?? 0), 2) }}/Child</div>
                                                                    </div>
                                                                @else
                                                                    <div class="person-block" style="opacity:0.4;pointer-events:none; border: 1px solid #a9a9a9;">
                                                                        <div class="person-block__count">0</div>
                                                                        <div class="person-block__label">Children</div>
                                                                        <div class="person-block__rate" style="color:#999;">Not Allowed</div>
                                                                    </div>
                                                                @endif
                                                                @if($activity['allow_infants'] ?? true)
                                                                    <div class="person-block" style="border: 1px solid #006400;">
                                                                        <div class="person-block__count">{{ $activity['booking']['infants'] }}</div>
                                                                        <div class="person-block__label">Infant</div>
                                                                        <div class="person-block__rate">{{ $room['currency'] }} {{ number_format((float) $room['infant_rate'] ?? ($room['adult_rate'] ?? 0), 2) }}/Infant</div>
                                                                    </div>
                                                                @else
                                                                    <div class="person-block" style="opacity:0.4;pointer-events:none; border: 1px solid #a9a9a9;">
                                                                        <div class="person-block__count">0</div>
                                                                        <div class="person-block__label">Infant</div>
                                                                        <div class="person-block__rate" style="color:#999;">Not Allowed</div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif

                                                        @if($room['private_exclusive_rate'])
                                                            <div class="activity-option-card__footer">
                                                                Private/Exclusive Rate: <strong>{{ $room['currency'] }} {{ number_format((float) $room['private_exclusive_rate'], 2) }}</strong> (added once)
                                                            </div>
                                                        @endif
                                                        @if(!empty($room['max_participants']))
                                                            <div class="activity-option-card__meta">
                                                                Max Participants: <strong>{{ $room['max_participants'] }}</strong>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($room['total_price'] !== null)
                                                        <form method="POST" action="{{ route('frontend.booking.cart.add') }}" class="variant-booking-form"
                                                            data-rate-specificity="{{ $room['rate_specificity'] }}"
                                                            data-adult-rate="{{ $room['adult_rate'] ?? 0 }}"
                                                            data-children-rate="{{ $room['children_rate'] ?? ($room['adult_rate'] ?? 0) }}"
                                                            data-infant-rate="{{ $room['infant_rate'] ?? ($room['adult_rate'] ?? 0) }}"
                                                            data-equipment-rate="{{ $room['equipment_rate'] ?? 0 }}"
                                                            data-private-exclusive-rate="{{ $room['private_exclusive_rate'] ?? 0 }}"
                                                            data-max-participants="{{ $room['max_participants'] ?? 0 }}"
                                                        >
                                                            @csrf
                                                            <input type="hidden" name="type" value="activity">
                                                            <input type="hidden" name="activity_id" value="{{ $activity['id'] }}">
                                                            <input type="hidden" name="variant_id" value="{{ $room['room_id'] ?? '' }}">
                                                            <input type="hidden" name="variant_name" value="{{ $room['room_name'] }}">
                                                            <input type="hidden" name="title" value="{{ $activity['title'] }}">
                                                            <input type="hidden" name="image" value="{{ $activity['image'] }}">
                                                            <input type="hidden" name="activity_date" value="{{ $booking['activity_date'] }}">
                                                            <input type="hidden" name="participants" class="participants-input" value="{{ $booking['participants'] }}">
                                                            <input type="hidden" name="total_price" class="total-price-input" value="{{ $room['total_price'] }}">
                                                            <input type="hidden" name="currency" value="{{ $room['currency'] }}">
                                                            <input type="hidden" name="adults" class="hidden-adults" value="{{ $activity['booking']['adults'] ?? 1 }}">
                                                            <input type="hidden" name="children" class="hidden-children" value="{{ $activity['booking']['children'] ?? 0 }}">
                                                            <input type="hidden" name="infants" class="hidden-infants" value="{{ $activity['booking']['infants'] ?? 0 }}">
                                                            <div class="activity-option-summary">
                                                                <div class="activity-option-summary__top">
                                                                    <span>Total:</span>
                                                                    <strong class="variant-total">
                                                                        {{ $room['currency'] }} {{ number_format((float) $room['total_price'], 2) }}
                                                                    </strong>
                                                                </div>
                                                                <div class="activity-option-summary__message-group">
                                                                    <div class="activity-option-summary__discount" style="display:none;margin-top:0px;color:#28a745;font-weight:600;">
                                                                        Discount: <strong class="variant-discount"></strong>
                                                                    </div>
                                                                    <div class="activity-option-summary__error" aria-live="polite"></div>
                                                                </div>
                                                                @if(!empty($room['time_slots']))
                                                                    <label class="activity-option-summary__label" for="activity_time_slot_id_{{ $room['room_id'] }}">Select Time Slot</label>
                                                                    <select id="activity_time_slot_id_{{ $room['room_id'] }}" name="activity_time_slot_id" class="form-control activity-time-slot-select" required>
                                                                        <option value="">Select a time slot</option>
                                                                        @foreach($room['time_slots'] as $slot)
                                                                            <option value="{{ $slot['id'] }}" data-discount="{{ isset($slot['discount_value']) ? number_format((float)$slot['discount_value'], 2, '.', '') : '' }}" data-available="{{ $slot['available'] ?? 0 }}" data-capacity="{{ $slot['capacity_per_slot'] ?? 0 }}">
                                                                                {{ $slot['display'] }}
                                                                                @if(isset($slot['available']))
                                                                                    ({{ $slot['available'] }} available)
                                                                                @endif
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <div class="activity-option-summary__availability">
                                                                        Available: <strong class="available-count">--</strong>
                                                                        <span class="availability-extra"></span>
                                                                    </div>
                                                                    <input type="hidden" name="timeslot_discount_value" class="timeslot-discount-input" value="">
                                                                    <button type="submit" class="btn-book-now">Book Now</button>
                                                                @else
                                                                    <button type="button" class="btn-book-now" disabled>No time slot available</button>
                                                                @endif
                                                            </div>
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
                        </div>
                    @endif
                </aside>
            </div>

            <div class="detail-main">
                <div class="detail-card">
                    <h2>Overview</h2>
                    <div class="detail-text">{!! $activity['overview_text'] ?: $activity['excerpt'] !!}</div>
                </div>

                @if($approvedActivityReviews && count($approvedActivityReviews) > 0)
                    <div class="detail-card">
                        <h2>Guest Reviews</h2>
                        @foreach($approvedActivityReviews as $review)
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

@endsection

@push('styles')
    <style>
        .detail-top-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .detail-gallery-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .detail-main-image {
            width: 100%;
            border-radius: 12px;
            object-fit: cover;
            aspect-ratio: 1 / 1;
        }

        .detail-thumbs-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 4px;
        }

        .detail-thumb {
            width: 100%;
            height: 80px;
            border: 2px solid transparent;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            background: none;
            padding: 0;
            transition: border-color 0.2s;
        }

        .detail-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .detail-thumb.is-active {
            border-color: #19b5b5;
        }

        .detail-booking-card {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 12px;
            height: fit-content;
        }

        .booking-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .booking-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .booking-field label {
            font-weight: 600;
            font-size: 13px;
            color: var(--ink);
        }

        .booking-input {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 0 12px;
            font: inherit;
            color: var(--ink);
            background: #fff;
        }

        .booking-btn {
            width: 100%;
            border: 0;
            cursor: pointer;
            grid-column: 1 / -1;
        }

        .booking-note {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
            grid-column: 1 / -1;
        }

        .detail-main {
            display: grid;
            gap: 24px;
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
            vertical-align: top;
        }

        .availability-table th {
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
            font-size: 12px;
        }

        .activity-option-card {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 18px;
            border-radius: 16px;
            background: #f5f8ff;
        }

        .activity-option-card__header {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .activity-option-card__equipment,
        .activity-option-card__footer,
        .activity-option-card__meta {
            font-size: 13px;
            color: #222;
        }

        .activity-option-card__meta {
            margin-top: 8px;
        }

        .activity-option-person-grid {
            display: flex;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .person-block {
            border-radius: 14px;
            background: #ffffff;
            padding: 16px 10px;
            box-shadow: 0 8px 18px rgba(16, 34, 71, 0.06);
            text-align: center;
            width: 110px; 
        }

        .person-block__count {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
        }

        .person-block__label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
        }

        .person-block__rate {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a2e;
            line-height: 1.4;
        }

        .activity-option-summary {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 18px;
            border-radius: 16px;
            background: #fff;
            border: 1px solid rgba(16, 34, 71, 0.08);
            min-width: 240px;
            max-width: 300px;
        }

        .activity-option-summary__top {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .activity-option-summary__top span {
            font-size: 13px;
            color: #666;
        }

        .activity-option-summary__top .variant-total {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .activity-option-summary__label {
            font-size: 13px;
            color: #333;
            margin-bottom: 0;
            display: block;
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
            color: #1a1a2e;
        }

        .review-card__header p {
            margin: 0;
            color: #666;
            font-size: 14px;
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
            color: #1a1a2e;
            font-size: 14px;
        }

        .review-card__criteria-item span {
            color: #4b5563;
            font-weight: 600;
        }

        .review-card__text {
            margin: 0;
            color: #1f2937;
            line-height: 1.85;
            font-size: 15px;
        }

        .activity-option-summary__error {
            font-size: 13px;
            color: #c53030;
            /* min-height: 18px; */
        }

        .activity-option-summary__message-group {
            min-height: 35px; 
        }

        .activity-option-summary select.form-control {
            width: 100%;
            min-height: 44px;
            border-radius: 10px;
            border: 1px solid #d6d9e6;
            padding: 0 12px;
            background: #fff;
            font: inherit;
            color: #1a1a2e;
        }

        .activity-option-summary .btn-book-now {
            width: 100%;
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

        .count-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            min-width: 30px;
            height: 30px;
            border: 1px solid #d6d9e6;
            border-radius: 8px;
            background: #fff;
            color: #1a1a2e;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            line-height: 1;
            padding: 0;
            white-space: nowrap;
        }

        .count-input {
            width: 50px;
            height: 34px;
            min-width: 50px;
            border: 1px solid #d6d9e6;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
            color: #1a1a2e;
            background: #fff;
            line-height: 1.2;
        }

        .location-map iframe {
            width: 100%;
            min-height: 320px;
            border: 0;
            border-radius: 16px;
            margin-top: 14px;
        }

        .activity-option-summary__discount {
            background: #f1f1f1;
            padding: 5px 11px;
            border-radius: 13px;
        }

        @media (max-width: 1080px) {
            .detail-top-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.querySelectorAll('.detail-thumb').forEach(thumb => {
            thumb.addEventListener('click', function() {
                const imageSrc = this.dataset.galleryImage;
                const mainImage = document.getElementById('detailMainImage');
                if (mainImage) {
                    mainImage.src = imageSrc;
                }
                document.querySelectorAll('.detail-thumb').forEach(t => t.classList.remove('is-active'));
                this.classList.add('is-active');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Handle participant type restrictions in "Check Rates" section
            const bookingForm = document.querySelector('.booking-form-grid');
            if (bookingForm) {
                const adultsInput = bookingForm.querySelector('input[name="adults"]');
                const childrenInput = bookingForm.querySelector('input[name="children"]');
                const infantsInput = bookingForm.querySelector('input[name="infants"]');
                const activityAllowAdults = {{ ($activity['allow_adults'] ?? true) ? 'true' : 'false' }};
                const activityAllowChildren = {{ ($activity['allow_children'] ?? true) ? 'true' : 'false' }};
                const activityAllowInfants = {{ ($activity['allow_infants'] ?? true) ? 'true' : 'false' }};

                // Enforce restrictions: reset to 0 if not allowed
                if (!activityAllowAdults && adultsInput) {
                    adultsInput.value = 0;
                }
                if (!activityAllowChildren && childrenInput) {
                    childrenInput.value = 0;
                }
                if (!activityAllowInfants && infantsInput) {
                    infantsInput.value = 0;
                }

                // Prevent modification of disabled inputs and validate on submit
                if (bookingForm) {
                    bookingForm.addEventListener('submit', function(event) {
                        // If adults not allowed but input has value, reset and prevent
                        if (!activityAllowAdults && adultsInput && parseInt(adultsInput.value) > 0) {
                            adultsInput.value = 0;
                        }
                        if (!activityAllowChildren && childrenInput && parseInt(childrenInput.value) > 0) {
                            childrenInput.value = 0;
                        }
                        if (!activityAllowInfants && infantsInput && parseInt(infantsInput.value) > 0) {
                            infantsInput.value = 0;
                        }
                    });
                }
            }

            // If the page was loaded via the booking form (query params present),
            // scroll to the available options section if it's rendered.
            try {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('activity_date') || urlParams.has('adults') || urlParams.has('children') || urlParams.has('infants')) {
                    const target = document.getElementById('available-options-section') || document.querySelector('.available-options-section');
                    if (target) {
                        setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 60);
                    }
                }
            } catch (e) {
                // ignore on older browsers
            }

            document.querySelectorAll('.variant-booking-form').forEach(form => {
                const rateSpecificity = form.dataset.rateSpecificity;
                const adultRate = parseFloat(form.dataset.adultRate) || 0;
                const childrenRate = parseFloat(form.dataset.childrenRate) || adultRate;
                const infantRate = parseFloat(form.dataset.infantRate) || adultRate;
                const equipmentRate = parseFloat(form.dataset.equipmentRate) || 0;
                const privateExclusiveRate = parseFloat(form.dataset.privateExclusiveRate) || 0;
                const currency = form.querySelector('input[name="currency"]')?.value || '';

                const maxParticipants = parseInt(form.dataset.maxParticipants, 10) || 0;
                const hiddenAdults = form.querySelector('.hidden-adults');
                const hiddenChildren = form.querySelector('.hidden-children');
                const hiddenInfants = form.querySelector('.hidden-infants');
                const participantsInput = form.querySelector('.participants-input');
                const totalPriceInput = form.querySelector('.total-price-input');
                const timeslotDiscountInput = form.querySelector('.timeslot-discount-input');
                const totalDisplay = form.closest('tr')?.querySelector('.variant-total');
                const discountDisplay = form.closest('tr')?.querySelector('.activity-option-summary__discount');
                const timeSlotSelect = form.querySelector('select[name="activity_time_slot_id"]');
                const errorContainer = form.querySelector('.activity-option-summary__error');
                const bookNowButton = form.querySelector('.btn-book-now');
                const availabilityCount = form.querySelector('.available-count');
                const availabilityWrapper = form.querySelector('.activity-option-summary__availability');
                const availabilityExtra = form.querySelector('.availability-extra');

                function getNumericValue(inputElement, fallback = 0) {
                    return Math.max(0, parseInt(inputElement?.value, 10) || fallback);
                }

                function getSelectedTimeslotDiscount() {
                    if (!timeSlotSelect) {
                        return 0;
                    }

                    const selectedOption = timeSlotSelect.options[timeSlotSelect.selectedIndex];
                    if (!selectedOption) {
                        return 0;
                    }

                    const discountValue = parseFloat(selectedOption.dataset.discount || '0');
                    return Number.isFinite(discountValue) ? discountValue : 0;
                }

                function updateAvailability() {
                    if (!timeSlotSelect || !availabilityCount) {
                        return;
                    }

                    const selectedOption = timeSlotSelect.options[timeSlotSelect.selectedIndex];
                    const available = selectedOption ? parseInt(selectedOption.dataset.available || '0', 10) : 0;
                    const capacity = selectedOption ? parseInt(selectedOption.dataset.capacity || '0', 10) : 0;

                    if (!selectedOption || !timeSlotSelect.value) {
                        availabilityCount.textContent = '--';
                        if (availabilityWrapper) {
                            availabilityWrapper.style.display = 'block';
                        }
                        return;
                    }

                    if (availabilityWrapper) {
                        availabilityWrapper.style.display = 'block';
                        if (available <= 0) {
                            availabilityWrapper.style.color = '#d9534f';
                            availabilityCount.textContent = 0;
                            if (availabilityExtra) {
                                availabilityExtra.textContent = ` — Fully booked · Capacity ${capacity}`;
                            }
                        } else {
                            availabilityWrapper.style.color = '';
                            availabilityCount.textContent = available;
                            if (availabilityExtra) {
                                availabilityExtra.textContent = '';
                            }
                        }
                    }
                    if (bookNowButton) {
                        bookNowButton.disabled = available <= 0;
                    }
                }

                function validateParticipants() {
                    const adults = getNumericValue(hiddenAdults, 1);
                    const children = getNumericValue(hiddenChildren, 0);
                    const infants = getNumericValue(hiddenInfants, 0);
                    const participants = adults + children + infants;

                    participantsInput.value = Math.max(1, participants);

                    const isValid = !(maxParticipants > 0 && participants > maxParticipants);
                    if (!isValid) {
                        if (errorContainer) {
                            errorContainer.textContent = `Maximum participants allowed for this option is ${maxParticipants}. Please reduce adults, children, or infants.`;
                        }
                    } else if (errorContainer) {
                        errorContainer.textContent = '';
                    }

                    if (bookNowButton) {
                        bookNowButton.disabled = !isValid;
                    }

                    return isValid;
                }

                function updateTotals() {
                    const adults = getNumericValue(hiddenAdults, 1);
                    const children = getNumericValue(hiddenChildren, 0);
                    const infants = getNumericValue(hiddenInfants, 0);
                    const participants = Math.max(1, adults + children + infants);

                    participantsInput.value = participants;

                    let total = 0;
                    if (rateSpecificity === 'Per Equipment') {
                        total = equipmentRate * participants;
                    } else {
                        total = (adultRate * adults) + (childrenRate * children) + (infantRate * infants);
                    }

                    if (privateExclusiveRate > 0) {
                        total += privateExclusiveRate;
                    }

                    const selectedDiscount = getSelectedTimeslotDiscount();
                    const totalAfterDiscount = Math.max(0, total - selectedDiscount);

                    if (totalDisplay) {
                        totalDisplay.textContent = `${currency} ${totalAfterDiscount.toFixed(2)}`;
                    }
                    if (totalPriceInput) {
                        totalPriceInput.value = total.toFixed(2);
                    }
                    if (timeslotDiscountInput) {
                        timeslotDiscountInput.value = selectedDiscount > 0 ? selectedDiscount.toFixed(2) : '';
                    }
                    if (discountDisplay) {
                        if (selectedDiscount > 0) {
                            discountDisplay.style.display = 'block';
                            discountDisplay.querySelector('.variant-discount').textContent = `- ${currency} ${selectedDiscount.toFixed(2)}`;
                        } else {
                            discountDisplay.style.display = 'none';
                        }
                    }
                }

                if (timeSlotSelect) {
                    timeSlotSelect.addEventListener('change', function() {
                        updateAvailability();
                        updateTotals();
                    });
                }

                form.addEventListener('submit', function(event) {
                    if (timeSlotSelect && !timeSlotSelect.value) {
                        event.preventDefault();
                        alert('Please select a time slot before booking.');
                        return;
                    }

                    if (!validateParticipants()) {
                        event.preventDefault();
                        return;
                    }
                });

                updateAvailability();
                updateTotals();
                validateParticipants();
            });
        });
    </script>
@endpush
