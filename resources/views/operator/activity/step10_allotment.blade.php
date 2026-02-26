@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <!-- Sidebar -->
            @php $currentStep = 10; @endphp
            <div class="col-md-3">
                @include('operator.activity._steps_sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <!-- Header -->
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:start;">
                        <div>
                            <h4 style="font-weight:600;color:#333;margin:0;">Step 10: Allotment</h4>
                            <p style="margin:4px 0 0 0;font-size:13px;color:#666;">{{ $activity->activity_name }}</p>
                        </div>
                        <div style="text-align:right;">
                            <p style="margin:0;font-size:12px;color:#999;">Service ID: {{ $activity->id }}</p>
                            <p style="margin:4px 0 0 0;font-size:12px;color:#19b5b5;font-weight:600;">Variants: {{ $variants->count() }}</p>
                        </div>
                    </div>
                </div>

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
                <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px;margin-bottom:16px;">
                    <p style="margin:0;font-size:13px;color:#856404;">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> Define allotment strategy, slot limits, and blackout dates. If calendar is not set, no date restriction applies.
                    </p>
                </div>

                @php
                    $variantMap = $variants->pluck('variant_name', 'variant_id');
                    $timeSlotMap = $timeSlots->mapWithKeys(function ($slot) {
                        $label = trim(($slot->start_time ?? '') . ' - ' . ($slot->end_time ?? ''));
                        $label = $label !== '-' ? $label : 'Time Slot';
                        return [$slot->timeslot_id => $label];
                    });
                @endphp

                <!-- Add New Allotment Button -->
                <div style="margin-bottom:16px;text-align:right;">
                    <button type="button" onclick="openAllotmentForm()" style="padding:10px 20px;background:#19b5b5;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">
                        <i class="fas fa-plus"></i> Add Allotment
                    </button>
                </div>

                <!-- Allotments List -->
                @if($allotments->count() > 0)
                    <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:20px;">
                        <h5 style="margin:0 0 16px 0;font-weight:600;color:#333;font-size:14px;">Allotment Overview</h5>
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                                <thead>
                                    <tr style="background:#f5f5f5;border-bottom:2px solid #e0e0e0;">
                                        <th style="padding:12px;text-align:left;font-weight:600;">Variant</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Type</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Strategy</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Slots</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Allotment</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Calendar</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Season</th>
                                        <th style="padding:12px;text-align:center;font-weight:600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allotments as $allotment)
                                        @php
                                            $slotLabels = [];
                                            if (!empty($allotment->slot_times)) {
                                                foreach ($allotment->slot_times as $slotId) {
                                                    $slotLabels[] = $timeSlotMap[$slotId] ?? $slotId;
                                                }
                                            }
                                        @endphp
                                        <tr style="border-bottom:1px solid #e0e0e0;">
                                            <td style="padding:12px;">{{ $allotment->variant_name }}</td>
                                            <td style="padding:12px;">{{ $allotment->participant_equipment_id }}</td>
                                            <td style="padding:12px;">{{ $allotment->allotment_strategy }}</td>
                                            <td style="padding:12px;font-size:12px;">
                                                {{ $slotLabels ? implode(', ', $slotLabels) : '-' }}
                                            </td>
                                            <td style="padding:12px;">{{ $allotment->allotment }}</td>
                                            <td style="padding:12px;font-size:12px;">
                                                {{ $allotment->calendar_enabled ? ($allotment->calendar_start?->format('d M Y') . ' - ' . $allotment->calendar_end?->format('d M Y')) : 'No date restriction' }}
                                            </td>
                                            <td style="padding:12px;">{{ $allotment->season ?? '-' }}</td>
                                            <td style="padding:12px;text-align:center;">
                                                <button type="button"
                                                    class="allotment-edit"
                                                    data-allotment-id="{{ $allotment->allotment_id }}"
                                                    data-variant-id="{{ $allotment->variant_id }}"
                                                    data-participant-equipment-id="{{ $allotment->participant_equipment_id }}"
                                                    data-allotment-strategy="{{ $allotment->allotment_strategy }}"
                                                    data-slot-times='@json($allotment->slot_times ?? [])'
                                                    data-allotment="{{ $allotment->allotment }}"
                                                    data-calendar-enabled="{{ $allotment->calendar_enabled ? 'Yes' : 'No' }}"
                                                    data-calendar-start="{{ $allotment->calendar_start?->format('Y-m-d') ?? '' }}"
                                                    data-calendar-end="{{ $allotment->calendar_end?->format('Y-m-d') ?? '' }}"
                                                    data-season="{{ $allotment->season ?? '' }}"
                                                    style="padding:6px 10px;background:#e3f2fd;color:#1565c0;border:none;border-radius:4px;cursor:pointer;font-size:12px;margin-right:4px;">
                                                    Edit
                                                </button>
                                                <form action="{{ route('operator.activity.step10.delete', [$activity->id, $allotment->allotment_id]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this allotment?');">
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
                        <p style="color:#999;font-size:14px;">No allotments created yet. Click "Add Allotment" to get started.</p>
                    </div>
                @endif

                <!-- Allotment Form -->
                <div id="allotmentFormContainer" style="background:#fff;border-radius:12px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;display:none;border:2px solid #19b5b5;">
                    <h5 style="margin:0 0 20px 0;font-weight:600;color:#333;" id="allotmentFormTitle">Add Allotment</h5>

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

                    <form id="allotmentForm" method="POST" action="{{ route('operator.activity.step10.store', $activity->id) }}">
                        @csrf
                        <input type="hidden" id="allotmentFormMethod" name="_method" value="POST">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Service Name *</label>
                                <input type="text" class="form-control" value="{{ $activity->activity_name }}" disabled style="font-size:13px;">
                                <small style="color:#666;font-size:12px;">Service ID: {{ $activity->id }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Variant Name *</label>
                                <select name="variant_id" id="variantId" class="form-control" required style="font-size:13px;">
                                    <option value="">Select Variant</option>
                                    @foreach($variants as $variant)
                                        <option value="{{ $variant->variant_id }}">{{ $variant->variant_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Participant/Equipment ID *</label>
                                <select name="participant_equipment_id" id="participantEquipmentId" class="form-control" required style="font-size:13px;">
                                    <option value="">Select</option>
                                    <option value="Per Person">Per Person</option>
                                    <option value="Per Equipment">Per Equipment</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Allotment Strategy *</label>
                                <select name="allotment_strategy" id="allotmentStrategy" class="form-control" required style="font-size:13px;">
                                    <option value="">Select Strategy</option>
                                    <option value="Per Slot">Per Slot</option>
                                    <option value="Daily Cap">Daily Cap</option>
                                    <option value="Equipment-based">Equipment-based</option>
                                </select>
                            </div>
                        </div>

                        <div class="row" id="slotTimesRow" style="display:none;">
                            <div class="col-md-12 mb-3">
                                <label style="font-weight:600;font-size:13px;">Slot Time (Multi-select)</label>
                                <select name="slot_times[]" id="slotTimes" class="form-control" multiple style="font-size:13px;">
                                    @foreach($timeSlots as $slot)
                                        <option value="{{ $slot->timeslot_id }}">{{ $slot->start_time }} - {{ $slot->end_time }}</option>
                                    @endforeach
                                </select>
                                <small style="color:#666;font-size:12px;">Select slot times if strategy is "Per Slot"</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label style="font-weight:600;font-size:13px;">Allotment *</label>
                                <input type="number" name="allotment" id="allotmentValue" class="form-control" min="0" required style="font-size:13px;">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-weight:600;font-size:13px;">Calendar *</label>
                                <select name="calendar_enabled" id="calendarEnabled" class="form-control" required style="font-size:13px;">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label style="font-weight:600;font-size:13px;">Season</label>
                                <select name="season" id="seasonValue" class="form-control" style="font-size:13px;">
                                    <option value="">None</option>
                                    <option value="One Season">One Season</option>
                                    <option value="High">High</option>
                                    <option value="Low">Low</option>
                                    <option value="Peak">Peak</option>
                                </select>
                            </div>
                        </div>

                        <div class="row" id="calendarRangeRow" style="display:none;">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Calendar Start *</label>
                                <input type="date" name="calendar_start" id="calendarStart" class="form-control" style="font-size:13px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Calendar End *</label>
                                <input type="date" name="calendar_end" id="calendarEnd" class="form-control" style="font-size:13px;">
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
                            <button type="button" onclick="hideAllotmentForm()" style="padding:10px 20px;background:#f0f0f0;color:#333;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">Cancel</button>
                            <button type="submit" style="padding:10px 20px;background:#19b5b5;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;"><span id="allotmentSubmitText">Save Allotment</span></button>
                        </div>
                    </form>
                </div>

                <!-- Blackout Dates -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <h5 style="margin:0;font-weight:600;color:#333;font-size:14px;">Blackout Dates</h5>
                    <button type="button" onclick="openBlackoutForm()" style="padding:8px 16px;background:#ff6f61;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;">
                        <i class="fas fa-calendar-times"></i> Add Blackout Dates
                    </button>
                </div>

                @if($blackouts->count() > 0)
                    <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:20px;">
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                                <thead>
                                    <tr style="background:#f5f5f5;border-bottom:2px solid #e0e0e0;">
                                        <th style="padding:12px;text-align:left;font-weight:600;">Variant</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Season</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">Start</th>
                                        <th style="padding:12px;text-align:left;font-weight:600;">End</th>
                                        <th style="padding:12px;text-align:center;font-weight:600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($blackouts as $blackout)
                                        <tr style="border-bottom:1px solid #e0e0e0;">
                                            <td style="padding:12px;">{{ $variantMap[$blackout->variant_id] ?? 'All Variants' }}</td>
                                            <td style="padding:12px;">{{ $blackout->season ?? '-' }}</td>
                                            <td style="padding:12px;">{{ $blackout->start_date->format('d M Y') }}</td>
                                            <td style="padding:12px;">{{ $blackout->end_date->format('d M Y') }}</td>
                                            <td style="padding:12px;text-align:center;">
                                                <form action="{{ route('operator.activity.step10.blackout.delete', [$activity->id, $blackout->blackout_id]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this blackout range?');">
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
                    <div style="background:#fff;border-radius:12px;padding:24px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:20px;">
                        <p style="color:#999;font-size:14px;">No blackout dates added yet.</p>
                    </div>
                @endif

                <!-- Blackout Form -->
                <div id="blackoutFormContainer" style="background:#fff;border-radius:12px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;display:none;border:2px solid #ff6f61;">
                    <h5 style="margin:0 0 20px 0;font-weight:600;color:#333;">Add Blackout Dates</h5>

                    <form id="blackoutForm" method="POST" action="{{ route('operator.activity.step10.blackout.store', $activity->id) }}">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Variant (Optional)</label>
                                <select name="variant_id" class="form-control" style="font-size:13px;">
                                    <option value="">All Variants</option>
                                    @foreach($variants as $variant)
                                        <option value="{{ $variant->variant_id }}">{{ $variant->variant_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label style="font-weight:600;font-size:13px;">Season (Optional)</label>
                                <select name="season" class="form-control" style="font-size:13px;">
                                    <option value="">None</option>
                                    <option value="One Season">One Season</option>
                                    <option value="High">High</option>
                                    <option value="Low">Low</option>
                                    <option value="Peak">Peak</option>
                                </select>
                            </div>
                        </div>

                        <h6 style="font-weight:600;margin:16px 0 12px 0;">Select Blackout Dates</h6>
                        <small style="display:block;margin-bottom:12px;color:#666;">Click on dates in the calendar below to select them. You can select individual dates or date ranges.</small>
                        
                        <div style="display:flex;gap:12px;margin-bottom:16px;">
                            <div style="flex:1;">
                                <label style="font-weight:600;font-size:13px;">From Date</label>
                                <input type="date" id="blackout_start_date" class="form-control" style="margin-bottom:8px;font-size:13px;">
                                <small style="color:#666;">Or click dates in calendar</small>
                            </div>
                            <div style="flex:1;">
                                <label style="font-weight:600;font-size:13px;">To Date (for range)</label>
                                <input type="date" id="blackout_end_date" class="form-control" style="margin-bottom:8px;font-size:13px;">
                                <small style="color:#666;">Leave empty for single date</small>
                            </div>
                            <div style="flex:0 0 auto;display:flex;align-items:flex-end;">
                                <button type="button" id="addRangeBtn" style="padding:8px 12px;background:#19b5b5;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;font-weight:600;">Add Range</button>
                            </div>
                        </div>

                        <!-- Calendar -->
                        <div id="blackoutCalendar" style="border:1px solid #ddd;border-radius:4px;padding:12px;background:#fafafa;margin-bottom:16px;"></div>

                        <!-- Selected blackout dates display -->
                        <div id="selectedBlackoutDates" style="background:#f0f8f8;padding:12px;border-radius:4px;min-height:40px;margin-bottom:16px;">
                            <label style="font-weight:600;display:block;margin-bottom:8px;font-size:13px;">Selected Blackout Dates/Periods:</label>
                            <div id="blackoutDatesList" style="font-size:13px;color:#333;">
                                <em style="color:#999;">No dates selected yet</em>
                            </div>
                        </div>
                        
                        <input type="hidden" name="blackout_dates" id="blackout_dates_input" value="">

                        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
                            <button type="button" onclick="hideBlackoutForm()" style="padding:10px 20px;background:#f0f0f0;color:#333;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">Cancel</button>
                            <button type="submit" style="padding:10px 20px;background:#ff6f61;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">Save Blackout</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.allotment-edit').forEach(button => {
                button.addEventListener('click', function() {
                    openAllotmentForm(this.dataset);
                });
            });

            document.getElementById('allotmentStrategy').addEventListener('change', toggleSlotTimes);
            document.getElementById('calendarEnabled').addEventListener('change', toggleCalendarRange);

            @if(old('allotment_strategy') || old('participant_equipment_id'))
                openAllotmentForm();
            @elseif(old('start_date') || old('end_date'))
                openBlackoutForm();
            @endif
        });

        function toggleSlotTimes() {
            const strategy = document.getElementById('allotmentStrategy').value;
            const slotRow = document.getElementById('slotTimesRow');
            slotRow.style.display = strategy === 'Per Slot' ? 'flex' : 'none';
        }

        function toggleCalendarRange() {
            const calendarEnabled = document.getElementById('calendarEnabled').value;
            const rangeRow = document.getElementById('calendarRangeRow');
            rangeRow.style.display = calendarEnabled === 'Yes' ? 'flex' : 'none';
        }

        function hideAllotmentForm() {
            document.getElementById('allotmentFormContainer').style.display = 'none';
            document.getElementById('allotmentForm').reset();
            document.getElementById('slotTimesRow').style.display = 'none';
            document.getElementById('calendarRangeRow').style.display = 'none';
        }

        function openAllotmentForm(data = null) {
            const form = document.getElementById('allotmentForm');
            form.reset();

            const isEdit = data && data.allotmentId;
            document.getElementById('allotmentFormMethod').value = isEdit ? 'PUT' : 'POST';
            document.getElementById('allotmentFormTitle').innerText = isEdit ? 'Edit Allotment' : 'Add Allotment';
            document.getElementById('allotmentSubmitText').innerText = isEdit ? 'Update Allotment' : 'Save Allotment';
            form.action = isEdit
                ? '{{ route('operator.activity.step10.update', [$activity->id, '__ALLOTMENT_ID__']) }}'.replace('__ALLOTMENT_ID__', data.allotmentId)
                : '{{ route('operator.activity.step10.store', $activity->id) }}';

            if (isEdit) {
                document.getElementById('variantId').value = data.variantId;
                document.getElementById('participantEquipmentId').value = data.participantEquipmentId;
                document.getElementById('allotmentStrategy').value = data.allotmentStrategy;
                document.getElementById('allotmentValue').value = data.allotment;
                document.getElementById('calendarEnabled').value = data.calendarEnabled || 'No';
                document.getElementById('calendarStart').value = data.calendarStart || '';
                document.getElementById('calendarEnd').value = data.calendarEnd || '';
                document.getElementById('seasonValue').value = data.season || '';

                const slotTimes = data.slotTimes ? JSON.parse(data.slotTimes).map(String) : [];
                const slotSelect = document.getElementById('slotTimes');
                Array.from(slotSelect.options).forEach(option => {
                    option.selected = slotTimes.includes(option.value);
                });
            }

            toggleSlotTimes();
            toggleCalendarRange();

            document.getElementById('allotmentFormContainer').style.display = 'block';
            document.getElementById('allotmentFormContainer').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Calendar management for blackout dates
        class BlackoutCalendarActivity {
            constructor() {
                this.selectedDates = [];
                this.currentMonth = new Date();
            }

            addDate(date) {
                const dateStr = this.formatDate(date);
                if (!this.selectedDates.includes(dateStr)) {
                    this.selectedDates.push(dateStr);
                    this.selectedDates.sort();
                }
            }

            addRange(startDate, endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const current = new Date(start);
                
                while (current <= end) {
                    this.addDate(new Date(current));
                    current.setDate(current.getDate() + 1);
                }
            }

            removeDate(dateStr) {
                this.selectedDates = this.selectedDates.filter(d => d !== dateStr);
            }

            formatDate(date) {
                const d = new Date(date);
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${d.getFullYear()}-${month}-${day}`;
            }

            renderCalendar() {
                const year = this.currentMonth.getFullYear();
                const month = this.currentMonth.getMonth();
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const daysInMonth = lastDay.getDate();
                const startingDayOfWeek = firstDay.getDay();

                let html = `
                    <div style="padding:12px;background:#fff;border-radius:4px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <button type="button" onclick="blackoutCalendar.previousMonth()" style="padding:4px 8px;background:#f0f0f0;border:none;cursor:pointer;border-radius:3px;">← Prev</button>
                            <strong>${new Date(year, month).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</strong>
                            <button type="button" onclick="blackoutCalendar.nextMonth()" style="padding:4px 8px;background:#f0f0f0;border:none;cursor:pointer;border-radius:3px;">Next →</button>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;text-align:center;font-size:11px;">
                            <div style="font-weight:600;color:#666;">Sun</div>
                            <div style="font-weight:600;color:#666;">Mon</div>
                            <div style="font-weight:600;color:#666;">Tue</div>
                            <div style="font-weight:600;color:#666;">Wed</div>
                            <div style="font-weight:600;color:#666;">Thu</div>
                            <div style="font-weight:600;color:#666;">Fri</div>
                            <div style="font-weight:600;color:#666;">Sat</div>
                `;

                for (let i = 0; i < startingDayOfWeek; i++) {
                    html += `<div style="padding:4px;"></div>`;
                }

                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(year, month, day);
                    const dateStr = this.formatDate(date);
                    const isSelected = this.selectedDates.includes(dateStr);
                    const isToday = this.formatDate(new Date()) === dateStr;
                    
                    html += `
                        <button type="button" onclick="blackoutCalendar.toggleDate('${dateStr}')" 
                            style="padding:4px;border:1px solid ${isSelected ? '#ff6f61' : '#ddd'};
                                background:${isSelected ? '#ff6f61' : (isToday ? '#f0f0f0' : '#fff')};
                                color:${isSelected ? '#fff' : '#333'};
                                border-radius:3px;cursor:pointer;font-size:11px;font-weight:${isSelected ? 'bold' : 'normal'};">
                            ${day}
                        </button>
                    `;
                }

                html += `</div></div>`;
                return html;
            }

            render() {
                document.getElementById('blackoutCalendar').innerHTML = this.renderCalendar();
                this.updateDatesList();
            }

            toggleDate(dateStr) {
                if (this.selectedDates.includes(dateStr)) {
                    this.removeDate(dateStr);
                } else {
                    this.selectedDates.push(dateStr);
                    this.selectedDates.sort();
                }
                this.render();
                this.updateInput();
            }

            previousMonth() {
                this.currentMonth.setMonth(this.currentMonth.getMonth() - 1);
                this.render();
            }

            nextMonth() {
                this.currentMonth.setMonth(this.currentMonth.getMonth() + 1);
                this.render();
            }

            updateDatesList() {
                const listDiv = document.getElementById('blackoutDatesList');
                if (this.selectedDates.length === 0) {
                    listDiv.innerHTML = '<em style="color:#999;">No dates selected yet</em>';
                    return;
                }

                const ranges = this.groupConsecutiveDates(this.selectedDates);
                let html = ranges.map(range => {
                    if (range.start === range.end) {
                        return `<div style="padding:4px;background:#ffe0b2;margin:4px 0;border-radius:3px;display:inline-block;margin-right:8px;">
                            ${this.formatDateDisplay(range.start)}
                            <button type="button" onclick="blackoutCalendar.removeDate('${range.start}');blackoutCalendar.render();blackoutCalendar.updateInput();" 
                                style="background:none;border:none;color:#d32f2f;cursor:pointer;margin-left:4px;font-weight:bold;">✕</button>
                        </div>`;
                    } else {
                        return `<div style="padding:4px;background:#ffe0b2;margin:4px 0;border-radius:3px;display:inline-block;margin-right:8px;">
                            ${this.formatDateDisplay(range.start)} → ${this.formatDateDisplay(range.end)}
                            <button type="button" onclick="blackoutCalendar.removeRange('${range.start}', '${range.end}');blackoutCalendar.render();blackoutCalendar.updateInput();" 
                                style="background:none;border:none;color:#d32f2f;cursor:pointer;margin-left:4px;font-weight:bold;">✕</button>
                        </div>`;
                    }
                }).join('');
                listDiv.innerHTML = html;
            }

            groupConsecutiveDates(dates) {
                const ranges = [];
                let currentRange = { start: dates[0], end: dates[0] };

                for (let i = 1; i < dates.length; i++) {
                    const currentDate = new Date(dates[i]);
                    const lastDate = new Date(currentRange.end);
                    lastDate.setDate(lastDate.getDate() + 1);

                    if (this.formatDate(lastDate) === dates[i]) {
                        currentRange.end = dates[i];
                    } else {
                        ranges.push(currentRange);
                        currentRange = { start: dates[i], end: dates[i] };
                    }
                }
                ranges.push(currentRange);
                return ranges;
            }

            removeRange(startDate, endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const current = new Date(start);
                
                while (current <= end) {
                    this.removeDate(this.formatDate(current));
                    current.setDate(current.getDate() + 1);
                }
            }

            formatDateDisplay(dateStr) {
                return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            }

            updateInput() {
                document.getElementById('blackout_dates_input').value = this.selectedDates.join(',');
            }
        }

        const blackoutCalendar = new BlackoutCalendarActivity();

        function openBlackoutForm() {
            document.getElementById('blackoutFormContainer').style.display = 'block';
            blackoutCalendar.render();
            document.getElementById('blackoutFormContainer').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function hideBlackoutForm() {
            document.getElementById('blackoutFormContainer').style.display = 'none';
            document.getElementById('blackoutForm').reset();
            blackoutCalendar.selectedDates = [];
            blackoutCalendar.render();
        }

        // Add range button functionality
        document.getElementById('addRangeBtn').addEventListener('click', function() {
            const startDate = document.getElementById('blackout_start_date').value;
            const endDate = document.getElementById('blackout_end_date').value;

            if (!startDate) {
                alert('Please enter a start date');
                return;
            }

            const end = endDate || startDate;
            blackoutCalendar.addRange(startDate, end);
            blackoutCalendar.render();
            blackoutCalendar.updateInput();
            document.getElementById('blackout_start_date').value = '';
            document.getElementById('blackout_end_date').value = '';
        });
    </script>

<!-- Back Button -->
<div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
    <a href="{{ route('operator.activity.show', $activity->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
        ← Back to Activity Overview
    </a>
</div>
@endsection
