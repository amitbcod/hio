@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0" style="border-radius:14px; overflow:hidden;">
                <div class="card-header bg-light border-0">
                    <h4 class="mb-0">Package Default Policy</h4>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="alert alert-info mb-4">
                        This is the admin default package policy. New operators will inherit it on first creation, and existing operators keep their own custom policy unless they choose to change it.
                    </div>

                    <form method="POST" action="{{ route('admin.policy.package-default-policy.save') }}">
                        @csrf
                        @php
                            $policy = is_array($packagePolicy) ? $packagePolicy : [];
                            $policyRows = [
                                'cancellation' => ['label' => 'Cancellation','types' => ['Flexible', 'Moderate', 'Strict', 'Package (Default)', 'Group', 'Non-Refundable', 'No Show'],'beforeOptions' => ['100% Refund', '50% Refund', '20% Refund', '0% Refund'],'afterOptions' => ['100% Refund', '50% Refund', '20% Refund', '0% Refund']],
                                'amendments' => ['label' => 'Amendments','types' => ['Moderate', 'Flexible', 'Strict'],'beforeOptions' => ['Available', 'Not Available'],'afterOptions' => ['Available', 'Not Available']],
                                'postponement' => ['label' => 'Postponement','types' => ['Moderate', 'Flexible', 'Strict'],'beforeOptions' => ['Available', 'Not Available'],'afterOptions' => ['Available', 'Not Available']],
                                'payment' => ['label' => 'Payment','types' => ['100% Payment', '50% Payment', '20% Payment', '0% Payment'],'beforeOptions' => ['100% Payment', '50% Payment', '20% Payment', '0% Payment'],'afterOptions' => []],
                                'refund' => ['label' => 'Refund','types' => ['Refund Policy'],'beforeOptions' => [],'afterOptions' => []],
                                'security_deposit' => ['label' => 'Security Deposit','types' => ['Required'],'beforeOptions' => [],'afterOptions' => []],
                                'house_rules' => ['label' => 'House & Gen. Rules','types' => ['Applicable'],'beforeOptions' => [],'afterOptions' => []],
                            ];
                        @endphp

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 15%;">Policy</th>
                                        <th style="width: 20%;">Details (Type)</th>
                                        <th style="width: 22%;">Before Deadline</th>
                                        <th style="width: 22%;">After Deadline</th>
                                        <th style="width: 21%;">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($policyRows as $key => $meta)
                                        <tr>
                                            <td><strong>{{ $meta['label'] }}</strong></td>
                                            <td>
                                                @if(in_array($key, ['payment', 'refund', 'security_deposit', 'house_rules'], true))
                                                    <div class="form-control bg-light">{{ old('package_policy.' . $key . '.type', $policy[$key]['type'] ?? $meta['types'][0]) }}</div>
                                                @else
                                                    <select name="package_policy[{{ $key }}][type]" class="form-control">
                                                        <option value="">Select</option>
                                                        @foreach($meta['types'] as $type)
                                                            <option value="{{ $type }}" {{ old('package_policy.' . $key . '.type', $policy[$key]['type'] ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </td>
                                            <td>
                                                @if(($meta['beforeOptions'] ?? []) !== [])
                                                    <select name="package_policy[{{ $key }}][before_deadline]" class="form-control">
                                                        @foreach(($meta['beforeOptions'] ?? []) as $option)
                                                            <option value="{{ $option }}" {{ old('package_policy.' . $key . '.before_deadline', $policy[$key]['before_deadline'] ?? ($meta['beforeOptions'][0] ?? '')) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <input type="text" name="package_policy[{{ $key }}][before_deadline]" class="form-control" value="{{ old('package_policy.' . $key . '.before_deadline', $policy[$key]['before_deadline'] ?? '') }}" placeholder="-">
                                                @endif
                                            </td>
                                            <td>
                                                @if(($meta['afterOptions'] ?? []) !== [])
                                                    <select name="package_policy[{{ $key }}][after_deadline]" class="form-control">
                                                        @foreach(($meta['afterOptions'] ?? []) as $option)
                                                            <option value="{{ $option }}" {{ old('package_policy.' . $key . '.after_deadline', $policy[$key]['after_deadline'] ?? ($meta['afterOptions'][0] ?? '')) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <input type="text" name="package_policy[{{ $key }}][after_deadline]" class="form-control" value="{{ old('package_policy.' . $key . '.after_deadline', $policy[$key]['after_deadline'] ?? '') }}" placeholder="-">
                                                @endif
                                            </td>
                                            <td>
                                                <textarea name="package_policy[{{ $key }}][notes]" rows="2" class="form-control">{{ old('package_policy.' . $key . '.notes', $policy[$key]['notes'] ?? '') }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="card mt-4">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Booking Notes</label>
                                    <textarea name="package_policy[booking_notes]" rows="3" class="form-control">{{ old('package_policy.booking_notes', $policy['booking_notes'] ?? '') }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Package Notes</label>
                                    <textarea name="package_policy[package_notes]" rows="3" class="form-control">{{ old('package_policy.package_notes', $policy['package_notes'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary">Save Default Policy</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
