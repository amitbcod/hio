@extends('layouts.admin')

@php $sidebar = 'admin.packages._steps_sidebar'; $currentStep = 3; @endphp

@section('content')
<div class="container mt-5">
    <div style="background:#fff;border-radius:12px;padding:18px;margin-bottom:16px;">
        <h1 style="margin:0;font-weight:700;">Step 3: Allocation (Accommodation)</h1>
        <p style="color:#666;margin-top:6px;">Select rooms for each day from the accommodation chosen in Step 2. Use filters to narrow results.</p>
    </div>

    <form method="POST" action="{{ route('admin.packages.step3.store', $package->id) }}">
        @csrf

        @php $itinerary = $package->itinerary ?? []; @endphp

        <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
            <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px;flex-wrap:wrap;">
                <div style="flex:1;min-width:240px;">
                    <label class="form-label">Available Plans</label>
                    <div id="meal-chips" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px;">
                        @if(empty($mealPlans))
                            <div class="badge bg-secondary">No plans</div>
                        @else
                            @foreach($mealPlans as $plan)
                                <button type="button" class="btn btn-outline-success btn-sm filter-chip" data-filter-type="rate" data-value="{{ $plan }}">{{ $plan }}</button>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div style="flex:1;min-width:240px;">
                    <label class="form-label">Property Type / Bedding</label>
                    <div id="property-chips" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:6px;">
                        @if(empty($propertyTypes))
                            <div class="badge bg-secondary">No property types</div>
                        @else
                            @foreach($propertyTypes as $pt)
                                <button type="button" class="btn btn-outline-primary btn-sm filter-chip" data-filter-type="property" data-value="{{ $pt }}">{{ $pt }}</button>
                            @endforeach
                        @endif
                        @php
                            // also include bedding/room types gathered from rooms
                            $bedTypes = [];
                            foreach($roomsByDay as $rlist) {
                                foreach($rlist as $r) {
                                    if(!empty($r->room_type)) $bedTypes[] = $r->room_type;
                                }
                            }
                            $bedTypes = array_values(array_unique($bedTypes));
                        @endphp
                        @foreach($bedTypes as $bt)
                            <button type="button" class="btn btn-outline-primary btn-sm filter-chip" data-filter-type="bed" data-value="{{ $bt }}">{{ $bt }}</button>
                        @endforeach
                    </div>
                </div>
                <div style="flex:0;min-width:160px;">
                    <label class="form-label">&nbsp;</label>
                    <div><a href="#" id="apply-rooms-all">Apply selected rooms to all days</a></div>
                </div>
            </div>

            @foreach($dates as $i => $date)
                <div class="day-block" data-day-index="{{ $i }}" style="border:1px solid #eee;border-radius:8px;padding:14px;margin-bottom:12px;">
                    <h6 style="margin:0 0 8px 0;font-weight:700;">Day {{ $i + 1 }}</h6>

                    @php
                        $rooms = $roomsByDay[$i] ?? collect();
                        $selectedRooms = $itinerary[$i]['rooms'] ?? [];
                        $selectedAccommodation = $accommodationByDay[$i] ?? null;
                        $selectedActivity = $activityByDay[$i] ?? null;
                        $selectedTransport = $transportByDay[$i] ?? null;
                        $selectedActivitySelections = $itinerary[$i]['activity_selection'] ?? [];
                        if (!is_array($selectedActivitySelections)) {
                            $selectedActivitySelections = $selectedActivitySelections ? [$selectedActivitySelections] : [];
                        }
                        $variantPricingOptions = $activityVariantPricingByDay[$i] ?? [];
                        $transportGroupData = $transportServiceGroupsByDay[$i] ?? ['groups' => [], 'default' => null];
                    @endphp

                    <div style="margin-bottom:12px;padding:10px 12px;border:1px solid #e8f4f2;border-radius:8px;background:#f4fbfa;">
                        @if($selectedAccommodation)
                            @php
                                $accommodationTitle = $selectedAccommodation->property_name ?? $selectedAccommodation->name ?? optional($selectedAccommodation->business)->name ?? ('Accommodation #' . $selectedAccommodation->id);
                            @endphp
                            <strong style="display:block;color:#0e8f84;">Accommodation — {{ $accommodationTitle }}</strong>
                        @else
                            <span style="color:#666;">No accommodation selected for this day.</span>
                        @endif
                    </div>

                    @if($rooms->isEmpty())
                        <p style="color:#666;">No rooms found for the accommodation selected for this day.</p>
                    @else
                        <div class="row rooms-list">
                            @foreach($rooms as $room)
                                @php
                                    // get room's assigned rate names (only plans marked as is_rate_plan = true)
                                    $roomRateNames = $room->rates()->where('is_rate_plan', true)->whereNotNull('rate_name')->pluck('rate_name')->unique()->filter()->values()->toArray();
                                    $rateAttr = implode('|', $roomRateNames);
                                    $propType = optional($room->accommodation)->property_type;
                                    $bedType = $room->room_type ?? '';
                                @endphp
                                <div class="col-md-4 room-item" data-rate-names="{{ $rateAttr }}" data-property-type="{{ $propType }}" data-bed-type="{{ $bedType }}">
                                    <div style="border:1px solid #f1f1f1;padding:10px;border-radius:8px;">
                                        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                            <div style="flex:1;">
                                                <strong>{{ $room->room_name ?? ('Room #' . $room->id) }}</strong>
                                                <div style="font-size:12px;color:#666;margin-top:2px;">{{ $room->room_type ?? '' }}</div>
                                                @if(!empty($roomRateNames))
                                                    <div style="font-size:11px;color:#0e8f84;font-weight:500;margin-top:6px;line-height:1.4;">
                                                        @foreach($roomRateNames as $rateName)
                                                            <div>{{ $rateName }}</div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <input type="checkbox" name="allocations[{{ $i }}][rooms][]" value="{{ $room->id }}" class="room-checkbox" {{ in_array($room->id, $selectedRooms) ? 'checked' : '' }}> Select
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($variantPricingOptions))
                        <div style="margin-top:16px;padding:10px 12px;border:1px solid #dfeef0;border-radius:8px;background:#f7fcfd;">
                            <div style="font-weight:600;color:#0a6770;margin-bottom:10px;">Activity</div>
                            <div style="display:flex;flex-direction:column;gap:8px;">
                                @foreach($variantPricingOptions as $option)
                                    @php
                                        $checkboxValue = $option['variant_id'] . '|' . $option['pricing_option'];
                                        $isChecked = in_array($checkboxValue, $selectedActivitySelections, true);
                                    @endphp
                                    <label style="display:flex;align-items:center;gap:10px;padding:8px 10px;border:1px solid #bfe1e7;border-radius:8px;background:#fff;cursor:pointer;">
                                        <input type="checkbox" name="itinerary[{{ $i }}][activity_selection][]" value="{{ $checkboxValue }}" {{ $isChecked ? 'checked' : '' }}>
                                        <span>{{ $selectedActivity->activity_name ?? 'Activity' }} - {{ $option['variant_name'] }} - {{ $option['pricing_option'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @elseif($selectedActivity)
                        <div style="margin-top:16px;padding:10px 12px;border:1px dashed #e7d8b7;border-radius:8px;background:#fffaf0;color:#8a6d3b;">
                            No pricing option configured for this activity yet.
                        </div>
                    @endif

                    @if($selectedTransport)
                        @php
                            $selectedVehicleTitle = $selectedTransport->vehicle_name ?? $selectedTransport->name ?? 'Car';
                            $selectedRegistrationNumber = $selectedTransport->registration_number ?? '';
                            $selectedTransportHeading = trim('Transport — ' . $selectedVehicleTitle . ($selectedRegistrationNumber ? ' -- ' . $selectedRegistrationNumber : ''));
                        @endphp
                        <div style="margin-top:16px;padding:12px;border:1px solid #dfeef0;border-radius:8px;background:#f7fcfd;">
                            <div style="font-weight:600;color:#0a6770;margin-bottom:12px;">{{ $selectedTransportHeading }}</div>

                            @if(!empty($transportGroupData['groups']))
                                <ul class="nav nav-tabs mb-3" role="tablist">
                                    @foreach($transportGroupData['groups'] as $serviceKey => $serviceGroup)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{ (($transportGroupData['default'] ?? null) === $serviceKey || (!isset($transportGroupData['default']) && $loop->first)) ? 'active' : '' }}"
                                                    id="transport-tab-{{ $i }}-{{ $serviceKey }}"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#transport-pane-{{ $i }}-{{ $serviceKey }}"
                                                    type="button" role="tab"
                                                    aria-controls="transport-pane-{{ $i }}-{{ $serviceKey }}"
                                                    aria-selected="{{ (($transportGroupData['default'] ?? null) === $serviceKey || (!isset($transportGroupData['default']) && $loop->first)) ? 'true' : 'false' }}">
                                                {{ $serviceGroup['label'] }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="tab-content">
                                    @foreach($transportGroupData['groups'] as $serviceKey => $serviceGroup)
                                        @php
                                            $isActive = (($transportGroupData['default'] ?? null) === $serviceKey || (!isset($transportGroupData['default']) && $loop->first));
                                        @endphp
                                        <div class="tab-pane fade {{ $isActive ? 'show active' : '' }}" id="transport-pane-{{ $i }}-{{ $serviceKey }}" role="tabpanel" aria-labelledby="transport-tab-{{ $i }}-{{ $serviceKey }}">
                                            @if(empty($serviceGroup['routes']))
                                                <div style="padding:10px;border:1px dashed #ddd;border-radius:8px;color:#666;background:#fff;">No routes configured for this service.</div>
                                            @else
                                                @foreach($serviceGroup['routes'] as $routeIndex => $route)
                                                    @php
                                                        $routeKey = $route->route_id ?? ($serviceKey . '-' . $routeIndex);
                                                        $routeLabel = (($route->route_from ?? $route->pickup_value ?? 'From') . ' → ' . ($route->route_to ?? $route->dropoff_value ?? 'To'));
                                                        $savedRouteSchedule = $itinerary[$i]['transport_schedule'][$serviceKey][$routeKey] ?? [];
                                                        $routeSelected = !empty($savedRouteSchedule['selected']) || !empty($savedRouteSchedule['selected_route']);
                                                        $addReturnChecked = !empty($savedRouteSchedule['add_return']);
                                                        $savedStartHour = $savedRouteSchedule['start_hour'] ?? '';
                                                        $savedStartMin = $savedRouteSchedule['start_min'] ?? '';
                                                    @endphp
                                                    <div class="transport-route-row {{ $routeSelected ? 'route-selected' : '' }}" style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:10px 12px;border:1px solid {{ $routeSelected ? '#2bb673' : '#e0e0e0' }};border-radius:8px;margin-bottom:8px;background:{{ $routeSelected ? '#eefaf3' : '#fff' }};flex-wrap:wrap;">
                                                        <div style="font-weight:600;">{{ $routeLabel }}</div>
                                                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                                            <label style="margin:0;font-size:12px;color:#555;display:flex;align-items:center;gap:6px;">
                                                                <span>Start Time:</span>
                                                                <input type="text" name="itinerary[{{ $i }}][transport_schedule][{{ $serviceKey }}][{{ $routeKey }}][start_hour]" value="{{ old('itinerary.' . $i . '.transport_schedule.' . $serviceKey . '.' . $routeKey . '.start_hour', $savedStartHour) }}" maxlength="2" pattern="[0-9]{2}" placeholder="HH" style="width:52px;padding:4px 6px;border:1px solid #ccc;border-radius:4px;text-align:center;">
                                                                <span>:</span>
                                                                <input type="text" name="itinerary[{{ $i }}][transport_schedule][{{ $serviceKey }}][{{ $routeKey }}][start_min]" value="{{ old('itinerary.' . $i . '.transport_schedule.' . $serviceKey . '.' . $routeKey . '.start_min', $savedStartMin) }}" maxlength="2" pattern="[0-9]{2}" placeholder="MM" style="width:52px;padding:4px 6px;border:1px solid #ccc;border-radius:4px;text-align:center;">
                                                            </label>
                                                            <label style="margin:0;font-size:12px;color:#555;display:flex;align-items:center;gap:6px;">
                                                                <input type="checkbox" name="itinerary[{{ $i }}][transport_schedule][{{ $serviceKey }}][{{ $routeKey }}][add_return]" value="1" {{ $addReturnChecked ? 'checked' : '' }}>
                                                                <span>Add Return</span>
                                                            </label>
                                                            <input type="checkbox" id="route-selected-{{ $i }}-{{ $serviceKey }}-{{ $routeKey }}" name="itinerary[{{ $i }}][transport_schedule][{{ $serviceKey }}][{{ $routeKey }}][selected]" value="1" {{ $routeSelected ? 'checked' : '' }} style="display:none;">
                                                            <button type="button" class="btn btn-sm transport-route-select {{ $routeSelected ? 'btn-success' : 'btn-outline-success' }}" data-toggle-target="route-selected-{{ $i }}-{{ $serviceKey }}-{{ $routeKey }}">{{ $routeSelected ? 'Selected' : 'Select' }}</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div style="padding:10px;border:1px dashed #ddd;border-radius:8px;color:#666;background:#fff;">No route groups available for this transport.</div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach

            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:12px;">
                <a href="{{ route('admin.packages.step2', $package->id) }}" class="btn btn-light">Back</a>
                <button type="submit" class="btn btn-primary">Save Allocation & Continue</button>
            </div>
        </div>
    </form>

    @push('scripts')
        <style>
            .filter-chip.selected { background:#0e8f84; color:#fff; border-color:#0e8f84; }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.transport-route-select').forEach(function(button) {
                    button.addEventListener('click', function() {
                        const targetId = this.getAttribute('data-toggle-target');
                        const checkbox = document.getElementById(targetId);
                        if (!checkbox) return;

                        const nowSelected = !checkbox.checked;
                        checkbox.checked = nowSelected;
                        const row = this.closest('.transport-route-row');
                        if (row) {
                            row.style.borderColor = nowSelected ? '#2bb673' : '#e0e0e0';
                            row.style.background = nowSelected ? '#eefaf3' : '#fff';
                        }

                        this.classList.toggle('btn-success', nowSelected);
                        this.classList.toggle('btn-outline-success', !nowSelected);
                        this.textContent = nowSelected ? 'Selected' : 'Select';
                    });
                });

                const chips = document.querySelectorAll('.filter-chip');
                const selectedRates = new Set();
                const selectedProperties = new Set();
                const selectedBeds = new Set();

                function applyFilters() {
                    document.querySelectorAll('.room-item').forEach(function(item) {
                        let show = true;

                        // rate filter
                        if (selectedRates.size > 0) {
                            const rates = item.dataset.rateNames ? item.dataset.rateNames.split('|') : [];
                            const intersection = rates.filter(m => selectedRates.has(m));
                            if (intersection.length === 0) show = false;
                        }

                        // property / bed filter (either match)
                        if (selectedProperties.size > 0 || selectedBeds.size > 0) {
                            const pt = item.dataset.propertyType || '';
                            const bt = item.dataset.bedType || '';
                            let propMatch = false;
                            if (selectedProperties.size > 0 && selectedProperties.has(pt)) propMatch = true;
                            if (selectedBeds.size > 0 && selectedBeds.has(bt)) propMatch = true;
                            if (!propMatch) show = false;
                        }

                        item.style.display = show ? '' : 'none';
                    });
                }

                chips.forEach(function(chip){
                    chip.addEventListener('click', function(e){
                        const type = chip.dataset.filterType;
                        const val = chip.dataset.value;
                        const isSelected = chip.classList.toggle('selected');
                        if (type === 'rate') {
                            if (isSelected) selectedRates.add(val); else selectedRates.delete(val);
                        } else if (type === 'property') {
                            if (isSelected) selectedProperties.add(val); else selectedProperties.delete(val);
                        } else if (type === 'bed') {
                            if (isSelected) selectedBeds.add(val); else selectedBeds.delete(val);
                        }
                        applyFilters();
                    });
                });

                // Apply rooms from first day to all days
                const applyAll = document.getElementById('apply-rooms-all');
                applyAll.addEventListener('click', function(e){
                    e.preventDefault();
                    const firstDay = document.querySelector('.day-block[data-day-index="0"]');
                    if (!firstDay) return;
                    const checked = Array.from(firstDay.querySelectorAll('.room-checkbox:checked')).map(cb => cb.value);
                    if (checked.length === 0) { alert('Select at least one room on Day 1 first'); return; }
                    document.querySelectorAll('.day-block').forEach(function(dayBlock, idx){
                        if (idx === 0) return;
                        // uncheck existing
                        dayBlock.querySelectorAll('.room-checkbox').forEach(cb => cb.checked = false);
                        // check matching boxes by value
                        checked.forEach(function(val){
                            const cb = dayBlock.querySelector('.room-checkbox[value="' + val + '"]');
                            if (cb) cb.checked = true;
                        });
                    });
                });
            });
        </script>
    @endpush
</div>
@endsection
