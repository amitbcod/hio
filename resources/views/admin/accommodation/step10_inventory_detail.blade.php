@extends('layouts.admin')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <h2 style="font-weight:700;margin:0;">Inventory Allotment Details</h2>
                        <a href="{{ route('operator.accommodation.step10.show', $accommodation->id) }}" style="padding:8px 12px;background:#f0f0f0;color:#333;border-radius:4px;text-decoration:none;">← Back</a>
                    </div>
                </div>

                <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;">
                    <h4 style="margin-top:0;margin-bottom:20px;font-weight:600;border-bottom:2px solid #19b5b5;padding-bottom:12px;">
                        Allotment for {{ $inventory->date->format('F d, Y') }}
                    </h4>

                    {{-- Room/Unit Information --}}
                    <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:20px;">
                        <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Property/Unit Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label style="color:#666;font-size:12px;font-weight:600;">Room/Unit:</label>
                                <p style="margin:4px 0;font-size:14px;">
                                    @if($inventory->room)
                                        <strong>{{ $inventory->room->room_name }}</strong> ({{ $inventory->room->room_type }})
                                    @else
                                        <strong>Property-wide Allotment</strong> (All Rooms)
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label style="color:#666;font-size:12px;font-weight:600;">Date:</label>
                                <p style="margin:4px 0;font-size:14px;"><strong>{{ $inventory->date->format('l, F d, Y') }}</strong></p>
                            </div>
                        </div>
                    </div>

                    {{-- Inventory Numbers --}}
                    <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:20px;">
                        <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Inventory Numbers</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <label style="color:#666;font-size:12px;font-weight:600;">Sellable Units:</label>
                                <p style="margin:4px 0;font-size:14px;"><strong>{{ $inventory->sellable_units }}</strong></p>
                            </div>
                            <div class="col-md-3">
                                <label style="color:#666;font-size:12px;font-weight:600;">Sold/Confirmed:</label>
                                <p style="margin:4px 0;font-size:14px;"><strong>{{ $inventory->sold_units }}</strong></p>
                            </div>
                            <div class="col-md-3">
                                <label style="color:#666;font-size:12px;font-weight:600;">Available Units:</label>
                                <p style="margin:4px 0;font-size:14px;">
                                    <strong style="background:#e8f5f5;padding:2px 6px;border-radius:4px;color:#19b5b5;">{{ $inventory->available_units }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Length of Stay & Release Policy --}}
                    @if($inventory->minimum_nights || $inventory->days_before_release)
                    <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:20px;">
                        <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Length of Stay & Release Policy</h6>
                        <div class="row">
                            @if($inventory->minimum_nights)
                            <div class="col-md-6">
                                <label style="color:#666;font-size:12px;font-weight:600;">Minimum Nights:</label>
                                <p style="margin:4px 0;font-size:14px;"><strong>{{ $inventory->minimum_nights }} nights</strong></p>
                            </div>
                            @endif
                            @if($inventory->days_before_release)
                            <div class="col-md-6">
                                <label style="color:#666;font-size:12px;font-weight:600;">Days Before Check-in to Release:</label>
                                <p style="margin:4px 0;font-size:14px;"><strong>{{ $inventory->days_before_release }} days</strong></p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Availability Controls --}}
                    <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:20px;">
                        <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Availability Controls</h6>
                        <div style="display:flex;gap:16px;flex-wrap:wrap;">
                            <div>
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" {{ $inventory->sell_and_report ? 'checked' : '' }} disabled style="cursor:not-allowed;">
                                    <span>Active Sell & Report Status</span>
                                </label>
                                @if($inventory->sell_and_report)
                                    <span style="display:inline-block;background:#d4edda;color:#155724;padding:2px 6px;border-radius:3px;font-size:11px;margin-top:4px;">✓ Enabled</span>
                                @else
                                    <span style="display:inline-block;background:#f8d7da;color:#721c24;padding:2px 6px;border-radius:3px;font-size:11px;margin-top:4px;">✗ Disabled</span>
                                @endif
                            </div>
                            <div>
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" {{ $inventory->stop_sell ? 'checked' : '' }} disabled style="cursor:not-allowed;">
                                    <span>Blackout Period - Stop Sell</span>
                                </label>
                                @if($inventory->stop_sell)
                                    <span style="display:inline-block;background:#f8d7da;color:#721c24;padding:2px 6px;border-radius:3px;font-size:11px;margin-top:4px;">⚠ Active</span>
                                @else
                                    <span style="display:inline-block;background:#e2e3e5;color:#383d41;padding:2px 6px;border-radius:3px;font-size:11px;margin-top:4px;">— Inactive</span>
                                @endif
                            </div>
                            <div>
                                <label style="display:flex;align-items:center;gap:8px;">
                                    <input type="checkbox" {{ $inventory->block_arrivals ? 'checked' : '' }} disabled style="cursor:not-allowed;">
                                    <span>Block Arrivals</span>
                                </label>
                                @if($inventory->block_arrivals)
                                    <span style="display:inline-block;background:#fff3cd;color:#856404;padding:2px 6px;border-radius:3px;font-size:11px;margin-top:4px;">⚠ Blocked</span>
                                @else
                                    <span style="display:inline-block;background:#e2e3e5;color:#383d41;padding:2px 6px;border-radius:3px;font-size:11px;margin-top:4px;">— Open</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Blackout Dates --}}
                    @if($inventory->stop_sell && !empty($inventory->blackout_dates))
                    <div style="background:#fff3cd;border:1px solid #ffc107;padding:16px;border-radius:8px;margin-bottom:20px;">
                        <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;color:#856404;">📅 Blackout Dates</h6>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            @foreach($inventory->blackout_dates as $date)
                                <span style="background:#ffc107;color:#fff;padding:4px 10px;border-radius:4px;font-size:12px;font-weight:600;">
                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Block Days --}}
                    @if($inventory->block_arrivals && $inventory->block_days)
                    <div style="background:#e7f3ff;border:1px solid #007bff;padding:16px;border-radius:8px;margin-bottom:20px;">
                        <h6 style="margin-top:0;font-weight:600;margin-bottom:8px;color:#004085;">🚫 Block Days</h6>
                        <p style="margin:0;font-size:14px;color:#004085;">
                            Arrivals blocked for <strong>{{ $inventory->block_days }} day(s)</strong> on/around selected dates
                        </p>
                    </div>
                    @endif

                    {{-- Booking Options --}}
                    <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:20px;">
                        <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Booking Options</h6>
                        <div>
                            <label style="color:#666;font-size:12px;font-weight:600;">Booking Type:</label>
                            <p style="margin:4px 0;font-size:14px;">
                                <span style="background:{{ $inventory->instant_on_request === 'Instant' ? '#d4edda' : '#e7f3ff' }};color:{{ $inventory->instant_on_request === 'Instant' ? '#155724' : '#004085' }};padding:4px 8px;border-radius:4px;display:inline-block;">
                                    <strong>{{ $inventory->instant_on_request }}</strong>
                                </span>
                            </p>
                        </div>
                    </div>

                    {{-- Metadata --}}
                    <div style="background:#f0f0f0;padding:12px;border-radius:8px;font-size:11px;color:#666;">
                        <div style="margin-bottom:4px;"><strong>Created:</strong> {{ $inventory->created_at->format('M d, Y h:i A') }}</div>
                        <div><strong>Last Updated:</strong> {{ $inventory->updated_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>

                {{-- Navigation --}}
                <div style="display:flex;justify-content:space-between;gap:12px;">
                    <a href="{{ route('operator.accommodation.step10.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">← Back to Allotments</a>
                    <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">Back to Property</a>
                </div>
            </div>
        </div>
    </div>
@endsection
