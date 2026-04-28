@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div id="sidebar" class="col-md-3 net-section">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9">
                {{-- Header --}}
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h2 style="font-weight:700;margin:0;">My Activities</h2>
                        <p style="margin:8px 0 0 0;color:#666;">Manage your tours, experiences, and services</p>
                    </div>
                    <a href="{{ route('operator.activity.create') }}" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;text-decoration:none;font-size:14px;font-weight:600;">
                        + Create New Activity
                    </a>
                </div>

                @if(session('success'))
                <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;">
                    <strong>✓ {{ session('success') }}</strong>
                </div>
                @endif

                @if($activities->count() > 0)
                    {{-- Activity List Table --}}
                    <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);overflow:hidden;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f5f5f5;border-bottom:1px solid #e0e0e0;">
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Activity Name</th>
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Service Type</th>
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Status</th>
                                    <th style="padding:16px;text-align:left;font-weight:600;font-size:13px;color:#666;">Progress</th>
                                    <th style="padding:16px;text-align:center;font-weight:600;font-size:13px;color:#666;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activities as $activity)
                                <tr style="border-bottom:1px solid #e0e0e0;transition:background 0.2s;">
                                    <td style="padding:16px;">
                                        <strong>{{ $activity->activity_name }}</strong>
                                        <br>
                                        <small style="color:#999;">ID: {{ $activity->service_id }}</small>
                                    </td>
                                    <td style="padding:16px;">{{ $activity->service_type }}</td>
                                    <td style="padding:16px;">
                                        <span style="display:inline-block;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;background:{{ $activity->status === 'Draft' ? '#fff3e0' : ($activity->status === 'In Review' ? '#e3f2fd' : '#e8f5e9') }};color:{{ $activity->status === 'Draft' ? '#e65100' : ($activity->status === 'In Review' ? '#1565c0' : '#2e7d32') }};">
                                            {{ $activity->status }}
                                        </span>
                                    </td>
                                    <td style="padding:16px;">
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div style="width:100px;height:6px;background:#e0e0e0;border-radius:3px;overflow:hidden;">
                                                <div style="width:{{ $activity->step1_basic ? '100' : '0' }}%;height:100%;background:#19b5b5;transition:width 0.3s;"></div>
                                            </div>
                                            <small style="color:#666;white-space:nowrap;">{{ $activity->step1_basic ? '1/3' : '0/3' }}</small>
                                        </div>
                                    </td>
                                    <td style="padding:16px;text-align:center;">
                                        <a href="{{ route('operator.activity.show', $activity->id) }}" style="display:inline-block;padding:6px 12px;background:#f0f0f0;text-decoration:none;border-radius:3px;font-size:12px;color:#333;margin-right:4px;">
                                            View
                                        </a>
                                        @if($activity->step1_basic)
                                        <a href="{{ route('operator.activity.step1.show', $activity->id) }}" style="display:inline-block;padding:6px 12px;background:#e8f4f8;text-decoration:none;border-radius:3px;font-size:12px;color:#19b5b5;">
                                            Edit
                                        </a>
                                        @else
                                        <a href="{{ route('operator.activity.step1.show', $activity->id) }}" style="display:inline-block;padding:6px 12px;background:#fff3cd;text-decoration:none;border-radius:3px;font-size:12px;color:#856404;">
                                            Complete
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div style="margin-top:20px;">
                        {{ $activities->links() }}
                    </div>
                @else
                    {{-- Empty State --}}
                    <div style="background:#fff;border-radius:16px;padding:60px 20px;text-align:center;box-shadow:0 2px 16px rgba(0,0,0,0.07);">
                        <div style="font-size:48px;margin-bottom:16px;">🎯</div>
                        <h4 style="color:#333;margin:0 0 8px 0;">No Activities Yet</h4>
                        <p style="color:#999;margin:0 0 20px 0;">Get started by creating your first activity or experience.</p>
                        <a href="{{ route('operator.activity.create') }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:12px 24px;border-radius:4px;text-decoration:none;font-weight:600;">
                            Create Your First Activity
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
