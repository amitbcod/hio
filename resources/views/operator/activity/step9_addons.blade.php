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
                            <h4 style="font-weight:600;color:#333;margin:0;">Fees & Add-Ons</h4>
                            <p style="margin:4px 0 0 0;font-size:13px;color:#666;">{{ $activity->activity_name }}</p>
                        </div>
                        <div style="text-align:right;">
                            <a href="{{ route('operator.activity.step9.show', $activity->id) }}" style="padding:8px 16px;background:#f0f0f0;color:#333;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600;">
                                ← Back to Rates
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div style="background:#d4edda;border:1px solid #c3e6cb;border-radius:8px;padding:12px;margin-bottom:16px;color:#155724;">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div style="background:#f8d7da;border:1px solid #f5c2c7;border-radius:8px;padding:12px;margin-bottom:16px;color:#842029;">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Info Box -->
                <div style="background:#e7f3ff;border:1px solid #2196f3;border-radius:8px;padding:12px;margin-bottom:16px;">
                    <p style="margin:0;font-size:13px;color:#0d47a1;">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> Add-ons are optional or compulsory extras that can be linked to specific variants. Examples: Hotel Pickup, BBQ Upgrade, Exclusivity F/B Package.
                    </p>
                </div>

                <!-- Add New Button -->
                <div style="margin-bottom:16px;text-align:right;">
                    <button type="button" onclick="openAddonForm()" style="padding:10px 20px;background:#ff9800;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">
                        <i class="fas fa-plus"></i> Add New Add-On
                    </button>
                </div>

                <!-- Add-Ons List -->
                @if($addons->count() > 0)
                    <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:20px;">
                        <h5 style="margin:0 0 16px 0;font-weight:600;color:#333;font-size:14px;">Current Add-Ons</h5>
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                                <thead>
                                    <tr style="background:#f5f5f5;border-bottom:2px solid #e0e0e0;">
                                        <th style="padding:12px;text-align:left;font-weight:600;">Add-On Name</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Pricing Type</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Price (MUR)</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Type</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Variant</th>
                                        <th style="padding:12px;text-align:center;font-weight:600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($addons as $addon)
                                        <tr style="border-bottom:1px solid #e0e0e0;">
                                            <td style="padding:12px;">{{ $addon->addon_name }}</td>
                                            <td style="padding:12px;">{{ $addon->pricing_type }}</td>
                                            <td style="padding:12px;">{{ number_format($addon->price, 2) }}</td>
                                            <td style="padding:12px;">
                                                <span style="padding:4px 8px;background:{{ $addon->addon_type === 'Compulsory' ? '#ffebee' : '#e8f5e9' }};color:{{ $addon->addon_type === 'Compulsory' ? '#c62828' : '#2e7d32' }};border-radius:4px;font-size:11px;font-weight:600;">
                                                    {{ $addon->addon_type }}
                                                </span>
                                            </td>
                                            <td style="padding:12px;font-size:12px;">{{ $addon->variant_name ?? 'All Variants' }}</td>
                                            <td style="padding:12px;text-align:center;">
                                                <button type="button"
                                                    class="addon-edit"
                                                    data-addon-id="{{ $addon->addon_id }}"
                                                    data-addon-name="{{ $addon->addon_name }}"
                                                    data-pricing-type="{{ $addon->pricing_type }}"
                                                    data-price="{{ $addon->price }}"
                                                    data-addon-type="{{ $addon->addon_type }}"
                                                    data-variant-id="{{ $addon->variant_id ?? '' }}"
                                                    data-availability-rules="{{ $addon->availability_rules ?? '' }}"
                                                    style="padding:6px 10px;background:#e3f2fd;color:#1565c0;border:none;border-radius:4px;cursor:pointer;font-size:12px;margin-right:4px;">
                                                    Edit
                                                </button>
                                                <form action="{{ route('operator.activity.step9.addons.delete', [$activity->id, $addon->addon_id]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this add-on?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="padding:6px 10px;background:#ffebee;color:#c62828;border:none;border-radius:4px;cursor:pointer;font-size:12px;">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div style="background:#fff;border-radius:12px;padding:32px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:20px;">
                        <p style="color:#999;font-size:14px;">No add-ons created yet. Click "Add New Add-On" to get started.</p>
                    </div>
                @endif

                <!-- Form Container -->
                <div id="addonFormContainer" style="background:#fff;border-radius:12px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;display:none;border:2px solid #ff9800;">
                    <h5 style="margin:0 0 20px 0;font-weight:600;color:#333;" id="formTitle">Add New Add-On</h5>

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

                    <form id="addonForm" method="POST" action="{{ route('operator.activity.step9.addons.store', $activity->id) }}">
                        @csrf
                        <input type="hidden" id="formMethod" name="_method" value="POST">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Add-On Name *</label>
                                <input type="text" name="addon_name" class="form-control" required style="font-size:13px;" placeholder="e.g., Hotel Pickup, BBQ Upgrade">
                                @error('addon_name')<small style="color:#dc3545;">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Pricing Type *</label>
                                <select name="pricing_type" class="form-control" required style="font-size:13px;">
                                    <option value="">Select</option>
                                    <option value="Per Person">Per Person</option>
                                    <option value="Per Booking">Per Booking</option>
                                </select>
                                @error('pricing_type')<small style="color:#dc3545;">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Price (MUR) *</label>
                                <input type="number" name="price" class="form-control" required step="0.01" min="0" style="font-size:13px;">
                                @error('price')<small style="color:#dc3545;">{{ $message }}</small>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Add-On Type *</label>
                                <select name="addon_type" class="form-control" required style="font-size:13px;">
                                    <option value="">Select</option>
                                    <option value="Optional">Optional</option>
                                    <option value="Compulsory">Compulsory</option>
                                </select>
                                @error('addon_type')<small style="color:#dc3545;">{{ $message }}</small>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label style="font-weight:600;font-size:13px;">Link to Variant (Optional)</label>
                                <select name="variant_id" class="form-control" style="font-size:13px;">
                                    <option value="">All Variants</option>
                                    @foreach($variants as $variant)
                                        <option value="{{ $variant->variant_id }}">{{ $variant->variant_name }}</option>
                                    @endforeach
                                </select>
                                <small style="color:#666;font-size:12px;">Leave blank to apply to all variants</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label style="font-weight:600;font-size:13px;">Availability Rules (Optional)</label>
                                <textarea name="availability_rules" class="form-control" rows="3" style="font-size:13px;" placeholder="e.g., Available only on weekends, Limited to morning slots"></textarea>
                                <small style="color:#666;font-size:12px;">Specify if this add-on is limited to certain slots/dates</small>
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
                            <button type="button" onclick="hideAddonForm()" style="padding:10px 20px;background:#f0f0f0;color:#333;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">Cancel</button>
                            <button type="submit" style="padding:10px 20px;background:#ff9800;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;"><span id="submitBtnText">Save Add-On</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Edit buttons
            document.querySelectorAll('.addon-edit').forEach(button => {
                button.addEventListener('click', function() {
                    openAddonForm(this.dataset);
                });
            });

            @if($errors->any())
                openAddonForm();
            @endif
        });

        function hideAddonForm() {
            document.getElementById('addonFormContainer').style.display = 'none';
            document.getElementById('addonForm').reset();
        }

        function openAddonForm(data = null) {
            const form = document.getElementById('addonForm');
            form.reset();

            const isEdit = data && data.addonId;
            document.getElementById('formMethod').value = isEdit ? 'PUT' : 'POST';
            document.getElementById('formTitle').innerText = isEdit ? 'Edit Add-On' : 'Add New Add-On';
            document.getElementById('submitBtnText').innerText = isEdit ? 'Update Add-On' : 'Save Add-On';
            form.action = isEdit
                ? '{{ route('operator.activity.step9.addons.update', [$activity->id, '__ADDON_ID__']) }}'.replace('__ADDON_ID__', data.addonId)
                : '{{ route('operator.activity.step9.addons.store', $activity->id) }}';

            if (isEdit) {
                document.querySelector('input[name="addon_name"]').value = data.addonName;
                document.querySelector('select[name="pricing_type"]').value = data.pricingType;
                document.querySelector('input[name="price"]').value = data.price;
                document.querySelector('select[name="addon_type"]').value = data.addonType;
                document.querySelector('select[name="variant_id"]').value = data.variantId || '';
                document.querySelector('textarea[name="availability_rules"]').value = data.availabilityRules || '';
            }

            document.getElementById('addonFormContainer').style.display = 'block';
            document.getElementById('addonFormContainer').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    </script>

<!-- Back Button -->
<div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
    <a href="{{ route('operator.activity.show', $activity->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
        ← Back to Activity Overview
    </a>
</div>
@endsection
