@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                @include('operator.registration._sidebar_main')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <h2 style="font-weight:700;margin:0;">Step 8: Rate Plans</h2>
                </div>

                {{-- Plan Management Section --}}
                <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;">
                    <h4 style="margin-top:0;margin-bottom:16px;"><a href="#" id="toggleAddPlan" style="text-decoration:none;color:inherit;">+ Add New Plan</a></h4>
                    
                    <div id="addPlanSection" style="display:none;">
                        {{-- Add Plan Form --}}
                        <form method="POST" action="{{ route('operator.accommodation.saveStep8', $accommodation->id) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label style="font-weight:600;">Plan Name *</label>
                                    <input type="text" name="rate_name" id="rate_name_input" class="form-control" required placeholder="e.g., Half Board, Group Rate">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label style="font-weight:600;">Meal Plan *</label>
                                    <select name="meal_plan" class="form-control" required>
                                        <option value="">Select Meal Plan</option>
                                        @foreach(['Room Only','Breakfast','Half Board','Full Board','All Inclusive'] as $meal)
                                            <option value="{{ $meal }}">{{ $meal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label style="font-weight:600;">Pricing Setting *</label>
                                    <select name="pricing_setting" class="form-control" required>
                                        <option value="">Select</option>
                                        @foreach(['Per Person/Night','Per Room/Night','Per Property/Night'] as $setting)
                                            <option value="{{ $setting }}">{{ $setting }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label style="font-weight:600;">Inclusions</label>
                                    <select name="inclusions[]" class="form-control" multiple>
                                        @foreach(['Food arrangement','In-Room service','Butler service','Free Mini bar','Breakfast','Airport transfer','Welcome drink','Spa credits','Activities','Entertainment'] as $inc)
                                            <option value="{{ $inc }}">{{ $inc }}</option>
                                        @endforeach
                                    </select>
                                    <small style="color:#666;display:block;margin-top:4px;">Select all applicable inclusions</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">Save</button>
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

                {{-- Rooms Listing with Set Plan --}}
                <div style="background:#f9f9f9;border-radius:16px;padding:20px;margin-bottom:20px;">
                    <h5 style="margin-top:0;margin-bottom:16px;font-weight:600;">Assign Plans to Rooms</h5>
                    
                    @if(isset($rooms) && count($rooms) > 0)
                        @foreach($rooms as $room)
                            @php
                                $roomPlan = $room->rates()->where('is_rate_plan', true)->first();
                            @endphp
                            <div style="background:#fff;padding:12px;border-radius:6px;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px;font-size:13px;border:1px solid #eee;">
                                <div>
                                    <strong>{{ $room->room_name }}</strong> - Bed: {{ $room->room_type }} (Adults: {{ $room->capacity }}, Children: {{ $room->children_capacity ?? 0 }})
                                    @if($roomPlan)
                                        <br><small style="color:#666;"><strong>Current Plan:</strong> {{ $roomPlan->rate_name }} - {{ $roomPlan->meal_plan }}</small>
                                    @else
                                        <br><small style="color:#e67e22;"><strong>No plan assigned</strong></small>
                                    @endif
                                </div>
                                @if(!$roomPlan)
                                    <button type="button" onclick="openSetPlanModal('{{ $room->id }}', '{{ $room->room_name }}')" style="padding:6px 12px;background:#19b5b5;border-radius:4px;border:none;color:#fff;font-size:12px;cursor:pointer;">Set Plan</button>
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

    {{-- Modal for Setting Plan --}}
    <div id="setPlanModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:24px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.2);width:90%;max-width:500px;">
            <h5 style="margin-top:0;margin-bottom:16px;font-weight:600;" id="modalRoomName">Set Plan for Room</h5>
            
            <form id="setPlanForm" method="POST">
                @csrf
                <input type="hidden" id="roomIdInput" name="room_id">
                
                <div class="mb-3">
                    <label style="font-weight:600;">Select Plan *</label>
                    <select name="plan_id" id="planSelect" class="form-control" required>
                        <option value="">Choose a plan...</option>
                    </select>
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" onclick="closeSetPlanModal()" style="padding:8px 14px;background:#f0f0f0;color:#333;border:none;border-radius:4px;cursor:pointer;">Cancel</button>
                    <button type="submit" style="padding:8px 14px;background:#19b5b5;color:#fff;border:none;border-radius:4px;cursor:pointer;">Assign Plan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Store plans data for JavaScript --}}
    <script>
        const allPlans = @json($businessPlans ?? []);

        function openSetPlanModal(roomId, roomName) {
            document.getElementById('roomIdInput').value = roomId;
            document.getElementById('modalRoomName').textContent = 'Set Plan for ' + roomName;
            
            // Populate plan dropdown
            const planSelect = document.getElementById('planSelect');
            planSelect.innerHTML = '<option value="">Choose a plan...</option>';
            allPlans.forEach(plan => {
                const option = document.createElement('option');
                option.value = plan.id;
                option.textContent = plan.rate_name + ' - ' + plan.meal_plan;
                planSelect.appendChild(option);
            });
            
            document.getElementById('setPlanModal').style.display = 'flex';
        }

        function closeSetPlanModal() {
            document.getElementById('setPlanModal').style.display = 'none';
        }

        document.getElementById('setPlanForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const roomId = document.getElementById('roomIdInput').value;
            const planId = document.getElementById('planSelect').value;
            
            if (!planId) {
                alert('Please select a plan');
                return;
            }

            // Submit via fetch to assign plan to room
            fetch('{{ route("operator.accommodation.step8.assignPlan", $accommodation->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    room_id: roomId,
                    plan_id: planId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeSetPlanModal();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to assign plan'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });

        // Close modal when clicking outside
        document.getElementById('setPlanModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSetPlanModal();
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
@endsection
