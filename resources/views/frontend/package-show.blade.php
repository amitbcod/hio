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
                <button type="button" class="package-tab active" data-tab="itinerary" style="padding: 12px 0; border:none; background:none; border-bottom: 3px solid #1f9ae5; color:#1f9ae5; font-size:14px; font-weight:800; letter-spacing: .12em; text-transform: uppercase; cursor:pointer;">Itinerary</button>
                <button type="button" class="package-tab" data-tab="policies" style="padding: 12px 0; border:none; background:none; color:#475467; font-size:14px; font-weight:800; letter-spacing: .12em; text-transform: uppercase; cursor:pointer;">Policies</button>
                <button type="button" class="package-tab" data-tab="summary" style="padding: 12px 0; border:none; background:none; color:#475467; font-size:14px; font-weight:800; letter-spacing: .12em; text-transform: uppercase; cursor:pointer;">Summary</button>
            </div>
            <div style="font-size: 14px; color:#475467;">Share</div>
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
                    <span style="display:inline-flex; align-items:center; padding:8px 12px; border:1px solid #d0d5dd; border-radius:999px; color:#475467; background:#f5f7fa; font-size: 12px; font-weight:800; letter-spacing: .08em; text-transform: uppercase;">{{ $hotelCount }} Hotel{{ $hotelCount === 1 ? '' : 's' }}</span>
                    <span style="display:inline-flex; align-items:center; padding:8px 12px; border:1px solid #d0d5dd; border-radius:999px; color:#475467; background:#f5f7fa; font-size: 12px; font-weight:800; letter-spacing: .08em; text-transform: uppercase;">{{ $activityCount }} Activity{{ $activityCount === 1 ? '' : 'ies' }}</span>
                    <span style="display:inline-flex; align-items:center; padding:8px 12px; border:1px solid #d0d5dd; border-radius:999px; color:#475467; background:#f5f7fa; font-size: 12px; font-weight:800; letter-spacing: .08em; text-transform: uppercase;">{{ $mealCount }} Meal{{ $mealCount === 1 ? '' : 's' }}</span>
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
                    </div>
                @endforeach
            </div>

            <aside style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px 18px 16px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
                <div style="font-size:30px; font-weight:800; color:#1f2a37; margin-bottom: 18px;">$ {{ number_format((float) $package['price'], 2) }}</div>
                <button type="button" style="width:100%; background:#f39b4a; border:none; border-radius:8px; color:#fff; font-size:18px; font-weight:800; padding:14px 16px; cursor:pointer; text-transform: uppercase; letter-spacing: .05em;">Proceed to payment</button>
            </aside>
        </div>

        <div class="package-tab-panel" id="tab-policies" style="display:none; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:22px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            @if(!empty($package['inclusions']) || !empty($package['exclusions']))
                <div style="display:grid; gap:18px;">
                    @if(!empty($package['inclusions']))
                        <div>
                            <h3 style="margin:0 0 8px; font-size:18px; color:#1f2a37;">Inclusions</h3>
                            <div style="color:#475467; line-height:1.8; white-space:pre-line;">{{ $package['inclusions'] }}</div>
                        </div>
                    @endif
                    @if(!empty($package['exclusions']))
                        <div>
                            <h3 style="margin:0 0 8px; font-size:18px; color:#1f2a37;">Exclusions</h3>
                            <div style="color:#475467; line-height:1.8; white-space:pre-line;">{{ $package['exclusions'] }}</div>
                        </div>
                    @endif
                </div>
            @else
                <p style="margin:0; color:#475467;">Package policies are not yet defined for this listing.</p>
            @endif
        </div>

        <div class="package-tab-panel" id="tab-summary" style="display:none; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:22px; box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            @if(!empty($package['full_description']))
                <div style="color:#475467; line-height:1.8; white-space:pre-line;">{{ $package['full_description'] }}</div>
            @else
                <p style="margin:0; color:#475467;">No summary available for this package.</p>
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
