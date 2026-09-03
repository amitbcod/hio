@extends('frontend.layout')

@section('title', $package['name'])

@section('content')
@php
    $gallery = collect($package['gallery'] ?? [])->filter()->values()->all();
    $primaryImage = !empty($gallery) ? $gallery[0] : asset('images/holidays-io-logo.png');
    $itineraryDays = $package['itinerary_days'] ?? [];
@endphp

<section style="padding: 28px 0 40px; background: #f4f3f1;">
    <div class="wrap2" style="max-width: 1200px; margin: 0 auto;">
        <h1 style="margin: 0 0 6px; font-size: 38px; line-height: 1.2; font-weight: 900; color: #1f2a37; letter-spacing: -0.03em;">{{ $package['name'] }}</h1>
        <div style="display:flex; align-items:center; gap:10px; font-size: 13px; color:#6b7280; margin-bottom: 20px;">
            <span style="display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border-radius:999px; background:#f0f1f2; font-weight:600; color:#475467;">{{ $package['location'] }}</span>
            <span style="display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border-radius:999px; background:#f0f1f2; font-weight:600; color:#475467;">{{ $package['days_label'] }}</span>
        </div>

        <div style="display:grid; grid-template-columns: 1.7fr 1fr 1fr 1fr; gap: 12px; margin-bottom: 18px;">
            <div style="grid-column: span 1; border-radius: 14px; overflow:hidden; min-height: 320px; background:#e5e7eb;">
                <img src="{{ $primaryImage }}" alt="{{ $package['name'] }}" style="width:100%; height:100%; object-fit:cover; display:block;">
            </div>
            @foreach(array_slice($gallery, 1, 3) as $image)
                <div style="border-radius: 14px; overflow:hidden; min-height: 150px; background:#e5e7eb;">
                    <img src="{{ $image }}" alt="{{ $package['name'] }}" style="width:100%; height:100%; object-fit:cover; display:block;">
                </div>
            @endforeach
            @if(count($gallery) > 4)
                <div style="position:relative; border-radius: 14px; overflow:hidden; min-height: 150px; background:#e5e7eb;">
                    <img src="{{ $gallery[4] }}" alt="{{ $package['name'] }}" style="width:100%; height:100%; object-fit:cover; display:block; filter: brightness(0.68);">
                    <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px; font-weight:700; background: rgba(0,0,0,0.18);">+{{ max(0, count($gallery) - 5) }}</div>
                </div>
            @endif
        </div>

        <div style="display:flex; align-items:center; justify-content:space-between; border-bottom: 1px solid #dfe3e8; margin-bottom: 20px;">
                <div style="display:flex; gap:26px; align-items:end;">
                <button type="button" class="package-tab active" data-tab="itinerary" style="padding: 12px 0; border:none; background:none; border-bottom: 3px solid #1f9ae5; color:#1f9ae5; font-size:14px; font-weight:800; letter-spacing: .12em; text-transform: uppercase; cursor:pointer;">{{ __('package.tab.itinerary') }}</button>
                <button type="button" class="package-tab" data-tab="policies" style="padding: 12px 0; border:none; background:none; color:#475467; font-size:14px; font-weight:800; letter-spacing: .12em; text-transform: uppercase; cursor:pointer;">{{ __('package.tab.policies') }}</button>
                <button type="button" class="package-tab" data-tab="summary" style="padding: 12px 0; border:none; background:none; color:#475467; font-size:14px; font-weight:800; letter-spacing: .12em; text-transform: uppercase; cursor:pointer;">{{ __('package.tab.summary') }}</button>
            </div>
            <div style="font-size: 14px; color:#475467;">{{ __('package.share') }}</div>
        </div>

        <div class="package-tab-panel" id="tab-itinerary" style="display:grid; grid-template-columns: minmax(0,1.9fr) 360px; gap:22px; align-items:flex-start;">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding: 18px 20px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
                @php
                    $hotelCount = (int) ($package['hotel_count'] ?? 1);
                    $activityCount = (int) ($package['activity_count'] ?? 1);
                    $mealCount = (int) ($package['meal_count'] ?? 1);
                @endphp
                <div style="display:flex; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
                    <span style="display:inline-flex; align-items:center; padding:8px 12px; border:1px solid #a7d6f5; border-radius:999px; color:#1f9ae5; background:#edf7ff; font-size: 12px; font-weight:800; letter-spacing: .08em; text-transform: uppercase;">{{ $package['days_label'] }}</span>
                    <span style="display:inline-flex; align-items:center; padding:8px 12px; border:1px solid #d0d5dd; border-radius:999px; color:#475467; background:#f5f7fa; font-size: 12px; font-weight:800; letter-spacing: .08em; text-transform: uppercase;">{{ trans_choice('package.hotels', $hotelCount, ['count' => $hotelCount]) }}</span>
                    <span style="display:inline-flex; align-items:center; padding:8px 12px; border:1px solid #d0d5dd; border-radius:999px; color:#475467; background:#f5f7fa; font-size: 12px; font-weight:800; letter-spacing: .08em; text-transform: uppercase;">{{ trans_choice('package.activities', $activityCount, ['count' => $activityCount]) }}</span>
                    <span style="display:inline-flex; align-items:center; padding:8px 12px; border:1px solid #d0d5dd; border-radius:999px; color:#475467; background:#f5f7fa; font-size: 12px; font-weight:800; letter-spacing: .08em; text-transform: uppercase;">{{ trans_choice('package.meals', $mealCount, ['count' => $mealCount]) }}</span>
                </div>

                @foreach($itineraryDays as $day)
                    <div style="border:1px solid #e5e7eb; border-radius:12px; background:#fafafa; padding:14px 14px 12px; margin-bottom: 12px;">
                                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                                    <span style="display:inline-flex; align-items:center; justify-content:center; width:40px; height:26px; border-radius:6px; background:#f7d8c8; color:#cf6b2a; font-size:12px; font-weight:900;">{{ __('package.day') }} {{ $day['day'] }}</span>
                                    <strong style="font-weight:800; color:#1f2a37;">{{ $day['label'] }}</strong>
                                </div>

                        @if(!empty($day['description']))
                            <div style="margin-bottom:10px; color:#475467; line-height:1.7; white-space:pre-line;">{{ $day['description'] }}</div>
                        @endif

                        @if(!empty($day['images']))
                            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap:8px; margin-top:8px;">
                                @foreach(array_slice($day['images'], 0, 4) as $image)
                                    <img src="{{ $image }}" alt="Day {{ $day['day'] }}" style="width:100%; height:90px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb;">
                                @endforeach
                            </div>
                        @endif
                        {{-- Day-wise service details --}}
                        @if(!empty($day['accommodation']))
                            <div style="margin-top:12px; padding:12px; border-radius:8px; background:#fff; border:1px solid #eef2f6;">
                                <div style="font-weight:800; color:#1f2a37; margin-bottom:6px;">Accommodation</div>
                                <div style="color:#475467; margin-bottom:6px;">{{ $day['accommodation']['property_name'] }} · {{ $day['accommodation']['property_type'] }}</div>
                                <div style="font-size:13px; color:#556; margin-bottom:6px;">Location: {{ $day['accommodation']['location'] }}</div>
                                @if(!empty($day['accommodation']['star_rating']))
                                    <div style="font-size:13px; color:#556; margin-bottom:6px;">Rating: {{ $day['accommodation']['star_rating'] }}</div>
                                @endif
                                @if(!empty($day['accommodation']['meal_plans']))
                                    <div style="font-size:13px; color:#333; margin-top:6px; font-weight:600;">Meal Plans:</div>
                                    <ul style="margin:6px 0 0 18px; color:#475467;">
                                        @foreach($day['accommodation']['meal_plans'] as $mp)
                                            <li>{{ $mp }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif

                        @if(!empty($day['activity']))
                            <div style="margin-top:12px; padding:12px; border-radius:8px; background:#fff; border:1px solid #eef2f6;">
                                <div style="font-weight:800; color:#1f2a37; margin-bottom:6px;">Activity</div>
                                <div style="color:#475467; margin-bottom:6px;">{{ $day['activity']['activity_name'] }} @if(!empty($day['activity']['town'])) · {{ $day['activity']['town'] }} @endif</div>
                                @if(!empty($day['activity']['time']))
                                    <div style="font-size:13px; color:#556;">Time: {{ $day['activity']['time'] }}</div>
                                @endif
                                @if(!empty($day['activity']['duration']))
                                    <div style="font-size:13px; color:#556;">Duration: {{ $day['activity']['duration'] }}</div>
                                @endif
                                @if(!empty($day['activity']['notes']))
                                    <div style="margin-top:8px; color:#475467; white-space:pre-line;">{{ $day['activity']['notes'] }}</div>
                                @endif
                            </div>
                        @endif

                        @if(!empty($day['transport']))
                            <div style="margin-top:12px; padding:12px; border-radius:8px; background:#fff; border:1px solid #eef2f6;">
                                <div style="font-weight:800; color:#1f2a37; margin-bottom:6px;">Transport</div>
                                <div style="color:#475467; margin-bottom:6px;">{{ $day['transport']['vehicle_name'] }} · {{ $day['transport']['vehicle_type'] }}</div>
                                @if(!empty($day['transport']['pickup_time']))
                                    <div style="font-size:13px; color:#556;">Pickup: {{ $day['transport']['pickup_time'] }}</div>
                                @endif
                                @if(!empty($day['transport']['return_time']))
                                    <div style="font-size:13px; color:#556;">Return: {{ $day['transport']['return_time'] }}</div>
                                @endif
                                @if(!empty($day['transport']['routes']))
                                    <div style="margin-top:8px; font-size:13px; color:#333; font-weight:600;">Route(s):</div>
                                    <ul style="margin:6px 0 0 18px; color:#475467;">
                                        @foreach($day['transport']['routes'] as $r)
                                            <li>{{ $r['from'] }} @if(!empty($r['to'])) → {{ $r['to'] }} @endif @if(!empty($r['pricing']['price'])) · {{ $r['pricing']['price'] }} @endif</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <aside style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px 18px 16px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
                <div style="font-size:30px; font-weight:800; color:#1f2a37; margin-bottom: 18px;">$ {{ number_format((float) $package['price'], 2) }}</div>
                <form method="POST" action="{{ route('frontend.booking.cart.add') }}">
                    @csrf
                    <input type="hidden" name="type" value="package">
                    <input type="hidden" name="package_id" value="{{ $package['id'] }}">
                    <input type="hidden" name="package_name" value="{{ $package['name'] }}">
                    <input type="hidden" name="package_total_price" id="package_total_price" value="{{ $package['price'] }}">
                    <input type="hidden" name="currency" value="USD">
                    <input type="hidden" name="package_image" value="{{ $package['image'] }}">
                    <input type="hidden" name="nights" value="{{ $package['no_of_nights'] }}">
                    <input type="hidden" name="days" value="{{ $package['no_of_days'] }}">
                    {{-- Preserve user-selected package start date when adding to cart --}}
                    @php
                        $packageStartDate = request()->query('traveling_date') ?: (request()->query('check_in') ?: null);
                    @endphp
                    @if(!empty($packageStartDate))
                        <input type="hidden" name="package_start_date" value="{{ $packageStartDate }}">
                    @endif
                    {{-- include guest selectors for user to change and recalc via query params if needed --}}
                    @php
                        $packageAdults = max(1, (int) request()->query('adults', 2));
                        $packageChildren = max(0, (int) request()->query('children', 0));
                        $packageInfants = max(0, (int) request()->query('infants', 0));
                        $packageRoomsRequired = 1;
                        $packageRoomCatalog = collect($itineraryDays ?? [])->pluck('accommodation.rooms')->flatten(1)->filter();
                        if ($packageRoomCatalog->isNotEmpty()) {
                            $maxPackageRoomUnits = max(1, (int) $packageRoomCatalog->count());
                            for ($candidateRooms = 1; $candidateRooms <= $maxPackageRoomUnits; $candidateRooms++) {
                                $availableUnits = 0;
                                foreach ($packageRoomCatalog as $room) {
                                    if (\App\Http\Controllers\Frontend\HomeController::roomMatchesSelectedGuestRequirements(
                                        $room,
                                        $packageAdults,
                                        $packageChildren,
                                        $packageInfants,
                                        $candidateRooms
                                    )) {
                                        $availableUnits += max(1, (int) ($room->allotment ?? $room->quantity ?? 1));
                                    }
                                }
                                if ($availableUnits >= $candidateRooms) {
                                    $packageRoomsRequired = $candidateRooms;
                                    break;
                                }
                            }
                        }
                        $packageRoomsRequired = max(1, $packageRoomsRequired);
                    @endphp
                    @php
                        $packagePlanLabel = '';
                        $packageMealPlan = '';
                        foreach (($itineraryDays ?? []) as $day) {
                            $mealPlans = $day['accommodation']['meal_plans'] ?? [];
                            if (!empty($mealPlans)) {
                                $packageMealPlan = trim((string) collect($mealPlans)->first());
                                if ($packageMealPlan !== '') {
                                    $packagePlanLabel = $packageMealPlan;
                                    break;
                                }
                            }
                        }
                        if ($packagePlanLabel === '') {
                            $packageMealPlan = 'Breakfast';
                            $packagePlanLabel = 'Breakfast';
                        }
                    @endphp
                    <input type="hidden" name="adults" value="{{ $packageAdults }}">
                    <input type="hidden" name="children" value="{{ $packageChildren }}">
                    <input type="hidden" name="infants" value="{{ $packageInfants }}">
                    <input type="hidden" name="rooms" value="{{ $packageRoomsRequired }}">
                    <input type="hidden" name="rooms_required" value="{{ $packageRoomsRequired }}">
                    <input type="hidden" name="room_name" value="Standard Room">
                    <input type="hidden" name="plan_label" value="{{ $packagePlanLabel }}">
                    <input type="hidden" name="rate_name" value="Package">
                    <input type="hidden" name="meal_plan" value="{{ $packageMealPlan }}">
                    <button type="submit" style="width:100%; background:#f39b4a; border:none; border-radius:8px; color:#fff; font-size:18px; font-weight:800; padding:14px 16px; cursor:pointer; text-transform: uppercase; letter-spacing: .05em;">{{ __('package.add_to_cart') }}</button>
                </form>
            </aside>
        </div>

        <div class="package-tab-panel" id="tab-policies" style="display:none; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:22px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            @php
                $policyRows = [
                    'cancellation' => ['label' => __('package.policy.cancellation')],
                    'amendments' => ['label' => __('package.policy.amendments')],
                    'postponement' => ['label' => __('package.policy.postponement')],
                    'payment' => ['label' => __('package.policy.payment')],
                    'refund' => ['label' => __('package.policy.refund')],
                    'security_deposit' => ['label' => __('package.policy.security_deposit')],
                    'house_rules' => ['label' => __('package.policy.house_rules')],
                ];
                $effectivePolicy = $package['effective_policy'] ?? [];
            @endphp

            @if(!empty($effectivePolicy))
                <div style="border:1px solid #e4e7eb;border-radius:10px;overflow:hidden;background:#fff;">
                    <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
                        <thead style="background:#f7f7f7;">
                            <tr>
                                <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:10%;font-size:13px;color:#333;">{{ __('package.policy.table.policy') }}</th>
                                <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:15%;font-size:13px;color:#333;">{{ __('package.policy.table.details') }}</th>
                                <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:20%;font-size:13px;color:#333;">{{ __('package.policy.table.before_deadline') }}</th>
                                <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:20%;font-size:13px;color:#333;">{{ __('package.policy.table.after_deadline') }}</th>
                                <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:24%;font-size:13px;color:#333;">{{ __('package.policy.table.notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($policyRows as $key => $meta)
                                @php
                                    $row = $effectivePolicy[$key] ?? ['type' => '-', 'before_deadline' => '-', 'after_deadline' => '-', 'notes' => ''];
                                @endphp
                                <tr>
                                    <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;font-weight:600;color:#2b2d31;">{{ $meta['label'] }}</td>
                                    <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;">
                                        <div style="padding:6px 10px;border:1px solid #dfeaf9;border-radius:6px;background:#f8fbff;min-height:36px;display:flex;align-items:center;">{{ $row['type'] ?? '-' }}</div>
                                    </td>
                                    <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;">
                                        @php $beforeValue = $row['before_deadline'] ?? '-'; @endphp
                                        <div style="padding:6px 10px;border:1px solid #dfeaf9;border-radius:6px;background:#f8fbff;min-height:36px;display:flex;align-items:center;">{{ $beforeValue }}</div>
                                    </td>
                                    <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;">
                                        @php $afterValue = $row['after_deadline'] ?? '-'; @endphp
                                        <div style="padding:6px 10px;border:1px solid #dfeaf9;border-radius:6px;background:#f8fbff;min-height:36px;display:flex;align-items:center;">{{ $afterValue }}</div>
                                    </td>
                                    <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;">
                                        <div style="padding:6px 10px;border:1px solid #dfeaf9;border-radius:6px;background:#f8fbff;min-height:36px;display:flex;align-items:center;white-space:pre-wrap;">{{ $row['notes'] ?? '' }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(!empty($effectivePolicy['booking_notes']) || !empty($effectivePolicy['package_notes']))
                    <div style="margin-top:18px; display:grid; gap:12px;">
                        @if(!empty($effectivePolicy['booking_notes']))
                            <div style="border:1px solid #e4e7eb;border-radius:10px;padding:12px 14px;background:#f7f7f7;">
                                <div style="font-weight:600;color:#333;margin-bottom:8px;">{{ __('package.booking_notes') }}</div>
                                <div style="padding:10px 12px;border:1px solid #dfeaf9;border-radius:6px;background:#fff;white-space:pre-wrap;">{{ $effectivePolicy['booking_notes'] }}</div>
                            </div>
                        @endif

                        @if(!empty($effectivePolicy['package_notes']))
                            <div style="border:1px solid #e4e7eb;border-radius:10px;padding:12px 14px;background:#f7f7f7;">
                                <div style="font-weight:600;color:#333;margin-bottom:8px;">{{ __('package.package_notes') }}</div>
                                <div style="padding:10px 12px;border:1px solid #dfeaf9;border-radius:6px;background:#fff;white-space:pre-wrap;">{{ $effectivePolicy['package_notes'] }}</div>
                            </div>
                        @endif
                    </div>
                @endif
            @else
                <p style="margin:0; color:#475467;">{{ __('package.policies_not_defined') }}</p>
            @endif
        </div>

        <div class="package-tab-panel" id="tab-summary" style="display:none; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:22px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            @if(!empty($package['full_description']))
                <div style="color:#475467; line-height:1.8; white-space:pre-line;">{{ $package['full_description'] }}</div>
            @else
                <p style="margin:0; color:#475467;">{{ __('package.no_summary') }}</p>
            @endif
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.package-tab');
        const panels = document.querySelectorAll('.package-tab-panel');

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                const target = tab.getAttribute('data-tab');

                tabs.forEach(function (item) {
                    const isActive = item === tab;
                    item.classList.toggle('active', isActive);
                    item.style.borderBottom = isActive ? '3px solid #1f9ae5' : 'none';
                    item.style.color = isActive ? '#1f9ae5' : '#475467';
                });

                panels.forEach(function (panel) {
                    panel.style.display = panel.id === 'tab-' + target ? 'grid' : 'none';
                });
            });
        });
    });
</script>
@endsection
