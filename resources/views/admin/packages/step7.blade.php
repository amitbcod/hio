@extends('layouts.admin')

@php $sidebar = 'admin.packages._steps_sidebar'; $currentStep = 7; @endphp

@section('content')
<div class="container mt-4 mb-5">
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px; overflow:hidden;">
        <div class="card-body p-4">
            <h2 class="mb-1 fw-bold" style="font-size:2rem; color:#1f2a37;">Step 7: Payment & Policies</h2>
            <p class="mb-0 text-muted">Effective package policy summary across the selected accommodations/operators for this package.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden;">
        <div class="card-body p-4">
            <div style="background:#f7faff;border:1px solid #dfeaf9;border-radius:10px;padding:12px 16px;margin-bottom:12px;">
                <div style="font-weight:700;color:#1d5ec7;">Package Policy Summary</div>
            </div>

            <div style="border:1px solid #e4e7eb;border-radius:10px;overflow:hidden;background:#fff;">
                <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
                    <thead style="background:#f7f7f7;">
                        <tr>
                            <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:10%;font-size:13px;color:#333;">Policy</th>
                            <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:15%;font-size:13px;color:#333;">Details (Type)</th>
                            <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:20%;font-size:13px;color:#333;">Before Deadline</th>
                            <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:20%;font-size:13px;color:#333;">After Deadline</th>
                            <th style="padding:12px 10px;text-align:left;border-bottom:1px solid #e4e7eb;width:24%;font-size:13px;color:#333;">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
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
                        @endphp

                        @foreach($policyRows as $key => $meta)
                            @php
                                $row = $effectivePolicy[$key] ?? ['type' => '-', 'before_deadline' => '-', 'after_deadline' => '-', 'notes' => ''];
                            @endphp
                            <tr>
                                <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;font-weight:600;color:#2b2d31;">{{ $meta['label'] }}</td>
                                <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;">
                                    @if(in_array($key, ['payment', 'refund', 'security_deposit', 'house_rules'], true))
                                        <div style="padding:6px 10px;border:1px solid #dfeaf9;border-radius:6px;background:#f8fbff;min-height:36px;display:flex;align-items:center;">
                                            {{ $row['type'] ?? '-' }}
                                        </div>
                                    @else
                                        <div style="padding:6px 10px;border:1px solid #dfeaf9;border-radius:6px;background:#f8fbff;min-height:36px;display:flex;align-items:center;">
                                            {{ $row['type'] ?? '-' }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;">
                                    @php
                                        $beforeValue = $row['before_deadline'] ?? '-';
                                        $beforeLabel = ($key === 'cancellation') ? 'Before 30 days:' : (($key === 'amendments' || $key === 'postponement') ? 'Before 48 hours:' : null);
                                    @endphp
                                    @if($beforeLabel)
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span style="white-space:nowrap;color:#333;font-weight:500;">{{ $beforeLabel }}</span>
                                            <div style="padding:6px 10px;border:1px solid #dfeaf9;border-radius:6px;background:#f8fbff;min-height:36px;display:flex;align-items:center;flex:1;">{{ $beforeValue }}</div>
                                        </div>
                                    @else
                                        <div style="padding:6px 10px;border:1px solid #dfeaf9;border-radius:6px;background:#f8fbff;min-height:36px;display:flex;align-items:center;">{{ $beforeValue }}</div>
                                    @endif
                                </td>
                                <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;">
                                    @php
                                        $afterValue = $row['after_deadline'] ?? '-';
                                        $afterLabel = ($key === 'cancellation') ? 'Within 30 days:' : (($key === 'amendments' || $key === 'postponement') ? 'Within 48 hours:' : null);
                                    @endphp
                                    @if($afterLabel)
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span style="white-space:nowrap;color:#333;font-weight:500;">{{ $afterLabel }}</span>
                                            <div style="padding:6px 10px;border:1px solid #dfeaf9;border-radius:6px;background:#f8fbff;min-height:36px;display:flex;align-items:center;flex:1;">{{ $afterValue }}</div>
                                        </div>
                                    @else
                                        <div style="padding:6px 10px;border:1px solid #dfeaf9;border-radius:6px;background:#f8fbff;min-height:36px;display:flex;align-items:center;">{{ $afterValue }}</div>
                                    @endif
                                </td>
                                <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;">
                                    <div style="padding:6px 10px;border:1px solid #dfeaf9;border-radius:6px;background:#f8fbff;min-height:36px;display:flex;align-items:center;white-space:pre-wrap;">{{ $row['notes'] ?? '' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px;border:1px solid #e4e7eb;border-radius:10px;padding:12px 14px;background:#f7f7f7;">
                <div style="font-weight:600;color:#333;margin-bottom:8px;">Booking Notes</div>
                <div style="padding:10px 12px;border:1px solid #dfeaf9;border-radius:6px;background:#fff;white-space:pre-wrap;">{{ $effectivePolicy['booking_notes'] ?? '' }}</div>
            </div>

            <div style="margin-top:16px;border:1px solid #e4e7eb;border-radius:10px;padding:12px 14px;background:#f7f7f7;">
                <div style="font-weight:600;color:#333;margin-bottom:8px;">Package Notes</div>
                <div style="padding:10px 12px;border:1px solid #dfeaf9;border-radius:6px;background:#fff;white-space:pre-wrap;">{{ $effectivePolicy['package_notes'] ?? '' }}</div>
            </div>

            <div class="d-flex justify-content-between mt-4 align-items-center">
                <a href="{{ route('admin.packages.step6', $package->id) }}" class="btn btn-outline-secondary">Back</a>

                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('admin.packages.step7.save', $package->id) }}" style="display:inline;">
                        @csrf
                        <input type="hidden" name="action" value="draft">
                        <button type="submit" class="btn btn-outline-primary">Save as Draft</button>
                    </form>

                    <form method="POST" action="{{ route('admin.packages.step7.save', $package->id) }}" style="display:inline;">
                        @csrf
                        <input type="hidden" name="action" value="published">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to publish this package?')">Save and Publish</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
