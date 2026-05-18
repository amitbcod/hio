@extends('layouts.admin')

@section('content')
    <div class="container mt-5">
        @php $currentStep = 8; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.accommodation._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <h2 style="font-weight:700;margin:0;">Step 8: Rate Plans</h2>
                </div>

                {{-- Plan Management Section --}}
                <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;">
                    <h4 style="margin-top:0;margin-bottom:16px;">
                        @if(isset($plan))
                            <a href="{{ route('operator.accommodation.step8.show', $accommodation->id) }}" style="text-decoration:none;color:inherit;">← Back to Plans</a> | Edit Plan
                        @else
                            <a href="#" id="toggleAddPlan" style="text-decoration:none;color:inherit;">+ Add New Plan</a>
                        @endif
                    </h4>
                    
                    <div id="addPlanSection" style="display:{{ isset($plan) ? 'block' : 'none' }};">
                        {{-- Add/Edit Plan Form --}}
                        <form method="POST" action="{{ isset($plan) ? route('operator.accommodation.step8.plan.update', ['id' => $accommodation->id, 'plan' => $plan->id]) : route('operator.accommodation.saveStep8', $accommodation->id) }}">
                            @csrf
                            @if(isset($plan))
                                @method('PUT')
                            @endif
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label style="font-weight:600;">Plan Name *</label>
                                    <input type="text" name="rate_name" id="rate_name_input" class="form-control" required placeholder="e.g., Half Board, Group Rate" value="{{ isset($plan) ? $plan->rate_name : old('rate_name') }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label style="font-weight:600;">Meal Plan *</label>
                                    <select name="meal_plan" class="form-control" required>
                                        <option value="">Select Meal Plan</option>
                                        @foreach(['Room Only','Breakfast','Half Board','Full Board','All Inclusive'] as $meal)
                                            <option value="{{ $meal }}" {{ isset($plan) && $plan->meal_plan == $meal ? 'selected' : '' }}>{{ $meal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label style="font-weight:600;">Pricing Setting *</label>
                                    <select name="pricing_setting" class="form-control" required>
                                        <option value="">Select</option>
                                        @foreach(['Per Person/Night','Per Room/Night','Per Property/Night'] as $setting)
                                            <option value="{{ $setting }}" {{ isset($plan) && $plan->pricing_setting == $setting ? 'selected' : '' }}>{{ $setting }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label style="font-weight:600;">Inclusions</label>
                                    <select name="inclusions[]" class="form-control" multiple>
                                        @php
                                            $selectedInclusions = isset($plan) ? json_decode($plan->inclusions, true) ?? [] : [];
                                        @endphp
                                        @foreach(['Food arrangement','In-Room service','Butler service','Free Mini bar','Breakfast','Airport transfer','Welcome drink','Spa credits','Activities','Entertainment'] as $inc)
                                            <option value="{{ $inc }}" {{ in_array($inc, $selectedInclusions) ? 'selected' : '' }}>{{ $inc }}</option>
                                        @endforeach
                                    </select>
                                    <small style="color:#666;display:block;margin-top:4px;">Select all applicable inclusions</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">
                                        {{ isset($plan) ? 'Update Plan' : 'Save' }}
                                    </button>
                                    @if(isset($plan))
                                        <a href="{{ route('operator.accommodation.step8.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;margin-left:10px;">Cancel</a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Plans Listing --}}
                @if(isset($businessPlans) && count($businessPlans) > 0)
                <div style="background:#f9f9f9;border-radius:16px;padding:20px;margin-bottom:20px;">
                    <h5 style="margin-top:0;margin-bottom:16px;font-weight:600;">Available Plans</h5>
                    @foreach($businessPlans as $plan)
                        <div style="background:#fff;padding:12px;border-radius:6px;display:flex;align-items:center;gap:12px;margin-bottom:10px;font-size:13px;border:1px solid #eee;">
                            <div style="flex:1;">
                                <strong>{{ $plan->rate_name }}</strong> - {{ $plan->meal_plan }} ({{ $plan->pricing_setting }})
                                @if($plan->inclusions)
                                    <br><small style="color:#666;">Inclusions: {{ implode(', ', json_decode($plan->inclusions, true) ?? []) }}</small>
                                @endif
                            </div>
                            <div style="display:flex;gap:6px;">
                                <a href="{{ route('operator.accommodation.step8.plan.edit', ['id' => $accommodation->id, 'plan' => $plan->id]) }}" style="padding:5px 10px;background:#fff;border:1px solid #ddd;border-radius:4px;color:#333;text-decoration:none;font-size:12px;">Edit</a>
                                <form method="POST" action="{{ route('operator.accommodation.step8.plan.delete', ['id' => $accommodation->id, 'plan' => $plan->id]) }}" style="display:inline;" onsubmit="return confirm('Delete this plan?');">
                                    @csrf
                                    <button type="submit" style="padding:5px 10px;background:#ff6b6b;border-radius:4px;border:none;color:#fff;font-size:12px;cursor:pointer;">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif

                {{-- Rooms Listing with Assign Plans --}}
                <div style="background:#f9f9f9;border-radius:16px;padding:20px;margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <h5 style="margin:0;font-weight:600;">Assign Plans to Rooms</h5>
                        @if(isset($rooms) && count($rooms) > 0 && isset($businessPlans) && count($businessPlans) > 0)
                            <button type="button" onclick="assignAllPlansToAllRooms()" style="padding:6px 12px;background:#007bff;border-radius:4px;border:none;color:#fff;font-size:12px;cursor:pointer;">Assign All Plans to All Rooms</button>
                        @endif
                    </div>
                    
                    @if(isset($rooms) && count($rooms) > 0)
                        @foreach($rooms as $room)
                            @php
                                $roomPlans = $room->rates()->where('is_rate_plan', true)->get();
                            @endphp
                            <div style="background:#fff;padding:12px;border-radius:6px;margin-bottom:10px;font-size:13px;border:1px solid #eee;">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px;">
                                    <div>
                                        <strong>{{ $room->room_name }}</strong> - Bed: {{ $room->room_type }} (Adults: {{ $room->capacity }}, Children: {{ $room->children_capacity ?? 0 }})
                                    </div>
                                    <div style="display:flex;gap:6px;margin-top:4px;">
                                        <button type="button" onclick="openAssignPlansModal('{{ $room->id }}', '{{ $room->room_name }}')" style="padding:4px 8px;background:#19b5b5;border-radius:4px;border:none;color:#fff;font-size:11px;cursor:pointer;">Assign Plans</button>
                                        <button type="button" onclick="assignAllPlansToRoom('{{ $room->id }}', '{{ $room->room_name }}')" style="padding:4px 8px;background:#28a745;border-radius:4px;border:none;color:#fff;font-size:11px;cursor:pointer;">Assign All</button>
                                    </div>
                                </div>
                                @if($roomPlans->count() > 0)
                                    <div style="margin-top:8px;">
                                        <small style="color:#666;"><strong>Assigned Plans:</strong></small>
                                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
                                            @foreach($roomPlans as $plan)
                                                <span style="background:#e8f5f5;padding:2px 8px;border-radius:12px;font-size:11px;color:#19b5b5;border:1px solid #19b5b5;">
                                                    {{ $plan->rate_name }} - {{ $plan->meal_plan }} ({{ $plan->pricing_setting }})
                                                    <button type="button" onclick="removePlanFromRoom('{{ $room->id }}', '{{ $plan->id }}', '{{ $plan->rate_name }}')" style="margin-left:4px;background:none;border:none;color:#19b5b5;cursor:pointer;font-size:10px;">×</button>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div style="margin-top:8px;">
                                        <small style="color:#e67e22;"><strong>No plans assigned yet</strong></small>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div style="background:#fff3cd;padding:12px;border-radius:6px;color:#856404;">
                            No rooms added yet. <a href="{{ route('operator.accommodation.step7.show', $accommodation->id) }}" style="color:#856404;font-weight:600;">Create rooms first</a>
                        </div>
                    @endif
                </div>
                <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:16px;">
                    <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">← Back</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for Assigning Plans --}}
    <div id="assignPlansModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:24px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.2);width:90%;max-width:600px;max-height:80vh;overflow-y:auto;">
            <h5 style="margin-top:0;margin-bottom:16px;font-weight:600;" id="modalRoomName">Assign Plans for Room</h5>
            
            <form id="assignPlansForm" method="POST">
                @csrf
                <input type="hidden" id="roomIdInput" name="room_id">
                
                <div class="mb-3">
                    <label style="font-weight:600;">Select Plans *</label>
                    <small style="display:block;color:#666;margin-bottom:8px;">Choose one or more plans to assign to this room</small>
                    <div id="plansCheckboxes" style="max-height:200px;overflow-y:auto;border:1px solid #ddd;padding:8px;border-radius:4px;">
                        <!-- Plans will be populated here -->
                    </div>
                    <div style="margin-top:8px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                            <input type="checkbox" id="selectAllPlans" style="margin:0;">
                            <span>Select All Plans</span>
                        </label>
                    </div>
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" onclick="closeAssignPlansModal()" style="padding:8px 14px;background:#f0f0f0;color:#333;border:none;border-radius:4px;cursor:pointer;">Cancel</button>
                    <button type="submit" style="padding:8px 14px;background:#19b5b5;color:#fff;border:none;border-radius:4px;cursor:pointer;">Assign Selected Plans</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Store plans and room plans data for JavaScript --}}
    <script>
        const allPlans = @json($businessPlans ?? []);
        const roomPlansData = @json($roomPlansData ?? []);

        function closeAssignPlansModal() {
            document.getElementById('assignPlansModal').style.display = 'none';
        }

        function openAssignPlansModal(roomId, roomName) {
            document.getElementById('roomIdInput').value = roomId;
            document.getElementById('modalRoomName').textContent = 'Assign Plans for ' + roomName;
            
            // Find current plans for this room (now includes plan details)
            const roomData = roomPlansData.find(r => String(r.roomId) === String(roomId));
            const assignedPlanKeys = roomData ? (roomData.plans || []) : [];
            console.debug('AssignPlans modal open', {roomId, roomData, assignedPlanKeys, allPlans});
            
            // Populate plan checkboxes
            const plansContainer = document.getElementById('plansCheckboxes');
            plansContainer.innerHTML = '';
            
            if (allPlans.length === 0) {
                plansContainer.innerHTML = '<p style="color:#666;margin:0;">No plans available. Create plans first.</p>';
                return;
            }
            
            allPlans.forEach(plan => {
                // Compare by (rate_name, meal_plan, pricing_setting) since assigning creates new records
                const isChecked = assignedPlanKeys.some(assignedPlan => 
                    assignedPlan.rate_name === plan.rate_name &&
                    assignedPlan.meal_plan === plan.meal_plan &&
                    assignedPlan.pricing_setting === plan.pricing_setting
                );
                console.debug('Plan check', {plan_id: plan.id, plan_name: plan.rate_name, isChecked, assigned: assignedPlanKeys});
                const checkboxDiv = document.createElement('div');
                checkboxDiv.style.marginBottom = '8px';

                const label = document.createElement('label');
                label.style.display = 'flex';
                label.style.alignItems = 'center';
                label.style.gap = '8px';
                label.style.cursor = 'pointer';

                const input = document.createElement('input');
                input.type = 'checkbox';
                input.name = 'plan_ids[]';
                input.value = plan.id;
                input.style.margin = '0';
                input.className = 'plan-checkbox';
                if (isChecked) input.checked = true;

                const span = document.createElement('span');
                span.style.fontSize = '13px';
                span.textContent = plan.rate_name + ' - ' + plan.meal_plan + ' (' + plan.pricing_setting + ')';

                label.appendChild(input);
                label.appendChild(span);
                checkboxDiv.appendChild(label);
                plansContainer.appendChild(checkboxDiv);
            });
            
            // Handle Select All functionality
            const selectAllCheckbox = document.getElementById('selectAllPlans');
            const planCheckboxes = document.querySelectorAll('.plan-checkbox');

            selectAllCheckbox.checked = planCheckboxes.length > 0 && Array.from(planCheckboxes).every(cb => cb.checked);

            // Use onchange to avoid adding duplicate listeners when opening modal repeatedly
            selectAllCheckbox.onchange = function() {
                planCheckboxes.forEach(cb => cb.checked = this.checked);
            };

            // Update Select All when individual checkboxes change
            planCheckboxes.forEach(cb => {
                cb.onchange = function() {
                    const allChecked = Array.from(planCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                };
            });
            
            document.getElementById('assignPlansModal').style.display = 'flex';
        }

        function assignAllPlansToAllRooms() {
            if (!confirm('Assign all available plans to all rooms? This will assign every plan to every room.')) {
                return;
            }

            if (allPlans.length === 0) {
                alert('No plans available to assign.');
                return;
            }

            const planIds = allPlans.map(plan => plan.id);
            const roomIds = @json($rooms->pluck('id')->toArray());

            if (roomIds.length === 0) {
                alert('No rooms available.');
                return;
            }

            // assign to all rooms sequentially
            let completed = 0;
            const total = roomIds.length;

            roomIds.forEach(roomId => {
                const formData = new FormData();
                formData.append('room_id', roomId);
                planIds.forEach(planId => {
                    formData.append('plan_ids[]', planId);
                });

                fetch('{{ route("operator.accommodation.step8.assignPlans", $accommodation->id) }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Response text:', text);
                            try {
                                const json = JSON.parse(text);
                                throw new Error(json.message || 'Failed to assign plans');
                            } catch (e) {
                                throw new Error('Server error: ' + response.status + ' ' + response.statusText);
                            }
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    completed++;
                    if (completed === total) {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to assign plans to some rooms'));
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    completed++;
                    if (completed === total) {
                        alert('An error occurred while assigning plans: ' + error.message);
                    }
                });
            });
        }

        function assignAllPlansToRoom(roomId, roomName) {
            if (!confirm(`Assign all available plans to "${roomName}"?`)) {
                return;
            }

            if (allPlans.length === 0) {
                alert('No plans available to assign.');
                return;
            }

            const planIds = allPlans.map(plan => plan.id);

            const formData = new FormData();
            formData.append('room_id', roomId);
            planIds.forEach(planId => {
                formData.append('plan_ids[]', planId);
            });

            fetch('{{ route("operator.accommodation.step8.assignPlans", $accommodation->id) }}', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Response text:', text);
                        try {
                            const json = JSON.parse(text);
                            throw new Error(json.message || 'Failed to assign plans');
                        } catch (e) {
                            throw new Error('Server error: ' + response.status + ' ' + response.statusText);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to assign plans'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        }

        function removePlanFromRoom(roomId, planId, planName) {
            if (!confirm(`Remove "${planName}" from this room?`)) {
                return;
            }

            const formData = new FormData();
            formData.append('room_id', roomId);
            formData.append('plan_id', planId);

            fetch('{{ route("operator.accommodation.step8.removePlan", $accommodation->id) }}', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Response text:', text);
                        try {
                            const json = JSON.parse(text);
                            throw new Error(json.message || 'Failed to remove plan');
                        } catch (e) {
                            throw new Error('Server error: ' + response.status + ' ' + response.statusText);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to remove plan'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        }

        document.getElementById('assignPlansForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const roomId = document.getElementById('roomIdInput').value;
            const selectedPlans = Array.from(document.querySelectorAll('input[name="plan_ids[]"]:checked')).map(cb => cb.value);
            
            if (selectedPlans.length === 0) {
                alert('Please select at least one plan');
                return;
            }

            // Submit via fetch to assign plans to room
            const formData = new FormData();
            formData.append('room_id', roomId);
            selectedPlans.forEach(planId => {
                formData.append('plan_ids[]', planId);
            });

            fetch('{{ route("operator.accommodation.step8.assignPlans", $accommodation->id) }}', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Response text:', text);
                        try {
                            const json = JSON.parse(text);
                            throw new Error(json.message || 'Validation failed');
                        } catch (e) {
                            throw new Error('Server error: ' + response.status + ' ' + response.statusText);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeAssignPlansModal();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to assign plans'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        });

        // Close modal when clicking outside
        document.getElementById('assignPlansModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAssignPlansModal();
            }
        });
        
        // Toggle Add Plan form
        (function(){
            var toggle = document.getElementById('toggleAddPlan');
            var section = document.getElementById('addPlanSection');
            var rateInput = document.getElementById('rate_name_input');
            if(toggle && section){
                toggle.addEventListener('click', function(e){
                    e.preventDefault();
                    section.style.display = (section.style.display === 'none' || section.style.display === '') ? 'block' : 'none';
                    if(section.style.display !== 'none'){
                        if(rateInput) rateInput.focus();
                        section.scrollIntoView({behavior:'smooth', block:'start'});
                    }
                });
            }
        })();
    </script>
    
    <!-- Back Button -->
    <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
        <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
            ← Back to Accommodation Overview
        </a>
    </div>
@endsection
