@extends('layouts.admin')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                @include('operator.accommodation._steps_sidebar')
            </div>

            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <div>
                        <h2 style="font-weight:700;margin:0;">Accommodation Booking Report</h2>
                        <p style="margin:6px 0 0 0;color:#666;font-size:13px;">{{ $accommodation->property_name }} · {{ $monthStart->format('F Y') }}</p>
                    </div>
                    <form method="GET" action="{{ route('operator.accommodation.booking-report', $accommodation->id) }}" style="display:flex;align-items:center;gap:8px;">
                        <label for="month" style="font-weight:600;font-size:13px;margin:0;">Month</label>
                        <input type="month" id="month" name="month" value="{{ $selectedMonth }}" class="form-control" style="width:180px;">
                        <button type="submit" class="btn" style="background:#19b5b5;color:#fff;">View</button>
                    </form>
                </div>

                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
                        <span style="padding:4px 8px;border-radius:12px;background:#eaf8ef;color:#1e8449;font-size:12px;font-weight:600;">Available</span>
                        <span style="padding:4px 8px;border-radius:12px;background:#fff8e8;color:#f39c12;font-size:12px;font-weight:600;">Partially Used</span>
                        <span style="padding:4px 8px;border-radius:12px;background:#fff2e5;color:#d35400;font-size:12px;font-weight:600;">Fully Used</span>
                        <span style="padding:4px 8px;border-radius:12px;background:#fdecec;color:#dc3545;font-size:12px;font-weight:600;">Blocked</span>
                        <span style="padding:4px 8px;border-radius:12px;background:#f2f2f2;color:#6c757d;font-size:12px;font-weight:600;">No Inventory</span>
                    </div>

                    @if(empty($roomTypeMatrix))
                        <div style="padding:14px;border:1px solid #f0e6a6;background:#fffbe6;border-radius:8px;color:#7c6a00;">
                            No room type data found for this accommodation.
                        </div>
                    @else
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;min-width:1200px;font-size:12px;">
                                <thead>
                                    <tr>
                                        <th rowspan="2" style="padding:8px;background:#0d5ea8;color:#fff;border:1px solid #c8d5e6;text-align:left;min-width:130px;">Room Type</th>
                                        <th rowspan="2" style="padding:8px;background:#0d5ea8;color:#fff;border:1px solid #c8d5e6;text-align:center;min-width:80px;">Metric</th>
                                        @foreach($days as $day)
                                            <th style="padding:6px 7px;background:#0d5ea8;color:#fff;border:1px solid #c8d5e6;text-align:center;min-width:34px;">{{ $day['day'] }}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach($days as $day)
                                            <th style="padding:4px 6px;background:#2d79be;color:#e9f3ff;border:1px solid #c8d5e6;text-align:center;font-size:10px;">
                                                {{ \Carbon\Carbon::parse($day['date'])->format('D') }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roomTypeMatrix as $roomType)
                                        <tr>
                                            <td rowspan="2" style="padding:8px;border:1px solid #d6dfe8;background:#edf4fb;font-weight:700;vertical-align:middle;">
                                                {{ $roomType['label'] }}
                                            </td>
                                            <td style="padding:8px;border:1px solid #d6dfe8;background:#f6fbff;font-weight:700;text-align:center;">Allotment</td>
                                            @foreach($roomType['days'] as $cell)
                                                <td style="padding:6px;border:1px solid #d6dfe8;background:#f6fbff;text-align:center;{{ $cell['is_today'] ? 'box-shadow: inset 0 0 0 2px #19b5b5;' : '' }}">
                                                    <strong>{{ $cell['sellable_units'] }}</strong>
                                                </td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td style="padding:8px;border:1px solid #d6dfe8;background:#fff;font-weight:700;text-align:center;">Used</td>
                                            @foreach($roomType['days'] as $cell)
                                                @php
                                                    $cellBg = '#ffffff';
                                                    $cellColor = '#2f3a4a';
                                                    if ($cell['status_key'] === 'blocked') {
                                                        $cellBg = '#fdecec';
                                                        $cellColor = '#aa2b38';
                                                    } elseif ($cell['status_key'] === 'no_inventory') {
                                                        $cellBg = '#f2f2f2';
                                                        $cellColor = '#666';
                                                    } elseif ($cell['status_key'] === 'full') {
                                                        $cellBg = '#fff2e5';
                                                        $cellColor = '#a65500';
                                                    } elseif ($cell['status_key'] === 'partial') {
                                                        $cellBg = '#fff8e8';
                                                        $cellColor = '#9b6b00';
                                                    } elseif ($cell['status_key'] === 'available') {
                                                        $cellBg = '#eaf8ef';
                                                        $cellColor = '#1e6b3c';
                                                    }
                                                @endphp
                                                <td style="padding:4px;border:1px solid #d6dfe8;background:{{ $cellBg }};color:{{ $cellColor }};text-align:center;font-weight:700;line-height:1.15;{{ $cell['is_today'] ? 'box-shadow: inset 0 0 0 2px #19b5b5;' : '' }}">
                                                    <div>{{ $cell['used_units'] }}</div>
                                                    @if($cell['confirmed_units'] > 0 || $cell['pending_units'] > 0)
                                                        <div style="font-size:9px;font-weight:600;color:#5b6575;">C{{ $cell['confirmed_units'] }}/P{{ $cell['pending_units'] }}</div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:16px;">
                    <a href="{{ route('operator.accommodation.step10.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">← Back to Step 10</a>
                    <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">Back to Property</a>
                </div>
            </div>
        </div>
    </div>
@endsection
