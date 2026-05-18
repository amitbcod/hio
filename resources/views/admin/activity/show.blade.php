@extends('layouts.admin')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9">
                {{-- Header Card --}}
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <h2 style="font-weight:700;margin:0;">{{ $activity->activity_name }}</h2>
                            <p style="margin:8px 0 0 0;color:#666;font-size:14px;">
                                Service ID: <strong>{{ $activity->service_id }}</strong> • Service Type: <strong>{{ $activity->service_type }}</strong>
                            </p>
                            <p style="margin:4px 0 0 0;color:#666;font-size:14px;">
                                Status: <strong style="color:{{ $activity->status === 'Draft' ? '#f57c00' : ($activity->status === 'In Review' ? '#1976d2' : '#388e3c') }}">{{ $activity->status }}</strong>
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('operator.activity.index') }}" style="color:#19b5b5;text-decoration:none;font-size:14px;">← Back to Activities</a>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;">
                    <strong>✓ {{ session('success') }}</strong>
                </div>
                @endif

                {{-- Step Progress --}}
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;">
                    <h5 style="font-weight:600;margin:0 0 16px 0;">Activity Setup Progress</h5>
                    
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;">
                        {{-- Step 1: Basic Information --}}
                        <div style="border:2px solid {{ $activity->step1_basic ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ $activity->step1_basic ? '#f1f8f4' : '#fafafa' }};">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;">Step 1: Basic</h6>
                                    <p style="margin:0;font-size:13px;color:#666;">Service details, location, content</p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ $activity->step1_basic ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ $activity->step1_basic ? '✓' : '1' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if($activity->step1_basic)
                                    <a href="{{ route('operator.activity.step1.show', $activity->id) }}" style="display:inline-block;background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('operator.activity.step1.show', $activity->id) }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Complete Step
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Step 2: Management & Communication --}}
                        <div style="border:2px solid {{ $activity->step2_management_communication ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ $activity->step2_management_communication ? '#f1f8f4' : '#fafafa' }};{{ !$activity->step1_basic ? 'opacity:0.5;' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;{{ !$activity->step1_basic ? 'color:#999;' : '' }}">Step 2: Management</h6>
                                    <p style="margin:0;font-size:13px;color:{{ $activity->step1_basic ? '#666' : '#999' }};">Contacts & communication</p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ $activity->step2_management_communication ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ $activity->step2_management_communication ? '✓' : '2' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if(!$activity->step1_basic)
                                    <button disabled style="display:inline-block;background:#e0e0e0;color:#999;padding:8px 16px;border-radius:4px;border:none;font-size:13px;cursor:not-allowed;">
                                        Complete Step 1 First
                                    </button>
                                @elseif($activity->step2_management_communication)
                                    <a href="{{ route('operator.activity.step2.show', $activity->id) }}" style="display:inline-block;background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('operator.activity.step2.show', $activity->id) }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Complete Step
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Step 3: Photos & Media --}}
                        <div style="border:2px solid {{ $activity->step3_photos_media ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ $activity->step3_photos_media ? '#f1f8f4' : '#fafafa' }};{{ (!$activity->step1_basic || !$activity->step2_management_communication) ? 'opacity:0.5;' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;{{ (!$activity->step1_basic || !$activity->step2_management_communication) ? 'color:#999;' : '' }}">Step 3: Photos & Media</h6>
                                    <p style="margin:0;font-size:13px;color:{{ ($activity->step1_basic && $activity->step2_management_communication) ? '#666' : '#999' }};">Images, logo & video</p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ $activity->step3_photos_media ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ $activity->step3_photos_media ? '✓' : '3' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if(!$activity->step1_basic || !$activity->step2_management_communication)
                                    <button disabled style="display:inline-block;background:#e0e0e0;color:#999;padding:8px 16px;border-radius:4px;border:none;font-size:13px;cursor:not-allowed;">
                                        Complete Steps 1 & 2 First
                                    </button>
                                @elseif($activity->step3_photos_media)
                                    <a href="{{ route('operator.activity.step3.show', $activity->id) }}" style="display:inline-block;background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('operator.activity.step3.show', $activity->id) }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Complete Step
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Step 4: Legal & Compliance --}}
                        <div style="border:2px solid {{ $activity->step4_legal_compliance ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ $activity->step4_legal_compliance ? '#f1f8f4' : '#fafafa' }};{{ (!$activity->step1_basic || !$activity->step2_management_communication || !$activity->step3_photos_media) ? 'opacity:0.5;' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;{{ (!$activity->step1_basic || !$activity->step2_management_communication || !$activity->step3_photos_media) ? 'color:#999;' : '' }}">Step 4: Legal & Compliance</h6>
                                    <p style="margin:0;font-size:13px;color:{{ ($activity->step1_basic && $activity->step2_management_communication && $activity->step3_photos_media) ? '#666' : '#999' }};">Permits, insurance & docs</p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ $activity->step4_legal_compliance ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ $activity->step4_legal_compliance ? '✓' : '4' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if(!$activity->step1_basic || !$activity->step2_management_communication || !$activity->step3_photos_media)
                                    <button disabled style="display:inline-block;background:#e0e0e0;color:#999;padding:8px 16px;border-radius:4px;border:none;font-size:13px;cursor:not-allowed;">
                                        Complete Steps 1-3 First
                                    </button>
                                @elseif($activity->step4_legal_compliance)
                                    <a href="{{ route('operator.activity.step4.show', $activity->id) }}" style="display:inline-block;background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('operator.activity.step4.show', $activity->id) }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Complete Step
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Step 5: Accounting & Transaction --}}
                        <div style="border:2px solid {{ $activity->step5_accounting_transaction ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ $activity->step5_accounting_transaction ? '#f1f8f4' : '#fafafa' }};{{ (!$activity->step1_basic || !$activity->step2_management_communication || !$activity->step3_photos_media || !$activity->step4_legal_compliance) ? 'opacity:0.5;' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;{{ (!$activity->step1_basic || !$activity->step2_management_communication || !$activity->step3_photos_media || !$activity->step4_legal_compliance) ? 'color:#999;' : '' }}">Step 5: Accounting & Transaction</h6>
                                    <p style="margin:0;font-size:13px;color:{{ ($activity->step1_basic && $activity->step2_management_communication && $activity->step3_photos_media && $activity->step4_legal_compliance) ? '#666' : '#999' }};">Banking, VAT & tax setup</p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ $activity->step5_accounting_transaction ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ $activity->step5_accounting_transaction ? '✓' : '5' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if(!$activity->step1_basic || !$activity->step2_management_communication || !$activity->step3_photos_media || !$activity->step4_legal_compliance)
                                    <button disabled style="display:inline-block;background:#e0e0e0;color:#999;padding:8px 16px;border-radius:4px;border:none;font-size:13px;cursor:not-allowed;">
                                        Complete Steps 1-4 First
                                    </button>
                                @elseif($activity->step5_accounting_transaction)
                                    <a href="{{ route('operator.activity.step5.show', $activity->id) }}" style="display:inline-block;background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('operator.activity.step5.show', $activity->id) }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Complete Step
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Step 6: Policies & Rules --}}
                        <div style="border:2px solid {{ $activity->step6_policies_rules ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ $activity->step6_policies_rules ? '#f1f8f4' : '#fafafa' }};{{ (!$activity->step1_basic || !$activity->step2_management_communication || !$activity->step3_photos_media || !$activity->step4_legal_compliance || !$activity->step5_accounting_transaction) ? 'opacity:0.5;' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;{{ (!$activity->step1_basic || !$activity->step2_management_communication || !$activity->step3_photos_media || !$activity->step4_legal_compliance || !$activity->step5_accounting_transaction) ? 'color:#999;' : '' }}">Step 6: Policies & Rules</h6>
                                    <p style="margin:0;font-size:13px;color:{{ ($activity->step1_basic && $activity->step2_management_communication && $activity->step3_photos_media && $activity->step4_legal_compliance && $activity->step5_accounting_transaction) ? '#666' : '#999' }};">Cancellation, safety & health</p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ $activity->step6_policies_rules ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ $activity->step6_policies_rules ? '✓' : '6' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if(!$activity->step1_basic || !$activity->step2_management_communication || !$activity->step3_photos_media || !$activity->step4_legal_compliance || !$activity->step5_accounting_transaction)
                                    <button disabled style="display:inline-block;background:#e0e0e0;color:#999;padding:8px 16px;border-radius:4px;border:none;font-size:13px;cursor:not-allowed;">
                                        Complete Steps 1-5 First
                                    </button>
                                @elseif($activity->step6_policies_rules)
                                    <a href="{{ route('operator.activity.step6.show', $activity->id) }}" style="display:inline-block;background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('operator.activity.step6.show', $activity->id) }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Complete Step
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Step 7: Variants & Equipment --}}
                        <div style="border:2px solid {{ $activity->step7_variants_equipment ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ $activity->step7_variants_equipment ? '#f1f8f4' : '#fafafa' }};{{ (!$activity->step1_basic || !$activity->step2_management_communication || !$activity->step3_photos_media || !$activity->step4_legal_compliance || !$activity->step5_accounting_transaction || !$activity->step6_policies_rules) ? 'opacity:0.5;' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;{{ (!$activity->step1_basic || !$activity->step2_management_communication || !$activity->step3_photos_media || !$activity->step4_legal_compliance || !$activity->step5_accounting_transaction || !$activity->step6_policies_rules) ? 'color:#999;' : '' }}">Step 7: Variants & Equipment</h6>
                                    <p style="margin:0;font-size:13px;color:{{ ($activity->step1_basic && $activity->step2_management_communication && $activity->step3_photos_media && $activity->step4_legal_compliance && $activity->step5_accounting_transaction && $activity->step6_policies_rules) ? '#666' : '#999' }};">Equipment variants & capacity</p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ $activity->step7_variants_equipment ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ $activity->step7_variants_equipment ? '✓' : '7' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if(!$activity->step1_basic || !$activity->step2_management_communication || !$activity->step3_photos_media || !$activity->step4_legal_compliance || !$activity->step5_accounting_transaction || !$activity->step6_policies_rules)
                                    <button disabled style="display:inline-block;background:#e0e0e0;color:#999;padding:8px 16px;border-radius:4px;border:none;font-size:13px;cursor:not-allowed;">
                                        Complete Steps 1-6 First
                                    </button>
                                @elseif($activity->step7_variants_equipment)
                                    <a href="{{ route('operator.activity.step7.show', $activity->id) }}" style="display:inline-block;background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('operator.activity.step7.show', $activity->id) }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Complete Step
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Step 8: Scheduling TimeSlots --}}
                        <div style="border:2px solid {{ isset($activity->step8_scheduling_timeslots) && $activity->step8_scheduling_timeslots ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ isset($activity->step8_scheduling_timeslots) && $activity->step8_scheduling_timeslots ? '#f1f8f4' : '#fafafa' }};{{ !$activity->step7_variants_equipment ? 'opacity:0.5;' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;{{ !$activity->step7_variants_equipment ? 'color:#999;' : '' }}">Step 8: Scheduling TimeSlots</h6>
                                    <p style="margin:0;font-size:13px;color:{{ $activity->step7_variants_equipment ? '#666' : '#999' }};">Activity scheduling & availability</p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ isset($activity->step8_scheduling_timeslots) && $activity->step8_scheduling_timeslots ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ isset($activity->step8_scheduling_timeslots) && $activity->step8_scheduling_timeslots ? '✓' : '8' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if(!$activity->step7_variants_equipment)
                                    <button disabled style="display:inline-block;background:#e0e0e0;color:#999;padding:8px 16px;border-radius:4px;border:none;font-size:13px;cursor:not-allowed;">
                                        Complete Step 7 First
                                    </button>
                                @elseif(isset($activity->step8_scheduling_timeslots) && $activity->step8_scheduling_timeslots)
                                    <a href="{{ route('operator.activity.step8.show', $activity->id) }}" style="display:inline-block;background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('operator.activity.step8.show', $activity->id) }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Complete Step
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Step 9: Rates --}}
                        <div style="border:2px solid {{ isset($activity->step9_rates) && $activity->step9_rates ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ isset($activity->step9_rates) && $activity->step9_rates ? '#f1f8f4' : '#fafafa' }};{{ !$activity->step8_scheduling_timeslots ? 'opacity:0.5;' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;{{ !$activity->step8_scheduling_timeslots ? 'color:#999;' : '' }}">Step 9: Rates</h6>
                                    <p style="margin:0;font-size:13px;color:{{ $activity->step8_scheduling_timeslots ? '#666' : '#999' }};">Seasonal pricing & rates</p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ isset($activity->step9_rates) && $activity->step9_rates ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ isset($activity->step9_rates) && $activity->step9_rates ? '✓' : '9' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if(!$activity->step8_scheduling_timeslots)
                                    <button disabled style="display:inline-block;background:#e0e0e0;color:#999;padding:8px 16px;border-radius:4px;border:none;font-size:13px;cursor:not-allowed;">
                                        Complete Step 8 First
                                    </button>
                                @elseif(isset($activity->step9_rates) && $activity->step9_rates)
                                    <a href="{{ route('operator.activity.step9.show', $activity->id) }}" style="display:inline-block;background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('operator.activity.step9.show', $activity->id) }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Complete Step
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Step 10: Allotment --}}
                        <div style="border:2px solid {{ isset($activity->step10_allotment) && $activity->step10_allotment ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ isset($activity->step10_allotment) && $activity->step10_allotment ? '#f1f8f4' : '#fafafa' }};{{ !isset($activity->step9_rates) || !$activity->step9_rates ? 'opacity:0.5;' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;{{ !isset($activity->step9_rates) || !$activity->step9_rates ? 'color:#999;' : '' }}">Step 10: Allotment</h6>
                                    <p style="margin:0;font-size:13px;color:{{ isset($activity->step9_rates) && $activity->step9_rates ? '#666' : '#999' }};">Inventory limits & blackout dates</p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ isset($activity->step10_allotment) && $activity->step10_allotment ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ isset($activity->step10_allotment) && $activity->step10_allotment ? '✓' : '10' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if(!isset($activity->step9_rates) || !$activity->step9_rates)
                                    <button disabled style="display:inline-block;background:#e0e0e0;color:#999;padding:8px 16px;border-radius:4px;border:none;font-size:13px;cursor:not-allowed;">
                                        Complete Step 9 First
                                    </button>
                                @elseif(isset($activity->step10_allotment) && $activity->step10_allotment)
                                    <a href="{{ route('operator.activity.step10.show', $activity->id) }}" style="display:inline-block;background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('operator.activity.step10.show', $activity->id) }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Complete Step
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Step 11: Promotions & Offers --}}
                        <div style="border:2px solid {{ isset($activity->step11_promotions_offers) && $activity->step11_promotions_offers ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ isset($activity->step11_promotions_offers) && $activity->step11_promotions_offers ? '#f1f8f4' : '#fafafa' }};{{ !isset($activity->step10_allotment) || !$activity->step10_allotment ? 'opacity:0.5;' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;{{ !isset($activity->step10_allotment) || !$activity->step10_allotment ? 'color:#999;' : '' }}">Step 11: Promotions & Offers</h6>
                                    <p style="margin:0;font-size:13px;color:{{ isset($activity->step10_allotment) && $activity->step10_allotment ? '#666' : '#999' }};">Create campaigns & discounts</p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ isset($activity->step11_promotions_offers) && $activity->step11_promotions_offers ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ isset($activity->step11_promotions_offers) && $activity->step11_promotions_offers ? '✓' : '11' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if(!isset($activity->step10_allotment) || !$activity->step10_allotment)
                                    <button disabled style="display:inline-block;background:#e0e0e0;color:#999;padding:8px 16px;border-radius:4px;border:none;font-size:13px;cursor:not-allowed;">
                                        Complete Step 10 First
                                    </button>
                                @elseif(isset($activity->step11_promotions_offers) && $activity->step11_promotions_offers)
                                    <a href="{{ route('operator.activity.step11.show', $activity->id) }}" style="display:inline-block;background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('operator.activity.step11.show', $activity->id) }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Complete Step
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Step 12: SEO & Social --}}
                        <div style="border:2px solid {{ isset($activity->step12_seo_social) && $activity->step12_seo_social ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ isset($activity->step12_seo_social) && $activity->step12_seo_social ? '#f1f8f4' : '#fafafa' }};{{ !isset($activity->step11_promotions_offers) || !$activity->step11_promotions_offers ? 'opacity:0.5;' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;{{ !isset($activity->step11_promotions_offers) || !$activity->step11_promotions_offers ? 'color:#999;' : '' }}">Step 12: SEO & Social</h6>
                                    <p style="margin:0;font-size:13px;color:{{ isset($activity->step11_promotions_offers) && $activity->step11_promotions_offers ? '#666' : '#999' }};">Optimize for search & sharing</p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ isset($activity->step12_seo_social) && $activity->step12_seo_social ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ isset($activity->step12_seo_social) && $activity->step12_seo_social ? '✓' : '12' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if(!isset($activity->step11_promotions_offers) || !$activity->step11_promotions_offers)
                                    <button disabled style="display:inline-block;background:#e0e0e0;color:#999;padding:8px 16px;border-radius:4px;border:none;font-size:13px;cursor:not-allowed;">
                                        Complete Step 11 First
                                    </button>
                                @elseif(isset($activity->step12_seo_social) && $activity->step12_seo_social)
                                    <a href="{{ route('operator.activity.step12.show', $activity->id) }}" style="display:inline-block;background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('operator.activity.step12.show', $activity->id) }}" style="display:inline-block;background:#19b5b5;color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        Complete Step
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Step 13: Publish --}}
                        <div style="border:2px solid {{ isset($activity->step13_publish) && $activity->step13_publish ? '#28a745' : '#e0e0e0' }};border-radius:12px;padding:16px;background:{{ isset($activity->step13_publish) && $activity->step13_publish ? '#f1f8f4' : '#fafafa' }};{{ !isset($activity->step12_seo_social) || !$activity->step12_seo_social ? 'opacity:0.5;' : '' }}">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <h6 style="margin:0 0 4px 0;font-weight:600;{{ !isset($activity->step12_seo_social) || !$activity->step12_seo_social ? 'color:#999;' : '' }}">Step 13: Publish</h6>
                                    <p style="margin:0;font-size:13px;color:{{ isset($activity->step12_seo_social) && $activity->step12_seo_social ? '#666' : '#999' }};">
                                        @if($activity->approval_status === 'Approved')
                                            ✅ Approved & Live
                                        @elseif($activity->approval_status === 'Pending')
                                            ⏳ Pending Approval
                                        @elseif($activity->approval_status === 'Rejected')
                                            ❌ Changes Required
                                        @else
                                            Submit for approval
                                        @endif
                                    </p>
                                </div>
                                <div style="font-size:24px;font-weight:700;color:{{ isset($activity->step13_publish) && $activity->step13_publish ? '#28a745' : '#ccc' }};text-align:center;min-width:40px;">
                                    {{ isset($activity->step13_publish) && $activity->step13_publish ? '✓' : '13' }}
                                </div>
                            </div>
                            <div style="margin-top:12px;">
                                @if(!isset($activity->step12_seo_social) || !$activity->step12_seo_social)
                                    <button disabled style="display:inline-block;background:#e0e0e0;color:#999;padding:8px 16px;border-radius:4px;border:none;font-size:13px;cursor:not-allowed;">
                                        Complete Step 12 First
                                    </button>
                                @else
                                    <a href="{{ route('operator.activity.step13.show', $activity->id) }}" style="display:inline-block;background:{{ $activity->approval_status === 'Approved' ? '#28a745' : '#19b5b5' }};color:#fff;padding:8px 16px;border-radius:4px;text-decoration:none;font-size:13px;">
                                        {{ $activity->approval_status === 'Approved' ? 'View Status' : ($activity->approval_status === 'Pending' ? 'View Status' : 'Publish') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 3 Photos & Media Overview --}}
                @if($activity->step3_photos_media)
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;">
                    <h5 style="font-weight:600;margin:0 0 16px 0;">Photos & Media</h5>
                    
                    @if($activity->hero_banner_image)
                    <div style="margin-bottom:20px;">
                        <label style="font-size:13px;color:#666;font-weight:600;display:block;margin-bottom:8px;">Hero/Banner Image</label>
                        <img src="{{ asset('storage/' . $activity->hero_banner_image) }}" alt="Hero Banner" style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid #ddd;">
                    </div>
                    @endif

                    @if($activity->gallery_images && count($activity->gallery_images) > 0)
                    <div style="margin-bottom:20px;">
                        <label style="font-size:13px;color:#666;font-weight:600;display:block;margin-bottom:8px;">Gallery Images ({{ count($activity->gallery_images) }})</label>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;">
                            @foreach($activity->gallery_images as $image)
                                <img src="{{ asset('storage/' . $image) }}" alt="Gallery" style="width:100%;height:120px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($activity->vehicle_images && count($activity->vehicle_images) > 0)
                    <div style="margin-bottom:20px;">
                        <label style="font-size:13px;color:#666;font-weight:600;display:block;margin-bottom:8px;">Vehicle/Equipment Images</label>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
                            @foreach($activity->vehicle_images as $vehicle)
                                <div style="border:1px solid #ddd;border-radius:8px;padding:8px;background:#f9f9f9;">
                                    <img src="{{ asset('storage/' . $vehicle['image']) }}" alt="{{ $vehicle['type'] }}" style="width:100%;height:120px;object-fit:cover;border-radius:4px;margin-bottom:8px;">
                                    <p style="margin:0;font-size:13px;font-weight:600;text-align:center;">{{ $vehicle['type'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                        @if($activity->logo)
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;display:block;margin-bottom:8px;">Logo</label>
                            <img src="{{ asset('storage/' . $activity->logo) }}" alt="Logo" style="max-width:150px;max-height:150px;border-radius:8px;border:1px solid #ddd;">
                        </div>
                        @endif

                        @if($activity->video)
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;display:block;margin-bottom:8px;">Video</label>
                            <video controls style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid #ddd;">
                                <source src="{{ asset('storage/' . $activity->video) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Step 4 Legal & Compliance Overview --}}
                @if($activity->step4_legal_compliance && $activity->compliance)
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;">
                    <h5 style="font-weight:600;margin:0 0 16px 0;">Legal & Compliance Details</h5>
                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Compliance ID</label>
                            <p style="margin:4px 0 0 0;font-weight:600;color:#19b5b5;">{{ $activity->compliance->compliance_id }}</p>
                        </div>
                        @if($activity->compliance->parent_service_id)
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Parent Service ID</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->compliance->parent_service_id }}</p>
                        </div>
                        @endif
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Business Registration Number</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->compliance->business_registration_number }}</p>
                        </div>
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Tourism Activity Permit</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->compliance->tourism_activity_permit }}</p>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Public Liability Insurance</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->compliance->public_liability_insurance }}</p>
                        </div>
                        @if($activity->compliance->insurance_expiration)
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Insurance Expiration</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->compliance->insurance_expiration->format('M d, Y') }}</p>
                        </div>
                        @endif
                    </div>

                    @if($activity->compliance->equipment_registration_serial)
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Equipment Registration/Serial</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->compliance->equipment_registration_serial }}</p>
                        </div>
                    </div>
                    @endif

                    @if($activity->compliance->permits_authorisations_files && count($activity->compliance->permits_authorisations_files) > 0)
                    <div style="border-top:1px solid #e0e0e0;padding-top:16px;margin-top:16px;">
                        <label style="font-size:13px;color:#666;font-weight:600;display:block;margin-bottom:12px;">Permits/Authorisations</label>
                        
                        <div style="margin-bottom:8px;">
                            @foreach($activity->compliance->permits_authorisations_files as $file)
                                <div style="margin-top:4px;">
                                    <a href="{{ asset('storage/' . $file) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-size:13px;">
                                        📄 {{ basename($file) }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($activity->compliance->tourism_permit_file || $activity->compliance->insurance_file || 
                        $activity->compliance->operational_assessment_doc || $activity->compliance->emergency_plan_doc ||
                        $activity->compliance->equipment_compliance_doc ||
                        ($activity->compliance->other_permit_files && count($activity->compliance->other_permit_files) > 0))
                    <div style="border-top:1px solid #e0e0e0;padding-top:16px;margin-top:16px;">
                        <label style="font-size:13px;color:#666;font-weight:600;display:block;margin-bottom:12px;">Additional Documents</label>
                        
                        @if($activity->compliance->tourism_permit_file)
                        <div style="margin-bottom:8px;">
                            <a href="{{ asset('storage/' . $activity->compliance->tourism_permit_file) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-size:14px;">
                                📄 Tourism Activity Permit
                            </a>
                        </div>
                        @endif

                        @if($activity->compliance->insurance_file)
                        <div style="margin-bottom:8px;">
                            <a href="{{ asset('storage/' . $activity->compliance->insurance_file) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-size:14px;">
                                📄 Insurance Certificate
                            </a>
                        </div>
                        @endif

                        @if($activity->compliance->operational_assessment_doc)
                        <div style="margin-bottom:8px;">
                            <a href="{{ asset('storage/' . $activity->compliance->operational_assessment_doc) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-size:14px;">
                                📄 Operational Assessment Document
                            </a>
                        </div>
                        @endif

                        @if($activity->compliance->emergency_plan_doc)
                        <div style="margin-bottom:8px;">
                            <a href="{{ asset('storage/' . $activity->compliance->emergency_plan_doc) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-size:14px;">
                                📄 Emergency Plan
                            </a>
                        </div>
                        @endif

                        @if($activity->compliance->equipment_compliance_doc)
                        <div style="margin-bottom:8px;">
                            <a href="{{ asset('storage/' . $activity->compliance->equipment_compliance_doc) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-size:14px;">
                                📄 Equipment Compliance Documents
                            </a>
                        </div>
                        @endif

                        @if($activity->compliance->other_permit_files && count($activity->compliance->other_permit_files) > 0)
                        <div style="margin-bottom:8px;">
                            <span style="font-size:13px;color:#666;font-weight:600;">Other Documents:</span>
                            @foreach($activity->compliance->other_permit_files as $file)
                                <div style="margin-left:16px;margin-top:4px;">
                                    <a href="{{ asset('storage/' . $file) }}" target="_blank" style="color:#19b5b5;text-decoration:none;font-size:13px;">
                                        📄 {{ basename($file) }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endif

                {{-- Step 5 Accounting & Transaction Overview --}}
                @if($activity->step5_accounting_transaction && $activity->accounting)
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;">
                    <h5 style="font-weight:600;margin:0 0 16px 0;">Accounting & Transaction Details</h5>
                    
                    {{-- Banking & VAT Section --}}
                    <div style="border-bottom:1px solid #e0e0e0;padding-bottom:16px;margin-bottom:16px;">
                        <h6 style="font-weight:600;margin:0 0 12px 0;color:#19b5b5;">Banking Information</h6>
                        
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px;">
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Account Holder Name</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->accounting->bank_account_holder_name }}</p>
                            </div>
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Bank Name</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->accounting->bank_name }}</p>
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px;">
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Account Number</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->accounting->account_number }}</p>
                            </div>
                            @if($activity->accounting->iban)
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">IBAN</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->accounting->iban }}</p>
                            </div>
                            @endif
                        </div>

                        @if($activity->accounting->swift_code)
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px;">
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">SWIFT Code</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->accounting->swift_code }}</p>
                            </div>
                        </div>
                        @endif

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">VAT Number</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->accounting->vat_number ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">VAT Status</label>
                                <p style="margin:4px 0 0 0;">
                                    @if($activity->accounting->vat_exempted)
                                        <span style="background:#ffc107;color:#000;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;">Exempted</span>
                                    @else
                                        <span style="background:#28a745;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;">Registered</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Agreement & Commission Section --}}
                    <div style="border-bottom:1px solid #e0e0e0;padding-bottom:16px;margin-bottom:16px;">
                        <h6 style="font-weight:600;margin:0 0 12px 0;color:#19b5b5;">Agreement & Commission</h6>
                        
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px;">
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Agreement</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->accounting->agreement_name }}</p>
                            </div>
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Currency Net</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->accounting->currency_net }}</p>
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Commission Type</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->accounting->commission_type }}</p>
                            </div>
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Commission Value</label>
                                <p style="margin:4px 0 0 0;font-weight:600;color:#19b5b5;">
                                    {{ $activity->accounting->commission_value }}{{ $activity->accounting->commission_type == 'Percentage' ? '%' : ' ' . $activity->accounting->currency_net }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Tax Configuration Section --}}
                    <div>
                        <h6 style="font-weight:600;margin:0 0 12px 0;color:#19b5b5;">Tax Configuration</h6>
                        
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:16px;">
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Tax Type</label>
                                <p style="margin:4px 0 0 0;">
                                    @if($activity->accounting->tax_type == 'None')
                                        <span style="background:#6c757d;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;">No Tax</span>
                                    @else
                                        <span style="background:#007bff;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;">{{ $activity->accounting->tax_type }} Tax</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Tax Collection</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->accounting->tax_payment_collection ?? 'N/A' }}</p>
                            </div>
                        </div>

                        @if($activity->accounting->tax_type && $activity->accounting->tax_type != 'None')
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Charges Basis</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->accounting->tax_charges_basis }}</p>
                            </div>
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Charges Type</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->accounting->tax_charges_type }}</p>
                            </div>
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Charges Value</label>
                                <p style="margin:4px 0 0 0;font-weight:600;color:#19b5b5;">
                                    {{ $activity->accounting->tax_charges_value }}{{ $activity->accounting->tax_charges_type == 'Percentage' ? '%' : ' ' . $activity->accounting->currency_net }}
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Step 6 Policies & Rules Overview --}}
                @if($activity->step6_policies_rules && $activity->policy)
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;">
                    <h5 style="font-weight:600;margin:0 0 16px 0;">Policies & Rules</h5>
                    
                    {{-- Booking Rules Section --}}
                    @if($activity->policy->service_id || $activity->policy->booking_window_rules)
                    <div style="border-bottom:1px solid #e0e0e0;padding-bottom:16px;margin-bottom:16px;">
                        <h6 style="font-weight:600;margin:0 0 12px 0;color:#19b5b5;">Booking Rules</h6>
                        
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                            @if($activity->policy->service_id)
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Service ID</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->policy->service_id }}</p>
                            </div>
                            @endif
                            @if($activity->policy->booking_window_rules)
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Booking Window</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->policy->booking_window_rules }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Policies Section --}}
                    <div style="border-bottom:1px solid #e0e0e0;padding-bottom:16px;margin-bottom:16px;">
                        <h6 style="font-weight:600;margin:0 0 12px 0;color:#19b5b5;">Policies</h6>
                        
                        @if($activity->policy->cancellation_policy)
                        <div style="margin-bottom:16px;">
                            <label style="font-size:13px;color:#666;font-weight:600;">Cancellation Policy
                                <span style="background:#2196f3;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;margin-left:8px;">
                                    {{ $activity->policy->cancellation_policy_type }}
                                </span>
                            </label>
                            <p style="margin:4px 0 0 0;line-height:1.6;">{{ $activity->policy->cancellation_policy }}</p>
                        </div>
                        @endif

                        @if($activity->policy->amendment_policy)
                        <div style="margin-bottom:16px;">
                            <label style="font-size:13px;color:#666;font-weight:600;">Amendment Policy
                                <span style="background:#2196f3;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;margin-left:8px;">
                                    {{ $activity->policy->amendment_policy_type }}
                                </span>
                            </label>
                            <p style="margin:4px 0 0 0;line-height:1.6;">{{ $activity->policy->amendment_policy }}</p>
                        </div>
                        @endif

                        @if($activity->policy->no_show_policy)
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">No-Show Policy</label>
                            <p style="margin:4px 0 0 0;line-height:1.6;">{{ $activity->policy->no_show_policy }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- Cancellation Penalties Section --}}
                    <div style="border-bottom:1px solid #e0e0e0;padding-bottom:16px;margin-bottom:16px;">
                        <h6 style="font-weight:600;margin:0 0 12px 0;color:#19b5b5;">Cancellation Penalties</h6>
                        
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Penalties Apply</label>
                                <p style="margin:4px 0 0 0;">
                                    @if($activity->policy->cancellation_penalties_enabled === 'Yes')
                                        <span style="background:#dc3545;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;">Yes</span>
                                    @else
                                        <span style="background:#28a745;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;">No</span>
                                    @endif
                                </p>
                            </div>
                            @if($activity->policy->cancellation_penalties_enabled === 'Yes')
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Penalty Type</label>
                                <p style="margin:4px 0 0 0;">{{ $activity->policy->cancellation_penalties_type }}</p>
                            </div>
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Penalty Value</label>
                                <p style="margin:4px 0 0 0;font-weight:600;color:#dc3545;">
                                    {{ $activity->policy->cancellation_penalties_value }}{{ $activity->policy->cancellation_penalties_type == 'Percentage' ? '%' : '' }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Age Policies Section --}}
                    @if($activity->policy->child_policy_age || $activity->policy->infant_policy_age)
                    <div style="border-bottom:1px solid #e0e0e0;padding-bottom:16px;margin-bottom:16px;">
                        <h6 style="font-weight:600;margin:0 0 12px 0;color:#19b5b5;">Age Policies</h6>
                        
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                            @if($activity->policy->child_policy_age)
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Child Policy Age</label>
                                <p style="margin:4px 0 0 0;">Up to {{ $activity->policy->child_policy_age }} years</p>
                            </div>
                            @endif
                            @if($activity->policy->infant_policy_age)
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Infant Policy Age</label>
                                <p style="margin:4px 0 0 0;">Up to {{ $activity->policy->infant_policy_age }} years</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Safety & Health Section --}}
                    <div>
                        <h6 style="font-weight:600;margin:0 0 12px 0;color:#19b5b5;">Safety & Health Requirements</h6>
                        
                        <div style="margin-bottom:16px;">
                            <label style="font-size:13px;color:#666;font-weight:600;">Safety Requirements</label>
                            <p style="margin:4px 0 0 0;line-height:1.6;">{{ $activity->policy->safety_requirements }}</p>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Health Requirements Type</label>
                                <p style="margin:4px 0 0 0;">
                                    @if($activity->policy->health_requirements_type === 'None')
                                        <span style="background:#6c757d;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;">None</span>
                                    @elseif($activity->policy->health_requirements_type === 'Upload')
                                        <span style="background:#007bff;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;">Upload Form</span>
                                    @else
                                        <span style="background:#17a2b8;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600;">Generate from Template</span>
                                    @endif
                                </p>
                            </div>
                            @if($activity->policy->health_requirements_file)
                            <div>
                                <label style="font-size:13px;color:#666;font-weight:600;">Waiver Form</label>
                                <p style="margin:4px 0 0 0;">
                                    <a href="{{ asset('storage/' . $activity->policy->health_requirements_file) }}" target="_blank" style="color:#19b5b5;text-decoration:none;">
                                        <i class="fas fa-file-pdf"></i> View Document
                                    </a>
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- Step 7 Variants & Equipment Overview --}}
                @if($activity->step7_variants_equipment && $activity->variants && count($activity->variants) > 0)
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;">
                    <h5 style="font-weight:600;margin:0 0 16px 0;">Equipment Variants</h5>
                    
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
                        @foreach($activity->variants as $variant)
                        <div style="border:1px solid #ddd;border-radius:12px;padding:12px;background:#f9f9f9;">
                            @if($variant->equipment_image)
                            <img src="{{ asset('storage/' . $variant->equipment_image) }}" alt="{{ $variant->variant_name }}" style="width:100%;height:120px;object-fit:cover;border-radius:8px;margin-bottom:10px;">
                            @endif
                            
                            <h6 style="font-weight:600;margin:0 0 8px 0;font-size:13px;">{{ $variant->variant_name }}</h6>
                            
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">
                                <div>
                                    <label style="font-size:11px;color:#666;font-weight:600;">Tier</label>
                                    <p style="margin:2px 0;font-size:12px;">{{ $variant->quality_tier }}</p>
                                </div>
                                <div>
                                    <label style="font-size:11px;color:#666;font-weight:600;">Capacity</label>
                                    <p style="margin:2px 0;font-size:12px;">{{ $variant->max_pax }} pax</p>
                                </div>
                                <div>
                                    <label style="font-size:11px;color:#666;font-weight:600;">Min Pax</label>
                                    <p style="margin:2px 0;font-size:12px;">{{ $variant->min_participants }}</p>
                                </div>
                                <div>
                                    <label style="font-size:11px;color:#666;font-weight:600;">Max Pax</label>
                                    <p style="margin:2px 0;font-size:12px;">{{ $variant->max_participants }}</p>
                                </div>
                            </div>
                            
                            @if($variant->amenities && count($variant->amenities) > 0)
                            <p style="margin:6px 0;font-size:11px;color:#666;">
                                <strong>Amenities:</strong> {{ implode(', ', $variant->amenities) }}
                            </p>
                            @endif
                            
                            @if($variant->safety_equipment && count($variant->safety_equipment) > 0)
                            <p style="margin:6px 0;font-size:11px;color:#666;">
                                <strong>Safety:</strong> {{ implode(', ', $variant->safety_equipment) }}
                            </p>
                            @endif
                            
                            <p style="margin:6px 0;font-size:11px;color:#666;">
                                <strong>Exclusive:</strong> {{ $variant->private_exclusive }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Step 2 Contact Details Overview --}}
                @if($activity->step2_management_communication)
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;">
                    <h5 style="font-weight:600;margin:0 0 16px 0;">Contact Information</h5>
                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Reservation Contact</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->reservation_contact_name }}</p>
                            <p style="margin:2px 0 0 0;font-size:13px;color:#666;">{{ $activity->reservation_contact_email }}</p>
                            <p style="margin:2px 0 0 0;font-size:13px;color:#666;">{{ $activity->reservation_contact_phone }} / {{ $activity->reservation_contact_mobile }}</p>
                        </div>
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Management Contact</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->management_contact_name }}</p>
                            <p style="margin:2px 0 0 0;font-size:13px;color:#666;">{{ $activity->management_contact_email }}</p>
                            <p style="margin:2px 0 0 0;font-size:13px;color:#666;">{{ $activity->management_contact_phone }} / {{ $activity->management_contact_mobile }}</p>
                        </div>
                        @if($activity->accounting_contact_name)
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Accounting Contact</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->accounting_contact_name }}</p>
                            <p style="margin:2px 0 0 0;font-size:13px;color:#666;">{{ $activity->accounting_contact_email }}</p>
                            <p style="margin:2px 0 0 0;font-size:13px;color:#666;">{{ $activity->accounting_contact_phone }} / {{ $activity->accounting_contact_mobile }}</p>
                        </div>
                        @endif
                        @if($activity->operational_manager_name)
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Operational Manager</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->operational_manager_name }}</p>
                            <p style="margin:2px 0 0 0;font-size:13px;color:#666;">{{ $activity->operational_manager_phone }}</p>
                        </div>
                        @endif
                    </div>

                    <hr style="border:none;border-top:1px solid #e0e0e0;margin:20px 0;">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Booking Registration Type</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->booking_registration_type ?? 'Listing' }}</p>
                        </div>
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Booking Confirmation Type</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->booking_confirmation_type }}</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Activity Details Overview --}}
                @if($activity->step1_basic)
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;">
                    <h5 style="font-weight:600;margin:0 0 16px 0;">Activity Details</h5>
                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Activity Name</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->activity_name }}</p>
                        </div>
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Service Type</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->service_type }}</p>
                        </div>
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Physical Level</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->physical_level }}</p>
                        </div>
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Price Range</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->price_range }}</p>
                        </div>
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Location</label>
                            <p style="margin:4px 0 0 0;">
                                {{ $activity->town }}{{ $activity->region ? ', ' . $activity->region : '' }}{{ $activity->destination ? ', ' . $activity->destination : '' }}
                            </p>
                        </div>
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Duration</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->duration }}</p>
                        </div>
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Booking Confirmation</label>
                            <p style="margin:4px 0 0 0;">{{ $activity->booking_confirmation_type }}</p>
                        </div>
                        <div>
                            <label style="font-size:13px;color:#666;font-weight:600;">Team Categories</label>
                            <p style="margin:4px 0 0 0;">{{ implode(', ', $activity->team_categories ?? []) }}</p>
                        </div>
                    </div>

                    <hr style="border:none;border-top:1px solid #e0e0e0;margin:20px 0;">

                    <div>
                        <label style="font-size:13px;color:#666;font-weight:600;">Overview</label>
                        <p style="margin:4px 0 0 0;line-height:1.6;">{{ $activity->overview }}</p>
                    </div>

                    <div style="margin-top:16px;">
                        <label style="font-size:13px;color:#666;font-weight:600;">What's Included</label>
                        <p style="margin:4px 0 0 0;line-height:1.6;">{{ $activity->whats_included }}</p>
                    </div>

                    <div style="margin-top:16px;">
                        <label style="font-size:13px;color:#666;font-weight:600;">Itinerary</label>
                        <p style="margin:4px 0 0 0;line-height:1.6;">{{ $activity->itinerary }}</p>
                    </div>

                    @if($activity->add_ons_available || $activity->private_exclusive_option)
                    <hr style="border:none;border-top:1px solid #e0e0e0;margin:20px 0;">
                    <div>
                        <label style="font-size:13px;color:#666;font-weight:600;">Options</label>
                        <div style="margin:8px 0 0 0;">
                            @if($activity->add_ons_available)
                                <span style="display:inline-block;background:#e3f2fd;color:#1976d2;padding:4px 8px;border-radius:3px;font-size:12px;margin-right:8px;">Add-ons Available</span>
                            @endif
                            @if($activity->private_exclusive_option)
                                <span style="display:inline-block;background:#f3e5f5;color:#7b1fa2;padding:4px 8px;border-radius:3px;font-size:12px;">Private/Exclusive Available</span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <div style="background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:20px;text-align:center;color:#999;">
                    <p style="margin:0;">📋 No activity details yet. Complete Step 1 to continue.</p>
                </div>
                @endif

                {{-- Action Buttons --}}
                <div style="display:flex;gap:12px;margin-bottom:20px;">
                    <a href="{{ route('operator.activity.index') }}" style="color:#666;text-decoration:none;padding:10px 16px;border:1px solid #ddd;border-radius:4px;font-size:14px;">
                        ← Back to List
                    </a>
                    <button onclick="if(confirm('Are you sure you want to delete this activity?')) { document.getElementById('deleteForm').submit(); }" style="background:#dc3545;color:#fff;padding:10px 16px;border:none;border-radius:4px;font-size:14px;cursor:pointer;">
                        🗑️ Delete Activity
                    </button>
                </div>

                <form id="deleteForm" method="POST" action="{{ route('operator.activity.destroy', $activity->id) }}" style="display:none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
@endsection
