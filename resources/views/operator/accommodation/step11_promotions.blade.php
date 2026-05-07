@extends('layouts.app')

@section('content')
    <!-- Quill WYSIWYG Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    
    <div class="container mt-5">
        @php $currentStep = 11; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.accommodation._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <h2 style="font-weight:700;margin:0;">Step 11: Promotions & Offers</h2>
                </div>

                {{-- Success/Error Messages --}}
                @if($errors->any())
                <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                    <h5 style="margin-top:0;color:#c62828;">❌ Validation Errors:</h5>
                    @foreach($errors->all() as $error)
                        <div style="margin-bottom:4px;">• {{ $error }}</div>
                    @endforeach
                </div>
                @endif

                @if(session('success'))
                <div class="alert alert-success" style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;">
                    <strong>✓ {{ session('success') }}</strong>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger" style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                    <strong>✗ {{ session('error') }}</strong>
                </div>
                @endif

                {{-- AJAX Messages --}}
                <div id="ajax-success" class="alert alert-success" style="display:none;background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;"></div>
                <div id="ajax-error" class="alert alert-danger" style="display:none;background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;"></div>

                {{-- Add New Promotion --}}
                <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;">
                    <div style="margin-bottom:16px;">
                        <button type="button" id="toggleAddPromotion" style="background:none;border:none;color:#19b5b5;font-size:16px;font-weight:600;cursor:pointer;padding:0;margin:0;">+ Add New Promotion</button>
                    </div>
                    
                    <div id="addPromotionSection" style="display:none;">
                        <form id="promotionForm" method="POST" action="{{ route('operator.accommodation.step11.save', $accommodation->id) }}" enctype="multipart/form-data">
                            @csrf
                            
                            {{-- Room & Rate Plan Selection --}}
                            <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                                <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Room & Rate Plan</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label style="font-weight:600;">Room/Unit *</label>
                                        <select name="room_id" class="form-control" required>
                                            <option value="">-- Select a Room --</option>
                                            @foreach($rooms as $room)
                                                <option value="{{ $room->id }}">{{ $room->room_name }} ({{ $room->room_type }})</option>
                                            @endforeach
                                        </select>
                                        <small style="color:#999;display:block;margin-top:6px;">Rooms available: {{ $rooms->count() }}</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label style="font-weight:600;">Rate Plan *</label>
                                        <select name="rate_plan_id" class="form-control" required>
                                            <option value="">-- Select a Rate Plan --</option>
                                            @foreach($ratePlans as $plan)
                                                @if(isset($plan->rate_type) && $plan->rate_type === 'Standard')
                                                    <option value="{{ $plan->id }}" data-room-id="{{ $plan->room_id }}">{{ $plan->rate_name }} - {{ $plan->meal_plan }} ({{ $plan->pricing_setting }})</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Campaign Information --}}
                            <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                                <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Campaign Information</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label style="font-weight:600;">Campaign Name</label>
                                        <input type="text" name="campaign_name" class="form-control" max-length="255">
                                        <small style="color:#666;">Optional: Label for this promotion</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label style="font-weight:600;">Promotion Type</label>
                                        <select name="promotion_type" class="form-control">
                                            <option value="">-- Select Type --</option>
                                            <option value="Early-bird">Early-bird</option>
                                            <option value="Last-minute">Last-minute</option>
                                            <option value="Stay X Pay Y">Stay X Pay Y</option>
                                            <option value="Seasonal">Seasonal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label style="font-weight:600;">Campaign Description</label>
                                        <textarea name="campaign_description" id="campaign_description" style="display:none;"></textarea>
                                        <div id="campaign_description_editor" style="height:150px;background:#fff;border:1px solid #ddd;border-radius:4px;"></div>
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                                            <small id="campaign_description_count" style="color:#666;">0 / 500</small>
                                            @error('campaign_description')<small style="color:#d93025;">{{ $message }}</small>@enderror
                                        </div>
                                        <small style="color:#666;display:block;margin-top:4px;">Optional; Detailed offer description with formatting (max 500 characters)</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Discount Details --}}
                            <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                                <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Discount Details</h6>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label style="font-weight:600;">Discount Type</label>
                                        <select name="discount_type" class="form-control">
                                            <option value="">-- Select Type --</option>
                                            <option value="Amount/Night">Amount per Night</option>
                                            <option value="Percentage">Percentage (%)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label style="font-weight:600;">Discount Value</label>
                                        <input type="number" name="discount_value" class="form-control" step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <div class="col-md-4">
                                        <label style="font-weight:600;">Non-Refundable</label>
                                        <div style="margin-top:8px;">
                                            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                                <input type="checkbox" name="non_refundable" class="form-check-input">
                                                <span>Apply non-refundable rate</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Validity Period --}}
                            <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                                <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Offer Validity Period</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label style="font-weight:600;">Valid From</label>
                                        <input type="date" name="promo_valid_from" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label style="font-weight:600;">Valid To</label>
                                        <input type="date" name="promo_valid_to" class="form-control">
                                    </div>
                                </div>
                            </div>

                            {{-- Approval Status is managed by system; default to Pending Approval for operator submissions --}}
                            <input type="hidden" name="approval_status" value="Pending Approval">

                            <input type="hidden" name="promotion_id" id="promotion_id" value="">

                            {{-- Submit Button --}}
                            <div style="display:flex;gap:12px;">
                                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">
                                    Save Promotion
                                </button>
                                <button type="button" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;" onclick="toggleAddPromotion()">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Promotions Listing --}}
                @if($promotions->count() > 0)
                <div style="background:#f9f9f9;border-radius:16px;padding:20px;margin-bottom:20px;">
                    <h5 style="margin-top:0;margin-bottom:16px;font-weight:600;">Active Promotions</h5>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr style="background:#19b5b5;color:#fff;text-align:left;">
                                    <th style="padding:12px;border:1px solid #ddd;">Campaign</th>
                                    <th style="padding:12px;border:1px solid #ddd;">Room</th>
                                    <th style="padding:12px;border:1px solid #ddd;">Rate Plan</th>
                                    <th style="padding:12px;border:1px solid #ddd;">Discount</th>
                                    <th style="padding:12px;border:1px solid #ddd;">Valid Period</th>
                                    <th style="padding:12px;border:1px solid #ddd;text-align:center;">Status</th>
                                    <th style="padding:12px;border:1px solid #ddd;width:120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($promotions as $promo)
                                <tr style="background:#fff;border-bottom:1px solid #eee;">
                                    <td style="padding:12px;border:1px solid #ddd;">
                                        <strong>{{ $promo->campaign_name ?? 'Unnamed' }}</strong>
                                        @if($promo->promotion_type)
                                            <br><small style="color:#666;">{{ $promo->promotion_type }}</small>
                                        @endif
                                    </td>
                                    <td style="padding:12px;border:1px solid #ddd;">
                                        @if($promo->room)
                                            {{ $promo->room->room_name }}
                                        @else
                                            <em style="color:#999;">—</em>
                                        @endif
                                    </td>
                                    <td style="padding:12px;border:1px solid #ddd;">
                                        @if($promo->ratePlan)
                                            {{ $promo->ratePlan->rate_name }}
                                        @else
                                            <em style="color:#999;">—</em>
                                        @endif
                                    </td>
                                    <td style="padding:12px;border:1px solid #ddd;">
                                        @if($promo->discount_value)
                                            <span style="background:#e8f5f5;padding:4px 8px;border-radius:4px;color:#19b5b5;">
                                                {{ $promo->discount_value }}{{ $promo->discount_type === 'Percentage' ? '%' : '/' . ($promo->discount_type ?? 'night') }}
                                            </span>
                                        @else
                                            <em style="color:#999;">No discount</em>
                                        @endif
                                    </td>
                                    <td style="padding:12px;border:1px solid #ddd;font-size:11px;">
                                        @if($promo->promo_valid_from || $promo->promo_valid_to)
                                            {{ $promo->promo_valid_from ? $promo->promo_valid_from->format('M d') : 'Any' }}
                                            —
                                            {{ $promo->promo_valid_to ? $promo->promo_valid_to->format('M d, Y') : 'Any' }}
                                        @else
                                            <em style="color:#999;">Ongoing</em>
                                        @endif
                                    </td>
                                    <td style="padding:12px;border:1px solid #ddd;text-align:center;">
                                        <span style="background:{{ $promo->approval_status === 'Published' ? '#d4edda' : ($promo->approval_status === 'Pending Approval' ? '#fff3cd' : '#e2e3e5') }};color:{{ $promo->approval_status === 'Published' ? '#155724' : ($promo->approval_status === 'Pending Approval' ? '#856404' : '#383d41') }};padding:4px 8px;border-radius:4px;font-size:11px;display:inline-block;">
                                            {{ $promo->approval_status }}
                                        </span>
                                    </td>
                                    <td style="padding:12px;border:1px solid #ddd;">
                                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                            <button type="button" onclick="editPromotion({{ $promo->id }})" style="padding:4px 8px;background:#007bff;border:none;border-radius:3px;color:#fff;cursor:pointer;font-size:11px;">Edit</button>
                                            <button type="button" onclick="deletePromotion({{ $promo->id }})" style="padding:4px 8px;background:#ff6b6b;border:none;border-radius:3px;color:#fff;cursor:pointer;font-size:11px;">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($promotions->hasPages())
                    <div style="margin-top:16px;text-align:center;">
                        {{ $promotions->links() }}
                    </div>
                    @endif
                </div>
                @else
                <div style="background:#fff3cd;padding:16px;border-radius:8px;color:#856404;margin-bottom:20px;">
                    No promotions added yet. Create one to manage your special offers.
                </div>
                @endif

                {{-- Navigation --}}
                <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:16px;">
                    <a href="{{ route('operator.accommodation.step10.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">← Back to Step 10</a>
                    <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">Back to Property</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Assign Plans Modal (reused from Step 8) --}}
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

    <script>
        // Initialize Quill editor for Campaign Description
        let campaignDescEditor;
        document.addEventListener('DOMContentLoaded', function() {
            campaignDescEditor = new Quill('#campaign_description_editor', {
                theme: 'snow',
                placeholder: 'Enter detailed campaign description with formatting...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{'header': [1, 2, 3, false]}],
                        [{'list': 'ordered'}, {'list': 'bullet'}],
                        ['link'],
                        ['clean']
                    ]
                }
            });

            // Set initial content from textarea
            var campaignTextarea = document.getElementById('campaign_description');
            var campaignDescriptionCount = document.getElementById('campaign_description_count');
            var campaignDescriptionMax = 500;

            if(campaignTextarea.value){
                campaignDescEditor.root.innerHTML = campaignTextarea.value;
            }

            function updateCampaignDescriptionCounter() {
                if (!campaignDescriptionCount) return;
                var currentLength = campaignDescEditor.getText().trim().length;
                campaignDescriptionCount.textContent = currentLength + ' / ' + campaignDescriptionMax;
                campaignDescriptionCount.style.color = currentLength > campaignDescriptionMax ? '#d93025' : '#666';
            }

            var campaignDescriptionError = document.getElementById('campaign_description_error');

            function validateCampaignDescriptionLength() {
                var currentLength = campaignDescEditor.getText().trim().length;
                var valid = true;
                if (campaignDescriptionError) {
                    if (currentLength > campaignDescriptionMax) {
                        campaignDescriptionError.style.display = 'block';
                        campaignDescriptionError.textContent = 'Campaign Description exceeds ' + campaignDescriptionMax + ' characters.';
                        valid = false;
                    } else {
                        campaignDescriptionError.style.display = 'none';
                        campaignDescriptionError.textContent = '';
                    }
                }
                return valid;
            }

            // Sync editor with hidden textarea
            function syncCampaignDesc(){
                campaignTextarea.value = campaignDescEditor.root.innerHTML;
                updateCampaignDescriptionCounter();
            }

            campaignDescEditor.on('text-change', function() {
                syncCampaignDesc();
            });

            // Validate on form submit only
            var form = document.getElementById('promotionForm');
            if(form){
                form.addEventListener('submit', function(event){
                    event.preventDefault(); // Prevent default form submission
                    syncCampaignDesc();
                    if (!validateCampaignDescriptionLength()) {
                        if (campaignDescriptionError) {
                            campaignDescriptionError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        return; // Don't proceed if validation fails
                    }

                    // If validation passes, send AJAX request
                    var formData = new FormData(form);
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            var successDiv = document.getElementById('ajax-success');
                            if (successDiv) {
                                successDiv.style.display = 'block';
                                successDiv.innerHTML = '<strong>✓ ' + data.message + '</strong>';
                                successDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                            // Optionally reload or update UI
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            // Show error message
                            var errorDiv = document.getElementById('ajax-error');
                            if (errorDiv) {
                                errorDiv.style.display = 'block';
                                errorDiv.innerHTML = '<strong>✗ ' + data.message + '</strong>';
                                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        var errorDiv = document.getElementById('ajax-error');
                        if (errorDiv) {
                            errorDiv.style.display = 'block';
                            errorDiv.innerHTML = '<strong>✗ An error occurred. Please try again.</strong>';
                        }
                    });
                });
            }

            updateCampaignDescriptionCounter();
        });

        // Use business-level plans (room_id = null) for the Assign Plans modal,
        // so operators assign from global plans (these will be copied to the room).
        const allPlans = @json($businessPlansForAssign ?? []);
        const roomPlansData = @json($roomPlansData ?? []);

        function closeAssignPlansModal() {
            document.getElementById('assignPlansModal').style.display = 'none';
        }

        function openAssignPlansModal(roomId, roomName) {
            document.getElementById('roomIdInput').value = roomId;
            document.getElementById('modalRoomName').textContent = 'Assign Plans for ' + roomName;
            
            const roomData = roomPlansData.find(r => String(r.roomId) === String(roomId));
            const assignedPlanKeys = roomData ? (roomData.plans || []) : [];

            const plansContainer = document.getElementById('plansCheckboxes');
            plansContainer.innerHTML = '';
            if (allPlans.length === 0) {
                plansContainer.innerHTML = '<p style="color:#666;margin:0;">No plans available. Create plans first.</p>';
                return;
            }

            allPlans.forEach(plan => {
                const isChecked = assignedPlanKeys.some(assignedPlan => 
                    assignedPlan.rate_name === plan.rate_name &&
                    assignedPlan.meal_plan === plan.meal_plan &&
                    assignedPlan.pricing_setting === plan.pricing_setting
                );

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
                span.textContent = plan.rate_name + ' - ' + (plan.meal_plan || '') + ' (' + (plan.pricing_setting || '') + ')';

                label.appendChild(input);
                label.appendChild(span);
                checkboxDiv.appendChild(label);
                plansContainer.appendChild(checkboxDiv);
            });

            const selectAllCheckbox = document.getElementById('selectAllPlans');
            const planCheckboxes = document.querySelectorAll('.plan-checkbox');

            selectAllCheckbox.checked = planCheckboxes.length > 0 && Array.from(planCheckboxes).every(cb => cb.checked);
            selectAllCheckbox.onchange = function() {
                planCheckboxes.forEach(cb => cb.checked = this.checked);
            };
            planCheckboxes.forEach(cb => {
                cb.onchange = function() {
                    const allChecked = Array.from(planCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                };
            });

            document.getElementById('assignPlansModal').style.display = 'flex';
        }

        function assignAllPlansToRoom(roomId, roomName) {
            if (!confirm(`Assign all available plans to "${roomName}"?`)) return;
            if (allPlans.length === 0) { alert('No plans available to assign.'); return; }

            const planIds = allPlans.map(plan => plan.id);
            const formData = new FormData();
            formData.append('room_id', roomId);
            planIds.forEach(planId => formData.append('plan_ids[]', planId));

            fetch('{{ route("operator.accommodation.step8.assignPlans", $accommodation->id) }}', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => { if (data.success) location.reload(); else alert('Error: ' + (data.message || 'Failed')); })
            .catch(e => { console.error(e); alert('An error occurred'); });
        }

        function removePlanFromRoom(roomId, planId, planName) {
            if (!confirm(`Remove "${planName}" from this room?`)) return;
            const formData = new FormData();
            formData.append('room_id', roomId);
            formData.append('plan_id', planId);

            fetch('{{ route("operator.accommodation.step8.removePlan", $accommodation->id) }}', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => { if (data.success) location.reload(); else alert('Error: ' + (data.message || 'Failed')); })
            .catch(e => { console.error(e); alert('An error occurred'); });
        }

        document.getElementById('assignPlansForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const roomId = document.getElementById('roomIdInput').value;
            const selectedPlans = Array.from(document.querySelectorAll('input[name="plan_ids[]"]:checked')).map(cb => cb.value);
            if (selectedPlans.length === 0) { alert('Please select at least one plan'); return; }
            const formData = new FormData();
            formData.append('room_id', roomId);
            selectedPlans.forEach(planId => formData.append('plan_ids[]', planId));

            fetch('{{ route("operator.accommodation.step8.assignPlans", $accommodation->id) }}', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => { if (data.success) { closeAssignPlansModal(); location.reload(); } else alert('Error: ' + (data.message || 'Failed')); })
            .catch(e => { console.error(e); alert('An error occurred: ' + e.message); });
        });

        document.getElementById('assignPlansModal').addEventListener('click', function(e) { if (e.target === this) closeAssignPlansModal(); });
    </script>
    <script>
        // Toggle promotion form
        function toggleAddPromotion() {
            const section = document.getElementById('addPromotionSection');
            const btn = document.getElementById('toggleAddPromotion');
            const isHidden = section.style.display === 'none' || section.style.display === '';
            
            console.log('Toggle clicked. Currently hidden:', isHidden);
            
            if (isHidden) {
                section.style.display = 'block';
                btn.textContent = '- Add New Promotion';
                section.scrollIntoView({ behavior: 'smooth' });
            } else {
                section.style.display = 'none';
                btn.textContent = '+ Add New Promotion';
                document.getElementById('promotionForm').reset();
                document.getElementById('promotion_id').value = '';
                // Clear Quill editor
                if(campaignDescEditor) {
                    campaignDescEditor.setText('');
                }
                document.querySelector('#promotionForm button[type="submit"]').textContent = 'Save Promotion';
            }
        }

        // Attach event listener when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggleAddPromotion');
            if (toggleBtn) {
                console.log('Attaching click handler to toggle button');
                toggleBtn.addEventListener('click', function(e) {
                    console.log('Toggle button clicked');
                    e.preventDefault();
                    e.stopPropagation();
                    toggleAddPromotion();
                    return false;
                });
            } else {
                console.error('Toggle button not found');
            }
        });

        // Edit promotion
        function editPromotion(promotionId) {
            fetch('{{ route("operator.accommodation.step11.get", [$accommodation->id, "PROMOTION_ID"]) }}'.replace('PROMOTION_ID', promotionId), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate form fields
                    document.querySelector('select[name="room_id"]').value = data.data.room_id;

                    // Filter rate plans by room and set value
                    filterRatePlans(data.data.room_id);
                    document.querySelector('select[name="rate_plan_id"]').value = data.data.rate_plan_id;
                    document.querySelector('input[name="campaign_name"]').value = data.data.campaign_name || '';
                    
                    // Set campaign description in Quill editor
                    if(campaignDescEditor && data.data.campaign_description){
                        campaignDescEditor.root.innerHTML = data.data.campaign_description;
                    }
                    document.querySelector('select[name="promotion_type"]').value = data.data.promotion_type || '';
                    document.querySelector('select[name="discount_type"]').value = data.data.discount_type || '';
                    document.querySelector('input[name="discount_value"]').value = data.data.discount_value || '';
                    document.querySelector('input[name="promo_valid_from"]').value = data.data.promo_valid_from || '';
                    document.querySelector('input[name="promo_valid_to"]').value = data.data.promo_valid_to || '';
                    document.querySelector('input[name="non_refundable"]').checked = data.data.non_refundable;
                    // Set hidden promotion id so save will update
                    document.getElementById('promotion_id').value = data.data.id;
                    
                    // Open form
                    document.getElementById('addPromotionSection').style.display = 'block';
                    document.getElementById('toggleAddPromotion').textContent = '✎ Edit Promotion';
                    document.querySelector('#promotionForm button[type="submit"]').textContent = 'Update Promotion';
                    document.getElementById('promotionForm').scrollIntoView({ behavior: 'smooth' });
                } else {
                    alert('Error: ' + (data.message || 'Failed to load promotion'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        }

        // Filter rate plan dropdown to show only plans matching selected room
        function filterRatePlans(roomId) {
            const rateSelect = document.querySelector('select[name="rate_plan_id"]');
            if (!rateSelect) return;
            const options = Array.from(rateSelect.querySelectorAll('option'));
            options.forEach(opt => {
                const optRoom = opt.getAttribute('data-room-id');
                // If no room selected, show only the placeholder option
                if (!roomId) {
                    opt.style.display = (opt.value === '') ? '' : 'none';
                    return;
                }

                // When a room is selected, show only options that have a matching room_id
                // Hide global/business-level plans (optRoom is null/empty)
                if (!optRoom) {
                    opt.style.display = 'none';
                    return;
                }

                opt.style.display = (String(optRoom) === String(roomId)) ? '' : 'none';
            });
            // If currently selected option is hidden, reset
            if (rateSelect.value && rateSelect.selectedOptions[0] && rateSelect.selectedOptions[0].style.display === 'none') {
                rateSelect.value = '';
            }
        }

        // When room changes, filter rate plans
        document.addEventListener('DOMContentLoaded', function() {
            const roomSelect = document.querySelector('select[name="room_id"]');
            if (roomSelect) {
                roomSelect.addEventListener('change', function() {
                    filterRatePlans(this.value);
                });
                // initial filter (in case form open)
                filterRatePlans(roomSelect.value);
            }
        });

        // Delete promotion
        function deletePromotion(promotionId) {
            if (!confirm('Delete this promotion?')) return;

            fetch('{{ route("operator.accommodation.step11.delete", [$accommodation->id, "PROMOTION_ID"]) }}'.replace('PROMOTION_ID', promotionId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        // Keep form open if there are validation errors
        document.addEventListener('DOMContentLoaded', function() {
            @if($errors->any())
                const addSection = document.getElementById('addPromotionSection');
                if (addSection) {
                    addSection.style.display = 'block';
                    document.getElementById('toggleAddPromotion').textContent = '- Add New Promotion';
                }
            @endif
        });

        // Reset form function to also clear Quill editor
        function toggleAddPromotion() {
            const addSection = document.getElementById('addPromotionSection');
            const toggleBtn = document.getElementById('toggleAddPromotion');
            
            if (addSection.style.display === 'none' || !addSection.style.display) {
                addSection.style.display = 'block';
                toggleBtn.textContent = '- Hide Form';
            } else {
                addSection.style.display = 'none';
                toggleBtn.textContent = '+ Add New Promotion';
                document.getElementById('promotionForm').reset();
                document.getElementById('promotion_id').value = '';
                if(campaignDescEditor) {
                    campaignDescEditor.setText('');
                }
                document.querySelector('#promotionForm button[type="submit"]').textContent = 'Save Promotion';
            }
        }
    </script>
    
    <!-- Back Button -->
    <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
        <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
            ← Back to Accommodation Overview
        </a>
    </div>
@endsection
