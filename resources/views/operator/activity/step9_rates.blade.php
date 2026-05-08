@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <!-- Sidebar -->
            @php $currentStep = 9; @endphp
            <div class="col-md-3">
                @include('operator.activity._steps_sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Header -->
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:start;">
                        <div>
                            <h4 style="font-weight:600;color:#333;margin:0;">Step 9: Rates</h4>
                            <p style="margin:4px 0 0 0;font-size:13px;color:#666;">{{ $activity->activity_name }}</p>
                        </div>
                        <div style="text-align:right;">
                            <p style="margin:0;font-size:12px;color:#999;">Activity ID: {{ $activity->service_id }}</p>
                            <p style="margin:4px 0 0 0;font-size:12px;color:#19b5b5;font-weight:600;">Variants: {{ $variants->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px;margin-bottom:16px;">
                    <p style="margin:0;font-size:13px;color:#856404;">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> Define pricing for activity variants. You can set per-person or per-equipment rates with seasonal variations.
                    </p>
                </div>

                @php
                    // Build rate map from database
                    $rateMap = [];
                    $usedSeasons = [];
                    foreach ($rates as $rate) {
                        $seasonKey = $rate->season ?: 'One Season';
                        $rateMap[$rate->variant_id][$rate->rate_specificity][$seasonKey] = $rate;
                        
                        // Track which seasons are actually used per variant
                        if (!isset($usedSeasons[$rate->variant_id])) {
                            $usedSeasons[$rate->variant_id] = [];
                        }
                        if (!in_array($seasonKey, $usedSeasons[$rate->variant_id])) {
                            $usedSeasons[$rate->variant_id][] = $seasonKey;
                        }
                    }
                @endphp

                <!-- Variant Rate Grid -->
                @if($variants->count() > 0)
                    <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:20px;">
                        <h5 style="margin:0 0 16px 0;font-weight:600;color:#333;font-size:14px;">Rates Overview</h5>
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                                <thead>
                                    <tr style="background:#f5f5f5;border-bottom:2px solid #e0e0e0;">
                                        <th style="padding:12px;text-align:left;font-weight:600;">Variant</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Season</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Specificity</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Season Period</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Rates</th>
                                        <th style="padding:12px;text-align:center;font-weight:600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($variants as $variant)
                                        @php
                                            // Determine which seasons to show for this variant
                                            // Always show "One Season", plus any other seasons that have data
                                            $seasonsToShow = ['One Season'];
                                            if (isset($usedSeasons[$variant->variant_id])) {
                                                foreach ($usedSeasons[$variant->variant_id] as $usedSeason) {
                                                    if ($usedSeason !== 'One Season' && !in_array($usedSeason, $seasonsToShow)) {
                                                        $seasonsToShow[] = $usedSeason;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        @foreach(['Per Person', 'Per Equipment'] as $specificity)
                                            @foreach($seasonsToShow as $seasonName)
                                                @php
                                                    $rate = $rateMap[$variant->variant_id][$specificity][$seasonName] ?? null;
                                                    $sessionLabel = $rate && $rate->valid_from && $rate->valid_to
                                                        ? $rate->valid_from->format('d M Y') . ' - ' . $rate->valid_to->format('d M Y')
                                                        : 'Not set';
                                                @endphp
                                                <tr style="border-bottom:1px solid #e0e0e0;">
                                                    <td style="padding:12px;">{{ $variant->variant_name }}</td>
                                                    <td style="padding:12px;">{{ $seasonName }}</td>
                                                    <td style="padding:12px;">{{ $specificity }}</td>
                                                    <td style="padding:12px;font-size:12px;">{{ $sessionLabel }}</td>
                                                    <td style="padding:12px;font-size:12px;">
                                                        @if($specificity === 'Per Person')
                                                            Adult: {{ $rate?->adult_rate ?? '-' }} · Teen: {{ $rate?->teen_rate ?? '-' }} · Child: {{ $rate?->children_rate ?? '-' }} · Infant: {{ $rate?->infant_rate ?? '-' }}
                                                        @else
                                                            Equipment: {{ $rate?->equipment_rate ?? '-' }}
                                                        @endif
                                                        @if($rate?->private_exclusive_rate)
                                                            <div style="margin-top:4px;color:#666;">Private: {{ $rate->private_exclusive_rate }}</div>
                                                        @endif
                                                    </td>
                                                    <td style="padding:12px;text-align:center;">
                                                        <button type="button"
                                                            class="rate-action"
                                                            data-action="session"
                                                            data-rate-id="{{ $rate?->rate_id ?? '' }}"
                                                            data-variant-id="{{ $variant->variant_id }}"
                                                            data-season="{{ $seasonName }}"
                                                            data-specificity="{{ $specificity }}"
                                                            data-valid-from="{{ $rate?->valid_from ? $rate->valid_from->format('Y-m-d') : '' }}"
                                                            data-valid-to="{{ $rate?->valid_to ? $rate->valid_to->format('Y-m-d') : '' }}"
                                                            data-adult-rate="{{ $rate?->adult_rate ?? '' }}"
                                                            data-teen-rate="{{ $rate?->teen_rate ?? '' }}"
                                                            data-children-rate="{{ $rate?->children_rate ?? '' }}"
                                                            data-infant-rate="{{ $rate?->infant_rate ?? '' }}"
                                                            data-equipment-rate="{{ $rate?->equipment_rate ?? '' }}"
                                                            data-private-exclusive-rate="{{ $rate?->private_exclusive_rate ?? '' }}"
                                                            style="padding:6px 10px;background:#e3f2fd;color:#1565c0;border:none;border-radius:4px;cursor:pointer;font-size:12px;">
                                                            {{ $rate ? 'Edit Rate' : 'Set Season & Rate' }}
                                                        </button>
                                                        @if($rate)
                                                            <button type="button"
                                                                class="rate-duplicate"
                                                                data-rate-id="{{ $rate->rate_id }}"
                                                                data-variant-id="{{ $variant->variant_id }}"
                                                                data-specificity="{{ $specificity }}"
                                                                data-valid-from="{{ $rate->valid_from->format('Y-m-d') }}"
                                                                data-valid-to="{{ $rate->valid_to->format('Y-m-d') }}"
                                                                data-adult-rate="{{ $rate->adult_rate ?? '' }}"
                                                                data-teen-rate="{{ $rate->teen_rate ?? '' }}"
                                                                data-children-rate="{{ $rate->children_rate ?? '' }}"
                                                                data-infant-rate="{{ $rate->infant_rate ?? '' }}"
                                                                data-equipment-rate="{{ $rate->equipment_rate ?? '' }}"
                                                                data-private-exclusive-rate="{{ $rate->private_exclusive_rate ?? '' }}"
                                                                style="padding:6px 10px;background:#fff3cd;color:#856404;border:none;border-radius:4px;cursor:pointer;font-size:12px;margin-left:4px;">
                                                                Duplicate
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div style="background:#fff;border-radius:12px;padding:32px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:20px;">
                        <p style="color:#999;font-size:14px;">No variants found. Please add variants in Step 7 first.</p>
                    </div>
                @endif

                <!-- Fees & Add-Ons Link -->
                <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:20px;border-left:4px solid #ff9800;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <h5 style="margin:0 0 8px 0;font-weight:600;color:#333;font-size:14px;">
                                <i class="fas fa-plus-circle" style="color:#ff9800;margin-right:8px;"></i>Fees & Add-Ons
                            </h5>
                            <p style="margin:0;font-size:12px;color:#666;">
                                Manage optional or compulsory add-ons like hotel pickup, BBQ upgrades, exclusivity packages, etc.
                            </p>
                        </div>
                        <a href="{{ route('operator.activity.step9.addons', $activity->id) }}" style="padding:10px 20px;background:#ff9800;color:#fff;border:none;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;white-space:nowrap;">
                            Manage Add-Ons
                        </a>
                    </div>
                </div>

                <!-- Form Container -->
                <div id="rateFormContainer" style="background:#fff;border-radius:12px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;display:none;border:2px solid #e3f2fd;">
                    <h5 style="margin:0 0 20px 0;font-weight:600;color:#333;">Set Rate Details</h5>

                    @if($errors->any())
                        <div style="background:#f8d7da;border:1px solid #f5c2c7;border-radius:6px;padding:12px;margin-bottom:16px;">
                            <strong style="color:#842029;">Validation Errors:</strong>
                            <ul style="margin:8px 0 0 0;padding-left:20px;color:#842029;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="rateForm" method="POST" action="{{ route('operator.activity.step9.store', $activity->id) }}">
                        @csrf
                        <input type="hidden" id="formMethod" name="_method" value="POST">
                        <input type="hidden" id="rateId" name="rate_id" value="">
                        <input type="hidden" id="seasonValue" name="season" value="One Season">
                        <input type="hidden" id="rateSpecificityValue" name="rate_specificity" value="">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Variant Name *</label>
                                <select name="variant_id" id="variantSelect" class="form-control" required style="font-size:13px;">
                                    <option value="">Select variant</option>
                                    @foreach($variants as $variant)
                                        <option value="{{ $variant->variant_id }}">{{ $variant->variant_name }}</option>
                                    @endforeach
                                </select>
                                @error('variant_id')<small style="color:#dc3545;">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Season *</label>
                                <select id="seasonDisplay" class="form-control" style="font-size:13px;">
                                    <option value="One Season">One Season</option>
                                    <option value="High">High</option>
                                    <option value="Low">Low</option>
                                    <option value="Peak">Peak</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Valid From *</label>
                                <input type="date" name="valid_from" class="form-control" required style="font-size:13px;">
                                @error('valid_from')<small style="color:#dc3545;">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Valid To *</label>
                                <input type="date" name="valid_to" class="form-control" required style="font-size:13px;">
                                @error('valid_to')<small style="color:#dc3545;">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label style="font-weight:600;font-size:13px;">Rate Specificity *</label>
                                <select id="rateSpecificity" class="form-control" required style="font-size:13px;">
                                    <option value="">Select</option>
                                    <option value="Per Person">Per Person</option>
                                    <option value="Per Equipment">Per Equipment</option>
                                </select>
                                @error('rate_specificity')<small style="color:#dc3545;">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <div id="perPersonSection" style="display:none;padding:12px;background:#f9f9f9;border-radius:6px;border:1px solid #e0e0e0;margin-bottom:16px;">
                            <h6 style="margin:0 0 12px 0;font-weight:600;color:#333;">Per Person Rates</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label style="font-weight:600;font-size:13px;">Adult Rate (MUR) *</label>
                                    <input type="number" name="adult_rate" class="form-control" step="0.01" min="0" style="font-size:13px;">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="font-weight:600;font-size:13px;">Teen Rate (MUR) *</label>
                                    <input type="number" name="teen_rate" class="form-control" step="0.01" min="0" style="font-size:13px;">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label style="font-weight:600;font-size:13px;">Children Rate (MUR) *</label>
                                    <input type="number" name="children_rate" class="form-control" step="0.01" min="0" style="font-size:13px;">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label style="font-weight:600;font-size:13px;">Infant Rate (MUR) *</label>
                                    <input type="number" name="infant_rate" class="form-control" step="0.01" min="0" style="font-size:13px;">
                                </div>
                            </div>
                        </div>

                        <div id="perEquipmentSection" style="display:none;padding:12px;background:#f9f9f9;border-radius:6px;border:1px solid #e0e0e0;margin-bottom:16px;">
                            <h6 style="margin:0 0 12px 0;font-weight:600;color:#333;">Per Equipment Rate</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label style="font-weight:600;font-size:13px;">Equipment Rate (MUR) *</label>
                                    <input type="number" name="equipment_rate" class="form-control" step="0.01" min="0" style="font-size:13px;">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Private/Exclusive Rate (MUR)</label>
                                <input type="number" name="private_exclusive_rate" class="form-control" step="0.01" min="0" style="font-size:13px;">
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
                            <button type="button" onclick="hideRateForm()" style="padding:10px 20px;background:#f0f0f0;color:#333;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">Cancel</button>
                            <button type="submit" style="padding:10px 20px;background:#19b5b5;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;"><span id="submitBtnText">Save Rate</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Set/Edit Rate buttons
            document.querySelectorAll('.rate-action').forEach(button => {
                button.addEventListener('click', function() {
                    openRateForm(this.dataset, false);
                });
            });

            // Handle Duplicate buttons
            document.querySelectorAll('.rate-duplicate').forEach(button => {
                button.addEventListener('click', function() {
                    openRateForm(this.dataset, true);
                });
            });

            // Handle rate specificity change to show/hide rate sections
            const rateSpecificity = document.getElementById('rateSpecificity');
            rateSpecificity.addEventListener('change', function() {
                if (this.value === 'Per Person') {
                    document.getElementById('perPersonSection').style.display = 'block';
                    document.getElementById('perEquipmentSection').style.display = 'none';
                } else if (this.value === 'Per Equipment') {
                    document.getElementById('perPersonSection').style.display = 'none';
                    document.getElementById('perEquipmentSection').style.display = 'block';
                } else {
                    document.getElementById('perPersonSection').style.display = 'none';
                    document.getElementById('perEquipmentSection').style.display = 'none';
                }
            });

            const rateForm = document.getElementById('rateForm');
            if (rateForm) {
                rateForm.addEventListener('submit', function() {
                    document.getElementById('seasonValue').value = document.getElementById('seasonDisplay').value;
                    document.getElementById('rateSpecificityValue').value = document.getElementById('rateSpecificity').value;
                });
            }

            @if($errors->any())
                openRateForm({
                    action: 'session',
                    rateId: '',
                    variantId: '{{ old('variant_id', '') }}',
                    season: '{{ old('season', 'One Season') }}',
                    specificity: '{{ old('rate_specificity', '') }}',
                    validFrom: '{{ old('valid_from', '') }}',
                    validTo: '{{ old('valid_to', '') }}',
                    adultRate: '{{ old('adult_rate', '') }}',
                    teenRate: '{{ old('teen_rate', '') }}',
                    childrenRate: '{{ old('children_rate', '') }}',
                    infantRate: '{{ old('infant_rate', '') }}',
                    equipmentRate: '{{ old('equipment_rate', '') }}',
                    privateExclusiveRate: '{{ old('private_exclusive_rate', '') }}'
                }, false);
            @endif
        });

        function hideRateForm() {
            document.getElementById('rateFormContainer').style.display = 'none';
            document.getElementById('rateForm').reset();
            document.getElementById('perPersonSection').style.display = 'none';
            document.getElementById('perEquipmentSection').style.display = 'none';
        }

        function openRateForm(data, isDuplicate = false) {
            const form = document.getElementById('rateForm');
            form.reset();

            // If duplicating, treat as new rate (no rate_id) but populate all fields
            const isEdit = !isDuplicate && data.rateId !== '';
            document.getElementById('formMethod').value = isEdit ? 'PUT' : 'POST';
            document.getElementById('rateId').value = isEdit ? data.rateId : '';
            document.getElementById('submitBtnText').innerText = isEdit ? 'Update Rate' : (isDuplicate ? 'Save Duplicate Rate' : 'Save Rate');
            form.action = isEdit
                ? '{{ route('operator.activity.step9.update', [$activity->id, '__RATE_ID__']) }}'.replace('__RATE_ID__', data.rateId)
                : '{{ route('operator.activity.step9.store', $activity->id) }}';

            document.getElementById('variantSelect').value = data.variantId;
            
            // If duplicating, let operator choose season; if editing, use existing season
            if (isDuplicate) {
                document.getElementById('seasonDisplay').value = 'High'; // Default to High for duplicate
                document.getElementById('seasonValue').value = 'High';
            } else {
                document.getElementById('seasonDisplay').value = data.season || 'One Season';
                document.getElementById('seasonValue').value = data.season || 'One Season';
            }
            
            document.getElementById('rateSpecificity').value = data.specificity;
            document.getElementById('rateSpecificityValue').value = data.specificity;

            document.querySelector('input[name="valid_from"]').value = data.validFrom || '';
            document.querySelector('input[name="valid_to"]').value = data.validTo || '';

            if (data.specificity === 'Per Person') {
                document.getElementById('perPersonSection').style.display = 'block';
                document.getElementById('perEquipmentSection').style.display = 'none';
                document.querySelector('input[name="adult_rate"]').value = data.adultRate || '';
                document.querySelector('input[name="teen_rate"]').value = data.teenRate || '';
                document.querySelector('input[name="children_rate"]').value = data.childrenRate || '';
                document.querySelector('input[name="infant_rate"]').value = data.infantRate || '';
            } else if (data.specificity === 'Per Equipment') {
                document.getElementById('perPersonSection').style.display = 'none';
                document.getElementById('perEquipmentSection').style.display = 'block';
                document.querySelector('input[name="equipment_rate"]').value = data.equipmentRate || '';
            } else {
                document.getElementById('perPersonSection').style.display = 'none';
                document.getElementById('perEquipmentSection').style.display = 'none';
            }

            document.querySelector('input[name="private_exclusive_rate"]').value = data.privateExclusiveRate || '';

            document.getElementById('rateFormContainer').style.display = 'block';
            document.getElementById('rateFormContainer').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    </script>

<!-- Back Button -->
<div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
    <a href="{{ route('operator.activity.show', $activity->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
        ← Back to Activity Overview
    </a>
</div>
@endsection
