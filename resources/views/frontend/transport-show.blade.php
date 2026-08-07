@extends('frontend.layout')

@section('title', $transport['title'] . ' | Transport | Holidays.io')
@section('meta_description', $transport['excerpt'] ?? __('transport.meta_description', ['title' => $transport['title'] ?? 'Transport']))

@section('content')
    @php
        $booking = $transport['booking'] ?? [
            'pickup_date' => now()->format('Y-m-d'),
            'pickup_time' => '',
            'pickup_date_display' => now()->format('d/m/Y'),
            'return_date' => '',
            'return_time' => '',
            'return_date_display' => '',
            'passengers' => 1,
        ];
        $routes = $transport['routes_pricing'] ?? [];
        $amenities = $transport['amenities'] ?? [];
        $operator = $transport['operator'] ?? null;
        $serviceType = in_array(trim((string) request()->query('service_type', 'airport_transfer')), ['airport_transfer', 'activity_transfer', 'hotel_transfer', 'full_day_sightseeing', 'half_day_sightseeing'], true)
            ? trim((string) request()->query('service_type'))
            : 'airport_transfer';
        $serviceTypeLabel = match ($serviceType) {
            'airport_transfer' => __('transport.form.airport_transfer'),
            'activity_transfer' => __('transport.form.activity_transfer'),
            'hotel_transfer' => __('transport.form.hotel_transfer'),
            'full_day_sightseeing' => __('transport.form.full_day_sightseeing'),
            'half_day_sightseeing' => __('transport.form.half_day_sightseeing'),
            default => ucwords(str_replace('_', ' ', $serviceType)),
        };
        $booking['pickup_date'] = trim((string) request()->query('pickup_date', request()->query('arrival_date', request()->query('check_in', $booking['pickup_date']))));
        $booking['pickup_time'] = trim((string) request()->query('pickup_time', request()->query('arrival_time', $booking['pickup_time'])));
        $booking['return_date'] = trim((string) request()->query('return_date', ''));
        $booking['return_time'] = trim((string) request()->query('return_time', ''));
        $booking['passengers'] = request()->query('passengers', $booking['passengers']);
        $detailQuery = http_build_query([
            'pickup_date' => $booking['pickup_date'],
            'pickup_time' => $booking['pickup_time'],
            'return_date' => $booking['return_date'],
            'return_time' => $booking['return_time'],
            'passengers' => $booking['passengers'],
            'service_type' => $serviceType,
        ]);
        $carPrices = $transport['car_rental_prices'] ?? [];
        $effectiveCarPrices = $carPrices;
        $seasonalMatch = null;
        if (!empty($carPrices['seasonal'])) {
            $bookingDate = trim((string) ($booking['pickup_date'] ?? ''));
            if ($bookingDate === '') {
                $bookingDate = now()->toDateString();
            }
            $targetDate = \Carbon\Carbon::parse($bookingDate)->startOfDay();
            foreach ((array) $carPrices['seasonal'] as $season) {
                $start = $season['start'] ?? $season['start_date'] ?? null;
                $end = $season['end'] ?? $season['end_date'] ?? null;
                if (blank($start) || blank($end)) {
                    continue;
                }
                $startDate = \Carbon\Carbon::parse($start)->startOfDay();
                $endDate = \Carbon\Carbon::parse($end)->endOfDay();
                if ($targetDate->between($startDate, $endDate, true)) {
                    $seasonalMatch = $season;
                    break;
                }
            }

            if ($seasonalMatch) {
                $effectiveCarPrices = array_merge($carPrices, array_filter($seasonalMatch, function ($value) {
                    return $value !== null && $value !== '';
                }));
            }
        }

        // Compute car rental price when requested and dates/times are present
        $carRentalTotal = null;
        $debugInfo = [];
        try {
            $pickupDateParam = request()->query('pickup_date') ?: request()->query('arrival_date') ?: request()->query('check_in') ?: null;
            $pickupTimeParam = request()->query('pickup_time') ?: request()->query('arrival_time') ?: null;
            $returnDateParam = request()->query('return_date') ?: null;
            $returnTimeParam = request()->query('return_time') ?: null;

            $debugInfo['params'] = [
                'pickupDateParam' => $pickupDateParam,
                'pickupTimeParam' => $pickupTimeParam,
                'returnDateParam' => $returnDateParam,
                'returnTimeParam' => $returnTimeParam,
            ];

            if ($serviceType === 'car_rental' && $pickupDateParam && $pickupTimeParam && $returnDateParam && $returnTimeParam) {
                // Ensure times are properly formatted - trim and remove extra spaces
                $pickupTimeParam = trim($pickupTimeParam);
                $returnTimeParam = trim($returnTimeParam);
                
                $start = \Carbon\Carbon::parse($pickupDateParam . ' ' . $pickupTimeParam);
                $end = \Carbon\Carbon::parse($returnDateParam . ' ' . $returnTimeParam);
                $debugInfo['start'] = $start->toString();
                $debugInfo['end'] = $end->toString();
                
                // Ensure end is after start, swap if needed
                if ($end->isBefore($start)) {
                    $temp = $start;
                    $start = $end;
                    $end = $temp;
                    $debugInfo['swapped'] = true;
                }
                
                if ($end->greaterThan($start)) {
                    $totalMinutes = $end->diffInMinutes($start);
                    // Ensure we have positive minutes
                    $totalMinutes = abs($totalMinutes);
                    $totalHours = max(1, (int) ceil($totalMinutes / 60));
                    $debugInfo['totalMinutes'] = $totalMinutes;
                    $debugInfo['totalHours'] = $totalHours;

                    $days = intdiv($totalHours, 24);
                    $remainder = $totalHours % 24;

                    $blocks = [];
                    if (!empty($effectiveCarPrices['per_hour'])) {
                        $blocks[1] = floatval($effectiveCarPrices['per_hour']);
                    }
                    if (!empty($effectiveCarPrices['per_4h'])) {
                        $blocks[4] = floatval($effectiveCarPrices['per_4h']);
                    }
                    if (!empty($effectiveCarPrices['per_8h'])) {
                        $blocks[8] = floatval($effectiveCarPrices['per_8h']);
                    }
                    if (!empty($effectiveCarPrices['per_12h'])) {
                        $blocks[12] = floatval($effectiveCarPrices['per_12h']);
                    }
                    if (!empty($effectiveCarPrices['per_24h'])) {
                        $blocks[24] = floatval($effectiveCarPrices['per_24h']);
                    }

                    $debugInfo['blocks'] = $blocks;

                    if (!empty($blocks)) {
                        $dp = array_fill(0, $totalHours + 1, INF);
                        $dp[0] = 0.0;
                        for ($h = 1; $h <= $totalHours; $h++) {
                            foreach ($blocks as $blockHours => $blockPrice) {
                                $prev = max(0, $h - $blockHours);
                                $dp[$h] = min($dp[$h], $dp[$prev] + $blockPrice);
                            }
                        }
                        $total = $dp[$totalHours];
                        $debugInfo['total'] = $total;
                        $debugInfo['dp_array'] = $dp;
                    } else {
                        $total = 0.0;
                        $debugInfo['total'] = 'blocks_empty';
                    }

                    $carRentalTotal = number_format($total, 2, '.', '');
                }
            }
        } catch (\Exception $e) {
            $carRentalTotal = null;
        }
        $resolveSeasonalValue = function ($seasonalEntries, $dateValue, $defaultValue = null, $field = 'price') {
            $dateValue = trim((string) ($dateValue ?? ''));
            if ($dateValue === '') {
                $dateValue = now()->toDateString();
            }

            $targetDate = \Carbon\Carbon::parse($dateValue)->startOfDay();
            foreach ((array) ($seasonalEntries ?? []) as $entry) {
                $start = $entry['start'] ?? $entry['start_date'] ?? null;
                $end = $entry['end'] ?? $entry['end_date'] ?? null;
                if (blank($start) || blank($end)) {
                    continue;
                }

                $startDate = \Carbon\Carbon::parse($start)->startOfDay();
                $endDate = \Carbon\Carbon::parse($end)->endOfDay();
                if ($targetDate->between($startDate, $endDate, true)) {
                    return isset($entry[$field]) ? (float) $entry[$field] : $defaultValue;
                }
            }

            return $defaultValue;
        };

        $selectedRoute = $transport['selected_route'] ?? ($routes[0] ?? null);
        $selectedRouteFrom = $transport['selected_transport_from'] ?? ($selectedRoute['route_from'] ?? '');
        $selectedRouteTo = $transport['selected_transport_to'] ?? ($selectedRoute['route_to'] ?? '');
        $selectedDefaultPrice = $selectedRoute['pricing']['default_price'] ?? $transport['starting_rate'] ?? 0;
        $selectedDefaultPrice = $resolveSeasonalValue($selectedRoute['pricing']['seasonal'] ?? [], $booking['pickup_date'], $selectedDefaultPrice, 'price');
        $selectedReturnPrice = $selectedRoute['pricing']['return_price'] ?? null;
        $selectedReturnPrice = $resolveSeasonalValue($selectedRoute['pricing']['seasonal'] ?? [], $booking['return_date'], $selectedReturnPrice, 'return_price');
        $routeId = $selectedRoute['route_id'] ?? '';
        $detailImage = $transport['image'] ?? asset('images/transport.svg');
    @endphp

    <section class="page-hero">
        <div class="page-hero-media">
            <img src="{{ $detailImage }}" alt="{{ $transport['title'] }}">
        </div>
        <div class="wrap page-hero-content">
            <div class="breadcrumbs">
                <a href="{{ url('/') }}">{{ __('site.home') }}</a>
                <span>/</span>
                <a href="{{ url('/category-list?category=transport') }}">{{ __('site.transport') }}</a>
                <span>/</span>
                <span>{{ $transport['title'] }}</span>
            </div>

            <div class="hero-meta-row">
                <span class="hero-chip">{{ $transport['vehicle_type'] ?? __('transport.vehicle') }}</span>
                @if(!empty($transport['seating_capacity']))
                    <span class="hero-chip">{{ __('transport.seating_capacity') }}: {{ $transport['seating_capacity'] }}</span>
                @endif
                @if(!empty($transport['approval_status']))
                    <span class="hero-chip">{{ $transport['approval_status'] }}</span>
                @endif
            </div>

            <h1>{{ $transport['title'] }}</h1>
            <p>{{ $transport['excerpt'] }}</p>
        </div>
    </section>

    <section class="page-section detail-page-shell">
        <div class="wrap">
            <div class="detail-top-grid">
                    <div class="detail-gallery-card">
                    <img src="{{ $detailImage }}" alt="{{ $transport['title'] }}" id="detailMainImage" class="detail-main-image">
                </div>

                <aside class="detail-booking-card">
                    @php
                        $transportRegionOptions = [];
                        $selectedTransportFrom = trim((string) ($transport['selected_transport_from'] ?? ''));
                        $selectedTransportTo = trim((string) ($transport['selected_transport_to'] ?? ''));
                        foreach ($transport['place_region_map'] ?? [] as $place => $region) {
                            $region = trim((string) $region);
                            if ($region === '') {
                                continue;
                            }
                            $regionKey = strtolower($region);
                            if (!isset($transportRegionOptions[$regionKey])) {
                                $transportRegionOptions[$regionKey] = $region;
                            }
                        }

                        foreach ($transport['routes_pricing'] ?? [] as $route) {
                            $routeFrom = trim((string) ($route['route_from'] ?? ''));
                            $routeTo = trim((string) ($route['route_to'] ?? ''));
                            if ($routeFrom !== '') {
                                $routeFromKey = strtolower($routeFrom);
                                if (!isset($transportRegionOptions[$routeFromKey])) {
                                    $transportRegionOptions[$routeFromKey] = $routeFrom;
                                }
                            }
                            if ($routeTo !== '') {
                                $routeToKey = strtolower($routeTo);
                                if (!isset($transportRegionOptions[$routeToKey])) {
                                    $transportRegionOptions[$routeToKey] = $routeTo;
                                }
                            }
                        }

                        if (!empty($transportRegionOptions)) {
                            uasort($transportRegionOptions, fn ($a, $b) => strcmp($a, $b));
                        }

                        $selectedFromKey = '';
                        $selectedToKey = '';
                        if ($selectedTransportFrom !== '') {
                            $selectedFromNormalized = strtolower($selectedTransportFrom);
                            if (isset($transportRegionOptions[$selectedFromNormalized])) {
                                $selectedFromKey = $selectedFromNormalized;
                            } elseif (!empty($transport['place_region_map'][$selectedTransportFrom])) {
                                $mapped = strtolower(trim((string) $transport['place_region_map'][$selectedTransportFrom]));
                                if (isset($transportRegionOptions[$mapped])) {
                                    $selectedFromKey = $mapped;
                                }
                            }
                        }
                        if ($selectedTransportTo !== '') {
                            $selectedToNormalized = strtolower($selectedTransportTo);
                            if (isset($transportRegionOptions[$selectedToNormalized])) {
                                $selectedToKey = $selectedToNormalized;
                            } elseif (!empty($transport['place_region_map'][$selectedTransportTo])) {
                                $mapped = strtolower(trim((string) $transport['place_region_map'][$selectedTransportTo]));
                                if (isset($transportRegionOptions[$mapped])) {
                                    $selectedToKey = $mapped;
                                }
                            }
                        }
                    @endphp
                    <form method="GET" action="{{ route('frontend.transports.show', $transport['id']) }}" class="booking-form-grid" style="overflow:visible;">
                        <div class="booking-field">
                            <label>{{ __('home.search.departure_region') }}</label>
                            <select id="transport-place-from" name="transport_from" class="booking-input" style="position:relative;z-index:9999;background:#fff;">
                                <option value="">{{ __('home.search.departure_region') }}</option>
                                @foreach($transportRegionOptions as $regionKey => $regionName)
                                    <option value="{{ $regionName }}" {{ $selectedFromKey === $regionKey ? 'selected' : '' }}>{{ $regionName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="booking-field">
                            <label>{{ __('home.search.destination_region') }}</label>
                            <select id="transport-place-to" name="transport_to" class="booking-input" style="position:relative;z-index:9999;background:#fff;">
                                <option value="">{{ __('home.search.destination_region') }}</option>
                                @foreach($transportRegionOptions as $regionKey => $regionName)
                                    <option value="{{ $regionName }}" {{ $selectedToKey === $regionKey ? 'selected' : '' }}>{{ $regionName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="booking-field booking-field-inline">
                            <label>{{ __('transport.form.pickup_date') }}</label>
                            <div class="custom-picker-wrapper date-picker">
                                <div class="booking-input booking-input-text">{{ !empty($booking['pickup_date']) ? \Carbon\Carbon::parse($booking['pickup_date'])->format('d/m/Y') : '' }}</div>
                                <input type="date" name="pickup_date" value="{{ $booking['pickup_date'] }}" class="booking-input booking-input-native" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="booking-field booking-field-inline">
                            <label>{{ __('transport.form.pickup_time') }}</label>
                            <div class="custom-picker-wrapper time-picker" style="position:relative;">
                                <label class="booking-input booking-input-text booking-input-time" style="display:inline-flex;align-items:center;gap:6px;width:64px;justify-content:center;padding:6px 6px;cursor:pointer;">
                                    <span class="time-value">{{ $booking['pickup_time'] ?? '' }}</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                        <circle cx="12" cy="12" r="9" stroke="#666" stroke-width="1" fill="none" />
                                        <path d="M12 8v5l3 2" stroke="#666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                                    </svg>
                                </label>
                                <input id="transport-pickup_time" type="hidden" name="pickup_time" value="{{ $booking['pickup_time'] }}">
                            </div>
                        </div>
                        <div class="booking-field booking-field-inline">
                            <label>{{ __('transport.form.return_date') }}</label>
                            <div class="custom-picker-wrapper date-picker">
                                <div class="booking-input booking-input-text">{{ !empty($booking['return_date']) ? \Carbon\Carbon::parse($booking['return_date'])->format('d/m/Y') : '' }}</div>
                                <input type="date" name="return_date" value="{{ $booking['return_date'] }}" class="booking-input booking-input-native" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="booking-field booking-field-inline">
                            <label>{{ __('transport.form.return_time') }}</label>
                            <div class="custom-picker-wrapper time-picker" style="position:relative;">
                                <label class="booking-input booking-input-text booking-input-time" style="display:inline-flex;align-items:center;gap:6px;width:64px;justify-content:center;padding:6px 6px;cursor:pointer;">
                                    <span class="time-value">{{ $booking['return_time'] ?? '' }}</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                        <circle cx="12" cy="12" r="9" stroke="#666" stroke-width="1" fill="none" />
                                        <path d="M12 8v5l3 2" stroke="#666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                                    </svg>
                                </label>
                                <input id="transport-return_time" type="hidden" name="return_time" value="{{ $booking['return_time'] }}">
                            </div>
                        </div>
                        <div class="booking-field">
                            <label>{{ __('transport.form.passengers') }}</label>
                            <input type="number" name="passengers" min="1" max="{{ $transport['seating_capacity'] ?? '' }}" value="{{ min($booking['passengers'], $transport['seating_capacity'] ?? $booking['passengers']) }}" class="booking-input">
                        </div>
                        <input type="hidden" name="service_type" value="{{ $serviceType }}">
                        <div class="booking-field">
                            <label>{{ __('transport.form.service_type') }}</label>
                            <div class="booking-input booking-input-static">{{ $serviceTypeLabel }}</div>
                        </div>
                        <button id="booking-update-search-btn" type="submit" class="btn-primary booking-btn">{{ __('transport.form.update_search') }}</button>
                    </form>

                    <!-- <div class="booking-summary-line">
                        <span>{{ $booking['passengers'] }} {{ trans_choice('transport.summary.passengers', $booking['passengers']) }}</span>
                    </div> -->

                    <form method="POST" action="{{ route('frontend.booking.cart.add') }}" class="booking-add-form" style="gap:10px">
                        @csrf
                        <input type="hidden" name="type" value="transport">
                        <input type="hidden" name="transport_id" value="{{ $transport['id'] }}">
                        <input type="hidden" name="title" value="{{ $transport['title'] }}">
                        <input type="hidden" name="image" value="{{ $detailImage }}">
                        <input type="hidden" id="transport-price-per-passenger" name="price_per_passenger" value="{{ number_format((float) $selectedDefaultPrice, 2, '.', '') }}">
                        <input type="hidden" id="transport-car-rental-total" name="car_rental_total" value="{{ $carRentalTotal ?? 0 }}">
                        <input type="hidden" id="transport-return-price" name="return_price" value="{{ number_format((float) ($selectedReturnPrice ?? 0), 2, '.', '') }}">
                        <input type="hidden" name="currency" value="USD">
                        <input type="hidden" name="pickup_date" value="{{ $booking['pickup_date'] }}">
                        <input type="hidden" name="return_date" value="{{ $booking['return_date'] ?? '' }}">
                        <input type="hidden" name="pickup_time" id="booking-pickup-time" value="{{ $booking['pickup_time'] ?? '' }}">
                        <input type="hidden" name="return_time" id="booking-return-time" value="{{ $booking['return_time'] ?? '' }}">
                        <input type="hidden" name="service_type" id="booking-service-type" value="{{ $serviceType }}">
                        <input type="hidden" name="passengers" value="{{ $booking['passengers'] }}">
                        <input type="hidden" id="transport-route-id" name="route_id" value="{{ $routeId }}">
                        <input type="hidden" id="transport-route-from" name="route_from" value="{{ $selectedRouteFrom }}">
                        <input type="hidden" id="transport-route-to" name="route_to" value="{{ $selectedRouteTo }}">
                        <div class="booking-field">
                            <label for="booking-pickup-address">{{ __('transport.form.pickup_address') }}</label>
                            <textarea id="booking-pickup-address" name="pickup_address" rows="3" class="booking-input" placeholder="{{ __('transport.form.pickup_address_placeholder') }}" required></textarea>
                        </div>

                        <div class="booking-field">
                            <label for="booking-dropoff-address">{{ __('transport.form.dropoff_address') }}</label>
                            <textarea id="booking-dropoff-address" name="dropoff_address" rows="3" class="booking-input" placeholder="{{ __('transport.form.dropoff_address_placeholder') }}" required></textarea>
                        </div>
                        <input type="hidden" name="source" value="detail">

                        <button id="booking-now-btn" type="button" class="btn-secondary booking-btn">{{ __('transport.form.book_now') }}</button>
                    </form>

                    <div class="booking-summary-line" id="transport-price-summary">
                        <span>
                            @if(!empty($booking['return_date']) && $selectedReturnPrice)
                                {{ __('transport.price') }}: USD {{ number_format((float) $selectedReturnPrice, 2) }}
                            @else
                                {{ __('transport.price') }}: USD {{ number_format((float) ($selectedDefaultPrice ?? 0), 2) }}
                            @endif
                        </span>
                    </div>
                    
                    <!-- DEBUG: Car Rental Calculation -->

                    @if($operator)
                        <!-- <div class="booking-quick-links">
                            <p><strong>{{ __('transport.operator') }}:</strong> {{ $operator['name'] }}</p>
                            @if(!empty($operator['email']))
                                <a href="mailto:{{ $operator['email'] }}">{{ __('transport.contact_operator') }}</a>
                            @endif
                        </div> -->
                    @endif
                </aside>
            </div>
            @php
                $overviewContent = $transport['description'] ?: ($transport['overview'] ?? '');
            @endphp
            <nav class="detail-anchor-nav">
                
            @if(!empty(trim($overviewContent)))
                <a href="#overview">{{ __('transport.overview') }}</a>
            @endif
                <a href="#routes-pricing">{{ __('transport.routes_pricing') }}</a>
                <a href="#promotions">{{ __('transport.promotions') }}</a>
                <a href="#amenities">{{ __('transport.amenities') }}</a>
            </nav>

            <!-- <div class="detail-section-card" id="overview">
                <h2>{{ __('transport.overview') }}</h2>
                <p>{!! nl2br(e($transport['description'] ?: $transport['overview'] ?? '')) !!}</p>
            </div> -->



            @if(!empty(trim($overviewContent)))
                <div class="detail-section-card" id="overview">
                    <h2>{{ __('transport.overview') }}</h2>
                    <p>{!! nl2br(e($overviewContent)) !!}</p>
                </div>
            @endif

            @push('scripts')
            <script>
                (function() {
                    const form = document.querySelector('.booking-add-form');
                    if (!form) return;

                    const submitBtn = form.querySelector('#booking-now-btn');
                    if (!submitBtn) return;

                    submitBtn.addEventListener('click', function (e) {
                        e.preventDefault();

                        // disable immediately to avoid double-clicks
                        submitBtn.disabled = true;

                        const url = form.getAttribute('action');
                        // Sync visible booking inputs into the add-to-cart form
                        const visiblePickupDate = document.querySelector('input[name="pickup_date"]');
                        const visiblePickupTime = document.querySelector('input[name="pickup_time"]');
                        const visibleReturnDate = document.querySelector('input[name="return_date"]');
                        const visibleReturnTime = document.querySelector('input[name="return_time"]');
                        const visibleFrom = document.querySelector('select[name="transport_from"]');
                        const visibleTo = document.querySelector('select[name="transport_to"]');
                        const visiblePassengers = document.querySelector('input[name="passengers"]');

                        const pickupDateValue = visiblePickupDate ? visiblePickupDate.value.trim() : '';
                        const pickupTimeValue = visiblePickupTime ? visiblePickupTime.value.trim() : '';
                        const returnDateValue = visibleReturnDate ? visibleReturnDate.value.trim() : '';
                        const returnTimeValue = visibleReturnTime ? visibleReturnTime.value.trim() : '';

                        if (!pickupDateValue || !pickupTimeValue) {
                            alert('Please provide both pickup date and pickup time before booking.');
                            submitBtn.disabled = false;
                            return;
                        }

                        if ((returnDateValue && !returnTimeValue) || (!returnDateValue && returnTimeValue)) {
                            alert('Please provide both return date and return time for a return trip.');
                            submitBtn.disabled = false;
                            return;
                        }

                        const visiblePickupAddress = document.querySelector('textarea[name="pickup_address"]');
                        const pickupAddressValue = visiblePickupAddress ? visiblePickupAddress.value.trim() : '';
                        if (!pickupAddressValue) {
                            alert('Please provide a pick-up address before booking.');
                            submitBtn.disabled = false;
                            return;
                        }

                        const visibleDropoffAddress = document.querySelector('textarea[name="dropoff_address"]');
                        const dropoffAddressValue = visibleDropoffAddress ? visibleDropoffAddress.value.trim() : '';
                        if (!dropoffAddressValue) {
                            alert('Please provide a drop-off address before booking.');
                            submitBtn.disabled = false;
                            return;
                        }

                        if (visiblePickupDate) form.querySelector('input[name="pickup_date"]').value = visiblePickupDate.value || '';
                        if (visiblePickupTime) form.querySelector('input[name="pickup_time"]').value = visiblePickupTime.value || '';
                        if (visibleReturnDate) form.querySelector('input[name="return_date"]').value = visibleReturnDate.value || '';
                        if (visibleReturnTime) form.querySelector('input[name="return_time"]').value = visibleReturnTime.value || '';
                        if (visibleFrom) form.querySelector('input[name="route_from"]').value = visibleFrom.value || '';
                        if (visibleTo) form.querySelector('input[name="route_to"]').value = visibleTo.value || '';
                        if (visiblePassengers) form.querySelector('input[name="passengers"]').value = visiblePassengers.value || 1;
                        if (visibleDropoffAddress) visibleDropoffAddress.value = dropoffAddressValue;
                        if (visiblePickupAddress) visiblePickupAddress.value = pickupAddressValue;

                        const formData = new FormData(form);

                        fetch(url, {
                            method: 'POST',
                            body: formData,
                            credentials: 'same-origin',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).then(async (res) => {
                            let payload = null;
                            try { payload = await res.json(); } catch (err) { payload = null; }

                            if (!res.ok) {
                                // handle validation / conflict
                                const message = (payload && payload.message) ? payload.message : 'Failed to add to cart.';
                                alert(message);
                                submitBtn.disabled = false;
                                return;
                            }

                            if (payload && payload.success) {
                                // Redirect to cart review while preserving operator branding state
                                window.location.href = "{{ route('frontend.booking.cart', request()->query('operator_token') ? ['operator_token' => request()->query('operator_token')] : []) }}";
                                return;
                            }

                            // fallback
                            alert(payload && payload.message ? payload.message : 'Unable to add to cart.');
                            submitBtn.disabled = false;
                        }).catch((err) => {
                            console.error('Add to cart error', err);
                            alert('An error occurred. Please try again.');
                            submitBtn.disabled = false;
                        });
                    });

                    // Auto-fill airport address when service type is airport_transfer
                    const serviceTypeInput = document.querySelector('input[name="service_type"]#booking-service-type') || document.querySelector('input#booking-service-type') || document.querySelector('input[name="service_type"]');
                    function autoFillAirportAddress() {
                        try {
                            const service = serviceTypeInput ? (serviceTypeInput.value || '').trim() : '';
                            if (service !== 'airport_transfer') return;

                            const fromSelect = document.querySelector('select[name="transport_from"]') || document.getElementById('transport-place-from');
                            const toSelect = document.querySelector('select[name="transport_to"]') || document.getElementById('transport-place-to');
                            const pickupTextarea = document.getElementById('booking-pickup-address');
                            const dropoffTextarea = document.getElementById('booking-dropoff-address');

                            const fromVal = fromSelect ? String(fromSelect.value || '').trim() : '';
                            const toVal = toSelect ? String(toSelect.value || '').trim() : '';

                            if (fromVal && fromVal.toLowerCase() === 'airport') {
                                if (pickupTextarea && !pickupTextarea.value.trim()) pickupTextarea.value = 'Airport';
                            }

                            if (toVal && toVal.toLowerCase() === 'airport') {
                                if (dropoffTextarea && !dropoffTextarea.value.trim()) dropoffTextarea.value = 'Airport';
                            }
                        } catch (e) {
                            // ignore
                        }
                    }

                    document.addEventListener('DOMContentLoaded', autoFillAirportAddress);
                    const fromSelGlobal = document.getElementById('transport-place-from');
                    const toSelGlobal = document.getElementById('transport-place-to');
                    if (fromSelGlobal) fromSelGlobal.addEventListener('change', autoFillAirportAddress);
                    if (toSelGlobal) toSelGlobal.addEventListener('change', autoFillAirportAddress);
                    try { autoFillAirportAddress(); } catch (e) { /* ignore */ }

                })();
            </script>
            @endpush

            <div class="detail-section-card" id="vehicle-details">
                <h2>{{ __('transport.details_title') }}</h2>
                <div class="transport-detail-list">
                    <div class="transport-detail-item">
                        <strong>{{ __('transport.vehicle_name') }}</strong>
                        <span>{{ $transport['title'] ?? '' }}</span>
                    </div>
                    <div class="transport-detail-item">
                        <strong>{{ __('transport.vehicle_type_label') }}</strong>
                        <span>{{ $transport['vehicle_type'] ?? '' }}</span>
                    </div>
                    <div class="transport-detail-item">
                        <strong>{{ __('transport.seating_capacity') }}</strong>
                        <span>{{ $transport['seating_capacity'] ?? '' }}</span>
                    </div>
                </div>
            </div>

            <div class="detail-section-card" id="service-descriptions">
                <h2>{{ __('transport.service_description') }}</h2>
                <div class="transport-description-items">
                    @php
                        $locale = app()->getLocale();
                    @endphp

                    @if($locale === 'fr' && !empty($transport['long_description_fr']))
                        <div class="service-desc-item">{!! $transport['long_description_fr'] !!}</div>
                    @elseif(!empty($transport['long_description']))
                        <div class="service-desc-item">{!! $transport['long_description'] !!}</div>
                    @endif

                    @if($locale === 'fr' && !empty($transport['inclusions_fr']))
                        <h4>{{ __('transport.inclusions') }}</h4>
                        <div class="service-desc-item">{!! $transport['inclusions_fr'] !!}</div>
                    @elseif(!empty($transport['inclusions']))
                        <h4>{{ __('transport.inclusions') }}</h4>
                        <div class="service-desc-item">{!! $transport['inclusions'] !!}</div>
                    @endif

                    @if($locale === 'fr' && !empty($transport['exclusions_fr']))
                        <h4>{{ __('transport.exclusions') }}</h4>
                        <div class="service-desc-item">{!! $transport['exclusions_fr'] !!}</div>
                    @elseif(!empty($transport['exclusions']))
                        <h4>{{ __('transport.exclusions') }}</h4>
                        <div class="service-desc-item">{!! $transport['exclusions'] !!}</div>
                    @endif

                    @if($locale === 'fr' && !empty($transport['pickup_instructions_fr']))
                        <h4>{{ __('transport.pickup_instructions') }}</h4>
                        <div class="service-desc-item">{!! $transport['pickup_instructions_fr'] !!}</div>
                    @elseif(!empty($transport['pickup_instructions']))
                        <h4>{{ __('transport.pickup_instructions') }}</h4>
                        <div class="service-desc-item">{!! $transport['pickup_instructions'] !!}</div>
                    @endif
                </div>
            </div>

            <!-- <div class="detail-section-card" id="routes-pricing">
                <h2>{{ __('transport.routes_pricing') }}</h2>
                @if(empty($routes))
                    <p>{{ __('transport.no_route_pricing') }}</p>
                @else
                    <div class="transport-route-list">
                        @foreach($routes as $route)
                            <div class="transport-route-card">
                                <h3>{{ $route['route_name'] ?? ($route['route_from'] . ' → ' . $route['route_to']) }}</h3>
                                <p>{{ $route['route_from'] ?? '' }} {{ !empty($route['route_from']) && !empty($route['route_to']) ? '→' : '' }} {{ $route['route_to'] ?? '' }}</p>
                                @if(!empty($route['pricing']['default_price']))
                                    <p><strong>{{ __('transport.price') }}:</strong> USD {{ number_format((float) $route['pricing']['default_price'], 2) }}</p>
                                @endif
                                @if(!empty($route['pricing']['return_price']))
                                    <p><strong>{{ __('transport.return_price') }}</strong> USD {{ number_format((float) $route['pricing']['return_price'], 2) }}</p>
                                @endif
                                @if(!empty($route['pricing']['seasonal']))
                                    <div class="transport-seasonal-list">
                                        <h4>{{ __('transport.seasonal_pricing') }}</h4>
                                        @foreach($route['pricing']['seasonal'] as $season)
                                            <div class="transport-seasonal-item">
                                                <strong>{{ $season['start_date'] ?? '' }} - {{ $season['end_date'] ?? '' }}</strong>
                                                <p>{{ __('transport.price') }}: USD {{ number_format((float) $season['price'], 2) }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($route['notes']))
                                    <p>{{ $route['notes'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div> -->

            <div class="detail-section-card" id="promotions">
                <h2>{{ __('transport.promotions_offers') }}</h2>
                @if(!empty($transport['promotions_offers']['summary']) || !empty($transport['promotions_offers']['details']))
                    <p><strong>{{ $transport['promotions_offers']['summary'] ?? '' }}</strong></p>
                    <p>{!! nl2br(e($transport['promotions_offers']['details'] ?? '')) !!}</p>
                @else
                    <p>{{ __('transport.no_promotions') }}</p>
                @endif
            </div>

            <div class="detail-section-card" id="amenities">
                <h2>{{ __('transport.amenities') }}</h2>
                @if(empty($amenities))
                    <p>{{ __('transport.no_amenities') }}</p>
                @else
                    <div class="amenity-chip-row">
                        @foreach($amenities as $amenity)
                            <span class="hero-chip">{{ $amenity }}</span>
                        @endforeach
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
            grid-template-columns: minmax(0, 1.75fr) minmax(280px, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }

        .detail-gallery-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            position: static;
            top: auto;
        }

        .detail-main-image {
            width: 100%;
            border-radius: 12px;
            object-fit: cover;
            aspect-ratio: 1 / 1;
        }

        .detail-booking-card {
            position: static;
            top: auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 12px;
            height: fit-content;
        }

        .detail-gallery-card {
            position: static;
            top: auto;
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

        .custom-picker-wrapper {
            position: relative;
            width: 100%;
            cursor: pointer;
        }

        .custom-picker-wrapper::after {
            content: '';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            pointer-events: none;
            opacity: 0.65;
        }

        .custom-picker-wrapper.date-picker::after {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23666' d='M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z'/%3E%3C/svg%3E");
        }

        .custom-picker-wrapper.time-picker::after {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23666' d='M12 20c4.41 0 8-3.59 8-8s-3.59-8-8-8-8 3.59-8 8 3.59 8 8 8zm0-14c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6 2.69-6 6-6zm.5 3H11v5l4.25 2.52.75-1.23-3.5-2.04V9z'/%3E%3C/svg%3E");
        }

        .booking-input-text {
            width: 100%;
            background: #fff;
            cursor: pointer;
            pointer-events: auto;
        }

        .booking-input-native {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            opacity: 0 !important;
            cursor: pointer !important;
            border-radius: 8px !important;
            border: 1px solid transparent !important;
            background: transparent !important;
            padding: 0 12px !important;
            z-index: 3 !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            outline: none !important;
            color: transparent !important;
            caret-color: transparent !important;
            pointer-events: auto !important;
            mix-blend-mode: normal !important;
        }

        .booking-input-native-visible {
            opacity: 1 !important;
            color: var(--ink) !important;
            caret-color: var(--ink) !important;
            background: #fff !important;
            position: relative !important;
            z-index: 1 !important;
            border: 1px solid var(--line) !important;
            padding: 0 12px !important;
            min-height: 42px !important;
        }
        .booking-input-text {
            width: 100%;
            background: #fff;
            cursor: pointer;
            pointer-events: auto;
        }

        .booking-input-native::-webkit-calendar-picker-indicator,
        .booking-input-native::-webkit-clear-button,
        .booking-input-native::-webkit-inner-spin-button,
        .booking-input-native::-webkit-outer-spin-button,
        .booking-input-native::-ms-clear,
        .booking-input-native::-ms-expand {
            display: none !important;
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
        }

        .booking-input-native::-webkit-textfield-decoration-container,
        .booking-input-native::-webkit-clear-button,
        .booking-input-native::-webkit-inner-spin-button,
        .booking-input-native::-webkit-outer-spin-button,
        .booking-input-native::-webkit-textfield-decoration-container,
        .booking-input-native::-webkit-datetime-edit-text,
        .booking-input-native::-webkit-datetime-edit-fields-wrapper,
        .booking-input-native::-webkit-datetime-edit-month-field,
        .booking-input-native::-webkit-datetime-edit-day-field,
        .booking-input-native::-webkit-datetime-edit-year-field,
        .booking-input-native::-webkit-datetime-edit-hour-field,
        .booking-input-native::-webkit-datetime-edit-minute-field,
        .booking-input-native::-ms-clear,
        .booking-input-native::-ms-expand {
            display: none !important;
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .booking-input-native::-moz-focus-inner,
        .booking-input-native::-moz-placeholder {
            border: 0 !important;
            color: transparent !important;
        }

        .booking-input[type="date"] {
            min-height: 38px !important;
        }

        .booking-input[type="time"] {
            min-height: 34px !important;
        }

        .booking-input[type="date"]::-webkit-calendar-picker-indicator,
        .booking-input[type="time"]::-webkit-calendar-picker-indicator,
        .booking-input[type="date"]::-webkit-clear-button,
        .booking-input[type="time"]::-webkit-clear-button,
        .booking-input[type="date"]::-webkit-inner-spin-button,
        .booking-input[type="time"]::-webkit-inner-spin-button,
        .booking-input[type="date"]::-webkit-outer-spin-button,
        .booking-input[type="time"]::-webkit-outer-spin-button,
        .booking-input[type="date"]::-ms-clear,
        .booking-input[type="time"]::-ms-clear,
        .booking-input[type="date"]::-ms-expand,
        .booking-input[type="time"]::-ms-expand {
            display: none !important;
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
        }

        .booking-input[type="date"]::-moz-focus-inner,
        .booking-input[type="time"]::-moz-focus-inner {
            border: 0 !important;
        }

        .booking-input[type="date"] {
            min-height: 38px !important;
        }

        .booking-input[type="time"] {
            min-height: 34px !important;
        }

        .booking-btn {
            width: 100%;
            border: 0;
            cursor: pointer;
            grid-column: 1 / -1;
        }

        .booking-summary-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid #e5dfd6;
            font-size: 14px;
            flex-wrap: wrap;
        }

        .booking-add-form {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .booking-btn {
            width: 100%;
            border-radius: 8px;
        }

        .booking-quick-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .booking-quick-links a {
            color: var(--brand);
        }
    </style>
@endpush

@push('styles')
    <style>
        .custom-picker-wrapper.date-picker { position: relative; }
        .custom-picker-wrapper.date-picker .booking-input-text { position: relative; z-index: 1; cursor: pointer; }
        .custom-picker-wrapper.date-picker .booking-input-native { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0.01; border: 0; background: transparent; z-index: 5; cursor: pointer; }
    </style>
@endpush

@push('styles')
    <style>
        /* Custom time picker styles */
        .custom-picker-wrapper.time-picker { position: relative; }
        .custom-picker-wrapper.time-picker .booking-input-text { position: relative; z-index: 1; cursor: pointer; background: #fff; padding: 6px 12px; border-radius: 6px; border: 1px solid #e5e5e5; min-height: 34px; display: inline-block; }
        .custom-picker-wrapper.time-picker .booking-input-native { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0.01; z-index: 5; border: 0; background: transparent; }
        /* Ensure time picker UI sits above adjacent native inputs */
        .custom-picker-wrapper.time-picker { z-index: 10010; }
        .custom-picker-wrapper.time-picker .booking-input-text { z-index: 10011; }
        .custom-timepicker-popup { position: fixed; top: 0; left: 0; z-index: 10030; background: #fff; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 12px 30px rgba(0,0,0,0.12); padding: 12px; display: none; grid-template-columns: 1fr 1fr; gap: 10px; min-width: 220px; }
        .custom-timepicker-popup.open { display: grid; }
        .custom-timepicker-popup .custom-timepicker-column { display: flex; flex-direction: column; gap: 6px; }
        .custom-timepicker-popup .custom-timepicker-column label { font-size: 12px; color: #666; }
        .custom-timepicker-popup .custom-timepicker-select { width: 100%; min-width: 84px; padding: 6px 8px; border-radius: 6px; border: 1px solid #ccc; background: #fff; }
        .custom-timepicker-actions { grid-column: 1 / -1; display: flex; justify-content: flex-end; margin-top: 6px; }
        .custom-timepicker-ok { border: 0; background: #0a84ff; color: #fff; padding: 7px 12px; border-radius: 6px; cursor: pointer; }
        
        /* TomSelect dropdown fixes: render into body and ensure opaque, above other content */
        .ts-dropdown {
            background: #fff !important;
            z-index: 999999 !important;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08) !important;
            border-radius: 8px !important;
            max-height: 320px !important;
            overflow: auto !important;
        }
        .ts-control {
            background: #fff !important;
        }
        /* Fallback for native selects if TomSelect not initialized */
        select.booking-input {
            background: #fff !important;
            z-index: 9999 !important;
        }
    </style>
@endpush

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
            <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                const tmpFrom = document.getElementById('transport-place-from');
                const tmpTo = document.getElementById('transport-place-to');
                const tsOpts = {allowEmptyOption: true, create: false, dropdownParent: 'body', dropdownDirection: 'auto'};
                if (tmpFrom) new TomSelect(tmpFrom, tsOpts);
                if (tmpTo) new TomSelect(tmpTo, tsOpts);
            } catch (e) {
                console.error('TomSelect init error', e);
            }

            const fromSelect = document.getElementById('transport-place-from');
            const toSelect = document.getElementById('transport-place-to');
            const routeIdInput = document.getElementById('transport-route-id');
            const routeFromInput = document.getElementById('transport-route-from');
            const routeToInput = document.getElementById('transport-route-to');
            const pricePerPassengerInput = document.getElementById('transport-price-per-passenger');
            const returnPriceInput = document.getElementById('transport-return-price');
            const routePriceSummary = document.getElementById('transport-price-summary');
            const pickupDateInput = document.querySelector('input[name="pickup_date"]');
            const pickupDateText = pickupDateInput ? pickupDateInput.closest('.custom-picker-wrapper').querySelector('.booking-input-text') : null;
            const pickupTimeInput = document.querySelector('input[name="pickup_time"]');
            const pickupTimeText = pickupTimeInput ? pickupTimeInput.closest('.custom-picker-wrapper').querySelector('.booking-input-text') : null;
            const returnDateInput = document.querySelector('input[name="return_date"]');
            const returnDateText = returnDateInput ? returnDateInput.closest('.custom-picker-wrapper').querySelector('.booking-input-text') : null;
            const returnTimeInput = document.querySelector('input[name="return_time"]');
            const returnTimeText = returnTimeInput ? returnTimeInput.closest('.custom-picker-wrapper').querySelector('.booking-input-text') : null;

            const routes = @json($routes);
            const rawPlaceRegionMap = @json($transport['place_region_map'] ?? []);
            const serverServiceType = @json($serviceType);
            const serverCarRentalTotal = @json($carRentalTotal);
            const placeRegionMap = Object.fromEntries(
                Object.entries(rawPlaceRegionMap || {}).map(([key, value]) => [String(key).trim().toLowerCase(), String(value).trim().toLowerCase()])
            );
            const transportReturnLabel = @json(__('transport.return_price'));
            const booking = @json($booking ?? []);

            const TRANSPORT_DEBUG = true;
            const pad = (v) => String(v).padStart(2, '0');
            const normalizeTime = function(value) {
                if (!value) return '';
                const raw = String(value).trim();
                if (raw === '00:00' || raw === '24:00') {
                    return '24:00';
                }
                const direct = raw.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
                if (direct) {
                    return `${pad(direct[1])}:${direct[2]}`;
                }
                const ampm = raw.match(/^(\d{1,2}):(\d{2})\s*(am|pm)$/i);
                if (ampm) {
                    let hour = parseInt(ampm[1], 10);
                    const minute = ampm[2];
                    const suffix = ampm[3].toLowerCase();
                    if (suffix === 'pm' && hour < 12) {
                        hour += 12;
                    }
                    if (suffix === 'am' && hour === 12) {
                        hour = 0;
                    }
                    return `${pad(hour)}:${minute}`;
                }
                return raw;
            };
            const formatTimeValue = function(value) {
                const normalized = normalizeTime(value);
                return normalized === '00:00' ? '24:00' : normalized;
            };
            const formatPickerDate = function(value, type) {
                if (!value) {
                    return '';
                }
                if (type === 'date') {
                    const parts = String(value).split('-');
                    if (parts.length !== 3) {
                        return value;
                    }
                    return `${parts[2]}/${parts[1]}/${parts[0]}`;
                }
                if (type === 'time') {
                    return formatTimeValue(value);
                }
                return value;
            };

            const buildCustomTimePicker = function(wrapper) {
                if (!wrapper) return;
                const input = wrapper.querySelector('input[type="hidden"]');
                const display = wrapper.querySelector('.booking-input-text .time-value') || wrapper.querySelector('.booking-input-text');
                const clickableTarget = wrapper.querySelector('.booking-input-text') || display;
                if (!input || !display || !clickableTarget) return;

                let hiddenBookingInput = null;
                if (input.name === 'pickup_time') {
                    hiddenBookingInput = document.getElementById('booking-pickup-time');
                } else if (input.name === 'return_time') {
                    hiddenBookingInput = document.getElementById('booking-return-time');
                }

                const hours = Array.from({ length: 24 }, (_, i) => String(i + 1));
                const minutes = Array.from({ length: 60 }, (_, i) => String(i).padStart(2, '0'));

                const popup = document.createElement('div');
                popup.className = 'custom-timepicker-popup';
                popup.innerHTML = `
                    <div class="custom-timepicker-column">
                        <label>Hour</label>
                    </div>
                    <div class="custom-timepicker-column">
                        <label>Minute</label>
                    </div>
                    <div class="custom-timepicker-actions">
                        <button type="button" class="custom-timepicker-ok">Set</button>
                    </div>
                `;

                const hourSelect = document.createElement('select');
                const minuteSelect = document.createElement('select');
                hourSelect.className = 'custom-timepicker-select';
                minuteSelect.className = 'custom-timepicker-select';

                hours.forEach((hour) => {
                    const option = document.createElement('option');
                    option.value = hour;
                    option.textContent = hour;
                    hourSelect.appendChild(option);
                });
                minutes.forEach((minute) => {
                    const option = document.createElement('option');
                    option.value = minute;
                    option.textContent = minute;
                    minuteSelect.appendChild(option);
                });

                popup.querySelector('.custom-timepicker-column:nth-child(1)').appendChild(hourSelect);
                popup.querySelector('.custom-timepicker-column:nth-child(2)').appendChild(minuteSelect);
                const okButton = popup.querySelector('.custom-timepicker-ok');

                const updateMinuteOptions = function() {
                    if (hourSelect.value === '24') {
                        minuteSelect.value = '00';
                        minuteSelect.querySelectorAll('option').forEach((option) => {
                            option.disabled = option.value !== '00';
                        });
                    } else {
                        minuteSelect.querySelectorAll('option').forEach((option) => {
                            option.disabled = false;
                        });
                    }
                };

                const syncHiddenFormInputs = function(name, value) {
                    document.querySelectorAll(`input[type="hidden"][name="${name}"]`).forEach((hiddenInput) => {
                        hiddenInput.value = value;
                    });
                };

                const setValue = function() {
                    const hour = hourSelect.value;
                    const minute = minuteSelect.value;
                    const normalized = hour === '24' ? '24:00' : `${pad(hour)}:${minute}`;
                    input.value = normalized;
                    syncHiddenFormInputs(input.name, normalized);
                    display.textContent = hour === '24' ? '24:00' : `${hour}:${minute}`;
                    if (hiddenBookingInput) {
                        hiddenBookingInput.value = normalized;
                    }
                    popup.classList.remove('open');
                };

                const syncSelects = function() {
                    const normalized = normalizeTime(input.value || '');
                    const parts = normalized.split(':');
                    let currentHour = parts[0] || '1';
                    let currentMinute = parts[1] || '00';
                    let numericHour = parseInt(currentHour, 10);
                    if (Number.isNaN(numericHour)) numericHour = 1;
                    if (numericHour === 0) numericHour = 24;
                    const hourStr = String(numericHour);
                    hourSelect.value = hours.includes(hourStr) ? hourStr : '1';
                    if (hourSelect.value === '24') {
                        currentMinute = '00';
                    }
                    minuteSelect.value = minutes.includes(currentMinute) ? currentMinute : '00';
                    updateMinuteOptions();
                };

                hourSelect.addEventListener('change', updateMinuteOptions);
                okButton.addEventListener('click', function(event) {
                    event.stopPropagation();
                    setValue();
                });
                popup.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
                document.body.appendChild(popup);

                const positionPopup = function() {
                    const popupHeight = popup.offsetHeight || 220;
                    const popupWidth = popup.offsetWidth || 220;
                    const wrapperRect = wrapper.getBoundingClientRect();
                    let top = wrapperRect.bottom + 6;
                    let left = wrapperRect.left;
                    if (top + popupHeight > window.innerHeight && wrapperRect.top >= popupHeight + 10) {
                        top = wrapperRect.top - popupHeight - 6;
                    }
                    if (left + popupWidth > window.innerWidth - 10) {
                        left = Math.max(10, window.innerWidth - popupWidth - 10);
                    }
                    if (left < 10) {
                        left = 10;
                    }
                    popup.style.position = 'fixed';
                    popup.style.top = `${top}px`;
                    popup.style.left = `${left}px`;
                };

                const openPopup = function() {
                    document.querySelectorAll('.custom-timepicker-popup.open').forEach((openPopup) => openPopup.classList.remove('open'));
                    popup.classList.add('open');
                    positionPopup();
                };

                const togglePopup = function(event) {
                    if (event) { event.preventDefault(); event.stopPropagation(); }
                    if (popup.classList.contains('open')) {
                        popup.classList.remove('open');
                        return;
                    }
                    syncSelects();
                    openPopup();
                };

                // Prevent clicks on the display or its SVG from bubbling to nearby controls
                const onDisplayClick = function(e) { e.preventDefault(); e.stopPropagation(); togglePopup(e); };
                clickableTarget.addEventListener('click', onDisplayClick, false);
                const svgIcon = clickableTarget.querySelector('svg');
                if (svgIcon) svgIcon.addEventListener('click', onDisplayClick, true);

                // expose open/close helpers on wrapper for external control
                wrapper._openTimepicker = function() {
                    syncSelects();
                    openPopup();
                };
                wrapper._closeTimepicker = function() {
                    popup.classList.remove('open');
                };
                document.addEventListener('click', function(event) {
                    if (!wrapper.contains(event.target) && !popup.contains(event.target)) {
                        popup.classList.remove('open');
                    }
                });

                input.value = normalizeTime(input.value || '');
                syncHiddenFormInputs(input.name, input.value);
                display.textContent = formatTimeValue(input.value || '');
                if (hiddenBookingInput) {
                    hiddenBookingInput.value = input.value;
                }
            };

            const bindCustomTimePickers = function() {
                document.querySelectorAll('.custom-picker-wrapper.time-picker').forEach(buildCustomTimePicker);
            };

            bindCustomTimePickers();

            // Global click handler: if click falls within a time-picker wrapper rect
            // open that picker's popup (handles overlapping elements stealing clicks)
            // Ignore clicks inside the popup itself so Set/close actions are not intercepted.
            document.addEventListener('click', function(e) {
                try {
                    const path = (typeof e.composedPath === 'function') ? e.composedPath() : (e.path || (function() { let p=[]; let n=e.target; while(n){p.push(n); n=n.parentNode;} return p; })());
                    if ((path || []).some((node) => node && node.classList && node.classList.contains('custom-timepicker-popup'))) {
                        return;
                    }
                    for (const node of (path || [])) {
                        if (!node || !node.classList) continue;
                        if (node.classList.contains('custom-picker-wrapper') && node.classList.contains('time-picker')) {
                            if (typeof node._openTimepicker === 'function') {
                                node._openTimepicker();
                                e.stopPropagation();
                                e.preventDefault();
                                return;
                            }
                        }
                    }
                } catch (err) { /* ignore */ }
            }, true);

            function normalizeValue(value) {
                if (!value) {
                    return null;
                }
                const normalized = String(value).trim().replace(/\s+/g, ' ').toLowerCase();
                if (['north', 'south', 'airport'].includes(normalized)) {
                    return normalized;
                }
                if (placeRegionMap[normalized]) {
                    return placeRegionMap[normalized];
                }
                return normalized;
            }

            const routeLookup = new Map();
            routes.forEach(route => {
                if (String(route.service_type || 'airport_transfer') !== serverServiceType) {
                    return;
                }
                const fromRegion = normalizeValue(route.route_from || '');
                const toRegion = normalizeValue(route.route_to || '');
                if (fromRegion && toRegion) {
                    routeLookup.set(`${fromRegion}|${toRegion}`, route);
                }
            });

            function findRouteBySelection(fromValue, toValue) {
                const fromRegion = normalizeValue(fromValue);
                const toRegion = normalizeValue(toValue);
                if (!fromRegion || !toRegion) {
                    return null;
                }
                return routeLookup.get(`${fromRegion}|${toRegion}`) || null;
            }

            function resolveSeasonalValue(seasonalEntries, dateValue, defaultValue, field = 'price') {
                const normalizedDate = String(dateValue || '').trim();
                const targetDate = normalizedDate ? new Date(`${normalizedDate}T00:00:00`) : new Date();
                if (Number.isNaN(targetDate.getTime())) {
                    return defaultValue;
                }

                const entries = Array.isArray(seasonalEntries) ? seasonalEntries : [];
                for (const entry of entries) {
                    const start = entry?.start || entry?.start_date || null;
                    const end = entry?.end || entry?.end_date || null;
                    if (!start || !end) {
                        continue;
                    }

                    const startDate = new Date(`${start}T00:00:00`);
                    const endDate = new Date(`${end}T23:59:59`);
                    if (targetDate >= startDate && targetDate <= endDate) {
                        const value = entry?.[field];
                        return value != null ? parseFloat(value) : defaultValue;
                    }
                }

                return defaultValue;
            }

            function updateRouteFields() {
                if (!fromSelect || !toSelect) {
                    return;
                }

                routeFromInput.value = fromSelect.value || '';
                routeToInput.value = toSelect.value || '';

                const selectedRoute = findRouteBySelection(fromSelect.value, toSelect.value);
                const bookingDateValue = (pickupDateInput && pickupDateInput.value) ? pickupDateInput.value : (booking && booking.pickup_date ? booking.pickup_date : '');
                const bookingReturnDateValue = (returnDateInput && returnDateInput.value) ? returnDateInput.value : (booking && booking.return_date ? booking.return_date : '');
                if (selectedRoute) {
                    routeIdInput.value = selectedRoute.route_id || '';
                    const defaultPrice = selectedRoute.pricing?.default_price != null ? parseFloat(selectedRoute.pricing.default_price) : 0;
                    const effectivePrice = resolveSeasonalValue(selectedRoute.pricing?.seasonal || [], bookingDateValue, defaultPrice, 'price');
                    pricePerPassengerInput.value = (effectivePrice || 0).toFixed(2);

                    // Only set return price if a return date was included in the booking context
                    if (booking && booking.return_date) {
                        const defaultReturnPrice = selectedRoute.pricing?.return_price != null ? parseFloat(selectedRoute.pricing.return_price) : 0;
                        const effectiveReturnPrice = resolveSeasonalValue(selectedRoute.pricing?.seasonal || [], bookingReturnDateValue, defaultReturnPrice, 'return_price');
                        returnPriceInput.value = (effectiveReturnPrice || 0).toFixed(2);
                    } else {
                        returnPriceInput.value = '';
                    }
                } else {
                    routeIdInput.value = '';
                    pricePerPassengerInput.value = pricePerPassengerInput.value || '';
                    returnPriceInput.value = returnPriceInput.value || '';
                }

                if (routePriceSummary) {
                    // If a return price is set and a return date/value is present, show only the return price
                    const hasReturnPrice = returnPriceInput && returnPriceInput.value && parseFloat(returnPriceInput.value) > 0;
                    const hasReturnDate = (returnDateInput && returnDateInput.value) || (booking && booking.return_date);
                    if (hasReturnPrice && hasReturnDate) {
                        routePriceSummary.textContent = `USD ${parseFloat(returnPriceInput.value).toFixed(2)}`;
                    } else {
                        routePriceSummary.textContent = `USD ${pricePerPassengerInput.value}`;
                    }
                }
            }

            if (fromSelect && toSelect) {
                const syncRoutePrice = function () {
                    updateRouteFields();
                };
                fromSelect.addEventListener('change', syncRoutePrice);
                toSelect.addEventListener('change', syncRoutePrice);
                if (pickupDateInput) {
                    pickupDateInput.addEventListener('change', syncRoutePrice);
                }
                updateRouteFields();

                const hiddenPickupDate = document.querySelector('.booking-add-form input[name="pickup_date"]');
                const hiddenReturnDate = document.querySelector('.booking-add-form input[name="return_date"]');
                const hiddenPickup = document.getElementById('booking-pickup-time');
                const hiddenReturn = document.getElementById('booking-return-time');

                const setPickerDisplayValue = function (displayElement, value, type) {
                    const formattedValue = formatPickerDate(value, type) || '';
                    if (!displayElement) {
                        return;
                    }
                    if (displayElement.tagName === 'INPUT' || displayElement.tagName === 'TEXTAREA') {
                        displayElement.value = formattedValue;
                    } else {
                        displayElement.textContent = formattedValue;
                    }
                };

                const syncNativePicker = function (nativeInput, textInput, hiddenInput) {
                    if (!nativeInput || !textInput) {
                        return;
                    }
                    setPickerDisplayValue(textInput, nativeInput.value, nativeInput.type);
                    const wrapper = nativeInput.closest('.custom-picker-wrapper');
                    const openPicker = function () {
                        if (TRANSPORT_DEBUG) console.log('transport: openPicker', nativeInput.name);
                        nativeInput.focus();
                        if (typeof nativeInput.showPicker === 'function') {
                            nativeInput.showPicker();
                        } else {
                            nativeInput.click();
                        }
                    };
                    if (wrapper) {
                        wrapper.style.cursor = 'pointer';
                        wrapper.addEventListener('click', function (event) {
                            event.preventDefault();
                            openPicker();
                        });
                    }
                    textInput.style.cursor = 'pointer';
                    textInput.addEventListener('click', function (event) {
                        event.preventDefault();
                        openPicker();
                    });
                    nativeInput.addEventListener('focus', function () {
                        if (typeof nativeInput.showPicker === 'function') {
                            setTimeout(() => {
                                nativeInput.showPicker();
                            }, 0);
                        }
                    });
                    // on blur sync final value
                    nativeInput.addEventListener('blur', function () {
                        setPickerDisplayValue(textInput, nativeInput.value, nativeInput.type);
                        if (hiddenInput) hiddenInput.value = nativeInput.value || '';
                        if (nativeInput.name === 'pickup_date' || nativeInput.name === 'return_date') {
                            updateRouteFields();
                        }
                    });
                    if (TRANSPORT_DEBUG) console.log('transport: syncNativePicker init', nativeInput.name);
                    const nativeChangeHandler = function () {
                        if (TRANSPORT_DEBUG) console.log('transport: nativeChangeHandler', nativeInput.name, nativeInput.value);
                        setPickerDisplayValue(textInput, nativeInput.value, nativeInput.type);
                        if (hiddenInput) {
                            hiddenInput.value = nativeInput.value || '';
                        }
                        if (nativeInput.name === 'pickup_date' || nativeInput.name === 'return_date') {
                            updateRouteFields();
                        }
                    };
                    nativeInput.addEventListener('change', nativeChangeHandler);
                    nativeInput.addEventListener('input', nativeChangeHandler);
                };

                syncNativePicker(pickupDateInput, pickupDateText, hiddenPickupDate);
                syncNativePicker(returnDateInput, returnDateText, hiddenReturnDate);

                if (pickupTimeInput && hiddenPickup) {
                    hiddenPickup.value = pickupTimeInput.value || hiddenPickup.value || '';
                }
                if (returnTimeInput && hiddenReturn) {
                    hiddenReturn.value = returnTimeInput.value || hiddenReturn.value || '';
                }
            }

            // Enforce route selection and traveller login before adding to cart
            const bookingUpdateSearchForm = document.querySelector('.booking-form-grid');
            const bookingServiceTypeHidden = bookingUpdateSearchForm ? bookingUpdateSearchForm.querySelector('input[name="service_type"]') : null;
            const bookingUpdateSearchBtn = document.getElementById('booking-update-search-btn');
            const bookingNowBtn = document.getElementById('booking-now-btn');
            const bookingAddForm = document.querySelector('.booking-add-form');
            const transportRouteFromHidden = document.getElementById('transport-route-from');
            const transportRouteToHidden = document.getElementById('transport-route-to');
            const travelerLoggedIn = @json(auth('traveler')->check());

            const selectedServiceType = bookingServiceTypeHidden?.value || serverServiceType;

            const hasReturnTripTimes = function () {
                const pickupT = document.querySelector('input[name="pickup_time"]')?.value.trim() || '';
                const returnT = document.querySelector('input[name="return_time"]')?.value.trim() || '';
                return pickupT !== '' && returnT !== '';
            };

            const isPartialReturnTrip = function () {
                const returnDate = document.querySelector('input[name="return_date"]')?.value.trim() || '';
                const returnTime = document.querySelector('input[name="return_time"]')?.value.trim() || '';
                return (returnDate !== '' || returnTime !== '') && !(returnDate !== '' && returnTime !== '');
            };

            const updateSearchButtonState = function () {
                // All current service types may proceed without legacy car rental locking.
                if (bookingUpdateSearchBtn) {
                    bookingUpdateSearchBtn.disabled = false;
                    bookingUpdateSearchBtn.removeAttribute('disabled');
                }
                if (bookingNowBtn) {
                    bookingNowBtn.disabled = false;
                    bookingNowBtn.removeAttribute('disabled');
                }
            };

            updateSearchButtonState();

            if (bookingUpdateSearchForm) {
                bookingUpdateSearchForm.addEventListener('submit', function (ev) {
                    const pickupDateValue = document.querySelector('input[name="pickup_date"]')?.value.trim() || '';
                    const pickupTimeValue = document.querySelector('input[name="pickup_time"]')?.value.trim() || '';
                    if (!pickupDateValue || !pickupTimeValue) {
                        ev.preventDefault();
                        alert('Please provide both pickup date and pickup time before updating search.');
                        return false;
                    }
                    if (isPartialReturnTrip()) {
                        ev.preventDefault();
                        alert('Please provide both return date and return time for a return trip search.');
                        return false;
                    }
                });
            }

            if (bookingAddForm) {
                bookingAddForm.addEventListener('submit', function (ev) {
                    updateRouteFields();
                    const pickup = document.querySelector('input[name="pickup_date"]')?.value.trim() || '';
                    const pickupT = document.querySelector('input[name="pickup_time"]')?.value.trim() || '';
                    if (!pickup || !pickupT) {
                        ev.preventDefault();
                        alert('Please provide both pickup date and pickup time before booking.');
                        return false;
                    }

                    // ensure service type rules when adding to cart
                    if (isPartialReturnTrip()) {
                        ev.preventDefault();
                        alert('Please provide both return date and return time for a return trip.');
                        return false;
                    }
                    const routeFromVal = (fromSelect && fromSelect.value) ? fromSelect.value.trim() : ((routeFromInput && routeFromInput.value) ? routeFromInput.value.trim() : '');
                    const routeToVal = (toSelect && toSelect.value) ? toSelect.value.trim() : ((routeToInput && routeToInput.value) ? routeToInput.value.trim() : '');
                    if (!routeFromVal) {
                        ev.preventDefault();
                        ev.stopImmediatePropagation();
                        alert('{{ addslashes(__('transport.validation.select_departure')) }}');
                        document.getElementById('transport-place-from').focus();
                        return false;
                    }
                    if (!routeToVal) {
                        ev.preventDefault();
                        ev.stopImmediatePropagation();
                        alert('{{ addslashes(__('transport.validation.select_destination')) }}');
                        document.getElementById('transport-place-to').focus();
                        return false;
                    }

                    if (!travelerLoggedIn) {
                        ev.preventDefault();
                        ev.stopImmediatePropagation();
                        // Ask user to log in or register first (use JSON-escaped string to avoid syntax errors)
                        const loginPrompt = @json(__('transport.validation.login_or_register_first'));
                        if (confirm(loginPrompt)) {
                            // send to traveller login with redirect back
                            window.location.href = '{{ route('traveler.login') }}?redirect=' + encodeURIComponent(window.location.href);
                        }
                        return false;
                    }
                    return true;
                }, true);
            }
        });
    </script>
@endpush
