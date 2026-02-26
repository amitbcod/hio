@extends('layouts.app')

@section('content')
@php $currentStep = 8; @endphp
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
                        <h4 style="font-weight:600;color:#333;margin:0;">Step 8: Scheduling TimeSlots</h4>
                        <p style="color:#666;margin:8px 0 0 0;">Configure availability, schedule types, and booking slots for your activity variants</p>
                    </div>
                </div>

                {{-- Activity Info --}}
                <div style="background:#f9f9f9;border-radius:12px;padding:16px;margin-bottom:24px;border:1px solid #e0e0e0;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div>
                            <p style="margin:0 0 4px 0;font-size:13px;color:#999;font-weight:600;text-transform:uppercase;">Activity Name</p>
                            <p style="margin:0;font-size:15px;color:#333;font-weight:600;">{{ $activity->activity_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p style="margin:0 0 4px 0;font-size:13px;color:#999;font-weight:600;text-transform:uppercase;">Total Variants</p>
                        <p style="margin:0;font-size:15px;color:#333;font-weight:600;">{{ count($variants) }}</p>
                    </div>
                </div>
            </div>

            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div style="background:#d4edda;border:1px solid #c3e6cb;border-radius:6px;padding:12px 16px;margin-bottom:20px;color:#155724;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div style="background:#f8d7da;border:1px solid #f5c6cb;border-radius:6px;padding:12px 16px;margin-bottom:20px;color:#721c24;">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            {{-- TimeSlots Listing --}}
            @if(count($timeSlots) > 0)
            <div style="background:#fff;border-radius:12px;padding:20px;margin-bottom:32px;border:1px solid #e0e0e0;">
                <h5 style="font-weight:600;margin-bottom:16px;">TimeSlots Configured ({{ count($timeSlots) }})</h5>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead style="background:#f5f5f5;border-bottom:2px solid #ddd;">
                            <tr>
                                <th style="padding:12px;text-align:left;font-weight:600;border-right:1px solid #ddd;">Variant</th>
                                <th style="padding:12px;text-align:left;font-weight:600;border-right:1px solid #ddd;">Type</th>
                                <th style="padding:12px;text-align:left;font-weight:600;border-right:1px solid #ddd;">Time Window</th>
                                <th style="padding:12px;text-align:left;font-weight:600;border-right:1px solid #ddd;">Capacity</th>
                                <th style="padding:12px;text-align:left;font-weight:600;border-right:1px solid #ddd;">Billing</th>
                                <th style="padding:12px;text-align:center;font-weight:600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeSlots as $slot)
                            <tr style="border-bottom:1px solid #ddd;">
                                <td style="padding:12px;border-right:1px solid #ddd;">
                                    <strong>{{ $slot->variant_name ?? 'N/A' }}</strong>
                                </td>
                                <td style="padding:12px;border-right:1px solid #ddd;">
                                    <span style="display:inline-block;background:#e3f2fd;color:#1565c0;padding:4px 8px;border-radius:4px;font-size:11px;font-weight:600;">
                                        {{ $slot->schedule_type }}
                                    </span>
                                </td>
                                <td style="padding:12px;border-right:1px solid #ddd;">
                                    <div style="font-size:12px;">
                                        <strong>{{ date('H:i', strtotime($slot->start_time)) }} - {{ date('H:i', strtotime($slot->end_time)) }}</strong><br>
                                        <span style="color:#666;">{{ $slot->duration }}</span>
                                    </div>
                                </td>
                                    </div>
                                </td>
                                <td style="padding:12px;border-right:1px solid #ddd;">
                                    <span style="display:inline-block;background:#f0f0f0;padding:4px 8px;border-radius:4px;font-weight:600;">
                                        {{ $slot->capacity_per_slot }}
                                    </span>
                                </td>
                                <td style="padding:12px;border-right:1px solid #ddd;">
                                    <span style="font-size:11px;">{{ $slot->participant_equipment_id }}</span>
                                </td>
                                <td style="padding:12px;text-align:center;">
                                    <button type="button" class="btn btn-sm editTimeSlotBtn" 
                                            data-timeslot-id="{{ $slot->timeslot_id }}"
                                            data-variant-id="{{ $slot->variant_id }}"
                                            data-participant-equipment="{{ $slot->participant_equipment_id }}"
                                            data-capacity="{{ $slot->capacity_per_slot }}"
                                            data-schedule-type="{{ $slot->schedule_type }}"
                                            data-start-time="{{ substr($slot->start_time, 0, 5) }}"
                                            data-end-time="{{ substr($slot->end_time, 0, 5) }}"
                                            data-duration="{{ $slot->duration }}"
                                            data-recurring="{{ $slot->recurring ?? '' }}"
                                            data-lead-time="{{ $slot->lead_time_minutes ?? '' }}"
                                            data-days='{!! json_encode($slot->days_of_week) !!}'
                                            style="background:#19b5b5;color:#fff;padding:4px 10px;border-radius:4px;border:none;font-size:11px;cursor:pointer;display:inline-block;">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form action="{{ route('operator.activity.step8.delete', [$activity->id, $slot->timeslot_id]) }}" method="POST" style="display:inline;">
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

            {{-- Add TimeSlot Button --}}
            <div style="margin-bottom:32px;">
                <button type="button" onclick="showTimeSlotForm()" class="btn btn-primary" style="background:#27ae60;border-color:#27ae60;padding:10px 20px;border-radius:6px;color:#fff;border:none;cursor:pointer;font-weight:600;">
                    <i class="fas fa-plus"></i> Add TimeSlot
                </button>
            </div>

            {{-- TimeSlot Form (Hidden by default) --}}
            <div id="timeSlotFormContainer" style="background:#f9f9f9;border-radius:12px;padding:20px;border:1px solid #e0e0e0;display:none;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h5 style="font-weight:600;margin:0;">Scheduling TimeSlot</h5>
                    <button type="button" onclick="hideTimeSlotForm()" class="btn btn-sm" style="background:#999;color:#fff;padding:4px 12px;border-radius:4px;border:none;cursor:pointer;">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>

                <form id="timeSlotForm" action="{{ route('operator.activity.step8.store', $activity->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="timeslot_id" id="timeslotId" value="">

                    {{-- Row 1: Variant & Participant/Equipment ID --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label style="font-weight:600;">Variant / Equipment <span class="text-danger">*</span></label>
                            <select name="variant_id" id="variantSelect" class="form-control @error('variant_id') is-invalid @enderror" required>
                                <option value="">Select variant</option>
                                @if($variants && count($variants) > 0)
                                    @foreach($variants as $v)
                                    <option value="{{ $v->variant_id }}">
                                        {{ $v->variant_name }} ({{ $v->quality_tier }})
                                    </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('variant_id')
                                <div style="color:#dc3545;font-size:12px;">{{ $message }}</div>
                            @enderror
                            <div id="duplicateTimeslotWarning" style="display:none;margin-top:8px;padding:10px;background:#fff3cd;border-radius:6px;border-left:4px solid #ffc107;">
                                <i class="fas fa-exclamation-triangle" style="color:#d39e00;"></i> 
                                <span style="color:#d39e00;"><strong>TimeSlot already exists</strong> for this variant. </span>
                                <a href="#" id="editExistingTimeslotLink" onclick="handleExistingTimeslot(event)" style="color:#d39e00;text-decoration:underline;"><strong>Click here to edit</strong></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label style="font-weight:600;">Participant / Equipment ID <span class="text-danger">*</span></label>
                            <select name="participant_equipment_id" class="form-control @error('participant_equipment_id') is-invalid @enderror" required>
                                <option value="">Select type</option>
                                <option value="Per Person">Per Person</option>
                                <option value="Per Equipment">Per Equipment</option>
                            </select>
                            @error('participant_equipment_id')
                                <div style="color:#dc3545;font-size:12px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Row 2: Capacity per Slot & Schedule Type --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label style="font-weight:600;">Capacity per Slot <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="capacity_per_slot" 
                                   class="form-control @error('capacity_per_slot') is-invalid @enderror"
                                   placeholder="e.g., 4"
                                   min="1"
                                   required>
                            @error('capacity_per_slot')
                                <div style="color:#dc3545;font-size:12px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label style="font-weight:600;">Schedule Type <span class="text-danger">*</span></label>
                            <select name="schedule_type" class="form-control @error('schedule_type') is-invalid @enderror" required>
                                <option value="">Select type</option>
                                <option value="Fixed Slots">Fixed Slots</option>
                                <option value="Interval-Based">Interval-Based</option>
                                <option value="Open Booking">Open Booking (Walk-Ins)</option>
                                <option value="Group Events">Group Events</option>
                            </select>
                            @error('schedule_type')
                                <div style="color:#dc3545;font-size:12px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Row 3: Start Time & End Time --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label style="font-weight:600;">Start Time (HH:MM) <span class="text-danger">*</span></label>
                            <input type="time" 
                                   name="start_time" 
                                   class="form-control @error('start_time') is-invalid @enderror"
                                   required>
                            @error('start_time')
                                <div style="color:#dc3545;font-size:12px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label style="font-weight:600;">End Time (HH:MM) <span class="text-danger">*</span></label>
                            <input type="time" 
                                   name="end_time" 
                                   class="form-control @error('end_time') is-invalid @enderror"
                                   required>
                            @error('end_time')
                                <div style="color:#dc3545;font-size:12px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Row 4: Duration --}}
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label style="font-weight:600;">Duration <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="duration" 
                                   class="form-control @error('duration') is-invalid @enderror"
                                   placeholder="e.g., 2 Hours, 30 Minutes"
                                   required>
                            @error('duration')
                                <div style="color:#dc3545;font-size:12px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Row 5: Recurring & Lead Time --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label style="font-weight:600;">Recurring (Times per day)</label>
                            <input type="number" 
                                   name="recurring" 
                                   class="form-control @error('recurring') is-invalid @enderror"
                                   placeholder="e.g., 3"
                                   min="1">
                            @error('recurring')
                                <div style="color:#dc3545;font-size:12px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label style="font-weight:600;">Lead Time (Minutes)</label>
                            <input type="number" 
                                   name="lead_time_minutes" 
                                   class="form-control @error('lead_time_minutes') is-invalid @enderror"
                                   placeholder="e.g., 30"
                                   min="1">
                            @error('lead_time_minutes')
                                <div style="color:#dc3545;font-size:12px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Row 6: Days of Week --}}
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label style="font-weight:600;">Days of Week</label>
                            <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:12px;margin-top:8px;">
                                @php
                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                @endphp
                                @foreach($days as $day)
                                <label style="display:flex;align-items:center;gap:8px;font-weight:normal;cursor:pointer;">
                                    <input type="checkbox" name="days_of_week[]" value="{{ $day }}" class="daysCheckbox"
                                           style="cursor:pointer;transform:scale(1.2);">
                                    <span style="font-size:13px;">{{ $day }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <div class="d-flex justify-content-end pt-3 border-top">
                        <button type="submit" id="timeSlotSubmitBtn" class="btn" style="background:#19b5b5;color:#fff;">
                            <i class="fas fa-save me-2"></i><span id="submitBtnText">Save TimeSlot</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Step Completion Note --}}
            <div style="margin-top:24px;background:#e3f2fd;padding:16px;border-radius:8px;border-left:4px solid #2196f3;">
                <p style="margin:0;color:#1565c0;font-size:14px;">
                    <i class="fas fa-info-circle"></i> <strong>Note:</strong> Configure scheduling timeSlots for your activity variants. Multiple timeSlots per variant are supported.
                </p>
            </div>
            </div>
        </div>
    </div>
</div>

<script>
// Store existing timeslots organized by variant
const existingTimeslotsByVariant = {
    @if($timeSlots && count($timeSlots) > 0)
        @foreach($timeSlots as $slot)
            '{{ $slot->variant_id }}': {
                timeslot_id: {{ $slot->timeslot_id }},
                variant_id: {{ $slot->variant_id }},
                participant_equipment: '{{ $slot->participant_equipment_id }}',
                capacity: {{ $slot->capacity_per_slot }},
                schedule_type: '{{ $slot->schedule_type }}',
                start_time: '{{ substr($slot->start_time, 0, 5) }}',
                end_time: '{{ substr($slot->end_time, 0, 5) }}',
                duration: '{{ addslashes($slot->duration) }}',
                recurring: {{ $slot->recurring ?? 'null' }},
                lead_time: {{ $slot->lead_time_minutes ?? 'null' }},
                days: {!! json_encode($slot->days_of_week) !!}
            },
        @endforeach
    @endif
};

// Initialize event listeners on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize edit button listeners
    const editButtons = document.querySelectorAll('.editTimeSlotBtn');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const timeslotId = this.getAttribute('data-timeslot-id');
            const variantId = this.getAttribute('data-variant-id');
            const participantEquipment = this.getAttribute('data-participant-equipment');
            const capacity = this.getAttribute('data-capacity');
            const scheduleType = this.getAttribute('data-schedule-type');
            const startTime = this.getAttribute('data-start-time');
            const endTime = this.getAttribute('data-end-time');
            const duration = this.getAttribute('data-duration');
            const recurring = this.getAttribute('data-recurring');
            const leadTime = this.getAttribute('data-lead-time');
            const daysJson = this.getAttribute('data-days');
            
            editTimeSlot(timeslotId, variantId, participantEquipment, capacity, scheduleType, startTime, endTime, duration, recurring, leadTime, daysJson);
        });
    });
    
    // Initialize variant select listener for duplicate check
    const variantSelect = document.getElementById('variantSelect');
    if (variantSelect) {
        variantSelect.addEventListener('change', checkTimeslotDuplicate);
    }
});

