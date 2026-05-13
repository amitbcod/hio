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
                        <div class="booking-field">
                            <label>Adults</label>
                            <input type="number" name="adults" min="1" value="{{ $activity['booking']['adults'] ?? 1 }}" class="booking-input">
                        </div>
                        <div class="booking-field">
                            <label>Children</label>
                            <input type="number" name="children" min="0" value="{{ $activity['booking']['children'] ?? 0 }}" class="booking-input">
                        </div>
                        <div class="booking-field">
                            <label>Infants</label>
                            <input type="number" name="infants" min="0" value="{{ $activity['booking']['infants'] ?? 0 }}" class="booking-input">
                        </div>

                        <button type="submit" class="btn-primary booking-btn">Check Rates</button>
                        @if(empty($activity['time_slots']))
                            <p class="booking-note">This activity requires a time slot and none are available for the selected date.</p>
                        @endif
                        <p class="booking-note">Book for more than 20 people, please contact us directly.</p>
                        <p class="booking-note">On Request Booking (we will get back within 24 hours)</p>
                    </form>

                    @if(!empty($availableRooms))
                        <div class="available-options-section">
                            <h3>Available Options</h3>
                            <div class="availability-table-wrap">
                                <table class="availability-table">
                                    <thead>
                                        <tr>
                                            <th>Option / Variant</th>
                                            <th>Total Price</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($availableRooms as $room)
                                            <tr>
                                                <td>
                                                    <div style="display:flex;flex-direction:column;gap:12px;">
                                                        <strong>{{ $room['room_name'] }}</strong>

                                                        @if($room['rate_specificity'] === 'Per Equipment')
                                                            <div style="font-size:13px;color:#444;">Equipment Rate: <strong>{{ $room['currency'] }} {{ number_format((float) $room['equipment_rate'] ?? 0, 2) }}</strong></div>
                                                        @else
                                                            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;">
                                                                <div style="background:#f5f8ff;border-radius:10px;padding:12px; text-align:center;">
                                                                    <div style="font-size:12px;color:#666;margin-bottom:6px;">Adult</div>
                                                                    <span class="adult_count">{{ $activity['booking']['adults'] }}</span> * <div style="font-weight:600;">{{ $room['currency'] }} {{ number_format((float) $room['adult_rate'] ?? 0, 2) }}</div>
                                                                </div>
                                                                <div style="background:#f5f8ff;border-radius:10px;padding:12px; text-align:center;">
                                                                    <div style="font-size:12px;color:#666;margin-bottom:6px;">Children</div>
                                                                    <span class="children_count">{{ $activity['booking']['children'] }}</span> * <div style="font-weight:600;">{{ $room['currency'] }} {{ number_format((float) $room['children_rate'] ?? ($room['adult_rate'] ?? 0), 2) }}</div>
                                                                </div>
                                                                <div style="background:#f5f8ff;border-radius:10px;padding:12px; text-align:center;">
                                                                    <div style="font-size:12px;color:#666;margin-bottom:6px;">Infant</div>
                                                                    <span class="infant_count">{{ $activity['booking']['infants'] }}</span> * <div style="font-weight:600;">{{ $room['currency'] }} {{ number_format((float) $room['infant_rate'] ?? ($room['adult_rate'] ?? 0), 2) }}</div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if($room['private_exclusive_rate'])
                                                            <div style="font-size:13px;color:#222;">Private/Exclusive Rate: <strong>{{ $room['currency'] }} {{ number_format((float) $room['private_exclusive_rate'], 2) }}</strong> (added once)</div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong class="variant-total">
                                                        @if($room['total_price'] !== null)
                                                            {{ $room['currency'] }} {{ number_format((float) $room['total_price'], 2) }}
                                                        @else
                                                            Price unavailable
                                                        @endif
                                                    </strong>
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
                                                            @if(!empty($room['time_slots']))
                                                                <div style="margin-bottom:10px;">
                                                                    <label for="activity_time_slot_id_{{ $room['room_id'] }}" style="display:block;margin-bottom:6px;font-size:13px;color:#333;">Select Time Slot</label>
                                                                    <select id="activity_time_slot_id_{{ $room['room_id'] }}" name="activity_time_slot_id" class="form-control" required>
                                                                        <option value="">Select a time slot</option>
                                                                        @foreach($room['time_slots'] as $slot)
                                                                            <option value="{{ $slot['id'] }}">{{ $slot['display'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <button type="submit" class="btn-book-now">Book Now</button>
                                                            @else
                                                                <button type="button" class="btn-book-now" disabled>No time slot available</button>
                                                            @endif
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
            width: 80px;
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
            document.querySelectorAll('.variant-booking-form').forEach(form => {
                const rateSpecificity = form.dataset.rateSpecificity;
                const adultRate = parseFloat(form.dataset.adultRate) || 0;
                const childrenRate = parseFloat(form.dataset.childrenRate) || adultRate;
                const infantRate = parseFloat(form.dataset.infantRate) || adultRate;
                const equipmentRate = parseFloat(form.dataset.equipmentRate) || 0;
                const privateExclusiveRate = parseFloat(form.dataset.privateExclusiveRate) || 0;
                const currency = form.querySelector('input[name="currency"]')?.value || '';

                const row = form.closest('tr');
                const adultsInput = row.querySelector('input[name="adults"]');
                const childrenInput = row.querySelector('input[name="children"]');
                const infantsInput = row.querySelector('input[name="infants"]');
                const hiddenAdults = form.querySelector('.hidden-adults');
                const hiddenChildren = form.querySelector('.hidden-children');
                const hiddenInfants = form.querySelector('.hidden-infants');
                const participantsInput = form.querySelector('.participants-input');
                const totalPriceInput = form.querySelector('.total-price-input');
                const totalDisplay = row.querySelector('.variant-total');
                const timeSlotSelect = form.querySelector('select[name="activity_time_slot_id"]');

                const buttonControls = row.querySelectorAll('.count-btn');
                buttonControls.forEach(button => {
                    button.addEventListener('click', function() {
                        const fieldName = this.dataset.field;
                        const action = this.dataset.action;
                        const input = row.querySelector(`input[name="${fieldName}"]`);
                        if (!input) {
                            return;
                        }
                        let value = parseInt(input.value, 10) || 0;
                        if (action === 'increment') {
                            value += 1;
                        } else if (action === 'decrement') {
                            value = Math.max(fieldName === 'adults' ? 1 : 0, value - 1);
                        }
                        input.value = value;
                        updateTotals();
                    });
                });

                function getNumericValue(inputElement, fallback = 0) {
                    return Math.max(0, parseInt(inputElement?.value, 10) || fallback);
                }

                function updateTotals() {
                    const adults = getNumericValue(adultsInput, 1);
                    const children = getNumericValue(childrenInput, 0);
                    const infants = getNumericValue(infantsInput, 0);
                    const participants = Math.max(1, adults + children + infants);

                    hiddenAdults.value = adults;
                    hiddenChildren.value = children;
                    hiddenInfants.value = infants;
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

                    if (totalDisplay) {
                        totalDisplay.textContent = `${currency} ${total.toFixed(2)}`;
                    }
                    if (totalPriceInput) {
                        totalPriceInput.value = total.toFixed(2);
                    }
                }

                if (timeSlotSelect) {
                    timeSlotSelect.addEventListener('change', function() {
                        if (!this.value) {
                            return;
                        }
                        updateTotals();
                    });
                }

                form.addEventListener('submit', function(event) {
                    if (timeSlotSelect && !timeSlotSelect.value) {
                        event.preventDefault();
                        alert('Please select a time slot before booking.');
                    }
                });

                updateTotals();
            });
        });
    </script>
@endpush
