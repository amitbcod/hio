@extends('layouts.admin')

@section('content')
    <div class="container mt-5">
        @php $currentStep = 9; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.accommodation._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <h2 style="font-weight:700;margin:0;">Step 9: Season and Pricing</h2>
                </div>

                {{-- Room + Plan Combinations Listing --}}
                @if(count($roomPlanCombinations) > 0)
                <div style="background:#f9f9f9;border-radius:16px;padding:20px;margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <h5 style="margin:0;font-weight:600;">Room & Plan Pricing Setup</h5>
                        <button type="button" id="advancedSettingsBtn" style="padding:6px 12px;background:#6f42c1;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:12px;">⚙ Advanced Settings</button>
                    </div>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr style="background:#19b5b5;color:#fff;text-align:left;">
                                    <th style="padding:12px;border:1px solid #ddd;">Room & Plan Details</th>
                                    <th style="padding:12px;border:1px solid #ddd;width:200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roomPlanCombinations as $combo)
                                    <tr style="background:#fff;border-bottom:1px solid #eee;">
                                        <td style="padding:12px;border:1px solid #ddd;vertical-align:middle;">
                                            <div style="margin-bottom:4px;">
                                                <strong>{{ $combo['room']->room_name }}</strong> - Bed: {{ $combo['room']->room_type }} (Adults: {{ $combo['room']->capacity }}, Children: {{ $combo['room']->children_capacity ?? 0 }})
                                            </div>
                                            <div style="color:#666;font-size:12px;">
                                                {{ $combo['plan']->rate_name }} - {{ $combo['plan']->meal_plan }} ({{ $combo['plan']->pricing_setting }})
                                            </div>
                                            @if($combo['has_default'] && $combo['default_pricing'])
                                                <div style="color:#28a745;font-size:11px;margin-top:4px;">
                                                    <strong>✓ Default: USD {{ number_format($combo['default_pricing']->base_rate, 2) }}</strong>
                                                </div>
                                            @else
                                                <div style="color:#e67e22;font-size:11px;margin-top:4px;">
                                                    <strong>⚠ No default price set</strong>
                                                </div>
                                            @endif
                                        </td>
                                        <td style="padding:12px;border:1px solid #ddd;vertical-align:middle;">
                                            <div style="display:flex;gap:8px;flex-direction:column;">
                                                @if($combo['has_default'] && $combo['default_pricing'])
                                                    <button type="button" 
                                                        class="btn-edit-default"
                                                        data-room-id="{{ $combo['room']->id }}"
                                                        data-plan-id="{{ $combo['plan']->id }}"
                                                        data-room-name="{{ $combo['room']->room_name }}"
                                                        data-plan-name="{{ $combo['plan']->rate_name }}"
                                                        data-adult-rate="{{ $combo['default_pricing']->base_rate }}"
                                                        data-extra-adult-rate="{{ $combo['default_pricing']->extra_adult_rate }}"
                                                        data-extra-bed-rate="{{ $combo['default_pricing']->extra_bed_rate ?? 0 }}"
                                                        data-children-rate="{{ $combo['default_pricing']->children_rate }}"
                                                        data-infant-rate="{{ $combo['default_pricing']->infant_rate }}"
                                                        style="padding:6px 12px;background:#ff9800;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:12px;text-decoration:none;text-align:center;">
                                                        Edit Default Price
                                                    </button>
                                                @else
                                                    <button type="button" 
                                                        class="btn-set-default"
                                                        data-room-id="{{ $combo['room']->id }}"
                                                        data-plan-id="{{ $combo['plan']->id }}"
                                                        data-room-name="{{ $combo['room']->room_name }}"
                                                        data-plan-name="{{ $combo['plan']->rate_name }}"
                                                        style="padding:6px 12px;background:#19b5b5;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:12px;text-decoration:none;text-align:center;">
                                                        Set Default Price
                                                    </button>
                                                @endif
                                                <button type="button" 
                                                    onclick="@if(!$combo['has_default'])alert('Please set a default price first');return false;@endif document.getElementById('form_{{ $combo['room']->id }}_{{ $combo['plan']->id }}').scrollIntoView({behavior:'smooth'}); toggleAddSeasonalForm('form_{{ $combo['room']->id }}_{{ $combo['plan']->id }}')"
                                                    style="padding:6px 12px;background:{{ $combo['has_default'] ? '#007bff' : '#ccc' }};color:#fff;border:none;border-radius:4px;cursor:{{ $combo['has_default'] ? 'pointer' : 'not-allowed' }};font-size:12px;text-decoration:none;text-align:center;{{ !$combo['has_default'] ? 'opacity:0.6;' : '' }}">
                                                    Seasonal Pricing
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Seasonal Pricing Section -->
                                    <tr style="background:#f9f9f9;border-bottom:1px solid #eee;">
                                        <td colspan="2" style="padding:16px;border:1px solid #ddd;">
                                            <div style="margin-bottom:16px;">
                                                <!-- Existing Seasonal Entries Section (Only if entries exist) -->
                                                @php
                                                    $seasonalEntries = \App\Models\AccommodationRate::where('accommodation_id', $accommodation->id)
                                                        ->where('room_id', $combo['room']->id)
                                                        ->where('rate_name', $combo['plan']->rate_name)
                                                        ->where('meal_plan', $combo['plan']->meal_plan)
                                                        ->where('pricing_setting', $combo['plan']->pricing_setting)
                                                        ->where('is_rate_plan', false)
                                                        ->where('is_default', false)
                                                        ->orderBy('valid_from')
                                                        ->get();
                                                @endphp

                                                @if($seasonalEntries->count() > 0)
                                                    <div style="margin-bottom:16px;">
                                                        <div style="display:flex;justify-content:space-between;align-items:center;cursor:pointer;padding:10px;background:#f0f0f0;border-radius:4px;margin-bottom:8px;" onclick="toggleSeasonalEntries('entries_{{ $combo['room']->id }}_{{ $combo['plan']->id }}')">
                                                            <h6 style="margin:0;color:#19b5b5;font-size:13px;">Seasonal Pricing Entries ({{ $seasonalEntries->count() }})</h6>
                                                            <span id="entries_{{ $combo['room']->id }}_{{ $combo['plan']->id }}_toggle" style="color:#19b5b5;font-weight:bold;">▼</span>
                                                        </div>
                                                        
                                                        <div id="entries_{{ $combo['room']->id }}_{{ $combo['plan']->id }}" style="display:block;">
                                                            @foreach($seasonalEntries as $entry)
                                                                <div style="background:#fff;padding:10px;border-radius:4px;margin-bottom:8px;border:1px solid #e0e0e0;font-size:12px;">
                                                                    <div style="display:flex;justify-content:space-between;align-items:start;gap:12px;">
                                                                        <div style="flex:1;">
                                                                            <strong>{{ \Carbon\Carbon::parse($entry->valid_from)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($entry->valid_to)->format('M d, Y') }}</strong><br>
                                                                            Adult: USD {{ number_format($entry->base_rate, 2) }} | 
                                                                            Extra Adult: USD {{ number_format($entry->extra_adult_rate, 2) }} | 
                                                                            Children: USD {{ number_format($entry->children_rate, 2) }}
                                                                        </div>
                                                                        <div style="display:flex;gap:6px;">
                                                                            <button type="button" 
                                                                                class="btn-edit-seasonal"
                                                                                data-entry-id="{{ $entry->id }}"
                                                                                data-room-id="{{ $combo['room']->id }}"
                                                                                data-plan-id="{{ $combo['plan']->id }}"
                                                                                data-valid-from="{{ $entry->valid_from }}"
                                                                                data-valid-to="{{ $entry->valid_to }}"
                                                                                data-adult-rate="{{ $entry->base_rate }}"
                                                                                data-extra-adult-rate="{{ $entry->extra_adult_rate }}"
                                                                                data-extra-bed-rate="{{ $entry->extra_bed_rate ?? 0 }}"
                                                                                data-children-rate="{{ $entry->children_rate }}"
                                                                                data-infant-rate="{{ $entry->infant_rate }}"
                                                                                style="padding:4px 8px;background:#ff9800;border:none;border-radius:4px;color:#fff;cursor:pointer;font-size:11px;">Edit</button>
                                                                            <button type="button"
                                                                                class="btn-duplicate-seasonal"
                                                                                data-form-id="form_{{ $combo['room']->id }}_{{ $combo['plan']->id }}"
                                                                                data-valid-from="{{ $entry->valid_from }}"
                                                                                data-valid-to="{{ $entry->valid_to }}"
                                                                                data-adult-rate="{{ $entry->base_rate }}"
                                                                                data-extra-adult-rate="{{ $entry->extra_adult_rate }}"
                                                                                data-extra-bed-rate="{{ $entry->extra_bed_rate ?? 0 }}"
                                                                                data-children-rate="{{ $entry->children_rate }}"
                                                                                data-infant-rate="{{ $entry->infant_rate }}"
                                                                                style="padding:4px 8px;background:#17a2b8;border:none;border-radius:4px;color:#fff;cursor:pointer;font-size:11px;">Duplicate</button>
                                                                            <button type="button" onclick="deleteSeasonalEntry({{ $entry->id }})" style="padding:4px 8px;background:#dc3545;border:none;border-radius:4px;color:#fff;cursor:pointer;font-size:11px;">Delete</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Collapsible Add New Seasonal Entry Form (Only if default price is set) -->
                                                @if($combo['has_default'])
                                                <div style="background:#fff;padding:12px;border-radius:4px;border:1px solid #ddd;">
                                                    <div style="display:flex;justify-content:space-between;align-items:center;cursor:pointer;" onclick="toggleAddSeasonalForm('form_{{ $combo['room']->id }}_{{ $combo['plan']->id }}')">
                                                        <small style="color:#666;"><strong>+ Add New Seasonal Entry</strong></small>
                                                        <span id="form_{{ $combo['room']->id }}_{{ $combo['plan']->id }}_toggle" style="color:#19b5b5;font-weight:bold;">▼</span>
                                                    </div>
                                                    <form id="form_{{ $combo['room']->id }}_{{ $combo['plan']->id }}" onsubmit="saveSeasonalEntry(event, '{{ $combo['room']->id }}', '{{ $combo['plan']->id }}')" style="margin-top:8px;display:none;">
                                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">
                                                            <div>
                                                                <label style="font-weight:600;font-size:11px;">Valid From *</label>
                                                                <input type="date" name="valid_from" class="form-control" required style="font-size:12px;">

                                                            
                                                                <label style="font-weight:600;font-size:11px;">Valid To *</label>
                                                                <input type="date" name="valid_to" class="form-control" required style="font-size:12px;">
                                                            </div>
                                                        </div>
                                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">
                                                            <div>
                                                                <label style="font-weight:600;font-size:11px;">Room Rate (USD) *</label>
                                                                <input type="number" name="adult_rate" class="form-control" step="0.01" min="0" required style="font-size:12px;" value="{{ $combo['default_pricing']->base_rate }}">
                                                                <div style="font-size:11px;color:#666;margin-top:4px;">Base room price covers configured adults, children, and infants occupancy.</div>
                                                            </div>
                                                            <div>
                                                                <label style="font-weight:600;font-size:11px;">Extra Adult Rate (USD) *</label>
                                                                <input type="number" name="extra_adult_rate" class="form-control" step="0.01" min="0" required style="font-size:12px;" value="{{ $combo['default_pricing']->extra_adult_rate }}">
                                                            </div>
                                                        </div>
                                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">
                                                            <div>
                                                                <label style="font-weight:600;font-size:11px;">Extra Bed Rate (USD)</label>
                                                                <input type="number" name="extra_bed_rate" class="form-control" step="0.01" min="0" style="font-size:12px;" value="{{ $combo['default_pricing']->extra_bed_rate ?? 0 }}">
                                                            </div>
                                                            <div>
                                                                <label style="font-weight:600;font-size:11px;">Children Rate (USD) *</label>
                                                                <input type="number" name="children_rate" class="form-control" step="0.01" min="0" required style="font-size:12px;" value="{{ $combo['default_pricing']->children_rate }}">
                                                            </div>
                                                        </div>
                                                        <div style="margin-bottom:8px;">
                                                            <label style="font-weight:600;font-size:11px;">Infant Rate (USD) *</label>
                                                            <input type="number" name="infant_rate" class="form-control" step="0.01" min="0" required style="font-size:12px;" value="{{ $combo['default_pricing']->infant_rate }}">
                                                        </div>
                                                        <button type="submit" style="padding:6px 12px;background:#28a745;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:11px;">Add Seasonal Entry</button>
                                                    </form>
                                                </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                    <div style="background:#fff3cd;padding:16px;border-radius:8px;color:#856404;margin-bottom:20px;">
                        No room and plan combinations found. Please <a href="{{ route('operator.accommodation.step8.show', $accommodation->id) }}" style="color:#856404;font-weight:600;">assign plans to rooms first</a>
                    </div>
                @endif

                <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:16px;">
                    <a href="{{ route('operator.accommodation.step8.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">← Back to Rate Plans</a>
                    <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#28a745;color:#fff;padding:8px 12px;border-radius:4px;">Complete Setup</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for Setting Default Price --}}
    <div id="defaultPriceModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:24px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.2);width:90%;max-width:500px;max-height:80vh;overflow-y:auto;">
            <h5 id="defaultPriceModalTitle" style="margin-top:0;margin-bottom:16px;font-weight:600;">Set Default Price</h5>
            <div id="defaultPriceInfo" style="background:#f9f9f9;padding:12px;border-radius:4px;margin-bottom:16px;font-size:12px;color:#666;"></div>
            
            <form id="defaultPriceForm" method="POST">
                @csrf
                <input type="hidden" id="defaultRoomId" name="room_id">
                <input type="hidden" id="defaultPlanId" name="plan_id">
                
                <div class="mb-3">
                    <label style="font-weight:600;">Room Rate (USD) *</label>
                    <input type="number" name="adult_rate" class="form-control" step="0.01" min="0" required placeholder="0.00">
                    <small style="display:block;margin-top:4px;color:#666;">This base room price covers configured adults, children, and infants occupancy.</small>
                </div>
                <div class="mb-3">
                    <label style="font-weight:600;">Extra Adult Rate (USD) *</label>
                    <input type="number" name="extra_adult_rate" class="form-control" step="0.01" min="0" required placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label style="font-weight:600;">Extra Bed Rate (USD)</label>
                    <input type="number" name="extra_bed_rate" class="form-control" step="0.01" min="0" placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label style="font-weight:600;">Children Rate (USD) *</label>
                    <input type="number" name="children_rate" class="form-control" step="0.01" min="0" required placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label style="font-weight:600;">Infant Rate (USD) *</label>
                    <input type="number" name="infant_rate" class="form-control" step="0.01" min="0" required placeholder="0.00">
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" onclick="closeDefaultPriceModal()" style="padding:8px 14px;background:#f0f0f0;color:#333;border:none;border-radius:4px;cursor:pointer;">Cancel</button>
                    <button type="submit" id="defaultPriceSubmitBtn" style="padding:8px 14px;background:#19b5b5;color:#fff;border:none;border-radius:4px;cursor:pointer;">Save Default Price</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal for Seasonal Pricing --}}
    <div id="seasonalPricingModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:24px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.2);width:90%;max-width:500px;">
            <h5 style="margin-top:0;margin-bottom:16px;font-weight:600;">Add Seasonal Pricing</h5>
            <div id="seasonalPricingInfo" style="background:#f9f9f9;padding:12px;border-radius:4px;margin-bottom:16px;font-size:12px;color:#666;"></div>
            <p style="color:#666;">Seasonal pricing functionality coming soon...</p>
            <button type="button" onclick="closeSeasonalPricingModal()" style="padding:8px 14px;background:#f0f0f0;color:#333;border:none;border-radius:4px;cursor:pointer;">Close</button>
        </div>
    </div>

    {{-- Modal for Editing Seasonal Entry --}}
    <div id="editSeasonalModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:24px;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.2);width:90%;max-width:500px;max-height:80vh;overflow-y:auto;">
            <h5 style="margin-top:0;margin-bottom:16px;font-weight:600;">Edit Seasonal Entry</h5>
            
            <form id="editSeasonalForm" method="POST" style="margin-bottom:16px;">
                @csrf
                <input type="hidden" id="editEntryId" name="entry_id">
                
                <div class="mb-3">
                    <label style="font-weight:600;">Valid From *</label>
                    <input type="date" id="editValidFrom" name="valid_from" class="form-control" required style="font-size:12px;">
                </div>
                <div class="mb-3">
                    <label style="font-weight:600;">Valid To *</label>
                    <input type="date" id="editValidTo" name="valid_to" class="form-control" required style="font-size:12px;">
                </div>
                <div class="mb-3">
                    <label style="font-weight:600;">Room Rate (USD) *</label>
                    <input type="number" id="editAdultRate" name="adult_rate" class="form-control" step="0.01" min="0" required style="font-size:12px;">
                    <small style="display:block;margin-top:4px;color:#666;">This base room price covers configured adults, children, and infants occupancy.</small>
                </div>
                <div class="mb-3">
                    <label style="font-weight:600;">Extra Adult Rate (USD) *</label>
                    <input type="number" id="editExtraAdultRate" name="extra_adult_rate" class="form-control" step="0.01" min="0" required style="font-size:12px;">
                </div>
                <div class="mb-3">
                    <label style="font-weight:600;">Extra Bed Rate (USD)</label>
                    <input type="number" id="editExtraBedRate" name="extra_bed_rate" class="form-control" step="0.01" min="0" style="font-size:12px;">
                </div>
                <div class="mb-3">
                    <label style="font-weight:600;">Children Rate (USD) *</label>
                    <input type="number" id="editChildrenRate" name="children_rate" class="form-control" step="0.01" min="0" required style="font-size:12px;">
                </div>
                <div class="mb-3">
                    <label style="font-weight:600;">Infant Rate (USD) *</label>
                    <input type="number" id="editInfantRate" name="infant_rate" class="form-control" step="0.01" min="0" required style="font-size:12px;">
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" onclick="closeEditSeasonalModal()" style="padding:8px 14px;background:#f0f0f0;color:#333;border:none;border-radius:4px;cursor:pointer;">Cancel</button>
                    <button type="submit" style="padding:8px 14px;background:#ff9800;color:#fff;border:none;border-radius:4px;cursor:pointer;">Update Entry</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const updateSeasonUrlTemplate = @json(route('operator.accommodation.step9.updateSeason', ['id' => $accommodation->id, 'entryId' => '__ENTRY_ID__']));
        const deleteSeasonUrlTemplate = @json(route('operator.accommodation.step9.deleteSeason', ['id' => $accommodation->id, 'entryId' => '__ENTRY_ID__']));

        function openSetDefaultPriceModal(btn) {
            const roomId = btn.dataset.roomId;
            const planId = btn.dataset.planId;
            const roomName = btn.dataset.roomName;
            const planName = btn.dataset.planName;
            
            document.getElementById('defaultPriceModalTitle').textContent = 'Set Default Price';
            document.getElementById('defaultPriceSubmitBtn').textContent = 'Save Default Price';
            document.getElementById('defaultRoomId').value = roomId;
            document.getElementById('defaultPlanId').value = planId;
            document.getElementById('defaultPriceInfo').innerHTML = `
                <strong>${roomName}</strong><br>
                ${planName}
            `;
            document.getElementById('defaultPriceForm').reset();
            document.getElementById('defaultPriceModal').style.display = 'flex';
        }

        function openEditDefaultPriceModal(btn) {
            const roomId = btn.dataset.roomId;
            const planId = btn.dataset.planId;
            const roomName = btn.dataset.roomName;
            const planName = btn.dataset.planName;
            const adultRate = parseFloat(btn.dataset.adultRate);
            const extraAdultRate = parseFloat(btn.dataset.extraAdultRate);
            const extraBedRate = parseFloat(btn.dataset.extraBedRate);
            const childrenRate = parseFloat(btn.dataset.childrenRate);
            const infantRate = parseFloat(btn.dataset.infantRate);
            
            document.getElementById('defaultPriceModalTitle').textContent = 'Edit Default Price';
            document.getElementById('defaultPriceSubmitBtn').textContent = 'Update Default Price';
            document.getElementById('defaultRoomId').value = roomId;
            document.getElementById('defaultPlanId').value = planId;
            document.getElementById('defaultPriceInfo').innerHTML = `
                <strong>${roomName}</strong><br>
                ${planName}
            `;
            document.getElementById('defaultPriceForm').querySelector('input[name="adult_rate"]').value = adultRate;
            document.getElementById('defaultPriceForm').querySelector('input[name="extra_adult_rate"]').value = extraAdultRate;
            document.getElementById('defaultPriceForm').querySelector('input[name="extra_bed_rate"]').value = extraBedRate;
            document.getElementById('defaultPriceForm').querySelector('input[name="children_rate"]').value = childrenRate;
            document.getElementById('defaultPriceForm').querySelector('input[name="infant_rate"]').value = infantRate;
            document.getElementById('defaultPriceModal').style.display = 'flex';
        }

        function closeDefaultPriceModal() {
            document.getElementById('defaultPriceModal').style.display = 'none';
            document.getElementById('defaultPriceForm').reset();
        }

        function openSetSeasonalPricingModal(roomId, planId, roomName, planName) {
            document.getElementById('seasonalPricingInfo').innerHTML = `
                <strong>${roomName}</strong><br>
                ${planName}
            `;
            document.getElementById('seasonalPricingModal').style.display = 'flex';
        }

        function closeSeasonalPricingModal() {
            document.getElementById('seasonalPricingModal').style.display = 'none';
        }

        function openEditSeasonalModal(btn) {
            const entryId = btn.dataset.entryId;
            const validFrom = btn.dataset.validFrom;
            const validTo = btn.dataset.validTo;
            const adultRate = parseFloat(btn.dataset.adultRate);
            const extraAdultRate = parseFloat(btn.dataset.extraAdultRate);
            const extraBedRate = parseFloat(btn.dataset.extraBedRate);
            const childrenRate = parseFloat(btn.dataset.childrenRate);
            const infantRate = parseFloat(btn.dataset.infantRate);

            document.getElementById('editEntryId').value = entryId;
            document.getElementById('editValidFrom').value = validFrom;
            document.getElementById('editValidTo').value = validTo;
            document.getElementById('editAdultRate').value = adultRate;
            document.getElementById('editExtraAdultRate').value = extraAdultRate;
            document.getElementById('editExtraBedRate').value = extraBedRate;
            document.getElementById('editChildrenRate').value = childrenRate;
            document.getElementById('editInfantRate').value = infantRate;

            document.getElementById('editSeasonalModal').style.display = 'flex';
        }

        function closeEditSeasonalModal() {
            document.getElementById('editSeasonalModal').style.display = 'none';
            document.getElementById('editSeasonalForm').reset();
        }

        // Handle form submission
        document.getElementById('defaultPriceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const roomId = document.getElementById('defaultRoomId').value;
            const planId = document.getElementById('defaultPlanId').value;

            fetch('{{ route("operator.accommodation.step9.setDefaultPrice", $accommodation->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to save default price');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeDefaultPriceModal();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to save default price'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        });

        // Handle Edit Seasonal Form submission
        document.getElementById('editSeasonalForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const entryId = document.getElementById('editEntryId').value;
            const validFrom = document.getElementById('editValidFrom').value;
            const validTo = document.getElementById('editValidTo').value;

            if (new Date(validTo) <= new Date(validFrom)) {
                alert('Valid To date must be after Valid From date');
                return;
            }

            const formData = new FormData(this);

            fetch(updateSeasonUrlTemplate.replace('__ENTRY_ID__', encodeURIComponent(entryId)), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to update seasonal entry');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeEditSeasonalModal();
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update seasonal entry'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        });

        // Handle Set/Edit Default Price buttons
        document.querySelectorAll('.btn-set-default').forEach(btn => {
            btn.addEventListener('click', function() {
                openSetDefaultPriceModal(this);
            });
        });

        document.querySelectorAll('.btn-edit-default').forEach(btn => {
            btn.addEventListener('click', function() {
                openEditDefaultPriceModal(this);
            });
        });

        // Close modals when clicking outside
        document.getElementById('defaultPriceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDefaultPriceModal();
            }
        });

        document.getElementById('seasonalPricingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSeasonalPricingModal();
            }
        });

        document.getElementById('editSeasonalModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditSeasonalModal();
            }
        });

        // Handle Edit Seasonal Entry buttons
        document.querySelectorAll('.btn-edit-seasonal').forEach(btn => {
            btn.addEventListener('click', function() {
                openEditSeasonalModal(this);
            });
        });

        // Handle Duplicate Seasonal Entry buttons
        document.querySelectorAll('.btn-duplicate-seasonal').forEach(btn => {
            btn.addEventListener('click', function() {
                duplicateSeasonalEntry(this);
            });
        });

        // Collapsible Seasonal Entries Toggle
        function toggleSeasonalEntries(sectionId) {
            const section = document.getElementById(sectionId);
            const toggle = document.getElementById(sectionId + '_toggle');
            if (section.style.display === 'none' || section.style.display === '') {
                section.style.display = 'block';
                toggle.textContent = '▲';
            } else {
                section.style.display = 'none';
                toggle.textContent = '▼';
            }
        }

        function duplicateSeasonalEntry(btn) {
            const formId = btn.dataset.formId;
            const form = document.getElementById(formId);
            if (!form) {
                return;
            }

            form.querySelector('input[name="valid_from"]').value = btn.dataset.validFrom || '';
            form.querySelector('input[name="valid_to"]').value = btn.dataset.validTo || '';
            form.querySelector('input[name="adult_rate"]').value = btn.dataset.adultRate || '';
            form.querySelector('input[name="extra_adult_rate"]').value = btn.dataset.extraAdultRate || '';
            form.querySelector('input[name="extra_bed_rate"]').value = btn.dataset.extraBedRate || '';
            form.querySelector('input[name="children_rate"]').value = btn.dataset.childrenRate || '';
            form.querySelector('input[name="infant_rate"]').value = btn.dataset.infantRate || '';

            form.style.display = 'block';
            const toggle = document.getElementById(formId + '_toggle');
            if (toggle) {
                toggle.textContent = '▲';
            }

            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Collapsible Add Seasonal Form Toggle
        function toggleAddSeasonalForm(formId) {
            const form = document.getElementById(formId);
            const toggle = document.getElementById(formId + '_toggle');
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
                toggle.textContent = '▲';
            } else {
                form.style.display = 'none';
                toggle.textContent = '▼';
            }
        }

        // Save Seasonal Entry
        function saveSeasonalEntry(event, roomId, planId) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            formData.append('room_id', roomId);
            formData.append('plan_id', planId);

            const validFrom = new Date(formData.get('valid_from'));
            const validTo = new Date(formData.get('valid_to'));

            if (validTo <= validFrom) {
                alert('Valid To date must be after Valid From date');
                return;
            }

            fetch('{{ route("operator.accommodation.step9.addSeason", $accommodation->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to save seasonal entry');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to save seasonal entry'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        }

        // Delete Seasonal Entry
        function deleteSeasonalEntry(entryId) {
            if (!confirm('Delete this seasonal entry?')) {
                return;
            }

            fetch(deleteSeasonUrlTemplate.replace('__ENTRY_ID__', encodeURIComponent(entryId)), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to delete seasonal entry');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete seasonal entry'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        }

        // Edit Seasonal Entry (placeholder)
        function editSeasonalEntry(entryId, roomId, planId) {
            // kept for compatibility - find the button and trigger its handler if present
            const btn = document.querySelector('.btn-edit-seasonal[data-entry-id="' + entryId + '"]');
            if (btn) {
                openEditSeasonalModal(btn);
            } else {
                alert('Edit functionality coming soon');
            }
        }

        // Advanced Settings Button Handler
        document.addEventListener('DOMContentLoaded', function() {
            const advBtn = document.getElementById('advancedSettingsBtn');
            if(advBtn) {
                advBtn.addEventListener('click', function() {
                    const feesPanel = document.getElementById('fees-popup-panel');
                    if(feesPanel) {
                        feesPanel.style.display = feesPanel.style.display === 'none' ? 'block' : 'none';
                    } else {
                        console.warn('Fees popup panel not found. Make sure tools/fees_popup.js is loaded.');
                    }
                });
            }
        });
        
    </script>
    <script>
        window.hioFeesPopupConfig = {
            saveUrlTemplate: @json(route('operator.accommodation.additional_fees.save', ['id' => '__ACCOMMODATION_ID__'])),
            getUrlTemplate: @json(route('operator.accommodation.additional_fees.get', ['id' => '__ACCOMMODATION_ID__']))
        };
    </script>
    <script src="{{ asset('tools/fees_popup.js') }}?v={{ @filemtime(public_path('tools/fees_popup.js')) ?: time() }}"></script>
    
    <!-- Back Button -->
    <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
        <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
            ← Back to Accommodation Overview
        </a>
    </div>
@endsection
