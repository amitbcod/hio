@extends('layouts.admin')

@section('content')
@php $currentStep = 7; @endphp
<div class="container-fluid" style="padding:24px;">
    <div class="row">
        {{-- Sidebar --}}
        <div class="col-md-3">
            @include('operator.activity._steps_sidebar')
        </div>

        {{-- Main Content --}}
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 16px rgba(0,0,0,0.07);position:relative;">
                <div style="margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h4 style="font-weight:600;color:#333;margin:0;">Step 7: Variants & Equipment</h4>
                        <p style="color:#666;margin:8px 0 0 0;">Manage equipment variants, amenities, capacity, and exclusivity options</p>
                    </div>
                    {{-- Top Right Button --}}
                    @if(!is_null($variant) && $variant->variant_id)
                        {{-- Add Another Variant Button (top right) when editing --}}
                        <button type="button" onclick="clearVariantForm()" class="btn btn-primary" style="background:#27ae60;border-color:#27ae60;padding:8px 16px;border-radius:6px;color:#fff;border:none;cursor:pointer;font-weight:600;white-space:nowrap;">
                            <i class="fas fa-plus"></i> Add Another Variant
                        </button>
                    @else
                        {{-- Add New Variant Button (top right) when not editing --}}
                        <button type="button" onclick="showAddForm()" class="btn btn-primary" style="background:#27ae60;border-color:#27ae60;padding:8px 16px;border-radius:6px;color:#fff;border:none;cursor:pointer;font-weight:600;white-space:nowrap;">
                            <i class="fas fa-plus"></i> Add New Variant
                        </button>
                    @endif
                </div>

                @if(session('success'))
                    <div class="alert alert-success" style="border-radius:12px;margin-bottom:20px;">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" style="border-radius:12px;margin-bottom:20px;">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                {{-- Existing Variants List --}}
                @if($variants && count($variants) > 0)
                <div style="margin-bottom:32px;">
                    <h5 style="font-weight:600;margin-bottom:16px;">Registered Variants / Equipment</h5>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
                        @foreach($variants as $v)
                        <div style="border:1px solid #ddd;border-radius:12px;padding:16px;background:#f9f9f9;position:relative;">
                            @if($v->equipment_image)
                            <img src="{{ asset('storage/' . $v->equipment_image) }}" alt="{{ $v->variant_name }}" style="width:100%;height:150px;object-fit:cover;border-radius:8px;margin-bottom:12px;">
                            @else
                            <div style="width:100%;height:150px;background:#e0e0e0;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;color:#999;">
                                <i class="fas fa-image" style="font-size:32px;"></i>
                            </div>
                            @endif
                            
                            <h6 style="font-weight:600;margin:0 0 8px 0;">{{ $v->variant_name }}</h6>
                            <p style="margin:4px 0;font-size:12px;color:#666;">
                                <strong>Tier:</strong> {{ $v->quality_tier }}
                            </p>
                            <p style="margin:4px 0;font-size:12px;color:#666;">
                                <strong>Capacity:</strong> {{ $v->max_pax }} pax (Min: {{ $v->min_participants }}, Max: {{ $v->max_participants }})
                            </p>
                            <p style="margin:4px 0;font-size:12px;color:#19b5b5;font-weight:600;">
                                <strong>Allotment:</strong> {{ $v->allotment ?? 0 }} seats/equipment
                            </p>
                            @if($v->amenities && count($v->amenities) > 0)
                            <p style="margin:4px 0;font-size:12px;color:#666;">
                                <strong>Amenities:</strong> {{ implode(', ', $v->amenities) }}
                            </p>
                            @endif
                            @if($v->safety_equipment && count($v->safety_equipment) > 0)
                            <p style="margin:4px 0;font-size:12px;color:#666;">
                                <strong>Safety:</strong> {{ implode(', ', $v->safety_equipment) }}
                            </p>
                            @endif
                            <p style="margin:4px 0;font-size:12px;color:#666;">
                                <strong>Exclusive:</strong> {{ $v->private_exclusive }}
                            </p>
                            
                            <div style="margin-top:12px;display:flex;gap:8px;border-top:1px solid #ddd;padding-top:12px;">
                                <a href="{{ route('operator.activity.step7.edit', [$activity->id, $v->variant_id]) }}" class="btn btn-sm" style="background:#19b5b5;color:#fff;padding:4px 12px;border-radius:4px;text-decoration:none;font-size:12px;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('operator.activity.step7.delete', [$activity->id, $v->variant_id]) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm" style="background:#dc3545;color:#fff;padding:4px 12px;border-radius:4px;border:none;font-size:12px;cursor:pointer;" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Operations & Staffing Listing --}}
                @if($operationsRecords && count($operationsRecords) > 0)
                <div style="margin-bottom:32px;">
                    <h5 style="font-weight:600;margin-bottom:16px;">Operations & Staffing Configuration</h5>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;border:1px solid #ddd;border-radius:8px;font-size:13px;">
                            <thead style="background:#f5f5f5;border-bottom:2px solid #ddd;">
                                <tr>
                                    <th style="padding:12px;text-align:left;font-weight:600;border-right:1px solid #ddd;">Variant</th>
                                    <th style="padding:12px;text-align:left;font-weight:600;border-right:1px solid #ddd;">Age Groups</th>
                                    <th style="padding:12px;text-align:left;font-weight:600;border-right:1px solid #ddd;">Crew/Guide</th>
                                    <th style="padding:12px;text-align:left;font-weight:600;border-right:1px solid #ddd;">Contact</th>
                                    <th style="padding:12px;text-align:center;font-weight:600;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($operationsRecords as $ops)
                                <tr style="border-bottom:1px solid #ddd;">
                                    <td style="padding:12px;border-right:1px solid #ddd;">
                                        <strong>{{ $ops->variant?->variant_name ?? 'N/A' }}</strong>
                                    </td>
                                    <td style="padding:12px;border-right:1px solid #ddd;">
                                        {{ implode(', ', $ops->age_groups ?? []) }}
                                    </td>
                                    <td style="padding:12px;border-right:1px solid #ddd;">
                                        @if($ops->crew_guide_count)
                                            {{ $ops->crew_guide_count }} crew
                                        @else
                                            <span style="color:#999;">—</span>
                                        @endif
                                    </td>
                                    <td style="padding:12px;border-right:1px solid #ddd;">
                                        <div style="font-size:12px;">
                                            <strong>{{ $ops->ops_contact_name ?? 'N/A' }}</strong><br>
                                            <span style="color:#666;">{{ $ops->ops_contact_mobile ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td style="padding:12px;text-align:center;">
                                        <button type="button" onclick="editOperations({{ $ops->operation_id }}, {{ $ops->variant_id }}, '{{ addslashes($ops->ops_contact_name) }}', '{{ addslashes($ops->ops_contact_mobile) }}', {{ json_encode($ops->age_groups) }}, '{{ addslashes($ops->pickup_options) }}', '{{ addslashes($ops->dropoff_options) }}', {{ json_encode($ops->accessibility_features) }}, {{ $ops->crew_guide_count ?? 'null' }}, '{{ addslashes($ops->crew_guide_requirements) }}', '{{ addslashes($ops->special_equipment_notes) }}')" class="btn btn-sm" style="background:#19b5b5;color:#fff;padding:4px 10px;border-radius:4px;border:none;font-size:11px;cursor:pointer;display:inline-block;">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form action="{{ route('operator.activity.step7.operations.delete', [$activity->id, $ops->operation_id]) }}" method="POST" style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="background:#dc3545;color:#fff;padding:4px 10px;border-radius:4px;border:none;font-size:11px;cursor:pointer;margin-left:4px;" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                @if(!is_null($variant) && $variant->variant_id)
                <div id="variantFormContainer" style="background:#f9f9f9;border-radius:12px;padding:20px;border:1px solid #e0e0e0;">
                    <h5 id="formTitle" style="font-weight:600;margin:0 0 16px 0;">Edit Variant</h5>

                    <form action="{{ route('operator.activity.step7.update', [$activity->id, $variant->variant_id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($variant) && $variant->variant_id)
                            @method('PUT')
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;">Variant Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="variant_name" 
                                       class="form-control @error('variant_name') is-invalid @enderror" 
                                       value="{{ old('variant_name', $variant->variant_name ?? '') }}" 
                                       placeholder="e.g., Lagoon 42 Catamaran"
                                       required>
                                @error('variant_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;">Quality Tier <span class="text-danger">*</span></label>
                                <select name="quality_tier" class="form-control @error('quality_tier') is-invalid @enderror" required>
                                    <option value="">Select tier</option>
                                    <option value="Standard" {{ old('quality_tier', $variant->quality_tier ?? '') === 'Standard' ? 'selected' : '' }}>Standard</option>
                                    <option value="Premium" {{ old('quality_tier', $variant->quality_tier ?? '') === 'Premium' ? 'selected' : '' }}>Premium</option>
                                    <option value="Luxury" {{ old('quality_tier', $variant->quality_tier ?? '') === 'Luxury' ? 'selected' : '' }}>Luxury</option>
                                </select>
                                @error('quality_tier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label style="font-weight:600;">Max Capacity (Pax) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="max_pax" 
                                       class="form-control @error('max_pax') is-invalid @enderror" 
                                       value="{{ old('max_pax', $variant->max_pax ?? '') }}" 
                                       min="1" 
                                       required>
                                @error('max_pax')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:600;">Min Participants <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="min_participants" 
                                       class="form-control @error('min_participants') is-invalid @enderror" 
                                       value="{{ old('min_participants', $variant->min_participants ?? 1) }}" 
                                       min="1" 
                                       required>
                                @error('min_participants')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:600;">Max Participants <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="max_participants" 
                                       class="form-control @error('max_participants') is-invalid @enderror" 
                                       value="{{ old('max_participants', $variant->max_participants ?? '') }}" 
                                       min="1" 
                                       required>
                                @error('max_participants')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label style="font-weight:600;">Allotment (Sellable Seats/Equipment) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="allotment" 
                                       class="form-control @error('allotment') is-invalid @enderror" 
                                       value="{{ old('allotment', $variant->allotment ?? '') }}" 
                                       min="0" 
                                       required>
                                <small style="color:#666;display:block;margin-top:4px;">Number of sellable seats/equipment - used in Step 10 for allotment management</small>
                                @error('allotment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;">Amenities</label>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                    @php
                                        $amenitiesList = ['WC', 'Shade', 'Music', 'Snorkel gear'];
                                        $selectedAmenities = old('amenities', $variant->amenities ?? []);
                                    @endphp
                                    @foreach($amenitiesList as $amenity)
                                    <label style="font-weight:normal;">
                                        <input type="checkbox" name="amenities[]" value="{{ $amenity }}" 
                                               {{ in_array($amenity, $selectedAmenities) ? 'checked' : '' }}>
                                        {{ $amenity }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;">Safety Equipment</label>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                    @php
                                        $safetyList = ['Lifejacket', 'Helmet', 'Harness', 'First Aid kit'];
                                        $selectedSafety = old('safety_equipment', $variant->safety_equipment ?? []);
                                    @endphp
                                    @foreach($safetyList as $safety)
                                    <label style="font-weight:normal;">
                                        <input type="checkbox" name="safety_equipment[]" value="{{ $safety }}" 
                                               {{ in_array($safety, $selectedSafety) ? 'checked' : '' }}>
                                        {{ $safety }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;">Private/Exclusive Available</label>
                                <select name="private_exclusive" class="form-control">
                                    <option value="No" {{ old('private_exclusive', $variant->private_exclusive ?? 'No') === 'No' ? 'selected' : '' }}>No</option>
                                    <option value="Yes" {{ old('private_exclusive', $variant->private_exclusive ?? '') === 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;">Equipment Image</label>
                                <input type="file" 
                                       name="equipment_image" 
                                       class="form-control @error('equipment_image') is-invalid @enderror" 
                                       accept="image/*">
                                @error('equipment_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if(isset($variant->equipment_image) && $variant->equipment_image)
                                    <small style="color:#19b5b5;display:block;margin-top:4px;">
                                        <i class="fas fa-check"></i> Current image uploaded
                                    </small>
                                @endif
                            </div>
                        </div>
                        {{-- Form Actions --}}
                        <div class="d-flex justify-content-between pt-3 border-top">
                            @if(isset($variant) && $variant->variant_id)
                            <a href="{{ route('operator.activity.show', $activity->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Activity
                            </a>
                            @else
                            <button type="button" onclick="hideAddForm()" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </button>
                            @endif
                            <button type="submit" class="btn" style="background:#19b5b5;color:#fff;">
                                <i class="fas fa-save me-2"></i>Update Variant
                            </button>
                        </div>
                    </form>
                </div>
                @endif

                {{-- Add Form (Hidden by default, shown when clicking "Add New Variant") --}}
                <div id="addFormContainer" style="background:#f9f9f9;border-radius:12px;padding:20px;border:1px solid #e0e0e0;display:none;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <h5 id="addFormTitle" style="font-weight:600;margin:0;">Add New Variant</h5>
                    </div>

                    <form id="addVariantForm" action="{{ route('operator.activity.step7.store', $activity->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;">Variant Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="variant_name" 
                                       class="form-control" 
                                       placeholder="e.g., Lagoon 42 Catamaran"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;">Quality Tier <span class="text-danger">*</span></label>
                                <select name="quality_tier" class="form-control" required>
                                    <option value="">Select tier</option>
                                    <option value="Standard">Standard</option>
                                    <option value="Premium">Premium</option>
                                    <option value="Luxury">Luxury</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label style="font-weight:600;">Max Capacity (Pax) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="max_pax" 
                                       class="form-control" 
                                       placeholder="e.g., 3"
                                       required>
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:600;">Min Participants <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="min_participants" 
                                       class="form-control" 
                                       placeholder="e.g., 1"
                                       required>
                            </div>
                            <div class="col-md-4">
                                <label style="font-weight:600;">Max Participants <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="max_participants" 
                                       class="form-control" 
                                       placeholder="e.g., 3"
                                       required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label style="font-weight:600;">Allotment (Sellable Seats/Equipment) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="allotment" 
                                       class="form-control" 
                                       placeholder="e.g., 10"
                                       min="0" 
                                       required>
                                <small style="color:#666;display:block;margin-top:4px;">Number of sellable seats/equipment - used in Step 10 for allotment management</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;">Amenities</label>
                                <div>
                                    <label><input type="checkbox" name="amenities[]" value="WC"> WC</label>
                                    <label><input type="checkbox" name="amenities[]" value="Shade"> Shade</label>
                                    <label><input type="checkbox" name="amenities[]" value="Music"> Music</label>
                                    <label><input type="checkbox" name="amenities[]" value="Snorkel gear"> Snorkel gear</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;">Safety Equipment</label>
                                <div>
                                    <label><input type="checkbox" name="safety_equipment[]" value="Lifejacket"> Lifejacket</label>
                                    <label><input type="checkbox" name="safety_equipment[]" value="Helmet"> Helmet</label>
                                    <label><input type="checkbox" name="safety_equipment[]" value="Harness"> Harness</label>
                                    <label><input type="checkbox" name="safety_equipment[]" value="First Aid kit"> First Aid kit</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;">Private/Exclusive Available</label>
                                <select name="private_exclusive" class="form-control">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;">Equipment Image</label>
                                <input type="file" 
                                       name="equipment_image" 
                                       class="form-control" 
                                       accept="image/*">
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <button type="button" onclick="hideAddForm()" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </button>
                            <button type="submit" class="btn" style="background:#19b5b5;color:#fff;">
                                <i class="fas fa-save me-2"></i>Add Variant
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Add Operations & Staffing Button --}}
                <div style="margin-top:32px;margin-bottom:32px;">
                    <button type="button" onclick="showOperationsForm()" class="btn btn-primary" style="background:#27ae60;border-color:#27ae60;padding:10px 20px;border-radius:6px;color:#fff;border:none;cursor:pointer;font-weight:600;">
                        <i class="fas fa-plus"></i> Add Operations & Staffing
                    </button>
                </div>

                {{-- Operations & Staffing Form (Hidden by default) --}}
                <div id="operationsFormContainer" style="margin-top:32px;background:#f9f9f9;border-radius:12px;padding:20px;border:1px solid #e0e0e0;display:none;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <h5 style="font-weight:600;margin:0;">Operations & Staffing</h5>
                        <button type="button" onclick="hideOperationsForm()" class="btn btn-sm" style="background:#999;color:#fff;padding:4px 12px;border-radius:4px;border:none;cursor:pointer;">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                    
                    <form id="operationsForm" action="{{ route('operator.activity.step7.operations', $activity->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="formMethod" value="POST">
                        <input type="hidden" name="operation_id" id="operationId" value="">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;">Variant / Equipment <span class="text-danger">*</span></label>
                                <select id="operationVariantSelect" name="variant_id" class="form-control @error('variant_id') is-invalid @enderror" required>
                                    <option value="">Select variant</option>
                                    @if($variants && count($variants) > 0)
                                        @foreach($variants as $v)
                                        <option value="{{ $v->variant_id }}" {{ old('variant_id') == $v->variant_id ? 'selected' : '' }}>
                                            {{ $v->variant_name }} ({{ $v->quality_tier }})
                                        </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('variant_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="duplicateWarning" style="display:none;margin-top:8px;padding:10px;background:#fff3cd;border-radius:6px;border-left:4px solid #ffc107;">
                                    <i class="fas fa-exclamation-triangle" style="color:#d39e00;"></i> 
                                    <span style="color:#d39e00;"><strong>Record already exists</strong> for this variant. </span>
                                    <a href="#" id="editExistingLink" onclick="handleExistingEdit(event)" style="color:#d39e00;text-decoration:underline;"><strong>Click here to edit</strong></a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;">Participant Age Groups <span class="text-danger">*</span></label>
                                <div>
                                    <label><input type="checkbox" name="age_groups[]" value="Adults"> Adults</label>
                                    <label><input type="checkbox" name="age_groups[]" value="Teens"> Teens</label>
                                    <label><input type="checkbox" name="age_groups[]" value="Children"> Children</label>
                                    <label><input type="checkbox" name="age_groups[]" value="Infant"> Infant</label>
                                </div>
                                @error('age_groups')
                                    <div style="color:#dc3545;font-size:12px;">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;">Pickup Options</label>
                                <textarea name="pickup_options" 
                                          class="form-control @error('pickup_options') is-invalid @enderror"
                                          placeholder="e.g., Hotel pickup available from city center (Yes/No + zones)"
                                          rows="3">{{ old('pickup_options', $operationsStaffing->pickup_options ?? '') }}</textarea>
                                @error('pickup_options')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;">Drop-off Options</label>
                                <textarea name="dropoff_options" 
                                          class="form-control @error('dropoff_options') is-invalid @enderror"
                                          placeholder="e.g., Drop-off options and zones"
                                          rows="3">{{ old('dropoff_options', $operationsStaffing->dropoff_options ?? '') }}</textarea>
                                @error('dropoff_options')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;">Accessibility Features</label>
                                <div>
                                    <label><input type="checkbox" name="accessibility_features[]" value="Ramps"> Ramps</label>
                                    <label><input type="checkbox" name="accessibility_features[]" value="Wheelchair Seating"> Wheelchair Seating</label>
                                    <label><input type="checkbox" name="accessibility_features[]" value="Handrails"> Handrails</label>
                                    <label><input type="checkbox" name="accessibility_features[]" value="Accessible Restroom"> Accessible Restroom</label>
                                    <label><input type="checkbox" name="accessibility_features[]" value="Audio Guide"> Audio Guide</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;">Crew/Guide Count</label>
                                <input type="number" 
                                       name="crew_guide_count" 
                                       class="form-control @error('crew_guide_count') is-invalid @enderror"
                                       placeholder="e.g., 2"
                                       min="1">
                                @error('crew_guide_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label style="font-weight:600;">Operations Contact Name</label>
                                <input type="text" 
                                       name="ops_contact_name" 
                                       class="form-control @error('ops_contact_name') is-invalid @enderror"
                                       value="{{ old('ops_contact_name', $activity->management_contact_name ?? '') }}"
                                       placeholder="Auto-filled from Management & Communication"
                                       readonly>
                                @error('ops_contact_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:600;">Operations Contact Mobile</label>
                                <input type="text" 
                                       name="ops_contact_mobile" 
                                       class="form-control @error('ops_contact_mobile') is-invalid @enderror"
                                       value="{{ old('ops_contact_mobile', $activity->management_contact_mobile ?? '') }}"
                                       placeholder="Auto-filled from Management & Communication"
                                       readonly>
                                @error('ops_contact_mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label style="font-weight:600;">Crew/Guide Requirements</label>
                                <textarea name="crew_guide_requirements" 
                                          class="form-control @error('crew_guide_requirements') is-invalid @enderror"
                                          placeholder="e.g., Guide must be CPR certified, First Aid certified"
                                          rows="3">{{ old('crew_guide_requirements', $operationsStaffing->crew_guide_requirements ?? '') }}</textarea>
                                @error('crew_guide_requirements')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label style="font-weight:600;">Special Equipment Notes</label>
                                <textarea name="special_equipment_notes" 
                                          class="form-control @error('special_equipment_notes') is-invalid @enderror"
                                          placeholder="e.g., Fuel requirements, maintenance windows, prep time before activity"
                                          rows="3">{{ old('special_equipment_notes', $operationsStaffing->special_equipment_notes ?? '') }}</textarea>
                                @error('special_equipment_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="submit" id="operationsSubmitBtn" class="btn" style="background:#19b5b5;color:#fff;">
                                <i class="fas fa-save me-2"></i><span id="submitBtnText">Save Operations & Staffing</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Step Completion Note --}}
                <div style="margin-top:24px;background:#e3f2fd;padding:16px;border-radius:8px;border-left:4px solid #2196f3;">
                    <p style="margin:0;color:#1565c0;font-size:14px;">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> You can add as many equipment variants as needed. At least one variant is recommended to mark this step as complete.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Store existing operations records data
const existingOperationsData = {
    @if($operationsRecords && count($operationsRecords) > 0)
        @foreach($operationsRecords as $ops)
            '{{ $ops->variant_id }}': {
                operation_id: {{ $ops->operation_id }},
                variant_id: {{ $ops->variant_id }},
                ops_contact_name: '{{ addslashes($ops->ops_contact_name) }}',
                ops_contact_mobile: '{{ addslashes($ops->ops_contact_mobile) }}',
                age_groups: {!! json_encode($ops->age_groups) !!},
                pickup_options: '{{ addslashes($ops->pickup_options) }}',
                dropoff_options: '{{ addslashes($ops->dropoff_options) }}',
                accessibility_features: {!! json_encode($ops->accessibility_features) !!},
                crew_guide_count: {{ $ops->crew_guide_count ?? 'null' }},
                crew_guide_requirements: '{{ addslashes($ops->crew_guide_requirements) }}',
                special_equipment_notes: '{{ addslashes($ops->special_equipment_notes) }}'
            },
        @endforeach
    @endif
};

// Add event listener to variant select
document.addEventListener('DOMContentLoaded', function() {
    const variantSelect = document.getElementById('operationVariantSelect');
    
    if (variantSelect) {
        // Check on page load if a variant is already selected
        checkVariantDuplicate();
        
        // Add change event listener
        variantSelect.addEventListener('change', checkVariantDuplicate);
    }
});

function checkVariantDuplicate() {
    const variantSelect = document.getElementById('operationVariantSelect');
    const selectedVariantId = variantSelect.value;
    const duplicateWarning = document.getElementById('duplicateWarning');
    
    if (selectedVariantId && existingOperationsData[selectedVariantId]) {
        // Record already exists for this variant
        duplicateWarning.style.display = 'block';
        document.getElementById('operationsSubmitBtn').disabled = true;
        document.getElementById('operationsSubmitBtn').style.opacity = '0.5';
        document.getElementById('operationsSubmitBtn').style.cursor = 'not-allowed';
    } else {
        // Record does not exist
        duplicateWarning.style.display = 'none';
        document.getElementById('operationsSubmitBtn').disabled = false;
        document.getElementById('operationsSubmitBtn').style.opacity = '1';
        document.getElementById('operationsSubmitBtn').style.cursor = 'pointer';
    }
}

function handleExistingEdit(e) {
    e.preventDefault();
    
    const variantSelect = document.getElementById('operationVariantSelect');
    const selectedVariantId = variantSelect.value;
    const existingData = existingOperationsData[selectedVariantId];
    
    if (existingData) {
        editOperations(
            existingData.operation_id,
            existingData.variant_id,
            existingData.ops_contact_name,
            existingData.ops_contact_mobile,
            existingData.age_groups,
            existingData.pickup_options,
            existingData.dropoff_options,
            existingData.accessibility_features,
            existingData.crew_guide_count,
            existingData.crew_guide_requirements,
            existingData.special_equipment_notes
        );
    }
}

function showAddForm() {
    // Show the add form container
    const addFormContainer = document.getElementById('addFormContainer');
    addFormContainer.style.display = 'block';
    
    // Reset the add form
    document.getElementById('addVariantForm').reset();
    
    // Scroll to form
    document.getElementById('addFormTitle').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideAddForm() {
    // Hide the add form container
    const addFormContainer = document.getElementById('addFormContainer');
    addFormContainer.style.display = 'none';
    
    // Scroll back to top (variants list)
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function clearVariantForm() {
    // Reset the edit form
    document.querySelector('form').reset();
    
    // Hide edit form and show add form
    const editFormContainer = document.getElementById('variantFormContainer');
    const addFormContainer = document.getElementById('addFormContainer');
    
    editFormContainer.style.display = 'none';
    addFormContainer.style.display = 'block';
    
    // Scroll to add form
    document.getElementById('addFormTitle').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function viewOperationsDetails(operationId) {
    // Show a modal or alert with operations details
    alert('View details for operation #' + operationId + '. You can edit or delete this record using the action buttons.');
}

function showOperationsForm() {
    // Show the operations form container
    const operationsFormContainer = document.getElementById('operationsFormContainer');
    operationsFormContainer.style.display = 'block';
    
    // Reset form to new record state
    document.getElementById('operationsForm').reset();
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('operationId').value = '';
    document.getElementById('submitBtnText').innerText = 'Save Operations & Staffing';
    
    // Update form action to store route
    document.getElementById('operationsForm').action = '{{ route('operator.activity.step7.operations', $activity->id) }}';
    
    // Clear variant_id selection
    document.querySelector('select[name="variant_id"]').value = '';
    
    // Hide duplicate warning
    document.getElementById('duplicateWarning').style.display = 'none';
    
    // Enable submit button
    checkVariantDuplicate();
    
    // Scroll to form
    document.getElementById('operationsForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideOperationsForm() {
    // Hide the operations form container
    const operationsFormContainer = document.getElementById('operationsFormContainer');
    operationsFormContainer.style.display = 'none';
    
    // Reset form
    document.getElementById('operationsForm').reset();
    
    // Scroll back to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function editOperations(operationId, variantId, contactName, contactMobile, ageGroups, pickupOptions, dropoffOptions, accessibilityFeatures, crewGuideCount, crewGuideRequirements, specialEquipmentNotes) {
    // Show the operations form container
    const operationsFormContainer = document.getElementById('operationsFormContainer');
    operationsFormContainer.style.display = 'block';
    
    // Get the form
    const form = document.getElementById('operationsForm');
    
    // Set form to edit mode
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('operationId').value = operationId;
    document.getElementById('submitBtnText').innerText = 'Update Operations & Staffing';
    
    // Update form action to update route
    form.action = '{{ route('operator.activity.step7.operations.update', [$activity->id, 'OPERATION_ID']) }}'.replace('OPERATION_ID', operationId);
    
    // Populate form fields
    // Variant ID
    document.querySelector('select[name="variant_id"]').value = variantId;
    
    // Age Groups - parse JSON and check checkboxes
    const ageGroupsArray = typeof ageGroups === 'string' ? JSON.parse(ageGroups) : (Array.isArray(ageGroups) ? ageGroups : []);
    const ageGroupCheckboxes = document.querySelectorAll('input[name="age_groups[]"]');
    ageGroupCheckboxes.forEach(checkbox => {
        checkbox.checked = ageGroupsArray.includes(checkbox.value);
    });
    
    // Accessibility Features - parse JSON and check checkboxes
    const accessibilityArray = typeof accessibilityFeatures === 'string' ? JSON.parse(accessibilityFeatures) : (Array.isArray(accessibilityFeatures) ? accessibilityFeatures : []);
    const accessibilityCheckboxes = document.querySelectorAll('input[name="accessibility_features[]"]');
    accessibilityCheckboxes.forEach(checkbox => {
        checkbox.checked = accessibilityArray.includes(checkbox.value);
    });
    
    // Text areas and other fields
    document.querySelector('textarea[name="pickup_options"]').value = pickupOptions || '';
    document.querySelector('textarea[name="dropoff_options"]').value = dropoffOptions || '';
    document.querySelector('textarea[name="crew_guide_requirements"]').value = crewGuideRequirements || '';
    document.querySelector('textarea[name="special_equipment_notes"]').value = specialEquipmentNotes || '';
    
    // Number field
    document.querySelector('input[name="crew_guide_count"]').value = crewGuideCount || '';
    
    // Auto-filled readonly fields (these should match the passed parameters)
    document.querySelector('input[name="ops_contact_name"]').value = contactName || '';
    document.querySelector('input[name="ops_contact_mobile"]').value = contactMobile || '';
    
    // Scroll to form
    document.getElementById('operationsForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>

<!-- Back Button -->
<div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
    <a href="{{ route('operator.activity.show', $activity->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
        ← Back to Activity Overview
    </a>
</div>
@endsection
