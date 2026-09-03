@extends('layouts.admin')

@php $sidebar = 'admin.packages._steps_sidebar'; $currentStep = 7; @endphp

@section('content')
<div class="container mt-4 mb-5">


    <div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden;">
        <div class="card-body p-4">

            <div class="">
            <h2 class="mb-1 fw-bold" style="font-size:2rem; color:#1f2a37;">Step 7: Payment & Policies</h2>
            <p class="mb-0 text-muted">Effective package policy summary across the selected accommodations/operators for this package.</p>
        </div>
        
            <div style="background:#f7faff;border:1px solid #dfeaf9;border-radius:10px;padding:12px 16px;margin-bottom:12px;">
                <div style="font-weight:700;color:#1d5ec7;">Package Policy Summary</div>
            </div>

            <div style="border:1px solid #e4e7eb;border-radius:10px;overflow:hidden;background:#fff;">
                <form method="POST" action="{{ route('admin.packages.step7.save', $package->id) }}">
                    @csrf
                    <input type="hidden" name="action" value="draft">
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
                                    @php
                                        $options = $policyOptions[$key]['types'] ?? null;
                                        $currentType = $row['type'] ?? '-';
                                    @endphp
                                    @php
                                        $readonlyKeys = ['payment','refund','security_deposit','house_rules'];
                                    @endphp
                                    @if(in_array($key, $readonlyKeys, true))
                                        <div style="padding:6px 10px;border:1px solid #dfeaf9;border-radius:6px;background:#f8fbff;min-height:36px;display:flex;align-items:center;">{{ $row['type'] ?? '-' }}</div>
                                    @else
                                        @if(is_array($options))
                                            @php
                                                $baselineType = strtolower(trim((string) ($effectivePolicy[$key]['type'] ?? $effectivePolicy['cancellation']['type'] ?? 'package (default)')));
                                                $map = $severityMaps[$key] ?? null;
                                                $baselineScore = $map[$baselineType] ?? ($map['package (default)'] ?? 0);
                                            @endphp
                                            <select name="policies[{{ $key }}][type]" class="form-select" style="min-width:180px;">
                                                @foreach($options as $opt)
                                                    @php $optKey = strtolower(trim((string)$opt)); $optScore = $map[$optKey] ?? null; @endphp
                                                    <option value="{{ $opt }}" @if(trim((string)$opt) === trim((string)$currentType)) selected @endif @if($optScore !== null && $optScore < $baselineScore) disabled @endif>{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" name="policies[{{ $key }}][type]" value="{{ $currentType }}" class="form-control" />
                                        @endif
                                    @endif
                                </td>
                                <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;">
                                    @php
                                        $beforeValue = $row['before_deadline'] ?? '-';
                                        $beforeLabel = ($key === 'cancellation') ? 'Before 30 days:' : (($key === 'amendments' || $key === 'postponement') ? 'Before 48 hours:' : null);
                                    @endphp
                                    @php $beforeOptions = $policyOptions[$key]['beforeOptions'] ?? null; @endphp
                                    @if($beforeLabel)
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span style="white-space:nowrap;color:#333;font-weight:500;">{{ $beforeLabel }}</span>
                                            @if(is_array($beforeOptions))
                                                <select name="policies[{{ $key }}][before_deadline]" class="form-select" style="flex:1;">
                                                    @foreach($beforeOptions as $opt)
                                                        <option value="{{ $opt }}" @if(trim((string)$opt) === trim((string)$beforeValue)) selected @endif>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" name="policies[{{ $key }}][before_deadline]" value="{{ $beforeValue }}" class="form-control" />
                                            @endif
                                        </div>
                                    @else
                                        <div>
                                            @if(is_array($beforeOptions))
                                                <select name="policies[{{ $key }}][before_deadline]" class="form-select">
                                                    @foreach($beforeOptions as $opt)
                                                        <option value="{{ $opt }}" @if(trim((string)$opt) === trim((string)$beforeValue)) selected @endif>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" name="policies[{{ $key }}][before_deadline]" value="{{ $beforeValue }}" class="form-control" />
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;">
                                    @php
                                        $afterValue = $row['after_deadline'] ?? '-';
                                        $afterLabel = ($key === 'cancellation') ? 'Within 30 days:' : (($key === 'amendments' || $key === 'postponement') ? 'Within 48 hours:' : null);
                                    @endphp
                                    @php $afterOptions = $policyOptions[$key]['afterOptions'] ?? null; @endphp
                                    @if($afterLabel)
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span style="white-space:nowrap;color:#333;font-weight:500;">{{ $afterLabel }}</span>
                                            @if(is_array($afterOptions))
                                                <select name="policies[{{ $key }}][after_deadline]" class="form-select" style="flex:1;">
                                                    @foreach($afterOptions as $opt)
                                                        <option value="{{ $opt }}" @if(trim((string)$opt) === trim((string)$afterValue)) selected @endif>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" name="policies[{{ $key }}][after_deadline]" value="{{ $afterValue }}" class="form-control" />
                                            @endif
                                        </div>
                                    @else
                                        <div>
                                            @if(is_array($afterOptions))
                                                <select name="policies[{{ $key }}][after_deadline]" class="form-select">
                                                    @foreach($afterOptions as $opt)
                                                        <option value="{{ $opt }}" @if(trim((string)$opt) === trim((string)$afterValue)) selected @endif>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="text" name="policies[{{ $key }}][after_deadline]" value="{{ $afterValue }}" class="form-control" />
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:12px 10px;border-bottom:1px solid #edf0f2;">
                                    <input type="text" name="policies[{{ $key }}][notes]" value="{{ $row['notes'] ?? '' }}" class="form-control" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px;border:1px solid #e4e7eb;border-radius:10px;padding:12px 14px;background:#f7f7f7;">
                <div style="font-weight:600;color:#333;margin-bottom:8px;">Booking Notes</div>
                <textarea name="booking_notes" class="form-control" rows="3">{{ $effectivePolicy['booking_notes'] ?? '' }}</textarea>
            </div>

            <div style="margin-top:16px;border:1px solid #e4e7eb;border-radius:10px;padding:12px 14px;background:#f7f7f7;">
                <div style="font-weight:600;color:#333;margin-bottom:8px;">Package Notes</div>
                <textarea name="package_notes" class="form-control" rows="3">{{ $effectivePolicy['package_notes'] ?? '' }}</textarea>
            </div>

            <div class="d-flex justify-content-between mt-4 align-items-center">
                <a href="{{ route('admin.packages.step6', $package->id) }}" class="btn btn-outline-secondary">Back</a>

                <div class="d-flex gap-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-primary" name="action" value="draft">Save as Draft</button>
                        <button type="submit" class="btn btn-success" name="action" value="published" onclick="return confirm('Are you sure you want to publish this package?')">Save and Publish</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
