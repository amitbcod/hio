@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div id="sidebar" class="col-md-3 net-section">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h2 style="font-weight:700;margin:0;">{{ $serviceType === 'accommodation' ? 'Accommodation Feedback' : 'Activity Feedback' }}</h2>
                        <p style="margin:8px 0 0 0;color:#666;">Feedback details for {{ $service->property_name ?? $service->activity_name }}.</p>
                    </div>
                    <a href="{{ route('operator.feedback.index') }}" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;text-decoration:none;font-size:14px;font-weight:600;">
                        Back to Feedback
                    </a>
                </div>

                @if($reviewItems->count() > 0)
                    @foreach($reviewItems as $item)
                        @php
                            $trip = $item->parentReview->trip;
                            $traveler = optional($trip->traveler);
                            $booking = $serviceType === 'accommodation'
                                ? optional($trip->accommodationBookings)->firstWhere('id', $item->service_id)
                                : optional($trip->activityBookings)->firstWhere('id', $item->service_id);
                            $criteria = is_array($item->criteria) ? $item->criteria : [];
                        @endphp
                        <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);padding:20px;margin-bottom:20px;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                                <div>
                                    <h4 style="margin:0 0 6px 0;">{{ $service->property_name ?? $service->activity_name }}</h4>
                                    <p style="margin:0;color:#666;">
                                        Trip #{{ $trip->id }} · {{ $traveler->full_name ?? $traveler->email ?? 'Unknown traveler' }}
                                    </p>
                                </div>
                                <div style="text-align:right;">
                                    <p style="margin:0;color:#999;font-size:13px;">Submitted {{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : 'N/A' }}</p>
                                    @if($serviceType === 'accommodation' && optional($booking)->check_in_date)
                                        <p style="margin:0;color:#999;font-size:13px;">Check-in: {{ optional($booking)->check_in_date->format('Y-m-d') }}</p>
                                    @elseif($serviceType === 'activity' && optional($booking)->activity_date)
                                        <p style="margin:0;color:#999;font-size:13px;">Activity Date: {{ optional($booking)->activity_date->format('Y-m-d') }}</p>
                                    @endif
                                </div>
                            </div>

                            <div style="margin-top:18px;">
                                @if(count($criteria) > 0)
                                    <table style="width:100%;border-collapse:collapse;margin-bottom:18px;">
                                        <tbody>
                                            @foreach($criteria as $key => $value)
                                                <tr style="border-bottom:1px solid #f0f0f0;">
                                                    <td style="padding:12px 10px;width:40%;font-weight:600;color:#444;">{{ ucwords(str_replace(['_','-'], ' ', $key)) }}</td>
                                                    <td style="padding:12px 10px;color:#555;">{{ $value ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif

                                <div style="background:#f8f8f8;border-radius:12px;padding:16px;">
                                    <p style="margin:0 0 8px 0;font-weight:600;color:#444;">Comments</p>
                                    <p style="margin:0;color:#555;">{{ $item->review ?? 'No comments provided.' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="background:#fff;border-radius:16px;padding:60px 20px;text-align:center;box-shadow:0 2px 16px rgba(0,0,0,0.07);">
                        <div style="font-size:48px;margin-bottom:16px;">📝</div>
                        <h4 style="color:#333;margin:0 0 8px 0;">No feedback found for this service</h4>
                        <p style="color:#999;margin:0;">There are no submitted reviews for this service yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
