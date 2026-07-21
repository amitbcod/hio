@extends('frontend.layout')

@section('title', $transport['title'] . ' | Transport | Holidays.io')
@section('meta_description', $transport['excerpt'] ?? __('transport.meta_description', ['title' => $transport['title'] ?? 'Transport']))

@section('content')
    @php
        $booking = $transport['booking'] ?? [
            'pickup_date' => now()->format('Y-m-d'),
            'pickup_time' => '',
            'pickup_date_display' => now()->format('d-m-Y'),
            'return_date' => now()->addDay()->format('Y-m-d'),
            'return_time' => '',
            'return_date_display' => now()->addDay()->format('d-m-Y'),
            'passengers' => 1,
        ];
        $routes = $transport['routes_pricing'] ?? [];
        $amenities = $transport['amenities'] ?? [];
        $operator = $transport['operator'] ?? null;
        $serviceType = request()->query('service_type', 'route');
        $booking['pickup_date'] = trim((string) request()->query('pickup_date', request()->query('arrival_date', request()->query('check_in', $booking['pickup_date']))));
        $booking['pickup_time'] = trim((string) request()->query('pickup_time', request()->query('arrival_time', $booking['pickup_time'])));
        $booking['return_date'] = trim((string) request()->query('return_date', request()->query('check_out', $booking['return_date'])));
        $booking['return_time'] = trim((string) request()->query('return_time', $booking['return_time']));
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
            $returnDateParam = request()->query('return_date') ?: request()->query('return_date') ?: request()->query('check_out') ?: null;
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
                    <form method="GET" action="{{ route('frontend.transports.show', $transport['id']) }}" class="booking-form-grid">
                        <div class="booking-field">
                            <label>{{ __('home.search.from') }}</label>
                            <select id="transport-place-from" name="transport_from" class="booking-input">
                                <option value="">{{ __('home.search.departure_location') }}</option>
                                @foreach($transport['place_names'] ?? [] as $place)
                                    <option value="{{ $place }}" {{ ($transport['selected_transport_from'] ?? '') === $place ? 'selected' : '' }}>{{ $place }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="booking-field">
                            <label>{{ __('home.search.to') }}</label>
                            <select id="transport-place-to" name="transport_to" class="booking-input">
                                <option value="">{{ __('home.search.destination') }}</option>
                                @foreach($transport['place_names'] ?? [] as $place)
                                    <option value="{{ $place }}" {{ ($transport['selected_transport_to'] ?? '') === $place ? 'selected' : '' }}>{{ $place }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="booking-field booking-field-inline">
                            <label>{{ __('transport.form.pickup_date') }}</label>
                            <div class="custom-picker-wrapper date-picker">
                                <input type="text" readonly class="booking-input booking-input-text" value="{{ $booking['pickup_date'] }}">
                                <input type="date" name="pickup_date" value="{{ $booking['pickup_date'] }}" class="booking-input booking-input-native" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="booking-field booking-field-inline">
                            <label>{{ __('transport.form.pickup_time') }}</label>
                            <div class="custom-picker-wrapper time-picker">
                                <input type="text" readonly class="booking-input booking-input-text" value="{{ $booking['pickup_time'] }}">
                                <input type="time" name="pickup_time" value="{{ $booking['pickup_time'] }}" class="booking-input booking-input-native">
                            </div>
                        </div>
                        <div class="booking-field booking-field-inline">
                            <label>{{ __('transport.form.return_date') }}</label>
                            <div class="custom-picker-wrapper date-picker">
                                <input type="text" readonly class="booking-input booking-input-text" value="{{ $booking['return_date'] }}">
                                <input type="date" name="return_date" value="{{ $booking['return_date'] }}" class="booking-input booking-input-native" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="booking-field booking-field-inline">
                            <label>{{ __('transport.form.return_time') }}</label>
                            <div class="custom-picker-wrapper time-picker">
                                <input type="text" readonly class="booking-input booking-input-text" value="{{ $booking['return_time'] }}">
                                <input type="time" name="return_time" value="{{ $booking['return_time'] }}" class="booking-input booking-input-native">
                            </div>
                        </div>
                        <div class="booking-field">
                            <label>{{ __('transport.form.passengers') }}</label>
                            <input type="number" name="passengers" min="1" value="{{ $booking['passengers'] }}" class="booking-input">
                        </div>
                        <div class="booking-field">
                            <label>Service Type</label>
                            <select name="service_type" id="service-type-select" class="booking-input">
                                <option value="route" {{ ($serviceType ?? 'route') === 'route' ? 'selected' : '' }}>Route wise</option>
                                <option value="car_rental" {{ ($serviceType ?? '') === 'car_rental' ? 'selected' : '' }}>Car rental</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-primary booking-btn">{{ __('transport.form.update_search') }}</button>
                    </form>

                    <!-- <div class="booking-summary-line">
                        <span>{{ $booking['passengers'] }} {{ trans_choice('transport.summary.passengers', $booking['passengers']) }}</span>
                    </div> -->

                    <form method="POST" action="{{ route('frontend.booking.cart.add') }}" class="booking-add-form">
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
                        <input type="hidden" name="service_type" id="booking-service-type" value="{{ $serviceType ?? 'route' }}">
                        <input type="hidden" name="passengers" value="{{ $booking['passengers'] }}">
                        <input type="hidden" id="transport-route-id" name="route_id" value="{{ $routeId }}">
                        <input type="hidden" id="transport-route-from" name="route_from" value="{{ $selectedRouteFrom }}">
                        <input type="hidden" id="transport-route-to" name="route_to" value="{{ $selectedRouteTo }}">
                        <input type="hidden" name="source" value="detail">

                        <button type="submit" class="btn-secondary booking-btn">{{ __('transport.form.book_now') }}</button>
                    </form>

                    <div class="booking-summary-line" id="transport-price-summary">
                        @if(($serviceType ?? 'route') === 'car_rental' && !empty($carRentalTotal))
                            <span>{{ __('transport.price') }}: USD {{ $carRentalTotal }}</span>
                        @else
                            <span>{{ __('transport.price') }}: USD {{ !empty($booking['return_date']) && $selectedReturnPrice ? ' ' . number_format((float) $selectedReturnPrice, 2) : '' }}</span>
                        @endif
                    </div>
                    
                    <!-- DEBUG: Car Rental Calculation -->
                    @if(($serviceType ?? 'route') === 'car_rental')
                        <!-- <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; font-size: 12px; border: 1px solid #ccc;">
                            <strong>DEBUG INFO:</strong><br>
                            Service Type: {{ $serviceType ?? 'N/A' }}<br>
                            Pickup: {{ request()->query('arrival_date') ?? 'N/A' }} {{ request()->query('arrival_time') ?? 'N/A' }}<br>
                            Return: {{ request()->query('return_date') ?? 'N/A' }} {{ request()->query('return_time') ?? 'N/A' }}<br>
                            <strong>Debug Calculation Info:</strong><br>
                            Total Minutes: {{ $debugInfo['totalMinutes'] ?? 'N/A' }}<br>
                            Total Hours: {{ $debugInfo['totalHours'] ?? 'N/A' }}<br>
                            Blocks: <pre style="font-size: 11px;">{{ json_encode($debugInfo['blocks'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                            DP Array: <pre style="font-size: 11px;">{{ json_encode($debugInfo['dp_array'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                            Calculated Total: {{ $debugInfo['total'] ?? 'N/A' }}<br>
                            Car Prices: <pre style="font-size: 11px;">{{ json_encode($transport['car_rental_prices'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                            Car Rental Total (Formatted): {{ $carRentalTotal ?? 'NULL' }}<br>
                        </div> -->
                    @endif

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

            <nav class="detail-anchor-nav">
                <a href="#overview">{{ __('transport.overview') }}</a>
                <a href="#routes-pricing">{{ __('transport.routes_pricing') }}</a>
                <a href="#promotions">{{ __('transport.promotions') }}</a>
                <a href="#amenities">{{ __('transport.amenities') }}</a>
            </nav>

            <div class="detail-section-card" id="overview">
                <h2>{{ __('transport.overview') }}</h2>
                <p>{!! nl2br(e($transport['description'] ?: $transport['overview'] ?? '')) !!}</p>
            </div>

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
            pointer-events: none;
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

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
            <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                const tmpFrom = document.getElementById('transport-place-from');
                const tmpTo = document.getElementById('transport-place-to');
                if (tmpFrom) new TomSelect(tmpFrom, {allowEmptyOption: true, create: false});
                if (tmpTo) new TomSelect(tmpTo, {allowEmptyOption: true, create: false});
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
            const serverCarRentalTotal = @json($carRentalTotal);
            const serverServiceType = @json($serviceType ?? 'route');
            const placeRegionMap = Object.fromEntries(
                Object.entries(rawPlaceRegionMap || {}).map(([key, value]) => [String(key).trim().toLowerCase(), String(value).trim().toLowerCase()])
            );
            const transportReturnLabel = @json(__('transport.return_price'));
            const booking = @json($booking ?? []);

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
                    const main = routePriceSummary ? `USD ${pricePerPassengerInput.value}` : '';
                    const ret = (returnPriceInput.value && parseFloat(returnPriceInput.value) > 0) ? ` + ${transportReturnLabel} USD ${returnPriceInput.value}` : '';
                    routePriceSummary.textContent = main + ret;
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

                const syncNativePicker = function (nativeInput, textInput, hiddenInput) {
                    if (!nativeInput || !textInput) {
                        return;
                    }
                    textInput.value = nativeInput.value || '';
                    const wrapper = nativeInput.closest('.custom-picker-wrapper');
                    const openPicker = function () {
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
                    nativeInput.addEventListener('change', function () {
                        textInput.value = nativeInput.value || '';
                        if (hiddenInput) {
                            hiddenInput.value = nativeInput.value || hiddenInput.value || '';
                        }
                        if (nativeInput.name === 'pickup_date' || nativeInput.name === 'return_date') {
                            updateRouteFields();
                        }
                    });
                };

                syncNativePicker(pickupDateInput, pickupDateText, hiddenPickupDate);
                syncNativePicker(returnDateInput, returnDateText, hiddenReturnDate);
                syncNativePicker(pickupTimeInput, pickupTimeText, hiddenPickup);
                syncNativePicker(returnTimeInput, returnTimeText, hiddenReturn);

                if (pickupTimeInput && hiddenPickup) {
                    hiddenPickup.value = pickupTimeInput.value || hiddenPickup.value || '';
                }
                if (returnTimeInput && hiddenReturn) {
                    hiddenReturn.value = returnTimeInput.value || hiddenReturn.value || '';
                }
            }

            // Enforce route selection and traveller login before adding to cart
            const bookingAddForm = document.querySelector('.booking-add-form');
            const bookNowBtn = bookingAddForm ? bookingAddForm.querySelector('button[type="submit"]') : null;
            const transportRouteFromHidden = document.getElementById('transport-route-from');
            const transportRouteToHidden = document.getElementById('transport-route-to');
            const travelerLoggedIn = @json(auth('traveler')->check());

            if (bookingAddForm) {
                bookingAddForm.addEventListener('submit', function (ev) {
                    updateRouteFields();
                    // ensure service type rules when adding to cart
                    const hiddenService = document.getElementById('booking-service-type')?.value || serverServiceType;
                    if (hiddenService === 'car_rental') {
                        const pickup = document.querySelector('input[name="pickup_date"]')?.value || '';
                        const pickupT = document.querySelector('input[name="pickup_time"]')?.value || '';
                        const ret = document.querySelector('input[name="return_date"]')?.value || '';
                        const retT = document.querySelector('input[name="return_time"]')?.value || '';
                        if (!pickup || !pickupT || !ret || !retT) {
                            ev.preventDefault();
                            alert('Please provide pickup and return dates and times for car rental bookings.');
                            return false;
                        }
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
                        // Ask user to log in or register first
                        if (confirm('{{ addslashes(__('transport.validation.login_or_register_first')) }}')) {
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
