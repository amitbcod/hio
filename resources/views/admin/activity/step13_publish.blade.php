@extends('layouts.admin')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <!-- Sidebar -->
            @php $currentStep = 13; @endphp
            <div class="col-md-3">
                @include('operator.activity._steps_sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Header -->
                <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:24px;">
                    <div style="display:flex;justify-content:space-between;align-items:start;">
                        <div>
                            <h4 style="font-weight:700;color:#333;margin:0 0 8px 0;">Step 13: Publish</h4>
                            <p style="margin:0;font-size:14px;color:#666;">{{ $activity->activity_name }}</p>
                        </div>
                        <div style="text-align:right;">
                            <p style="margin:0;font-size:12px;color:#999;">Activity ID: {{ $activity->id }}</p>
                            @if($activity->approval_status)
                                <span style="display:inline-block;margin-top:8px;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;
                                    background:{{ $activity->approval_status === 'Approved' ? '#e8f5e9' : ($activity->approval_status === 'Pending' ? '#fff3e0' : ($activity->approval_status === 'Rejected' ? '#ffebee' : '#f5f5f5')) }};
                                    color:{{ $activity->approval_status === 'Approved' ? '#2e7d32' : ($activity->approval_status === 'Pending' ? '#f57c00' : ($activity->approval_status === 'Rejected' ? '#c62828' : '#666')) }};">
                                    {{ $activity->approval_status }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                @if($errors->any())
                <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:20px;color:#c62828;">
                    <h5 style="margin-top:0;color:#c62828;">❌ Validation Errors:</h5>
                    @foreach($errors->all() as $error)
                        <div style="margin-bottom:4px;">• {{ $error }}</div>
                    @endforeach
                </div>
                @endif

                @if(session('success'))
                <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:20px;color:#2e7d32;">
                    <strong>✓ {{ session('success') }}</strong>
                </div>
                @endif

                @if(session('error'))
                <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:20px;color:#c62828;">
                    <strong>❌ {{ session('error') }}</strong>
                </div>
                @endif

                <!-- Approval Status Card -->
                <div style="background:#fff;border-radius:12px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,0.04);text-align:center;">
                    @if($activity->approval_status === 'Approved')
                        <!-- Approved State -->
                        <div style="max-width:500px;margin:0 auto;">
                            <div style="font-size:48px;margin-bottom:16px;">✅</div>
                            <h3 style="color:#2e7d32;font-weight:600;margin-bottom:12px;">Activity Approved!</h3>
                            <p style="color:#666;margin-bottom:8px;">Your activity has been approved and is now live.</p>
                            <p style="font-size:13px;color:#999;">Approved on: {{ $activity->approved_at ? $activity->approved_at->format('M d, Y h:i A') : 'N/A' }}</p>
                            @if($activity->approval_notes)
                                <div style="background:#e8f5e9;border-left:4px solid #28a745;padding:16px;margin-top:20px;text-align:left;border-radius:4px;">
                                    <strong style="color:#2e7d32;">Admin Notes:</strong>
                                    <p style="margin:8px 0 0 0;color:#555;font-size:13px;">{{ $activity->approval_notes }}</p>
                                </div>
                            @endif
                        </div>
                    @elseif($activity->approval_status === 'Pending')
                        <!-- Pending State -->
                        <div style="max-width:500px;margin:0 auto;">
                            <div style="font-size:48px;margin-bottom:16px;">⏳</div>
                            <h3 style="color:#f57c00;font-weight:600;margin-bottom:12px;">Pending Approval</h3>
                            <p style="color:#666;margin-bottom:8px;">Your activity has been submitted and is awaiting admin approval.</p>
                            <p style="font-size:13px;color:#999;">Submitted on: {{ $activity->submitted_for_approval_at ? $activity->submitted_for_approval_at->format('M d, Y h:i A') : 'N/A' }}</p>
                            <div style="background:#fff3e0;border-left:4px solid #f57c00;padding:16px;margin-top:20px;text-align:left;border-radius:4px;">
                                <strong style="color:#f57c00;">⚠ What's Next?</strong>
                                <p style="margin:8px 0 0 0;color:#555;font-size:13px;">Our admin team will review your submission. You will receive a notification once it's approved or if any changes are required.</p>
                            </div>
                        </div>
                    @elseif($activity->approval_status === 'Rejected')
                        <!-- Rejected State -->
                        <div style="max-width:500px;margin:0 auto;">
                            <div style="font-size:48px;margin-bottom:16px;">❌</div>
                            <h3 style="color:#c62828;font-weight:600;margin-bottom:12px;">Changes Required</h3>
                            <p style="color:#666;margin-bottom:20px;">Your submission needs some modifications before approval.</p>
                            @if($activity->approval_notes)
                                <div style="background:#ffebee;border-left:4px solid #c62828;padding:16px;margin-bottom:20px;text-align:left;border-radius:4px;">
                                    <strong style="color:#c62828;">Admin Feedback:</strong>
                                    <p style="margin:8px 0 0 0;color:#555;font-size:13px;">{{ $activity->approval_notes }}</p>
                                </div>
                            @endif
                            <form method="POST" action="{{ route('operator.activity.submit-approval', $activity->id) }}">
                                @csrf
                                <button type="submit" style="padding:14px 32px;background:#19b5b5;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600;box-shadow:0 2px 8px rgba(25,181,181,0.3);">
                                    Resubmit for Approval
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Draft State -->
                        <div style="max-width:500px;margin:0 auto;">
                            <div style="font-size:64px;margin-bottom:20px;">🚀</div>
                            <h3 style="color:#333;font-weight:600;margin-bottom:12px;">Ready to Publish?</h3>
                            <p style="color:#666;margin-bottom:24px;">You've completed all the required steps. Submit your activity for admin approval to make it live on the platform.</p>
                            
                            <div style="background:#e3f2fd;border-left:4px solid #2196f3;padding:16px;margin-bottom:24px;text-align:left;border-radius:4px;">
                                <strong style="color:#1565c0;">📋 Before You Submit:</strong>
                                <ul style="margin:12px 0 0 0;padding-left:20px;color:#555;font-size:13px;line-height:1.8;">
                                    <li>All information is accurate and complete</li>
                                    <li>Photos and media are high quality</li>
                                    <li>Pricing and policies are clearly defined</li>
                                    <li>Legal compliance documents are valid</li>
                                </ul>
                            </div>

                            <form method="POST" action="{{ route('operator.activity.submit-approval', $activity->id) }}">
                                @csrf
                                <button type="submit" style="padding:16px 48px;background:#28a745;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:16px;font-weight:600;box-shadow:0 4px 12px rgba(40,167,69,0.3);transition:all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 16px rgba(40,167,69,0.4)';" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(40,167,69,0.3)';">
                                    📤 Send for Approval
                                </button>
                                <p style="margin-top:16px;font-size:12px;color:#999;">Your activity will be reviewed by the admin team</p>
                            </form>

                            <div style="margin-top:32px;padding-top:24px;border-top:1px solid #e0e0e0;">
                                <a href="{{ route('operator.activity.show', $activity->id) }}" style="color:#2196f3;text-decoration:none;font-size:13px;font-weight:500;">
                                    ← Back to Activity Overview
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
