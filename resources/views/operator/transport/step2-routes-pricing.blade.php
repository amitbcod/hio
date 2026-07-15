@extends('layouts.app')

@section('title', 'Transport Step 2 | Operator Dashboard')

@section('content')
<div class="container-fluid" style="padding-top: 93px;">
    <div class="row">
        <div class="col-md-3 net-section">
            @php $currentStep = 2; @endphp
            @include('operator.transport._steps_sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px; margin-top:40px">
                <h2 style="font-weight:700;margin:0;">Step 2: Routes & Pricing</h2>
                <p style="margin:8px 0 0 0;color:#666;">Define routes, fare options, and pricing by vehicle type.</p>
            </div>

            <form id="step2-routes-pricing-form" method="POST" action="{{ route('operator.transport.step2.save', $transport->id) }}">
                @csrf
                <div id="routes-container">
                    @php
                        // Fixed set of region pairs we require pricing for
                        $pairs = [
                            ['route_from' => 'Airport', 'route_to' => 'North'],
                            ['route_from' => 'Airport', 'route_to' => 'South'],
                            ['route_from' => 'North', 'route_to' => 'South'],
                            ['route_from' => 'South', 'route_to' => 'North'],
                            ['route_from' => 'North', 'route_to' => 'North'],
                            ['route_from' => 'South', 'route_to' => 'South'],
                        ];

                        $existing = collect($routes)->mapWithKeys(function ($route) {
                            $from = $route->route_from ?? $route->pickup_value;
                            $to = $route->route_to ?? $route->dropoff_value;
                            return [$from . '-' . $to => $route];
                        });

                        // Prefer old input on validation error
                        if (old('routes')) {
                            $savedRoutes = old('routes');
                        } else {
                            $savedRoutes = [];
                            foreach ($pairs as $p) {
                                $key = $p['route_from'] . '-' . $p['route_to'];
                                $route = $existing[$key] ?? null;
                                $savedRoutes[] = [
                                    'route_id' => $route->route_id ?? '',
                                    'route_from' => $p['route_from'],
                                    'route_to' => $p['route_to'],
                                    'route_type' => $route->route_type ?? 'Route',
                                    'pickup_type' => $route->pickup_type ?? 'Location zone',
                                    'pickup_value' => $route->pickup_value ?? $p['route_from'],
                                    'dropoff_type' => $route->dropoff_type ?? 'Location zone',
                                    'dropoff_value' => $route->dropoff_value ?? $p['route_to'],
                                    'duration_estimate' => $route->duration_estimate ?? null,
                                    'pricing' => $route->pricing ?? [],
                                ];
                            }
                        }
                    @endphp

                    @foreach($savedRoutes as $index => $route)
                        <div class="route-card" style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:16px;margin-bottom:16px;">
                            <h5 style="margin:0 0 8px 0;">{{ $route['route_from'] }} → {{ $route['route_to'] }}</h5>

                            <input type="hidden" name="routes[{{ $index }}][route_id]" value="{{ $route['route_id'] ?? '' }}">
                            <input type="hidden" name="routes[{{ $index }}][route_from]" value="{{ $route['route_from'] }}">
                            <input type="hidden" name="routes[{{ $index }}][route_to]" value="{{ $route['route_to'] }}">
                            <input type="hidden" name="routes[{{ $index }}][route_type]" value="{{ $route['route_type'] ?? 'Route' }}">
                            <input type="hidden" name="routes[{{ $index }}][pickup_type]" value="{{ $route['pickup_type'] ?? 'Location zone' }}">
                            <input type="hidden" name="routes[{{ $index }}][pickup_value]" value="{{ $route['pickup_value'] ?? $route['route_from'] }}">
                            <input type="hidden" name="routes[{{ $index }}][dropoff_type]" value="{{ $route['dropoff_type'] ?? 'Location zone' }}">
                            <input type="hidden" name="routes[{{ $index }}][dropoff_value]" value="{{ $route['dropoff_value'] ?? $route['route_to'] }}">

                            <div style="background:#f8f9fa;border-radius:10px;padding:16px;margin-top:8px;">
                                <h6 style="margin-bottom:12px;">Pricing ({{ $vehicleTypes[$transport->vehicle_type] ?? $transport->vehicle_type }})</h6>

                                @php
                                    $pricing = $route['pricing'] ?? [];
                                @endphp

                                <div class="row mb-3">
                                    <div class="col-md-4"><label class="form-label">Single trip price (per vehicle)</label></div>
                                    <div class="col-md-8"><input type="number" name="routes[{{ $index }}][pricing][default_price]" class="form-control" value="{{ $pricing['default_price'] ?? '' }}" min="0" step="0.01" required></div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4"><label class="form-label">Return trip price (per vehicle)</label></div>
                                    <div class="col-md-8"><input type="number" name="routes[{{ $index }}][pricing][return_price]" class="form-control" value="{{ $pricing['return_price'] ?? '' }}" min="0" step="0.01"></div>
                                </div>

                                <input type="hidden" name="routes[{{ $index }}][pricing][vehicle_type]" value="{{ $transport->vehicle_type }}">
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save Step 2</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let routeIndex = {{ count($savedRoutes) }};
    const vehicleTypeLabel = @json($vehicleTypes[$transport->vehicle_type] ?? $transport->vehicle_type);
    const vehicleTypeValue = @json($transport->vehicle_type);
    const seasonalCounts = {};

    function clearRouteErrors(routeIndex) {
        const errorContainer = document.getElementById(`route-errors-${routeIndex}`);
        if (errorContainer) {
            errorContainer.textContent = '';
        }
    }

    function showRouteError(routeIndex, message) {
        const errorContainer = document.getElementById(`route-errors-${routeIndex}`);
        if (errorContainer) {
            errorContainer.textContent = message;
        }
    }

    function validateSeasonRanges(routeIndex) {
        const container = document.querySelector(`.seasonal-list[data-index="${routeIndex}"]`);
        if (!container) {
            return true;
        }

        const seasonRows = Array.from(container.querySelectorAll('.season-row'));
        const ranges = [];
        let hasValue = false;

        for (const row of seasonRows) {
            const startInput = row.querySelector(`input[name^="routes[${routeIndex}][pricing][seasonal]"][name$="[start]"]`);
            const endInput = row.querySelector(`input[name^="routes[${routeIndex}][pricing][seasonal]"][name$="[end]"]`);
            const priceInput = row.querySelector(`input[name^="routes[${routeIndex}][pricing][seasonal]"][name$="[price]"]`);
            if (!startInput || !endInput || !priceInput) {
                continue;
            }

            const startValue = startInput.value;
            const endValue = endInput.value;
            const priceValue = priceInput.value;

            if (!startValue && !endValue && !priceValue) {
                continue;
            }

            hasValue = true;

            if (!startValue || !endValue) {
                showRouteError(routeIndex, 'Seasonal prices require both start and end dates.');
                return false;
            }

            const startDate = new Date(startValue);
            const endDate = new Date(endValue);

            if (isNaN(startDate) || isNaN(endDate)) {
                showRouteError(routeIndex, 'Seasonal dates must be valid.');
                return false;
            }

            if (startDate > endDate) {
                showRouteError(routeIndex, 'Seasonal end date must be on or after the start date.');
                return false;
            }

            if (priceValue === '' || Number(priceValue) < 0) {
                showRouteError(routeIndex, 'Seasonal price must be a non-negative number.');
                return false;
            }

            for (const range of ranges) {
                if (!(endDate < range.start || startDate > range.end)) {
                    showRouteError(routeIndex, 'Seasonal date ranges must not overlap.');
                    return false;
                }
            }

            ranges.push({start: startDate, end: endDate});
        }

        const defaultPriceInput = document.querySelector(`input[name="routes[${routeIndex}][pricing][default_price]"]`);
        const defaultPrice = defaultPriceInput ? defaultPriceInput.value : '';

        if (!hasValue && defaultPrice.trim() === '') {
            showRouteError(routeIndex, 'Either default price or seasonal pricing is required.');
            return false;
        }

        return true;
    }

    function addSeason(routeIndex) {
        const container = document.querySelector(`.seasonal-list[data-index="${routeIndex}"]`);
        if (!container) {
            return;
        }

        if (typeof seasonalCounts[routeIndex] === 'undefined') {
            seasonalCounts[routeIndex] = container.querySelectorAll('.season-row').length;
        }

        const seasonCount = seasonalCounts[routeIndex];
        const seasonRow = document.createElement('div');
        seasonRow.className = 'season-row mb-2';
        seasonRow.dataset.seasonIndex = seasonCount;

        seasonRow.innerHTML = `
            <div class="row gx-2">
                <div class="col-md-3"><input type="date" name="routes[${routeIndex}][pricing][seasonal][${seasonCount}][start]" class="form-control"></div>
                <div class="col-md-3"><input type="date" name="routes[${routeIndex}][pricing][seasonal][${seasonCount}][end]" class="form-control"></div>
                <div class="col-md-3"><input type="number" name="routes[${routeIndex}][pricing][seasonal][${seasonCount}][price]" class="form-control" placeholder="Seasonal price" min="0" step="0.01"></div>
                <div class="col-md-3 d-flex align-items-center"><button type="button" class="btn btn-sm btn-danger w-100" onclick="this.closest('.season-row').remove();">Remove</button></div>
            </div>
        `;

        container.appendChild(seasonRow);
        seasonalCounts[routeIndex]++;
        clearRouteErrors(routeIndex);
    }

    function validateAllRoutes(event) {
        let valid = true;
        const seasonalContainers = document.querySelectorAll('.seasonal-list');

        seasonalContainers.forEach((container) => {
            const routeIndex = container.dataset.index;
            clearRouteErrors(routeIndex);
            const routeValid = validateSeasonRanges(routeIndex);
            if (!routeValid) {
                valid = false;
            }
        });

        if (!valid) {
            event.preventDefault();
            const firstError = document.querySelector('.route-errors:not(:empty)');
            if (firstError) {
                firstError.scrollIntoView({behavior: 'smooth', block: 'center'});
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('step2-routes-pricing-form');
        if (form) {
            form.addEventListener('submit', validateAllRoutes);
        }
    });

    function addRoute() {
        const container = document.getElementById('routes-container');
        const routeCard = document.createElement('div');
        routeCard.className = 'route-card';
        routeCard.style = 'background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:16px;margin-bottom:16px;';

        routeCard.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 style="margin:0;">Route ${routeIndex + 1}</h5>
                <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.route-card').remove();">Remove</button>
            </div>
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Route Type</label>
                    <select name="routes[${routeIndex}][route_type]" class="form-control" required>
                        <option value="Airport">Airport</option>
                        <option value="Route">Route</option>
                        <option value="Hourly">Hourly</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Route From</label>
                    <select name="routes[${routeIndex}][route_from]" class="form-control" required>
                        <option value="Airport">Airport</option>
                        <option value="North">North</option>
                        <option value="South">South</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Route To</label>
                    <select name="routes[${routeIndex}][route_to]" class="form-control" required>
                        <option value="Airport">Airport</option>
                        <option value="North">North</option>
                        <option value="South">South</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pickup Type</label>
                    <select name="routes[${routeIndex}][pickup_type]" class="form-control" required>
                        <option value="Airport">Airport</option>
                        <option value="Address">Address</option>
                        <option value="Hotel">Hotel</option>
                        <option value="Location zone">Location zone</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pickup Value</label>
                    <input type="text" name="routes[${routeIndex}][pickup_value]" class="form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Drop-off Type</label>
                    <select name="routes[${routeIndex}][dropoff_type]" class="form-control" required>
                        <option value="Airport">Airport</option>
                        <option value="Address">Address</option>
                        <option value="Hotel">Hotel</option>
                        <option value="Location zone">Location zone</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Drop-off Value</label>
                    <input type="text" name="routes[${routeIndex}][dropoff_value]" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Duration Estimate (minutes)</label>
                <input type="number" name="routes[${routeIndex}][duration_estimate]" class="form-control">
            </div>
            <input type="hidden" name="routes[${routeIndex}][route_id]" value="">
            <div style="background:#f8f9fa;border-radius:10px;padding:16px;">
                <h6 style="margin-bottom:16px;">Pricing for this Vehicle (${vehicleTypeLabel})</h6>
                <div class="row mb-3">
                    <div class="col-md-5">
                        <label class="form-label" style="font-weight:600;">Default Price</label>
                    </div>
                    <div class="col-md-7">
                        <input type="number" name="routes[${routeIndex}][pricing][default_price]" class="form-control" placeholder="Default price (required if no seasonal rates)" min="0" step="0.01">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-5">
                        <label class="form-label" style="font-weight:600;">Return Price</label>
                    </div>
                    <div class="col-md-7">
                        <input type="number" name="routes[${routeIndex}][pricing][return_price]" class="form-control" placeholder="Return price (optional)" min="0" step="0.01">
                    </div>
                </div>
                <div class="seasonal-list" data-index="${routeIndex}">
                    <h6 style="margin-top:12px;">Seasonal Prices</h6>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="addSeason(${routeIndex});">Add Seasonal Price</button>
                </div>
                <div class="route-errors text-danger mt-2" id="route-errors-${routeIndex}"></div>
                <input type="hidden" name="routes[${routeIndex}][pricing][vehicle_type]" value="${vehicleTypeValue}">
            </div>
        `;

        container.appendChild(routeCard);
        routeIndex++;
    }
</script>
@endpush
