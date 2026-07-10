@extends('frontend.layout')

@section('title', $transport['title'] . ' | Transport | Holidays.io')
@section('meta_description', $transport['excerpt'] ?? __('transport.meta_description', ['title' => $transport['title'] ?? 'Transport']))

@section('content')
    @php
        $booking = $transport['booking'] ?? [
            'pickup_date' => now()->format('Y-m-d'),
            'pickup_date_display' => now()->format('d-m-Y'),
            'return_date' => now()->addDay()->format('Y-m-d'),
            'return_date_display' => now()->addDay()->format('d-m-Y'),
            'passengers' => 1,
        ];
        $routes = $transport['routes_pricing'] ?? [];
        $amenities = $transport['amenities'] ?? [];
        $operator = $transport['operator'] ?? null;
        $detailQuery = http_build_query([
            'pickup_date' => $booking['pickup_date'],
            'return_date' => $booking['return_date'],
            'passengers' => $booking['passengers'],
        ]);
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
                        <div class="booking-field">
                            <label>{{ __('transport.form.pickup_date') }}</label>
                            <input type="date" name="pickup_date" value="{{ $booking['pickup_date'] }}" class="booking-input" min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="booking-field">
                            <label>{{ __('transport.form.pickup_time') }}</label>
                            <input type="time" name="pickup_time" value="{{ $booking['pickup_time'] ?? '' }}" class="booking-input">
                        </div>
                        <div class="booking-field">
                            <label>{{ __('transport.form.return_date') }}</label>
                            <input type="date" name="return_date" value="{{ $booking['return_date'] ?? '' }}" class="booking-input" min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="booking-field">
                            <label>{{ __('transport.form.return_time') }}</label>
                            <input type="time" name="return_time" value="{{ $booking['return_time'] ?? '' }}" class="booking-input">
                        </div>
                        <div class="booking-field">
                            <label>{{ __('transport.form.passengers') }}</label>
                            <input type="number" name="passengers" min="1" value="{{ $booking['passengers'] }}" class="booking-input">
                        </div>
                        <button type="submit" class="btn-primary booking-btn">{{ __('transport.form.update_search') }}</button>
                    </form>

                    <!-- <div class="booking-summary-line">
                        <span>{{ $booking['passengers'] }} {{ trans_choice('transport.summary.passengers', $booking['passengers']) }}</span>
                    </div> -->

                    @php
                        $selectedRoute = $transport['selected_route'] ?? ($routes[0] ?? null);
                        $selectedRouteFrom = $transport['selected_transport_from'] ?? ($selectedRoute['route_from'] ?? '');
                        $selectedRouteTo = $transport['selected_transport_to'] ?? ($selectedRoute['route_to'] ?? '');
                        $selectedDefaultPrice = $selectedRoute['pricing']['default_price'] ?? $transport['starting_rate'] ?? 0;
                        $selectedReturnPrice = $selectedRoute['pricing']['return_price'] ?? null;
                        $routeId = $selectedRoute['route_id'] ?? '';
                    @endphp

                    <form method="POST" action="{{ route('frontend.booking.cart.add') }}" class="booking-add-form">
                        @csrf
                        <input type="hidden" name="type" value="transport">
                        <input type="hidden" name="transport_id" value="{{ $transport['id'] }}">
                        <input type="hidden" name="title" value="{{ $transport['title'] }}">
                        <input type="hidden" name="image" value="{{ $detailImage }}">
                        <input type="hidden" id="transport-price-per-passenger" name="price_per_passenger" value="{{ number_format((float) $selectedDefaultPrice, 2, '.', '') }}">
                        <input type="hidden" id="transport-return-price" name="return_price" value="{{ number_format((float) ($selectedReturnPrice ?? 0), 2, '.', '') }}">
                        <input type="hidden" name="currency" value="USD">
                        <input type="hidden" name="pickup_date" value="{{ $booking['pickup_date'] }}">
                        <input type="hidden" name="return_date" value="{{ $booking['return_date'] ?? '' }}">
                        <input type="hidden" name="pickup_time" id="booking-pickup-time" value="{{ $booking['pickup_time'] ?? '' }}">
                        <input type="hidden" name="return_time" id="booking-return-time" value="{{ $booking['return_time'] ?? '' }}">
                        <input type="hidden" name="passengers" value="{{ $booking['passengers'] }}">
                        <input type="hidden" id="transport-route-id" name="route_id" value="{{ $routeId }}">
                        <input type="hidden" id="transport-route-from" name="route_from" value="{{ $selectedRouteFrom }}">
                        <input type="hidden" id="transport-route-to" name="route_to" value="{{ $selectedRouteTo }}">
                        <input type="hidden" name="source" value="detail">

                        <button type="submit" class="btn-secondary booking-btn">{{ __('transport.form.book_now') }}</button>
                    </form>

                    <div class="booking-summary-line" id="transport-price-summary">
                        <span>{{ __('transport.price') }}: USD {{ number_format((float) $selectedDefaultPrice, 2) }}{{ !empty($booking['return_date']) && $selectedReturnPrice ? ' + ' . __('transport.return_price') . ' USD ' . number_format((float) $selectedReturnPrice, 2) : '' }}</span>
                    </div>

                    @if($operator)
                        <div class="booking-quick-links">
                            <p><strong>{{ __('transport.operator') }}:</strong> {{ $operator['name'] }}</p>
                            @if(!empty($operator['email']))
                                <a href="mailto:{{ $operator['email'] }}">{{ __('transport.contact_operator') }}</a>
                            @endif
                        </div>
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

            <div class="detail-section-card" id="routes-pricing">
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
            </div>

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
            <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fromSelect = document.getElementById('transport-place-from');
            const toSelect = document.getElementById('transport-place-to');
            const routeIdInput = document.getElementById('transport-route-id');
            const routeFromInput = document.getElementById('transport-route-from');
            const routeToInput = document.getElementById('transport-route-to');
            const pricePerPassengerInput = document.getElementById('transport-price-per-passenger');
            const returnPriceInput = document.getElementById('transport-return-price');
            const routePriceSummary = document.getElementById('transport-price-summary');

            const routes = @json($routes);
            const rawPlaceRegionMap = @json($transport['place_region_map'] ?? []);
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

            function updateRouteFields() {
                if (!fromSelect || !toSelect) {
                    return;
                }

                routeFromInput.value = fromSelect.value || '';
                routeToInput.value = toSelect.value || '';

                const selectedRoute = findRouteBySelection(fromSelect.value, toSelect.value);
                if (selectedRoute) {
                    routeIdInput.value = selectedRoute.route_id || '';
                    pricePerPassengerInput.value = (parseFloat(selectedRoute.pricing?.default_price || 0) || 0).toFixed(2);

                    // Only set return price if a return date was included in the booking context
                    if (booking && booking.return_date) {
                        returnPriceInput.value = (parseFloat(selectedRoute.pricing?.return_price || 0) || 0).toFixed(2);
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
                updateRouteFields();
                // ensure hidden booking time fields reflect top form values
                const topPickupTime = document.querySelector('input[name="pickup_time"]');
                const topReturnTime = document.querySelector('input[name="return_time"]');
                const hiddenPickup = document.getElementById('booking-pickup-time');
                const hiddenReturn = document.getElementById('booking-return-time');
                if (topPickupTime && hiddenPickup) {
                    topPickupTime.addEventListener('change', () => hiddenPickup.value = topPickupTime.value);
                    // initialize
                    hiddenPickup.value = topPickupTime.value || hiddenPickup.value || '';
                }
                if (topReturnTime && hiddenReturn) {
                    topReturnTime.addEventListener('change', () => hiddenReturn.value = topReturnTime.value);
                    hiddenReturn.value = topReturnTime.value || hiddenReturn.value || '';
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
