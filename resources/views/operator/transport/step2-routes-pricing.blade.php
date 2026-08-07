@extends('layouts.app')

@section('title', 'Transport Step 2 | Operator Dashboard')

@section('content')
<div class="container-fluid" style="padding-top: 93px;">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @php $currentStep = 2; @endphp
            @include('operator.transport._steps_sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px; margin-top:40px; display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <div>
                    <h2 style="font-weight:700;margin:0;">Step 2: Routes & Pricing</h2>
                    <p style="margin:8px 0 0 0;color:#666;">Define routes, fare options, and pricing by vehicle type.</p>
                </div>
                <a href="{{ route('operator.transport.step2.car_rental.show', $transport->id) }}" class="btn btn-primary" style="background:#19b5b5;color:#fff;border:none;">Set Vehicle rental price</a>
            </div>

            <form id="step2-routes-pricing-form" method="POST" action="{{ route('operator.transport.step2.save', $transport->id) }}">
                @csrf
                <input type="hidden" name="save_service" id="save_service" value="">
                <div class="mb-4">
                    <ul class="nav nav-tabs" id="service-tabs" role="tablist">
                        @foreach($serviceGroups as $serviceKey => $serviceGroup)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $serviceKey }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $serviceKey }}-pane" type="button" role="tab" aria-controls="{{ $serviceKey }}-pane" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $serviceGroup['label'] }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="tab-content" id="service-tabs-content">
                    @foreach($serviceGroups as $serviceKey => $serviceGroup)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $serviceKey }}-pane" role="tabpanel" aria-labelledby="{{ $serviceKey }}-tab">
                            <div class="alert alert-light border mb-3">
                                <strong>{{ $serviceGroup['label'] }}</strong> pricing uses the region pairs configured for this service.
                            </div>
                            <div id="routes-container-{{ $serviceKey }}">
                                @php $serviceRoutes = $serviceGroup['routes']; @endphp
                                @foreach($serviceRoutes as $index => $route)
                                    @php $routeIndexValue = $loop->parent->index * 1000 + $index; @endphp
                                    <div class="route-card" data-service="{{ $serviceKey }}" style="background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:16px;margin-bottom:16px;">
                                        <h5 style="margin:0 0 8px 0;">{{ $route['route_from'] }} → {{ $route['route_to'] }} / {{ $route['route_to'] }} → {{ $route['route_from'] }}</h5>

                                        <input type="hidden" name="routes[{{ $routeIndexValue }}][route_id]" value="{{ $route['route_id'] ?? '' }}">
                                        <input type="hidden" name="routes[{{ $routeIndexValue }}][service_type]" value="{{ $serviceKey }}">
                                        <input type="hidden" name="routes[{{ $routeIndexValue }}][route_from]" value="{{ $route['route_from'] }}">
                                        <input type="hidden" name="routes[{{ $routeIndexValue }}][route_to]" value="{{ $route['route_to'] }}">
                                        <input type="hidden" name="routes[{{ $routeIndexValue }}][route_type]" value="{{ $route['route_type'] ?? 'Route' }}">
                                        <input type="hidden" name="routes[{{ $routeIndexValue }}][pickup_type]" value="{{ $route['pickup_type'] ?? 'Location zone' }}">
                                        <input type="hidden" name="routes[{{ $routeIndexValue }}][pickup_value]" value="{{ $route['pickup_value'] ?? $route['route_from'] }}">
                                        <input type="hidden" name="routes[{{ $routeIndexValue }}][dropoff_type]" value="{{ $route['dropoff_type'] ?? 'Location zone' }}">
                                        <input type="hidden" name="routes[{{ $routeIndexValue }}][dropoff_value]" value="{{ $route['dropoff_value'] ?? $route['route_to'] }}">

                                        <div style="background:#f8f9fa;border-radius:10px;padding:16px;margin-top:8px;">
                                            <h6 style="margin-bottom:12px;">Pricing ({{ $vehicleTypes[$transport->vehicle_type] ?? $transport->vehicle_type }})</h6>

                                            @php
                                                $pricing = $route['pricing'] ?? [];
                                            @endphp

                                            <div class="row mb-3">
                                                <div class="col-md-4"><label class="form-label">Single trip price (per vehicle)</label></div>
                                                <div class="col-md-8"><input type="number" name="routes[{{ $routeIndexValue }}][pricing][default_price]" class="form-control" value="{{ $pricing['default_price'] ?? '' }}" min="0" step="0.01"></div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-md-4"><label class="form-label">Return trip price (per vehicle)</label></div>
                                                <div class="col-md-8"><input type="number" name="routes[{{ $routeIndexValue }}][pricing][return_price]" class="form-control" value="{{ $pricing['return_price'] ?? '' }}" min="0" step="0.01"></div>
                                            </div>

                                            <div class="mt-3">
                                                <label class="form-label">Seasonal Prices</label>
                                                <p class="text-muted small mb-2">If a seasonal date range matches the booking date, that price will be used. Otherwise the default single trip price is applied.</p>
                                                <div class="seasonal-list" data-index="{{ $routeIndexValue }}">
                                                    @php $seasonalEntries = $pricing['seasonal'] ?? []; @endphp
                                                    @foreach($seasonalEntries as $seasonIndex => $season)
                                                        <div class="season-row mb-2">
                                                            <div class="row gx-2">
                                                                <div class="col-md-2"><input type="date" name="routes[{{ $routeIndexValue }}][pricing][seasonal][{{ $seasonIndex }}][start]" class="form-control" value="{{ $season['start'] ?? $season['start_date'] ?? '' }}"></div>
                                                                <div class="col-md-2"><input type="date" name="routes[{{ $routeIndexValue }}][pricing][seasonal][{{ $seasonIndex }}][end]" class="form-control" value="{{ $season['end'] ?? $season['end_date'] ?? '' }}"></div>
                                                                <div class="col-md-2"><input type="number" name="routes[{{ $routeIndexValue }}][pricing][seasonal][{{ $seasonIndex }}][price]" class="form-control" placeholder="Single trip" min="0" step="0.01" value="{{ $season['price'] ?? '' }}"></div>
                                                                <div class="col-md-2"><input type="number" name="routes[{{ $routeIndexValue }}][pricing][seasonal][{{ $seasonIndex }}][return_price]" class="form-control" placeholder="Return trip" min="0" step="0.01" value="{{ $season['return_price'] ?? '' }}"></div>
                                                                <div class="col-md-2 d-flex align-items-center"><button type="button" class="btn btn-sm btn-danger w-100" onclick="this.closest('.season-row').remove();">Remove</button></div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-secondary" onclick="addSeason({{ $routeIndexValue }});">Add Seasonal Price</button>
                                                </div>
                                                <div class="route-errors text-danger mt-2" id="route-errors-{{ $routeIndexValue }}"></div>
                                            </div>

                                            <input type="hidden" name="routes[{{ $routeIndexValue }}][pricing][vehicle_type]" value="{{ $transport->vehicle_type }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <button type="submit" value="{{ $serviceKey }}" class="btn btn-primary save-service-btn" data-service="{{ $serviceKey }}" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save {{ $serviceGroup['label'] }}</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
                <div class="col-md-2"><input type="date" name="routes[${routeIndex}][pricing][seasonal][${seasonCount}][start]" class="form-control"></div>
                <div class="col-md-2"><input type="date" name="routes[${routeIndex}][pricing][seasonal][${seasonCount}][end]" class="form-control"></div>
                <div class="col-md-2"><input type="number" name="routes[${routeIndex}][pricing][seasonal][${seasonCount}][price]" class="form-control" placeholder="Single trip" min="0" step="0.01"></div>
                <div class="col-md-2"><input type="number" name="routes[${routeIndex}][pricing][seasonal][${seasonCount}][return_price]" class="form-control" placeholder="Return trip" min="0" step="0.01"></div>
                <div class="col-md-2 d-flex align-items-center"><button type="button" class="btn btn-sm btn-danger w-100" onclick="this.closest('.season-row').remove();">Remove</button></div>
            </div>
        `;

        container.appendChild(seasonRow);
        seasonalCounts[routeIndex]++;
        clearRouteErrors(routeIndex);
    }

    function validateAllRoutes(event) {
        const form = event.target;
        const saveServiceField = document.getElementById('save_service');
        let saveService = saveServiceField ? saveServiceField.value : '';
        if (!saveService && event.submitter) {
            saveService = event.submitter.value || '';
        }
        let valid = true;

        const seasonalContainers = Array.from(document.querySelectorAll('.seasonal-list'))
            .filter((container) => {
                if (!saveService) {
                    return true;
                }
                const routeCard = container.closest('.route-card');
                return routeCard && routeCard.dataset.service === saveService;
            });

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

        document.querySelectorAll('.save-service-btn').forEach((button) => {
            button.addEventListener('click', function () {
                const saveServiceField = document.getElementById('save_service');
                if (saveServiceField) {
                    saveServiceField.value = this.value;
                }
            });
        });
    });

</script>
@endpush
