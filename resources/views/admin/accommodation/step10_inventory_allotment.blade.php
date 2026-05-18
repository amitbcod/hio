@extends('layouts.admin')

@section('content')
    <div class="container mt-5">
        @php $currentStep = 10; @endphp
        <div class="row">
            <div class="col-md-3">
                @include('operator.accommodation._steps_sidebar')
            </div>
            <div class="col-md-9">
                <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                    <h2 style="font-weight:700;margin:0;">Step 10: Allotment Management</h2>
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
                <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:16px;margin-bottom:16px;color:#2e7d32;">
                    <strong>✓ {{ session('success') }}</strong>
                </div>
                @endif

                @if(session('error'))
                <div style="background:#ffebee;border:1px solid #ef5350;border-radius:8px;padding:16px;margin-bottom:16px;color:#c62828;">
                    <strong>✗ {{ session('error') }}</strong>
                </div>
                @endif

                {{-- Add New Inventory Allotment --}}
                <div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:20px;">
                    <h4 style="margin-top:0;margin-bottom:16px;">
                        <a href="#" id="toggleAddAllotment" style="text-decoration:none;color:inherit;">+ Add New Allotment</a>
                    </h4>
                    
                    <div id="addAllotmentSection" style="display:none;">
                        <form id="inventoryAllotmentForm" method="POST" action="{{ route('operator.accommodation.step10.save', $accommodation->id) }}" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label style="font-weight:600;">Room/Unit *</label>
                                    <select name="room_id" id="room_id_select" class="form-control" required>
                                        <option value="">-- Select a Room --</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}" 
                                                data-allotment="{{ $room->allotment ?? 0 }}" 
                                                data-capacity="{{ $room->max_capacity ?? 0 }}">
                                                {{ $room->room_name }} ({{ $room->room_type }}) - Allotment: {{ $room->allotment ?? 0 }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small style="color:#666;display:block;margin-top:4px;">Select a specific room for this allotment</small>
                                </div>
                            </div>

                            {{-- Inventory Numbers --}}
                            <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                                <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Inventory Numbers</h6>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label style="font-weight:600;">Sellable Units (Allotment) *</label>
                                        <input type="number" name="sellable_units" id="sellable_units_input" class="form-control" min="0" required value="0">
                                        <small style="color:#666;">From room setup - editable here</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label style="font-weight:600;">Sold/Confirmed Bookings *</label>
                                        <input type="number" name="sold_units" id="sold_units_input" class="form-control" min="0" required value="0">
                                        <small style="color:#666;">Number of rooms already sold</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label style="font-weight:600;">Available Units (Auto)</label>
                                        <input type="number" id="available_units" class="form-control" readonly>
                                        <small style="color:#19b5b5;">Calculated: Sellable - Sold</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Minimum Stay & Release --}}
                            <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                                <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Length of Stay & Release Policy</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label style="font-weight:600;">Minimum Nights</label>
                                        <input type="number" name="minimum_nights" class="form-control" min="0">
                                        <small style="color:#666;">Minimum stay requirement (optional)</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label style="font-weight:600;">Days Before Check-in to Release Allotment</label>
                                        <input type="number" name="days_before_release" class="form-control" min="0">
                                        <small style="color:#666;">Days before arrival to release allotment (optional)</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Availability Controls --}}
                            <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                                <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Availability Controls</h6>
                                <div class="mb-3">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                        <input type="checkbox" name="sell_and_report" class="form-check-input">
                                        <span>Active Sell & Report Status</span>
                                    </label>
                                    <small style="color:#666;">Enable active selling and reporting for this allotment</small>
                                </div>
                                <div class="mb-3">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                        <input type="checkbox" name="stop_sell" id="stop_sell" class="form-check-input">
                                        <span>Blackout Period - Stop Sell on Selected Dates</span>
                                    </label>
                                    <small style="color:#666;">Select dates when booking should be blocked</small>
                                </div>

                                {{-- Blackout Calendar (shown only when Stop Sell is checked) --}}
                                <div id="blackoutCalendarSection" style="display:none;margin-top:16px;padding:16px;background:#fff;border-radius:4px;border:1px solid #ddd;">
                                    <h6 style="margin-top:0;margin-bottom:12px;">Select Blackout Dates</h6>
                                    <small style="display:block;margin-bottom:12px;color:#666;">Click on dates in the calendar below to select them. You can select individual dates or date ranges.</small>
                                    
                                    <div style="display:flex;gap:12px;margin-bottom:16px;">
                                        <div style="flex:1;">
                                            <label style="font-weight:600;">From Date</label>
                                            <input type="date" id="blackout_start_date" class="form-control" style="margin-bottom:8px;">
                                            <small style="color:#666;">Or click dates in calendar</small>
                                        </div>
                                        <div style="flex:1;">
                                            <label style="font-weight:600;">To Date (for range)</label>
                                            <input type="date" id="blackout_end_date" class="form-control" style="margin-bottom:8px;">
                                            <small style="color:#666;">Leave empty for single date</small>
                                        </div>
                                        <div style="flex:0 0 auto;display:flex;align-items:flex-end;">
                                            <button type="button" id="addRangeBtn" style="padding:8px 12px;background:#19b5b5;color:#fff;border:none;border-radius:4px;cursor:pointer;">Add Range</button>
                                        </div>
                                    </div>

                                    {{-- Simple calendar --}}
                                    <div id="blackoutCalendar" style="border:1px solid #ddd;border-radius:4px;padding:12px;background:#fafafa;margin-bottom:16px;"></div>

                                    {{-- Selected blackout dates display --}}
                                    <div id="selectedBlackoutDates" style="background:#f0f8f8;padding:12px;border-radius:4px;min-height:40px;">
                                        <label style="font-weight:600;display:block;margin-bottom:8px;">Selected Blackout Dates/Periods:</label>
                                        <div id="blackoutDatesList" style="font-size:13px;color:#333;">
                                            <em style="color:#999;">No dates selected yet</em>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="blackout_dates" id="blackout_dates_input" value="">
                                </div>
                            </div>

                            {{-- Block Arrivals --}}
                            <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                                <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Block Arrivals (OTA/Walk-in Handling)</h6>
                                <div class="mb-3">
                                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                        <input type="checkbox" name="block_arrivals" id="block_arrivals" class="form-check-input">
                                        <span>Block Arrivals to Prevent Overbooking</span>
                                    </label>
                                    <small style="color:#666;">Prevents walk-in bookings and OTA arrivals on this date</small>
                                </div>
                                <div id="blockDaysField" style="display:none;">
                                    <label style="font-weight:600;">Number of Days to Block</label>
                                    <input type="number" name="block_days" class="form-control" min="1" value="1">
                                    <small style="color:#666;">Days before check-in to block (typically 1 day)</small>
                                </div>
                            </div>

                            {{-- Instant vs On Request --}}
                            <div style="background:#f9f9f9;padding:16px;border-radius:8px;margin-bottom:16px;">
                                <h6 style="margin-top:0;font-weight:600;margin-bottom:12px;">Booking Confirmation Mode</h6>
                                <label style="font-weight:600;">Instant / On Request *</label>
                                <select name="instant_on_request" class="form-control" required>
                                    <option value="Instant">Instant Confirmation</option>
                                    <option value="On Request">On Request (Manual Approval)</option>
                                </select>
                                <small style="color:#666;display:block;margin-top:4px;">Determine booking confirmation method</small>
                            </div>

                            {{-- Submit Button --}}
                            <div style="display:flex;gap:12px;">
                                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;">
                                    Save Allotment
                                </button>
                                <button type="button" class="btn" style="background:#f0f0f0;color:#333;padding:10px 20px;border-radius:4px;border:none;cursor:pointer;font-size:14px;" onclick="toggleAddAllotment()">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Inventory Allotments Listing --}}
                @if($inventoryAllotments->count() > 0)
                <div style="background:#f9f9f9;border-radius:16px;padding:20px;margin-bottom:20px;">
                    <h5 style="margin-top:0;margin-bottom:16px;font-weight:600;"> Allotments</h5>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:13px;">
                            <thead>
                                <tr style="background:#19b5b5;color:#fff;text-align:left;">
                                    <th style="padding:12px;border:1px solid #ddd;">Room/Unit</th>
                                    <th style="padding:12px;border:1px solid #ddd;">Date</th>
                                    <th style="padding:12px;border:1px solid #ddd;text-align:center;">Sellable</th>
                                    <th style="padding:12px;border:1px solid #ddd;text-align:center;">Sold</th>
                                    <th style="padding:12px;border:1px solid #ddd;text-align:center;">Available</th>
                                    <th style="padding:12px;border:1px solid #ddd;text-align:center;">Settings</th>
                                    <th style="padding:12px;border:1px solid #ddd;width:100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventoryAllotments as $inv)
                                <tr style="background:#fff;border-bottom:1px solid #eee;">
                                    <td style="padding:12px;border:1px solid #ddd;">
                                        <strong>
                                            @if($inv->room)
                                                {{ $inv->room->room_name }}
                                            @else
                                                Property-wide
                                            @endif
                                        </strong>
                                    </td>
                                    <td style="padding:12px;border:1px solid #ddd;">{{ $inv->date->format('M d, Y') }}</td>
                                    <td style="padding:12px;border:1px solid #ddd;text-align:center;">{{ $inv->sellable_units }}</td>
                                    <td style="padding:12px;border:1px solid #ddd;text-align:center;">{{ $inv->sold_units }}</td>
                                    <td style="padding:12px;border:1px solid #ddd;text-align:center;">
                                        <span style="background:#e8f5f5;padding:4px 8px;border-radius:4px;color:#19b5b5;">
                                            {{ $inv->available_units }}
                                        </span>
                                    </td>
                                    <td style="padding:12px;border:1px solid #ddd;text-align:center;font-size:12px;">
                                        <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap;">
                                            @if($inv->stop_sell)
                                                <span style="background:#ff6b6b;color:#fff;padding:2px 6px;border-radius:3px;">Stop Sell</span>
                                            @endif
                                            @if($inv->block_arrivals)
                                                <span style="background:#ffc107;color:#333;padding:2px 6px;border-radius:3px;">Block</span>
                                            @endif
                                            @if($inv->instant_on_request === 'On Request')
                                                <span style="background:#007bff;color:#fff;padding:2px 6px;border-radius:3px;">On Req.</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="padding:12px;border:1px solid #ddd;">
                                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                            <button type="button" onclick="viewAllotment({{ $inv->id }})" style="padding:4px 8px;background:#17a2b8;border:none;border-radius:3px;color:#fff;cursor:pointer;font-size:11px;" title="View">View</button>
                                            <button type="button" onclick="editAllotment({{ $inv->id }})" style="padding:4px 8px;background:#007bff;border:none;border-radius:3px;color:#fff;cursor:pointer;font-size:11px;" title="Edit">Edit</button>
                                            <button type="button" onclick="deleteAllotment({{ $inv->id }})" style="padding:4px 8px;background:#ff6b6b;border:none;border-radius:3px;color:#fff;cursor:pointer;font-size:11px;" title="Delete">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    @if($inventoryAllotments->hasPages())
                    <div style="margin-top:16px;text-align:center;">
                        {{ $inventoryAllotments->links() }}
                    </div>
                    @endif
                </div>
                @else
                <div style="background:#fff3cd;padding:16px;border-radius:8px;color:#856404;margin-bottom:20px;">
                    No allotments added yet. Create one to manage room availability and booking policies.
                </div>
                @endif

                {{-- Navigation --}}
                <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:16px;">
                    <a href="{{ route('operator.accommodation.step9.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">← Back to Step 9</a>
                    <a href="{{ route('operator.accommodation.booking-report', $accommodation->id) }}" class="btn" style="background:#19b5b5;color:#fff;padding:8px 12px;border-radius:4px;">Booking Report</a>
                    <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" class="btn" style="background:#f0f0f0;color:#333;padding:8px 12px;border-radius:4px;">Back to Property</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Calendar management for blackout dates
        class BlackoutCalendar {
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
                            <button type="button" onclick="calendar.previousMonth()" style="padding:4px 8px;background:#f0f0f0;border:none;cursor:pointer;border-radius:3px;">← Prev</button>
                            <strong>${new Date(year, month).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</strong>
                            <button type="button" onclick="calendar.nextMonth()" style="padding:4px 8px;background:#f0f0f0;border:none;cursor:pointer;border-radius:3px;">Next →</button>
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
                        <button type="button" onclick="calendar.toggleDate('${dateStr}')" 
                            style="padding:4px;border:1px solid ${isSelected ? '#19b5b5' : '#ddd'};
                                background:${isSelected ? '#19b5b5' : (isToday ? '#f0f0f0' : '#fff')};
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

                // Group consecutive dates into ranges
                const ranges = this.groupConsecutiveDates(this.selectedDates);
                let html = ranges.map(range => {
                    if (range.start === range.end) {
                        return `<div style="padding:4px;background:#e8f5f5;margin:4px 0;border-radius:3px;display:inline-block;margin-right:8px;">
                            ${this.formatDateDisplay(range.start)}
                            <button type="button" onclick="calendar.removeDate('${range.start}');calendar.render();calendar.updateInput();" 
                                style="background:none;border:none;color:#d32f2f;cursor:pointer;margin-left:4px;font-weight:bold;">✕</button>
                        </div>`;
                    } else {
                        return `<div style="padding:4px;background:#e8f5f5;margin:4px 0;border-radius:3px;display:inline-block;margin-right:8px;">
                            ${this.formatDateDisplay(range.start)} → ${this.formatDateDisplay(range.end)}
                            <button type="button" onclick="calendar.removeRange('${range.start}', '${range.end}');calendar.render();calendar.updateInput();" 
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

        const calendar = new BlackoutCalendar();

        function toggleAddAllotment() {
            const section = document.getElementById('addAllotmentSection');
            const isHidden = section.style.display === 'none' || section.style.display === '';
            
            if (isHidden) {
                section.style.display = 'block';
                document.getElementById('toggleAddAllotment').textContent = '- Add New Allotment';
            } else {
                section.style.display = 'none';
                document.getElementById('toggleAddAllotment').textContent = '+ Add New Allotment';
                // Reset form when closing
                document.getElementById('inventoryAllotmentForm').reset();
                document.querySelector('select[name="instant_on_request"]').value = 'Instant';
                calendar.selectedDates = [];
                calendar.render();
            }
        }

        // Auto-populate sellable units from room allotment when room is selected
        document.getElementById('room_id_select').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const allotment = selectedOption.getAttribute('data-allotment');
            const capacity = selectedOption.getAttribute('data-capacity');
            
            if (allotment) {
                document.getElementById('sellable_units_input').value = allotment;
                // Recalculate available units
                calculateAvailable();
            }
        });

        // Auto-calculate available units
        document.querySelector('input[name="sellable_units"]').addEventListener('change', calculateAvailable);
        document.querySelector('input[name="sold_units"]').addEventListener('change', calculateAvailable);

        function calculateAvailable() {
            const sellable = parseInt(document.querySelector('input[name="sellable_units"]').value) || 0;
            const sold = parseInt(document.querySelector('input[name="sold_units"]').value) || 0;
            document.getElementById('available_units').value = Math.max(0, sellable - sold);
        }

        // Toggle blackout calendar section
        document.getElementById('stop_sell').addEventListener('change', function() {
            const section = document.getElementById('blackoutCalendarSection');
            section.style.display = this.checked ? 'block' : 'none';
            if (this.checked) {
                calendar.render();
            }
        });

        // Show/hide block days field
        document.getElementById('block_arrivals').addEventListener('change', function() {
            document.getElementById('blockDaysField').style.display = this.checked ? 'block' : 'none';
        });

        // Add date range button
        document.getElementById('addRangeBtn').addEventListener('click', function() {
            const startDate = document.getElementById('blackout_start_date').value;
            const endDate = document.getElementById('blackout_end_date').value;

            if (!startDate) {
                alert('Please select a start date');
                return;
            }

            if (endDate && endDate < startDate) {
                alert('End date must be after start date');
                return;
            }

            if (endDate) {
                calendar.addRange(startDate, endDate);
            } else {
                calendar.addDate(startDate);
            }

            document.getElementById('blackout_start_date').value = '';
            document.getElementById('blackout_end_date').value = '';
            calendar.render();
            calendar.updateInput();
        });

        // Toggle Add Allotment form
        document.getElementById('toggleAddAllotment').addEventListener('click', function(e) {
            e.preventDefault();
            toggleAddAllotment();
        });

        // View allotment details
        function viewAllotment(inventoryId) {
            window.location.href = '{{ route("operator.accommodation.step10.show_detail", [$accommodation->id, "INVENTORY_ID"]) }}'.replace('INVENTORY_ID', inventoryId);
        }

        // Edit allotment
        function editAllotment(inventoryId) {
            // Fetch the allotment data and populate the form
            fetch('{{ route("operator.accommodation.step10.get", [$accommodation->id, "INVENTORY_ID"]) }}'.replace('INVENTORY_ID', inventoryId), {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate form fields with the fetched data
                    document.querySelector('select[name="room_id"]').value = data.data.room_id || '';
                    document.querySelector('input[name="sellable_units"]').value = data.data.sellable_units;
                    document.querySelector('input[name="sold_units"]').value = data.data.sold_units;
                    document.querySelector('input[name="minimum_nights"]').value = data.data.minimum_nights || '';
                    document.querySelector('input[name="days_before_release"]').value = data.data.days_before_release || '';
                    document.querySelector('input[name="block_days"]').value = data.data.block_days || '';
                    document.querySelector('select[name="instant_on_request"]').value = data.data.instant_on_request;
                    document.querySelector('input[name="sell_and_report"]').checked = data.data.sell_and_report;
                    document.querySelector('input[name="stop_sell"]').checked = data.data.stop_sell;
                    document.querySelector('input[name="block_arrivals"]').checked = data.data.block_arrivals;
                    
                    // Show blackout calendar section if stop_sell is checked
                    if (data.data.stop_sell) {
                        document.getElementById('blackoutCalendarSection').style.display = 'block';
                        
                        // Populate blackout dates if they exist
                        if (Array.isArray(data.data.blackout_dates) && data.data.blackout_dates.length > 0) {
                            calendar.selectedDates = data.data.blackout_dates.sort();
                            calendar.updateInput();
                        } else {
                            calendar.selectedDates = [];
                        }
                        calendar.render();
                    } else {
                        document.getElementById('blackoutCalendarSection').style.display = 'none';
                        calendar.selectedDates = [];
                    }
                    
                    // Show block days field if block_arrivals is checked
                    if (data.data.block_arrivals) {
                        document.getElementById('blockDaysField').style.display = 'block';
                    } else {
                        document.getElementById('blockDaysField').style.display = 'none';
                    }
                    
                    // Calculate available units
                    calculateAvailable();
                    
                    // Scroll to form and open it
                    document.getElementById('addAllotmentSection').style.display = 'block';
                    document.getElementById('toggleAddAllotment').textContent = '✎ Edit Allotment';
                    document.querySelector('#inventoryAllotmentForm button[type="submit"]').textContent = 'Update Allotment';
                    document.getElementById('inventoryAllotmentForm').scrollIntoView({ behavior: 'smooth' });
                } else {
                    alert('Error: ' + (data.message || 'Failed to load allotment'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred: ' + error.message);
            });
        }

        // Delete allotment
        function deleteAllotment(inventoryId) {
            if (!confirm('Delete this allotment?')) return;

            fetch('{{ route("operator.accommodation.step10.delete", [$accommodation->id, "INVENTORY_ID"]) }}'.replace('INVENTORY_ID', inventoryId), {
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

        // Calculate available on form load
        calculateAvailable();

        // Keep form open if there are validation errors
        @if($errors->any())
            document.getElementById('addAllotmentSection').style.display = 'block';
        @endif
    </script>
    
    <!-- Back Button -->
    <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0;">
        <a href="{{ route('operator.accommodation.show', $accommodation->id) }}" style="color: #2196f3; text-decoration: none; font-size: 13px; font-weight: 500;">
            ← Back to Accommodation Overview
        </a>
    </div>
@endsection
