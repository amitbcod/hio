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
                        <h2 style="font-weight:700;margin:0;">Service Feedback</h2>
                        <p style="margin:8px 0 0 0;color:#666;">View feedback listings for your accommodations and activities.</p>
                    </div>
                </div>

                @if(session('success'))
                    <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;">
                        <strong>✓ {{ session('success') }}</strong>
                    </div>
                @endif

                @if($reviews->count() > 0)
                    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);overflow:hidden;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f5f5f5;border-bottom:1px solid #e0e0e0;">
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Trip ID</th>
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Traveler Name</th>
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Service</th>
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Type</th>
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Rating</th>
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Review Date</th>
                                    <th style="padding:16px;text-align:center;font-weight:600;font-size:13px;color:#666;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reviews as $review)
                                    <tr style="border-bottom:1px solid #e0e0e0;transition:background 0.2s;">
                                        <td style="padding:16px;">
                                            <strong>#{{ $review['trip_id'] }}</strong>
                                        </td>
                                        <td style="padding:16px;">
                                            {{ $review['traveler_name'] }}
                                        </td>
                                        <td style="padding:16px;">
                                            {{ $review['service_name'] }}
                                        </td>
                                        <td style="padding:16px; text-transform: capitalize;">{{ $review['service_type'] }}</td>
                                        <td style="padding:16px;">
                                            {{ $review['rating'] }}
                                        </td>
                                        <td style="padding:16px;">{{ $review['review_date']->format('Y-m-d H:i') }}</td>
                                        <td style="padding:16px;text-align:center;">
                                            <a href="{{ route('operator.feedback.show', ['service_type' => $review['service_type'], 'service_id' => $review['service_id']]) }}" style="display:inline-block;padding:8px 14px;background:#19b5b5;color:#fff;text-decoration:none;border-radius:4px;font-size:13px;">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="background:#fff;border-radius:16px;padding:60px 20px;text-align:center;box-shadow:0 2px 16px rgba(0,0,0,0.07);">
                        <div style="font-size:48px;margin-bottom:16px;">💬</div>
                        <h4 style="color:#333;margin:0 0 8px 0;">No service feedback yet</h4>
                        <p style="color:#999;margin:0;">Feedback will appear here once travelers complete reviews for your services.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
