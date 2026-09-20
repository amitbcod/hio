@extends('layouts.admin')

@php $sidebar = 'admin.groups._steps_sidebar'; $currentStep = 4; @endphp

@section('content')
<div class="container mt-4 mb-5">
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px; overflow:hidden;">
        <div class="card-body p-4">
            <h2 class="mb-1 fw-bold" style="font-size:2rem; color:#1f2a37;">Step 4: Pricing & Rate Plan</h2>
            <p class="mb-0 text-muted">Set the rate for each service, then configure the package rate or discount offer.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.groups.step4.store', $group->id) }}">
        @csrf
        @php
            $itinerary = $group->itinerary ?? [];
            $pricingModes = $itinerary['pricing_modes'] ?? [
                'accommodation' => 'package_rate',
                'activity' => 'discount_offer',
                'transport' => 'discount_offer',
            ];
            $discounts = $itinerary['discounts'] ?? [
                'accommodation' => 20,
                'activity' => 10,
                'transport' => 5,
            ];
        @endphp

        <div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden;">
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 h-100" style="background:#eafaf7; border-color:#cfeee7 !important; min-height:126px;">
                            <div class="text-uppercase small fw-bold text-secondary mb-2">Accommodation Package Mode</div>
                            <select name="pricing_modes[accommodation]" class="form-select form-select-sm pricing-mode-select" data-service="accommodation" style="background:#fff; border:1px solid #dfe7ea; min-height:40px;">
                                <option value="discount_offer" {{ ($pricingModes['accommodation'] ?? 'package_rate') === 'discount_offer' ? 'selected' : '' }}>Discount Offer</option>
                                <option value="package_rate" {{ ($pricingModes['accommodation'] ?? 'package_rate') === 'package_rate' ? 'selected' : '' }}>Group Rate</option>
                            </select>
                            <div class="discount-field mt-3 d-flex justify-content-between align-items-center" style="display: {{ (($pricingModes['accommodation'] ?? 'package_rate') === 'discount_offer') ? 'flex' : 'none' }};">
                                <input type="number" name="discounts[accommodation]" class="form-control form-control-sm discount-percent" data-service="accommodation" min="0" max="100" step="1" value="{{ $discounts['accommodation'] ?? 20 }}" style="max-width:120px;">
                                <span class="small text-muted ms-2">% Off</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 h-100" style="background:#f5edf9; border-color:#e8d8f4 !important; min-height:126px;">
                            <div class="text-uppercase small fw-bold text-secondary mb-2">Activity Package Mode</div>
                            <select name="pricing_modes[activity]" class="form-select form-select-sm pricing-mode-select" data-service="activity" style="background:#fff; border:1px solid #dfe7ea; min-height:40px;">
                                <option value="discount_offer" {{ ($pricingModes['activity'] ?? 'discount_offer') === 'discount_offer' ? 'selected' : '' }}>Discount Offer</option>
                                <option value="package_rate" {{ ($pricingModes['activity'] ?? 'discount_offer') === 'package_rate' ? 'selected' : '' }}>Group Rate</option>
                            </select>
                            <div class="discount-field mt-3 d-flex justify-content-between align-items-center" style="display: {{ (($pricingModes['activity'] ?? 'discount_offer') === 'discount_offer') ? 'flex' : 'none' }};">
                                <input type="number" name="discounts[activity]" class="form-control form-control-sm discount-percent" data-service="activity" min="0" max="100" step="1" value="{{ $discounts['activity'] ?? 10 }}" style="max-width:120px;">
                                <span class="small text-muted ms-2">% Off</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 h-100" style="background:#eaf3fb; border-color:#d7eaf9 !important; min-height:126px;">
                            <div class="text-uppercase small fw-bold text-secondary mb-2">Transport Package Mode</div>
                            <select name="pricing_modes[transport]" class="form-select form-select-sm pricing-mode-select" data-service="transport" style="background:#fff; border:1px solid #dfe7ea; min-height:40px;">
                                <option value="discount_offer" {{ ($pricingModes['transport'] ?? 'discount_offer') === 'discount_offer' ? 'selected' : '' }}>Discount Offer</option>
                                <option value="package_rate" {{ ($pricingModes['transport'] ?? 'discount_offer') === 'package_rate' ? 'selected' : '' }}>Group Rate</option>
                            </select>
                            <div class="discount-field mt-3 d-flex justify-content-between align-items-center" style="display: {{ (($pricingModes['transport'] ?? 'discount_offer') === 'discount_offer') ? 'flex' : 'none' }};">
                                <input type="number" name="discounts[transport]" class="form-control form-control-sm discount-percent" data-service="transport" min="0" max="100" step="1" value="{{ $discounts['transport'] ?? 5 }}" style="max-width:120px;">
                                <span class="small text-muted ms-2">% Off</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3 row align-items-center text-uppercase small fw-semibold text-secondary px-2 pb-2" style="letter-spacing:0.02em; border-bottom:1px solid #edf2f6;">
                    <div class="col-md-6">Service</div>
                    <div class="col-md-3">Rate</div>
                    <div class="col-md-3 text-end">Final Price</div>
                </div>

                @foreach($dates as $dayIndex => $date)
                    @php
                        $dayItinerary = $itinerary[$dayIndex] ?? [];
                        $selectedAccommodationId = $dayItinerary['accommodation'] ?? null;
                        $selectedRooms = $dayItinerary['rooms'] ?? [];
                        $pricingForDay = $pricingByDay[$dayIndex] ?? [];
                        $savedPricing = $dayItinerary['pricing'] ?? [];
                    @endphp

                    @if(!$selectedAccommodationId || empty($selectedRooms))
                        <div class="p-3 text-muted small">No accommodation or room selection has been made for this day yet.</div>
                    @elseif(empty($pricingForDay))
                        <div class="p-3 text-muted small">No room and plan pricing is available for the selected accommodation.</div>
                    @else
                        <div class="mb-3 fw-bold text-dark" style="font-size:1.1rem;">
                            <strong>Day {{ $dayIndex + 1 }}</strong>
                        </div>

                        @foreach($pricingForDay as $roomEntry)
                            @php
                                $room = $roomEntry['room'];
                                $roomId = $room->id;
                                $roomPricing = $savedPricing[$roomId] ?? [];
                                $roomMode = $roomPricing['mode'] ?? 'discount_offer';
                                $discountPercent = $roomPricing['discount_percent'] ?? '20';
                                $selectedPackage = $roomPricing['selected_package'] ?? '';
                            @endphp

                            <div class="py-2">
                                <div class="mb-3 fw-bold text-dark" style="font-size:1.02rem;">
                                    <strong>{{ $room->room_name ?? ('Room #' . $room->id) }} · {{ $room->room_type ?? 'Room' }}</strong>
                                </div>

                                @foreach($roomEntry['plans'] as $planEntry)
                                    @php
                                        $plan = $planEntry['plan'];
                                        $planName = $plan->rate_name ?? 'Unknown Plan';
                                        $planId = $plan->id;
                                        $defaultPrice = $planEntry['default_pricing'];
                                        $groupPrice = $planEntry['group_pricing'] ?? null;
                                        $packagePrice = $planEntry['package_pricing'];
                                        $seasonalPricing = $planEntry['seasonal_pricing'];
                                        $preferredBasePrice = $groupPrice ? (float) ($groupPrice->base_rate ?? 0) : ((float) ($defaultPrice->base_rate ?? 0));
                                        $packageOptions = [];
                                        if ($groupPrice) {
                                            $packageOptions[] = [
                                                'value' => $groupPrice->id,
                                                'label' => 'Group Rate (' . number_format((float) ($groupPrice->base_rate ?? 0), 2) . ')',
                                                'price' => (float) ($groupPrice->base_rate ?? 0),
                                            ];
                                        }
                                        if ($packagePrice) {
                                            $packageOptions[] = [
                                                'value' => $packagePrice->id,
                                                'label' => 'Package Rate (' . number_format((float) ($packagePrice->base_rate ?? 0), 2) . ')',
                                                'price' => (float) ($packagePrice->base_rate ?? 0),
                                            ];
                                        }
                                        foreach ($seasonalPricing as $season) {
                                            if ($season->is_default || $season->rate_type === 'Package') continue;
                                            if ($season->base_rate !== null && $season->base_rate !== '' && $season->rate_name === $planName) {
                                                $packageOptions[] = [
                                                    'value' => $season->id,
                                                    'label' => 'Seasonal Rate (' . \Carbon\Carbon::parse($season->valid_from)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($season->valid_to)->format('d M Y') . '): ' . number_format((float) $season->base_rate, 2),
                                                    'price' => (float) $season->base_rate,
                                                ];
                                            }
                                        }
                                    @endphp

                                    @php $rows = []; @endphp
                                    @if($defaultPrice)
                                        @php $rows[] = ['label' => 'Flat Rate', 'base' => (float) $defaultPrice->base_rate, 'packageOptions' => $packageOptions]; @endphp
                                    @endif
                                    @foreach($seasonalPricing as $season)
                                        @if($season->base_rate !== null && $season->base_rate !== '' && $season->rate_name === $planName)
                                            @php $rows[] = ['label' => \Carbon\Carbon::parse($season->valid_from)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($season->valid_to)->format('d M Y') . ' — Season Rate', 'base' => (float) $season->base_rate, 'packageOptions' => $packageOptions]; @endphp
                                        @endif
                                    @endforeach

                                    @foreach($rows as $rowIndex => $rowItem)
                                        @php
                                            $rowKey = $dayIndex . '_' . $roomId . '_' . $planId . '_' . $rowIndex;
                                            $rowPackageOptions = $rowItem['packageOptions'];
                                            $rowPackageValue = $selectedPackage ?: (count($rowPackageOptions) ? $rowPackageOptions[0]['value'] : '');
                                        @endphp

                                        <div class="pricing-row row align-items-center py-3" data-service="accommodation" data-row-key="{{ $rowKey }}" data-base-price="{{ $rowItem['base'] }}" data-package-exists="{{ !empty($rowPackageOptions) ? 1 : 0 }}" data-package-price="{{ $rowPackageOptions[0]['price'] ?? 0 }}" style="border-top:1px solid #edf2f6;">
                                            <div class="col-md-6">
                                                <div class="fw-semibold text-dark" style="font-size:0.96rem;">{{ $rowItem['label'] }}</div>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control form-control-sm base-price" value="{{ number_format($preferredBasePrice ?: $rowItem['base'], 2) }}" data-base="{{ $preferredBasePrice ?: $rowItem['base'] }}" readonly style="border:1px solid #dfe7ea; background:#fff; text-align:left;">
                                            </div>
                                            <div class="col-md-3 text-end">
                                                <div class="final-price fw-bold text-success" style="font-size:1.02rem;">$0</div>
                                            </div>
                                        </div>
                                    @endforeach

                                    @if(!empty($rows))
                                        <div style="padding: 6px 0 12px 0; font-size:0.92rem; color:#0e8f84; font-weight:500; border-bottom: 1px solid #f0f0f0;">
                                            {{ $planName }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    @endif

                    <div class="mt-3">
                        <div class="mb-2 fw-bold text-dark" style="font-size:1.02rem;"><strong>Activity Price Details</strong></div>
                        @php $activitiesForDay = $activityPricingByDay[$dayIndex] ?? []; @endphp

                        @if(empty($activitiesForDay))
                            <div class="p-2 text-muted small">No activities selected for this day.</div>
                        @else
                            @foreach($activitiesForDay as $actEntry)
                                @php $activity = $actEntry['activity']; @endphp
                                <div class="mb-2">
                                    <div class="fw-semibold">{{ $activity->activity_name ?? ('Activity #' . ($activity->id ?? '')) }}</div>
                                    @foreach($actEntry['variants'] as $variantEntry)
                                        @php
                                            $variant = $variantEntry['variant'];
                                            $rates = $variantEntry['rates'];
                                        @endphp

                                        <div class="border rounded-3 p-2 mb-2">
                                            <div class="fw-semibold small mb-1">Variant: {{ $variant->variant_name ?? 'Variant' }}</div>

                                            @if($rates->isEmpty())
                                                <div class="small text-muted">No rates defined for this variant.</div>
                                            @else
                                                @foreach($rates as $rate)
                                                    @php
                                                        $label = $rate->season ?: (\Carbon\Carbon::parse($rate->valid_from)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($rate->valid_to)->format('d M Y'));
                                                        $adult = (float)($rate->adult_rate ?? 0);
                                                        $child = (float)($rate->children_rate ?? 0);
                                                        $infant = (float)($rate->infant_rate ?? 0);
                                                        $equipment = (float)($rate->equipment_rate ?? 0);
                                                        $pkg = null;
                                                        if (!empty($variantEntry['package_map'])) {
                                                            $pkg = $variantEntry['package_map'][$rate->rate_specificity] ?? null;
                                                        }
                                                        $packagePrice = 0;
                                                        if ($pkg) {
                                                            $packagePrice = ($rate->rate_specificity === 'Per Equipment') ? (float)($pkg->equipment_rate ?? 0) : (float)($pkg->adult_rate ?? 0);
                                                        }
                                                    @endphp

                                                    <div class="pricing-row row align-items-center py-2" data-service="activity" data-rate-specificity="{{ $rate->rate_specificity ?? '' }}" data-package-exists="{{ $pkg ? 1 : 0 }}" data-package-adult="{{ $pkg->adult_rate ?? 0 }}" data-package-child="{{ $pkg->children_rate ?? 0 }}" data-package-infant="{{ $pkg->infant_rate ?? 0 }}" data-package-equipment="{{ $pkg->equipment_rate ?? 0 }}" data-package-price="{{ $packagePrice }}" style="border-top:1px solid #edf2f6;">
                                                        <div class="col-md-6">
                                                            <div class="fw-semibold">{{ $label }} · {{ $rate->rate_specificity ?? '' }}</div>
                                                            <div class="small text-muted">Variant: {{ $variant->variant_name }}</div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            @if($rate->rate_specificity === 'Per Equipment')
                                                                <input type="text" class="form-control form-control-sm base-price" value="{{ number_format($equipment,2) }}" data-base="{{ $equipment }}" readonly style="border:1px solid #dfe7ea; background:#fff; text-align:left;">
                                                            @else
                                                                <div class="row gx-1">
                                                                    <div class="col-4 text-center" style="font-size:11px;color:#666;margin-bottom:4px;">Adult</div>
                                                                    <div class="col-4 text-center" style="font-size:11px;color:#666;margin-bottom:4px;">Child</div>
                                                                    <div class="col-4 text-center" style="font-size:11px;color:#666;margin-bottom:4px;">Infant</div>
                                                                </div>
                                                                <div class="row gx-1">
                                                                    <div class="col-4"><input type="text" class="form-control form-control-sm base-price base-adult" value="{{ number_format($adult,2) }}" data-base="{{ $adult }}" readonly style="border:1px solid #dfe7ea; background:#fff; text-align:left;" title="Adult"></div>
                                                                    <div class="col-4"><input type="text" class="form-control form-control-sm base-child" value="{{ number_format($child,2) }}" data-base="{{ $child }}" readonly style="border:1px solid #dfe7ea; background:#fff; text-align:left;" title="Child"></div>
                                                                    <div class="col-4"><input type="text" class="form-control form-control-sm base-infant" value="{{ number_format($infant,2) }}" data-base="{{ $infant }}" readonly style="border:1px solid #dfe7ea; background:#fff; text-align:left;" title="Infant"></div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-3 text-end">
                                                            @if($rate->rate_specificity === 'Per Equipment')
                                                                <div class="final-price fw-bold text-success">$0</div>
                                                            @else
                                                                <div class="final-price-persons">
                                                                    <div class="final-price-adult fw-bold text-success">Adult: $0</div>
                                                                    <div class="final-price-child fw-bold text-success">Child: $0</div>
                                                                    <div class="final-price-infant fw-bold text-success">Infant: $0</div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="mt-3">
                        <div class="mb-2 fw-bold text-dark" style="font-size:1.02rem;"><strong>Transport Price Details</strong></div>
                        @php $transportForDay = $transportPricingByDay[$dayIndex] ?? []; @endphp

                        @if(empty($transportForDay))
                            <div class="p-2 text-muted small">No transport selected for this day.</div>
                        @else
                            @php $transport = $transportForDay['transport']; @endphp
                            <div class="mb-2">
                                <div class="fw-semibold">Transport #{{ $loop->iteration }}</div>
                                @foreach(($transportForDay['routes'] ?? []) as $routeEntry)
                                    @php
                                        $route = $routeEntry['route'];
                                        $pricing = $routeEntry['pricing'] ?? [];
                                        $groupRate = (float) ($pricing['group_price'] ?? 0);
                                        $groupReturnRate = (float) ($pricing['group_return_price'] ?? 0);
                                        $baseRate = $groupRate > 0 ? $groupRate : ((float) ($pricing['default_price'] ?? $pricing['base_rate'] ?? $pricing['price'] ?? 0));
                                        $returnRate = $groupReturnRate > 0 ? $groupReturnRate : ((float) ($pricing['return_price'] ?? $pricing['package_return_price'] ?? 0));
                                        $packageRate = (float) ($pricing['package_price'] ?? $pricing['package_return_price'] ?? $baseRate);
                                        $from = $route->route_from ?? $route->pickup_value ?? 'From';
                                        $to = $route->route_to ?? $route->dropoff_value ?? 'To';

                                        // Determine saved schedule for this route (mirror package logic)
                                        $savedRouteSchedule = [];
                                        $direction = null; // 'fwd', 'rev', 'both', 'single'
                                        if (!empty($itinerary[$dayIndex]['transport_schedule']) && is_array($itinerary[$dayIndex]['transport_schedule'])) {
                                            foreach ($itinerary[$dayIndex]['transport_schedule'] as $svcKey => $svcGroup) {
                                                $foundFwd = null; $foundRev = null; $foundSingle = null;
                                                if (!is_array($svcGroup)) continue;
                                                foreach ($svcGroup as $k => $v) {
                                                    $isSelected = false;
                                                    if (is_array($v)) {
                                                        $isSelected = !empty($v['selected']) || !empty($v['selected_route']);
                                                    } else {
                                                        $isSelected = !empty($v) || $v === '0' || $v === 0;
                                                    }
                                                    if (!$isSelected) continue;

                                                    if ($k === ($route->route_id ?? null) || $k === ($route->id ?? null) || (is_string($k) && $k === (string) ($route->id ?? null))) {
                                                        $foundSingle = $v;
                                                    }
                                                    if (is_string($k) && (str_ends_with($k, '-fwd') || str_ends_with($k, '-rev'))) {
                                                        $base = preg_replace('/-(fwd|rev)$/', '', $k);
                                                        if ((string) $base === (string) ($route->route_id ?? $route->id)) {
                                                            if (str_ends_with($k, '-fwd')) $foundFwd = $v; else $foundRev = $v;
                                                        }
                                                    }
                                                }

                                                if ($foundFwd !== null && $foundRev !== null) {
                                                    $savedRouteSchedule = array_merge(is_array($foundFwd) ? $foundFwd : [], is_array($foundRev) ? $foundRev : []);
                                                    $savedRouteSchedule['add_return'] = true;
                                                    $direction = 'both';
                                                    break;
                                                }

                                                if ($foundSingle !== null) {
                                                    $savedRouteSchedule = is_array($foundSingle) ? $foundSingle : [];
                                                    $direction = 'single';
                                                    break;
                                                }

                                                if ($foundFwd !== null) {
                                                    $savedRouteSchedule = is_array($foundFwd) ? $foundFwd : [];
                                                    $direction = 'fwd';
                                                    break;
                                                }

                                                if ($foundRev !== null) {
                                                    $savedRouteSchedule = is_array($foundRev) ? $foundRev : [];
                                                    $direction = 'rev';
                                                    break;
                                                }
                                            }
                                        }

                                        $addReturn = !empty($savedRouteSchedule['add_return']);
                                        $selected = !empty($savedRouteSchedule) && (
                                            !empty($savedRouteSchedule['selected']) || !empty($savedRouteSchedule['selected_route']) || !empty($savedRouteSchedule['add_return'])
                                        );

                                        $isReverse = ($direction === 'rev');
                                        $displayFrom = $isReverse ? ($route->route_to ?? '') : ($route->route_from ?? '');
                                        $displayTo = $isReverse ? ($route->route_from ?? '') : ($route->route_to ?? '');

                                        if (!$selected) {
                                            // skip if not selected
                                        } 
                                    @endphp

                                    @if(!empty($selected))
                                        <div class="border rounded-3 p-2 mb-2">
                                            <div class="fw-semibold small mb-1">{{ $route->service_type ? ucfirst(str_replace('_', ' ', $route->service_type)) : 'Transport' }} · Route: {{ $displayFrom }} → {{ $displayTo }}</div>
                                            @php $displayBase = $addReturn ? ($returnRate ?? 0) : ($baseRate ?? 0); @endphp
                                            <div class="pricing-row row align-items-center py-2" data-service="transport" data-rate-specificity="Per Equipment" data-group-exists="{{ ($groupRate > 0 || $groupReturnRate > 0) ? 1 : 0 }}" data-group-price="{{ $groupRate }}" data-group-return="{{ $groupReturnRate }}" data-package-exists="{{ ($packageRate > 0 || $returnRate > 0) ? 1 : 0 }}" data-package-price="{{ $packageRate }}" data-package-return="{{ $returnRate }}" data-add-return="{{ $addReturn ? 1 : 0 }}" data-base-price="{{ $displayBase }}" style="border-top:1px solid #edf2f6;">
                                                <div class="col-md-6">
                                                    <div class="fw-semibold">Flat Rate</div>
                                                    <div class="small text-muted">Route: {{ $displayFrom }} → {{ $displayTo }}</div>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text" class="form-control form-control-sm base-price" value="{{ number_format($displayBase, 2) }}" data-base="{{ $displayBase }}" readonly style="border:1px solid #dfe7ea; background:#fff; text-align:left;">
                                                </div>
                                                <div class="col-md-3 text-end">
                                                    <div class="final-price fw-bold text-success">$0</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.groups.step3', $group->id) }}" class="btn btn-light">Back</a>
                    <button type="submit" class="btn btn-primary">Next: Content</button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function formatCurrency(value) {
        const number = Number(value || 0);
        return '$' + number.toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function updatePricingRow(row) {
        const service = row.dataset.service || 'accommodation';
        const modeSelect = document.querySelector('.pricing-mode-select[data-service="' + service + '"]');
        const discountInput = document.querySelector('.discount-percent[data-service="' + service + '"]');

        if (service === 'accommodation' && !row.dataset.rateSpecificity) {
            const baseInput = row.querySelector('.base-price');
            const result = row.querySelector('.final-price');
            if (!baseInput || !result) return;

            const baseValue = Number((baseInput.dataset.base || baseInput.value || 0).toString().replace(/[$,]/g, '')) || 0;
            let finalValue = baseValue;
            const groupExists = row.dataset.groupExists === '1' || false;
            const groupValue = Number(row.dataset.groupPrice || 0) || 0;
            const packageExists = row.dataset.packageExists === '1' || false;
            const packageValue = Number(row.dataset.packagePrice || 0) || 0;

            if (groupExists && groupValue > 0) {
                finalValue = groupValue;
            } else if (modeSelect && modeSelect.value === 'package_rate') {
                if (packageExists && packageValue > 0) {
                    finalValue = packageValue;
                } else {
                    finalValue = baseValue;
                }
            } else if (discountInput) {
                const percent = Number(discountInput.value || 0);
                finalValue = baseValue - (baseValue * percent / 100);
            }

            result.textContent = formatCurrency(finalValue);
            result.style.color = (groupExists && groupValue > 0) ? '#198754' : ((modeSelect && modeSelect.value === 'package_rate' && (!packageExists || packageValue <= 0)) ? '#1e88e5' : '#198754');
            return;
        }

        if (row.dataset.rateSpecificity === 'Per Equipment') {
            const baseInput = row.querySelector('.base-price');
            const result = row.querySelector('.final-price');
            if (!baseInput || !result) return;

            const baseValue = Number((baseInput.dataset.base || baseInput.value || 0).toString().replace(/[$,]/g, '')) || 0;
            let finalValue = baseValue;
            const groupExists = row.dataset.groupExists === '1' || false;
            const groupValue = Number(row.dataset.groupPrice || 0) || 0;
            const groupReturn = Number(row.dataset.groupReturn || 0) || 0;
            const isReturn = row.dataset.addReturn === '1' || false;

            if (modeSelect && modeSelect.value === 'package_rate') {
                const packageExists = row.dataset.packageExists === '1' || false;
                const packageValue = Number(row.dataset.packagePrice || 0) || 0;
                const packageReturn = Number(row.dataset.packageReturn || 0) || 0;

                // For group pages: when package_rate mode is selected, prefer the Group price if it exists
                if (groupExists && groupValue > 0) {
                    finalValue = isReturn && groupReturn > 0 ? groupReturn : groupValue;
                } else if (packageExists) {
                    if (isReturn && packageReturn > 0) {
                        finalValue = packageReturn;
                    } else if (!isReturn && packageValue > 0) {
                        finalValue = packageValue;
                    } else {
                        finalValue = baseValue;
                    }
                } else {
                    finalValue = baseValue;
                }
            } else {
                // discount_offer (or default) — apply percent to the effective base (prefer group prices when present)
                const percent = discountInput ? Number(discountInput.value || 0) : 0;
                const effectiveBase = (groupExists && groupValue > 0) ? (isReturn && groupReturn > 0 ? groupReturn : groupValue) : baseValue;
                finalValue = effectiveBase - (effectiveBase * percent / 100);
            }
            result.textContent = formatCurrency(finalValue);
            result.style.color = (groupExists && groupValue > 0) ? '#198754' : ((modeSelect && modeSelect.value === 'package_rate' && (!row.dataset.packageExists || Number(row.dataset.packagePrice || 0) <= 0)) ? '#1e88e5' : '#198754');
            return;
        }

        const adultInput = row.querySelector('.base-adult');
        const childInput = row.querySelector('.base-child');
        const infantInput = row.querySelector('.base-infant');
        const finalAdult = row.querySelector('.final-price-adult');
        const finalChild = row.querySelector('.final-price-child');
        const finalInfant = row.querySelector('.final-price-infant');

        if (!adultInput || !childInput || !infantInput) return;

        const adultBase = Number((adultInput.dataset.base || adultInput.value || 0).toString().replace(/[$,]/g, '')) || 0;
        const childBase = Number((childInput.dataset.base || childInput.value || 0).toString().replace(/[$,]/g, '')) || 0;
        const infantBase = Number((infantInput.dataset.base || infantInput.value || 0).toString().replace(/[$,]/g, '')) || 0;

        const groupExists = row.dataset.groupExists === '1' || false;
        const groupAdult = Number(row.dataset.groupAdult || 0) || 0;
        const groupChild = Number(row.dataset.groupChild || 0) || 0;
        const groupInfant = Number(row.dataset.groupInfant || 0) || 0;
        const groupValue = Number(row.dataset.groupPrice || 0) || 0;
        const packageExists = row.dataset.packageExists === '1' || false;
        const packageAdult = Number(row.dataset.packageAdult || 0) || 0;
        const packageChild = Number(row.dataset.packageChild || 0) || 0;
        const packageInfant = Number(row.dataset.packageInfant || 0) || 0;
        const packageValue = Number(row.dataset.packagePrice || 0) || 0;

        if (groupExists && groupValue > 0) {
            if (finalAdult) finalAdult.textContent = 'Adult: ' + formatCurrency(groupAdult || groupValue || adultBase);
            if (finalChild) finalChild.textContent = 'Child: ' + formatCurrency(groupChild || groupValue || childBase);
            if (finalInfant) finalInfant.textContent = 'Infant: ' + formatCurrency(groupInfant || groupValue || infantBase);
            return;
        }

        if (modeSelect && modeSelect.value === 'package_rate' && !packageExists) {
            if (finalAdult) finalAdult.textContent = 'Adult: ' + formatCurrency(adultBase);
            if (finalChild) finalChild.textContent = 'Child: ' + formatCurrency(childBase);
            if (finalInfant) finalInfant.textContent = 'Infant: ' + formatCurrency(infantBase);
            return;
        }

        if (modeSelect && modeSelect.value === 'package_rate' && packageExists) {
            if (finalAdult) finalAdult.textContent = 'Adult: ' + formatCurrency(packageAdult || packageValue || adultBase);
            if (finalChild) finalChild.textContent = 'Child: ' + formatCurrency(packageChild || packageValue || childBase);
            if (finalInfant) finalInfant.textContent = 'Infant: ' + formatCurrency(packageInfant || packageValue || infantBase);
            return;
        }

        const percent = discountInput ? Number(discountInput.value || 0) : 0;
        const adultFinal = adultBase - (adultBase * percent / 100);
        const childFinal = childBase - (childBase * percent / 100);
        const infantFinal = infantBase - (infantBase * percent / 100);

        if (finalAdult) finalAdult.textContent = 'Adult: ' + formatCurrency(adultFinal);
        if (finalChild) finalChild.textContent = 'Child: ' + formatCurrency(childFinal);
        if (finalInfant) finalInfant.textContent = 'Infant: ' + formatCurrency(infantFinal);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.pricing-mode-select').forEach(function (select) {
            const service = select.dataset.service;
            const discountField = select.parentElement.querySelector('.discount-field');

            function syncMode() {
                const isDiscount = select.value === 'discount_offer';
                if (discountField) {
                    if (isDiscount) {
                        discountField.style.display = 'flex';
                        discountField.style.visibility = 'visible';
                        discountField.hidden = false;
                    } else {
                        discountField.style.display = 'none !important';
                        discountField.style.visibility = 'hidden';
                        discountField.hidden = true;
                    }
                }

                document.querySelectorAll('.pricing-row[data-service="' + service + '"]').forEach(function (row) {
                    updatePricingRow(row);
                });
            }

            select.addEventListener('change', syncMode);
            document.querySelectorAll('.discount-percent[data-service="' + service + '"]').forEach(function (input) {
                input.addEventListener('input', function () {
                    document.querySelectorAll('.pricing-row[data-service="' + service + '"]').forEach(updatePricingRow);
                });
            });
            setTimeout(syncMode, 0);
        });

        setTimeout(function () {
            document.querySelectorAll('.pricing-mode-select').forEach(function (select) {
                const service = select.dataset.service;
                const discountField = select.parentElement.querySelector('.discount-field');
                const isDiscount = select.value === 'discount_offer';
                if (discountField && !isDiscount) {
                    discountField.style.display = 'none !important';
                    discountField.hidden = true;
                }
            });
        }, 100);
    });
</script>
@endpush
@endsection
