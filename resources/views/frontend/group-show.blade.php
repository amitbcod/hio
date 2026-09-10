@extends('frontend.layout')

@section('title', $group['name'])

@section('content')
@php
    $gallery = collect($group['gallery'] ?? [])->filter()->values()->all();
    $primaryImage = !empty($gallery) ? $gallery[0] : asset('images/holidays-io-logo.png');
    $itineraryDays = $group['itinerary_days'] ?? [];
@endphp

<section style="padding: 28px 0 40px; background: #f4f3f1;">
    <style>
        .group-sidebar { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px; box-shadow:0 1px 2px rgba(0,0,0,0.03); }
        .group-price { font-size:30px; font-weight:800; color:#1f2a37; margin-bottom: 18px; }
        .search-controls { margin-bottom:14px; }
        .search-row { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
        .travel-date { flex: 1 1 180px; min-width:140px; }
        .travel-date input { width:100%; padding:9px 10px; border-radius:8px; border:1px solid #e6eef6; box-sizing:border-box; }
        .guests-panel { display:flex; gap:10px; align-items:center; flex: 1 1 260px; min-width:200px; flex-wrap:wrap; }
        .guest-group { display:flex; align-items:center; gap:8px; background:#fafafa; padding:6px 8px; border-radius:10px; border:1px solid #eef2f6; }
        .guest-label { font-size:12px; color:#475467; margin-right:6px; }
        .guest-btn { width:30px; height:30px; border-radius:50%; border:1px solid #d1d5db; background:#fff; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; font-weight:700; }
        .guest-input { width:40px; text-align:center; border:none; background:transparent; font-weight:800; }
        .update-search-btn { background:#f39b4a; border:none; border-radius:8px; color:#fff; font-weight:800; padding:10px 14px; cursor:pointer; flex:0 0 auto; }
        .add-to-cart-btn { width:100%; background:#f39b4a; border:none; border-radius:8px; color:#fff; font-size:18px; font-weight:800; padding:14px 16px; cursor:pointer; text-transform: uppercase; letter-spacing: .05em; }
        .group-sidebar.sticky { position:sticky; top:24px; }
    </style>

    <div class="wrap2" style="max-width: 1200px; margin: 0 auto;">
        <h1 style="margin: 0 0 6px; font-size: 38px; line-height: 1.2; font-weight: 900; color: #1f2a37; letter-spacing: -0.03em;">{{ $group['name'] }}</h1>
        <div style="display:flex; align-items:center; gap:10px; font-size: 13px; color:#6b7280; margin-bottom: 20px;">
            <span style="display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border-radius:999px; background:#f0f1f2; font-weight:600; color:#475467;">{{ $group['location'] }}</span>
            <span style="display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border-radius:999px; background:#f0f1f2; font-weight:600; color:#475467;">{{ $group['days_label'] }}</span>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const incBtns = document.querySelectorAll('.count-btn-increment, .guest-btn.count-btn-increment');
                const decBtns = document.querySelectorAll('.count-btn-decrement, .guest-btn.count-btn-decrement');

                const updateInput = (name, delta) => {
                    const visible = document.querySelector(`form[method="GET"] input[name="${name}"]`);
                    const hidden = document.querySelector(`form[method="POST"] input[name="${name}"]`);
                    const input = visible || hidden;
                    if (!input) return;
                    let value = parseInt(input.value || '0', 10);
                    value = Math.max(name === 'adults' ? 1 : 0, value + delta);
                    if (visible) visible.value = value;
                    if (hidden) hidden.value = value;
                };

                incBtns.forEach(btn => btn.addEventListener('click', () => updateInput(btn.dataset.target, 1)));
                decBtns.forEach(btn => btn.addEventListener('click', () => updateInput(btn.dataset.target, -1)));

                const addToCartForm = document.querySelector('form[method="POST"][action*="booking/cart"]');
                if (addToCartForm) {
                    addToCartForm.addEventListener('submit', function () {
                        const getForm = document.querySelector('form[method="GET"]');
                        if (getForm) {
                            ['adults','children','infants'].forEach(name => {
                                const visible = getForm.querySelector(`input[name="${name}"]`);
                                const hidden = addToCartForm.querySelector(`input[name="${name}"]`);
                                if (visible && hidden) hidden.value = visible.value;
                            });
                            const visibleDate = getForm.querySelector('#traveling-date-native-detail');
                            const hiddenDate = addToCartForm.querySelector('input[name="package_start_date"]');
                            if (visibleDate && hiddenDate) hiddenDate.value = visibleDate.value;
                        }
                    });
                }
            });
        </script>

        <div style="display:grid; grid-template-columns: 1.7fr 1fr 1fr 1fr; gap: 12px; margin-bottom: 18px;">
            <div style="grid-column: span 1; border-radius: 14px; overflow:hidden; min-height: 320px; background:#e5e7eb;">
                <img src="{{ $primaryImage }}" alt="{{ $group['name'] }}" style="width:100%; height:100%; object-fit:cover; display:block;">
            </div>
            @foreach(array_slice($gallery, 1, 3) as $image)
                <div style="border-radius: 14px; overflow:hidden; min-height: 150px; background:#e5e7eb;">
                    <img src="{{ $image }}" alt="{{ $group['name'] }}" style="width:100%; height:100%; object-fit:cover; display:block;">
                </div>
            @endforeach
            @if(count($gallery) > 4)
                <div style="position:relative; border-radius: 14px; overflow:hidden; min-height: 150px; background:#e5e7eb;">
                    <img src="{{ $gallery[4] }}" alt="{{ $group['name'] }}" style="width:100%; height:100%; object-fit:cover; display:block; filter: brightness(0.68);">
                    <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px; font-weight:700; background: rgba(0,0,0,0.18);">+{{ max(0, count($gallery) - 5) }}</div>
                </div>
            @endif
        </div>

        <div style="display:flex; align-items:center; justify-content:space-between; border-bottom: 1px solid #dfe3e8; margin-bottom: 20px;">
            <div style="display:flex; gap:26px; align-items:end;">
                <button type="button" class="group-tab active" data-tab="itinerary" style="padding: 12px 0; border:none; background:none; border-bottom: 3px solid #1f9ae5; color:#1f9ae5; font-size:14px; font-weight:800; letter-spacing: .12em; text-transform: uppercase; cursor:pointer;">Itinerary</button>
                <button type="button" class="group-tab" data-tab="policies" style="padding: 12px 0; border:none; background:none; color:#475467; font-size:14px; font-weight:800; letter-spacing: .12em; text-transform: uppercase; cursor:pointer;">Policies</button>
                <button type="button" class="group-tab" data-tab="summary" style="padding: 12px 0; border:none; background:none; color:#475467; font-size:14px; font-weight:800; letter-spacing: .12em; text-transform: uppercase; cursor:pointer;">Summary</button>
            </div>
            <div style="font-size: 14px; color:#475467;">Share</div>
        </div>

        <div class="group-tab-panel" id="tab-itinerary" style="display:grid; grid-template-columns: minmax(0,1.9fr) 360px; gap:22px; align-items:flex-start;">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding: 18px 20px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
                @php
                    $hotelCount = (int) ($group['hotel_count'] ?? 1);
                    $activityCount = (int) ($group['activity_count'] ?? 1);
                    $mealCount = (int) ($group['meal_count'] ?? 1);
                @endphp
                <div style="display:flex; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
                    <span style="display:inline-flex; align-items:center; padding:8px 12px; border:1px solid #a7d6f5; border-radius:999px; color:#1f9ae5; background:#edf7ff; font-size: 12px; font-weight:800; letter-spacing: .08em; text-transform: uppercase;">{{ $group['days_label'] }}</span>
                    <span style="display:inline-flex; align-items:center; padding:8px 12px; border:1px solid #d0d5dd; border-radius:999px; color:#475467; background:#f5f7fa; font-size: 12px; font-weight:800; letter-spacing: .08em; text-transform: uppercase;">{{ $hotelCount }} hotel{{ $hotelCount === 1 ? '' : 's' }}</span>
                    <span style="display:inline-flex; align-items:center; padding:8px 12px; border:1px solid #d0d5dd; border-radius:999px; color:#475467; background:#f5f7fa; font-size: 12px; font-weight:800; letter-spacing: .08em; text-transform: uppercase;">{{ $activityCount }} activity{{ $activityCount === 1 ? '' : 'ies' }}</span>
                    <span style="display:inline-flex; align-items:center; padding:8px 12px; border:1px solid #d0d5dd; border-radius:999px; color:#475467; background:#f5f7fa; font-size: 12px; font-weight:800; letter-spacing: .08em; text-transform: uppercase;">{{ $mealCount }} meal{{ $mealCount === 1 ? '' : 's' }}</span>
                </div>

                @foreach($itineraryDays as $day)
                    <div style="border:1px solid #e5e7eb; border-radius:12px; background:#fafafa; padding:14px 14px 12px; margin-bottom: 12px;">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                            <span style="display:inline-flex; align-items:center; justify-content:center; width:40px; height:26px; border-radius:6px; background:#f7d8c8; color:#cf6b2a; font-size:12px; font-weight:900;">Day {{ $day['day'] }}</span>
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

            <aside class="group-sidebar sticky">
                <div class="group-price">$ {{ number_format((float) $group['price'], 2) }}</div>

                <form method="GET" action="{{ url()->current() }}" style="margin-bottom:12px;">
                    @php
                        $reqTraveling = request()->query('traveling_date') ?: request()->query('check_in') ?: '';
                        $travNative = '';
                        try {
                            if (!empty($reqTraveling)) {
                                $travNative = \Carbon\Carbon::createFromFormat('d/m/Y', $reqTraveling)->format('Y-m-d');
                            }
                        } catch (\Exception $e) {
                            try {
                                if (!empty($reqTraveling)) {
                                    $travNative = \Carbon\Carbon::parse($reqTraveling)->format('Y-m-d');
                                }
                            } catch (\Exception $e) {
                                $travNative = '';
                            }
                        }
                    @endphp

                    <div class="search-controls">
                        <div class="search-row">
                            <div class="travel-date" style="flex:1;">
                                <label class="guest-label">Travelling Date</label>
                                <input type="date" name="traveling_date" id="traveling-date-native-detail" value="{{ $travNative }}">
                            </div>

                            <div style="flex:1;">
                                <label class="guest-label">Guests</label>
                                <div class="guests-panel">
                                    <div class="guest-group">
                                        <div class="guest-label">Adult</div>
                                        <button type="button" class="guest-btn count-btn-decrement" data-target="adults">−</button>
                                        <input type="text" name="adults" value="{{ request()->query('adults', 2) }}" readonly class="guest-input">
                                        <button type="button" class="guest-btn count-btn-increment" data-target="adults">+</button>
                                    </div>

                                    <div class="guest-group">
                                        <div class="guest-label">Children</div>
                                        <button type="button" class="guest-btn count-btn-decrement" data-target="children">−</button>
                                        <input type="text" name="children" value="{{ request()->query('children', 0) }}" readonly class="guest-input">
                                        <button type="button" class="guest-btn count-btn-increment" data-target="children">+</button>
                                    </div>

                                    <div class="guest-group">
                                        <div class="guest-label">Infants</div>
                                        <button type="button" class="guest-btn count-btn-decrement" data-target="infants">−</button>
                                        <input type="text" name="infants" value="{{ request()->query('infants', 0) }}" readonly class="guest-input">
                                        <button type="button" class="guest-btn count-btn-increment" data-target="infants">+</button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="update-search-btn">Update search</button>
                            </div>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('frontend.booking.cart.add') }}">
                    @csrf
                    <input type="hidden" name="type" value="group">
                    <input type="hidden" name="group_id" value="{{ $group['id'] }}">
                    <input type="hidden" name="group_name" value="{{ $group['name'] }}">
                    <input type="hidden" name="group_total_price" id="group_total_price" value="{{ $group['price'] }}">
                    <input type="hidden" name="currency" value="USD">
                    <input type="hidden" name="group_image" value="{{ $group['image'] }}">
                    <input type="hidden" name="nights" value="{{ $group['no_of_nights'] }}">
                    <input type="hidden" name="days" value="{{ $group['no_of_days'] }}">
                    @php
                        $groupStartDate = request()->query('traveling_date') ?: (request()->query('check_in') ?: null);
                    @endphp
                    @if(!empty($groupStartDate))
                        <input type="hidden" name="group_start_date" value="{{ $groupStartDate }}">
                    @endif
                    @php
                        $groupAdults = max(1, (int) request()->query('adults', 2));
                        $groupChildren = max(0, (int) request()->query('children', 0));
                        $groupInfants = max(0, (int) request()->query('infants', 0));
                    @endphp
                    <input type="hidden" name="adults" value="{{ $groupAdults }}">
                    <input type="hidden" name="children" value="{{ $groupChildren }}">
                    <input type="hidden" name="infants" value="{{ $groupInfants }}">
                    <button type="submit" class="add-to-cart-btn">Add to cart</button>
                </form>
            </aside>
        </div>

        <div class="group-tab-panel" id="tab-policies" style="display:none; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:22px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            @php
                $policyRows = [
                    'cancellation' => ['label' => 'Cancellation'],
                    'amendments' => ['label' => 'Amendments'],
                    'postponement' => ['label' => 'Postponement'],
                    'payment' => ['label' => 'Payment'],
                    'refund' => ['label' => 'Refund'],
                    'security_deposit' => ['label' => 'Security Deposit'],
                    'house_rules' => ['label' => 'House & Gen. Rules'],
                ];
                $effectivePolicy = $group['effective_policy'] ?? [];
            @endphp

            @if(!empty($effectivePolicy))
                <div style="border:1px solid #e4e7eb;border-radius:10px;overflow:hidden;background:#fff;">
                    <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
                        <thead style="background:#f7f7f7;">
                            <tr>
                                <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:10%;font-size:13px;color:#333;">Policy</th>
                                <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:15%;font-size:13px;color:#333;">Details</th>
                                <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:20%;font-size:13px;color:#333;">Before Deadline</th>
                                <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:20%;font-size:13px;color:#333;">After Deadline</th>
                                <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:24%;font-size:13px;color:#333;">Notes</th>
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
                                <div style="font-weight:600;color:#333;margin-bottom:8px;">Booking Notes</div>
                                <div style="padding:10px 12px;border:1px solid #dfeaf9;border-radius:6px;background:#fff;white-space:pre-wrap;">{{ $effectivePolicy['booking_notes'] }}</div>
                            </div>
                        @endif

                        @if(!empty($effectivePolicy['package_notes']))
                            <div style="border:1px solid #e4e7eb;border-radius:10px;padding:12px 14px;background:#f7f7f7;">
                                <div style="font-weight:600;color:#333;margin-bottom:8px;">Package Notes</div>
                                <div style="padding:10px 12px;border:1px solid #dfeaf9;border-radius:6px;background:#fff;white-space:pre-wrap;">{{ $effectivePolicy['package_notes'] }}</div>
                            </div>
                        @endif
                    </div>
                @endif
            @else
                <p style="margin:0; color:#475467;">Policy details are not defined yet.</p>
            @endif
        </div>

        <div class="group-tab-panel" id="tab-summary" style="display:none; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:22px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            @if(!empty($group['full_description']))
                <div style="color:#475467; line-height:1.8; white-space:pre-line;">{{ $group['full_description'] }}</div>
            @else
                <p style="margin:0; color:#475467;">Summary is not available for this group package.</p>
            @endif
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.group-tab');
        const panels = document.querySelectorAll('.group-tab-panel');

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