function checkTimeslotDuplicate() {
    const variantSelect = document.getElementById('variantSelect');
    const selectedVariantId = variantSelect.value;
    const duplicateWarning = document.getElementById('duplicateTimeslotWarning');
    const submitBtn = document.getElementById('timeSlotSubmitBtn');
    
    if (selectedVariantId && existingTimeslotsByVariant[selectedVariantId]) {
        // Timeslot already exists for this variant
        duplicateWarning.style.display = 'block';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
    } else {
        // No timeslot exists or no variant selected
        duplicateWarning.style.display = 'none';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    }
}

function handleExistingTimeslot(e) {
    e.preventDefault();
    
    const variantSelect = document.getElementById('variantSelect');
    const selectedVariantId = variantSelect.value;
    const existingData = existingTimeslotsByVariant[selectedVariantId];
    
    if (existingData) {
        editTimeSlot(
            existingData.timeslot_id,
            existingData.variant_id,
            existingData.participant_equipment,
            existingData.capacity,
            existingData.schedule_type,
            existingData.start_time,
            existingData.end_time,
            existingData.duration,
            existingData.recurring,
            existingData.lead_time,
            existingData.days
        );
    }
}

function showTimeSlotForm() {
    // Show the timeSlot form container
    const timeSlotFormContainer = document.getElementById('timeSlotFormContainer');
    timeSlotFormContainer.style.display = 'block';
    
    // Reset form to new record state
    document.getElementById('timeSlotForm').reset();
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('timeslotId').value = '';
    document.getElementById('submitBtnText').innerText = 'Save TimeSlot';
    
    // Update form action to store route
    document.getElementById('timeSlotForm').action = '{{ route('operator.activity.step8.store', $activity->id) }}';
    document.getElementById('timeSlotForm').method = 'POST';
    
    // Clear all checkboxes
    document.querySelectorAll('.daysCheckbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Hide duplicate warning and reset validation
    document.getElementById('duplicateTimeslotWarning').style.display = 'none';
    document.getElementById('variantSelect').value = '';
    document.getElementById('timeSlotSubmitBtn').disabled = false;
    document.getElementById('timeSlotSubmitBtn').style.opacity = '1';
    document.getElementById('timeSlotSubmitBtn').style.cursor = 'pointer';
    
    // Scroll to form
    document.getElementById('timeSlotForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideTimeSlotForm() {
    // Hide the timeSlot form container
    const timeSlotFormContainer = document.getElementById('timeSlotFormContainer');
    timeSlotFormContainer.style.display = 'none';
    
    // Reset form
    document.getElementById('timeSlotForm').reset();
    
    // Hide duplicate warning
    document.getElementById('duplicateTimeslotWarning').style.display = 'none';
    
    // Reset variant select and button state
    document.getElementById('variantSelect').value = '';
    document.getElementById('timeSlotSubmitBtn').disabled = false;
    document.getElementById('timeSlotSubmitBtn').style.opacity = '1';
    document.getElementById('timeSlotSubmitBtn').style.cursor = 'pointer';
    
    // Scroll back to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function editTimeSlot(timeslotId, variantId, participantEquipmentId, capacityPerSlot, scheduleType, startTime, endTime, duration, recurring, leadTimeMinutes, daysOfWeek) {
    // Show the timeSlot form container
    const timeSlotFormContainer = document.getElementById('timeSlotFormContainer');
    timeSlotFormContainer.style.display = 'block';
    
    // Get the form
    const form = document.getElementById('timeSlotForm');
    
    // Set form to edit mode (use POST with _method PUT)
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('timeslotId').value = timeslotId;
    document.getElementById('submitBtnText').innerText = 'Update TimeSlot';
    
    // Update form action to update route and method to POST
    form.action = '{{ route('operator.activity.step8.update', [$activity->id, 'TIMESLOT_ID']) }}'.replace('TIMESLOT_ID', timeslotId);
    form.method = 'POST';
    
    // Populate form fields
    document.getElementById('variantSelect').value = variantId;
    document.querySelector('select[name="participant_equipment_id"]').value = participantEquipmentId;
    document.querySelector('input[name="capacity_per_slot"]').value = capacityPerSlot;
    document.querySelector('select[name="schedule_type"]').value = scheduleType;
    document.querySelector('input[name="start_time"]').value = startTime;
    document.querySelector('input[name="end_time"]').value = endTime;
    document.querySelector('input[name="duration"]').value = duration;
    document.querySelector('input[name="recurring"]').value = recurring || '';
    document.querySelector('input[name="lead_time_minutes"]').value = leadTimeMinutes || '';
    
    // Parse and check days of week
    let daysArray = [];
    try {
        daysArray = typeof daysOfWeek === 'string' ? JSON.parse(daysOfWeek) : (Array.isArray(daysOfWeek) ? daysOfWeek : []);
    } catch (e) {
        console.error('Error parsing days_of_week:', e);
        daysArray = [];
    }
    
    const dayCheckboxes = document.querySelectorAll('.daysCheckbox');
    dayCheckboxes.forEach(checkbox => {
        checkbox.checked = daysArray.includes(checkbox.value);
    });
    
    // Scroll to form
    document.getElementById('timeSlotForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>

<!-- Back Button -->
<div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
    <a href="{{ route('operator.activity.show', $activity->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
        ← Back to Activity Overview
    </a>
</div>
@endsection
