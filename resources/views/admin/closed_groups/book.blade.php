@extends('layouts.admin')

@push('styles')
<style>
/* Minimal styles adapted from provided HTML for the wizard */
.container-wizard { padding: 20px; }
.plan-card { border:1px solid #d1d5db; padding:18px; border-radius:8px; cursor:pointer; }
.plan-card.selected { border-color:#0d9488; box-shadow:0 1px 4px rgba(13,148,136,0.08); }
.badge-pending{background:#fffbeb;color:#b45309;padding:6px 8px;border-radius:4px;font-weight:700}

.wizard-shell {
    max-width: 980px;
    margin: 0 auto;
    padding: 18px 0 60px;
}

.review-shell {
    max-width: 760px;
    margin: 0 auto;
    font-family: "Segoe UI", Arial, sans-serif;
    color: #1f2937;
}

.review-shell h2,
.review-shell h3,
.review-shell h4,
.review-shell h5 {
    margin: 0;
    color: #0f172a;
}

.review-shell .portal-header {
    text-align: center;
    margin-bottom: 18px;
}

.review-shell .portal-header h2 {
    font-size: 2.6rem;
    font-weight: 800;
    color: #0d9488;
    letter-spacing: -0.04em;
    margin-bottom: 8px;
}

.review-shell .portal-header p {
    margin: 0;
    color: #4b5563;
    font-size: 1.05rem;
}

.review-shell .wizard-steps {
    display: flex;
    justify-content: center;
    gap: 12px;
    background: #f6f8f8;
    border: 1px solid #dbe7e6;
    border-radius: 12px;
    padding: 12px 18px;
    margin: 0 auto 18px;
    max-width: 630px;
    flex-wrap: wrap;
}

.review-shell .step-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 0.9rem;
    font-weight: 700;
    color: #0f172a;
    background: #e8f5f3;
    border: 1px solid #cfeae6;
}

.review-shell .step-pill.active {
    background: #0d9488;
    color: white;
    border-color: #0d9488;
}

.review-shell .step-pill.completed {
    background: #0d9488;
    color: #fff;
    border-color: #0d9488;
}

.review-success {
    background: #e4f5eb;
    border: 1px solid #bfe2ce;
    color: #0f172a;
    border-radius: 12px;
    padding: 26px 24px;
    text-align: center;
    margin-bottom: 18px;
}

.review-success .success-icon {
    font-size: 2.4rem;
    display: block;
    margin-bottom: 8px;
}

.review-success h3 {
    font-size: 2.15rem;
    font-weight: 800;
    color: #0f766e;
    letter-spacing: -0.04em;
    margin-bottom: 8px;
}

.review-success p {
    margin: 0 auto;
    max-width: 620px;
    font-size: 1rem;
    line-height: 1.45;
    color: #1f2937;
}

.review-card {
    background: #ffffff;
    border: 1px solid #dfe7e5;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
}

.review-summary {
    padding: 18px 22px 14px;
}

.review-summary-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.review-summary-header h3 {
    font-size: 1.1rem;
    font-weight: 800;
    color: #0d9488;
    margin: 0;
}

.review-summary-header .trip-meta {
    font-size: 0.85rem;
    color: #6b7280;
    font-style: italic;
    margin-top: 4px;
}

.review-status {
    background: #f5f1df;
    color: #8a6b14;
    display: inline-flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 0.77rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    border: 1px solid #eed9a3;
    white-space: nowrap;
}

.review-meta {
    padding-top: 16px;
    display: grid;
    gap: 12px;
}

.review-meta-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    color: #374151;
    font-size: 1.04rem;
    line-height: 1.4;
}

.review-meta-row strong {
    color: #0f172a;
}

.review-meta-row .muted {
    color: #6b7280;
}

.review-meta-row .link {
    color: #0d9488;
}

.review-roster {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 16px;
    margin-top: 12px;
}

.review-roster h4 {
    font-size: 1.03rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
}

.review-roster ul {
    margin: 0;
    padding-left: 20px;
    color: #1f2937;
    line-height: 1.8;
    font-size: 0.96rem;
}

.invoice-box {
    background: #ffffff;
    border: 1px solid #dfe7e5;
    border-radius: 12px;
    overflow: hidden;
    margin-top: 18px;
}

.invoice-box h4 {
    font-size: 1.05rem;
    color: #0f172a;
    padding: 18px 20px 12px;
    margin: 0;
    font-weight: 800;
}

.invoice-table {
    width: 100%;
    border-collapse: collapse;
}

.invoice-table th,
.invoice-table td {
    padding: 12px 16px;
    border-top: 1px solid #e5e7eb;
    text-align: left;
    font-size: 0.96rem;
    vertical-align: middle;
}

.invoice-table th {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    text-transform: none;
}

.invoice-table td {
    color: #1f2937;
}

.invoice-table .amount {
    text-align: right;
    font-weight: 700;
    white-space: nowrap;
}

.invoice-total {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 18px;
    padding: 14px 16px 18px;
    border-top: 1px solid #e5e7eb;
    font-weight: 800;
    color: #0f172a;
}

.invoice-total .label {
    font-size: 1.02rem;
    color: #374151;
}

.invoice-total .value {
    font-size: 1.05rem;
    color: #0d9488;
}

.payment-box {
    background: #f9f3db;
    border: 1px solid #e7d69a;
    border-radius: 12px;
    padding: 18px 20px 16px;
    margin-top: 18px;
}

.payment-box .box-header {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 800;
    color: #8a5d0a;
    margin-bottom: 14px;
}

.payment-box .box-header .icon {
    font-size: 1.15rem;
}

.payment-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px 18px;
    color: #374151;
    font-size: 0.95rem;
}

.payment-grid .item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.payment-grid .item .label {
    color: #6b7280;
    font-weight: 700;
}

.payment-grid .item .value {
    color: #1f2937;
    font-weight: 600;
}

.review-actions {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-top: 20px;
}

.review-actions .btn {
    min-width: 170px;
}

.review-actions .btn-outline {
    background: white;
    border: 1px solid #d1d5db;
    color: #374151;
}

.review-actions .btn-primary {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
    font-weight: 700;
}

@media (max-width: 768px) {
    .wizard-shell { padding: 12px 0 30px; }
    .review-shell .portal-header h2 { font-size: 2rem; }
    .review-summary-header { flex-direction: column; }
    .review-actions { flex-direction: column; }
    .review-actions .btn { width: 100%; }
    .payment-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="container-wizard">
    <h2>Closed Group Booking (Admin)</h2>
    <p>Select a closed group plan and proceed to register guests and create a booking.</p>

    <div id="step1">
        <h4>Step 1: Choose Your Approved Group Plan</h4>
        <div class="row" id="plans-grid">
            @foreach($groups as $grp)
            <div class="col-md-4 mb-3">
                <div class="plan-card" data-group='@json($grp)'>
                    <div style="font-weight:700">{{ $grp->name }}</div>
                    <div style="color:#6b7280; font-size:13px">{{ $grp->closed_group_client ?? '' }}</div>
                    <div style="margin-top:8px; font-size:13px">Dates: {{ optional($grp->available_from)->format('m/d/Y') ?? 'N/A' }} to {{ optional($grp->available_to)->format('m/d/Y') ?? 'N/A' }}</div>
                    <div style="margin-top:8px;"><span class="badge badge-pending">CLOSED GROUP</span></div>
                </div>
            </div>
            @endforeach
        </div>
        <div style="margin-top:16px">
            <button class="btn btn-outline" onclick="history.back()">Back</button>
            <button class="btn btn-primary" id="to-step2" disabled>Proceed to Details</button>
        </div>
    </div>

    <div id="step2" style="display:none; margin-top:18px">
        <h4>Step 2: Set Travellers Count and Responsible Traveller</h4>
        <p class="sub">Specify how many individuals are in this group booking. Fill out the details of the Lead Account Holder who is responsible for the overall booking management.</p>

        <div class="alert" style="background:#ecfdf5;border:1px solid #bbf0d6;padding:12px;border-radius:6px;color:#065f46">
            <strong>💡 Responsible Traveller can be a tour leader/guide.</strong>
        </div>

        <form id="admin-booking-form">
            <input type="hidden" name="group_id" id="group_id">
            <div class="row mt-3">
                <div class="col-md-4">
                    <label>Total Number of Travellers <span class="req">*</span></label>
                    <select class="form-control" id="pax" name="pax">
                        @for($i = 2; $i <= 20; $i++)
                            <option value="{{ $i }}">{{ $i }} Travellers{{ $i === 2 ? '' : '' }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-8">
                    <label>Responsible Traveller Name <span class="req">*</span></label>
                    <input class="form-control" name="lead_first_name" id="lead_first_name" placeholder="Full name" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label>Email Address <span class="req">*</span></label>
                    <input class="form-control" name="lead_email" id="lead_email" type="email" placeholder="amit29592@gmail.com">
                </div>
                <div class="col-md-6">
                    <label>Mobile Number <span class="req">*</span></label>
                    <input class="form-control" name="lead_phone" id="lead_phone" placeholder="+91 9876543210">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label>Passport Number <span class="req">*</span></label>
                    <input class="form-control" name="passport_number" id="passport_number" placeholder="PP980123">
                </div>
                <div class="col-md-6"></div>
            </div>

            <div class="mt-3 p-3" style="background:#f8fafb;border:1px solid #e6eef0;border-radius:6px">
                <label style="font-weight:700">Tour Leader / Guide Options:</label>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="tl_free">
                    <label class="form-check-label" for="tl_free">Tour leader/Guide is free</label>
                </div>
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" id="tl_local">
                    <label class="form-check-label" for="tl_local">Tour leader/Guide is local</label>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <button class="btn btn-outline" onclick="history.back()" type="button">Back</button>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn" id="to-step3" type="button" style="background:#10b981;color:#fff;border-radius:6px;padding:10px 18px;border:0">Next: Add Guests Profile</button>
                </div>
            </div>
        </form>
    </div>

    <div id="step3" style="display:none; margin-top:18px">
        <h4>Step 3: Provide Guest Traveller Profiles</h4>
        <p class="sub">Manage and enter details for all other guests included in this group booking. These individuals will be added to the single invoice.</p>

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <div style="font-weight:700">Guest Profiles</div>
            <div><button class="btn btn-primary" id="add-guest">+ Add Another Guest</button></div>
        </div>

        <div id="guests-area">
            <!-- guest forms appended here (accordion-style) -->
        </div>

        <div id="advanced-assignment" class="mt-4" style="border:2px solid #c7efe6;border-radius:8px;background:#f0fff8;padding:14px">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <div style="font-weight:700">Advanced Settings: Accommodation Guest Assignment</div>
                <div style="text-align:right"><small style="color:#6b7280">0 / 0 Assigned</small> <button class="btn btn-sm btn-outline" id="toggle-assignment">▾</button></div>
            </div>
            <div id="assignment-panel" style="margin-top:12px">
                <div style="border:1px solid #e6eef0;padding:12px;border-radius:6px;background:#fff">
                    <div id="unassigned-list">Unassigned Guests: <span id="unassigned-count">0</span> remaining</div>
                    <div class="mt-2">
                        <button class="btn btn-sm btn-success" id="auto-assign">⚡ Auto-Assign Guests</button>
                        <button class="btn btn-sm btn-outline-danger" id="reset-assign">Reset All</button>
                    </div>
                </div>

                <div id="properties-list" class="mt-3">
                    <!-- property cards rendered here -->
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6"><button class="btn btn-outline" id="back-from-step3">Back</button></div>
            <div class="col-md-6 text-end"><button class="btn btn-primary" id="to-step4">Proceed to Final Review</button></div>
        </div>
    </div>

    <div id="step4" style="display:none; margin-top:18px">
        <div class="wizard-shell">
            <div class="review-shell">
                <div class="portal-header">
                    <h2>Closed Group Booking Portal</h2>
                    <p>Register your group's details and finalize booking checkout without immediate payment</p>
                </div>

                <div class="wizard-steps" aria-label="Booking steps">
                    <span class="step-pill completed">1 Select Plan</span>
                    <span class="step-pill completed">2 Responsible Traveller</span>
                    <span class="step-pill completed">3 Add Guests</span>
                    <span class="step-pill active">4 Invoice & Confirm</span>
                </div>

                <div id="review-block"></div>

                <div class="review-actions">
                    <button class="btn btn-outline" id="back-to-step3">Back</button>
                    <button class="btn btn-primary" id="submit-booking">Confirm & Create Booking</button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
const groups = @json($groups->toArray());
let selectedGroup = null;

$(function(){
    // setup CSRF for AJAX
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
    $('.plan-card').on('click', function(){
        $('.plan-card').removeClass('selected');
        $(this).addClass('selected');
        selectedGroup = $(this).data('group');
        $('#group_id').val(selectedGroup.id);
        $('#to-step2').prop('disabled', false);
        // fetch itinerary rooms for selected group
        fetchRoomsForGroup(selectedGroup.id);
    });

    $('#to-step2').on('click', function(){
        $('#step1').hide(); $('#step2').show(); window.scrollTo(0,0);
    });

    $('#to-step3').on('click', function(){
        if(!$('#lead_first_name').val()) { alert('Lead first name required'); return; }
        // get pax number from select value (supports strings like '15 Travellers')
        let paxVal = $('#pax').val();
        let paxCount = 0;
        if(typeof paxVal === 'string'){
            const m = paxVal.match(/(\d+)/);
            paxCount = m ? parseInt(m[1],10) : 0;
        } else if(typeof paxVal === 'number') paxCount = paxVal;

        if(paxCount <= 0) { alert('Invalid pax count'); return; }

        // build guest forms — responsibility traveller is counted in pax and is NOT included
        // in the guest profiles list. So show pax-1 guest forms here.
        $('#guests-area').empty();
        const guestCount = Math.max(0, paxCount - 1);
        for(let i=1;i<=guestCount;i++) addGuestWithDefaults(i);

        $('#step2').hide(); $('#step3').show();
        populateGuestAssignOptions();
        updateUnassignedCount();
    });

    $('#add-guest').on('click', function(e){
        e.preventDefault();
        // prevent adding more guests than pax-1 (responsible traveller excluded)
        let paxVal = $('#pax').val();
        let paxCount = 0;
        if(typeof paxVal === 'string'){ const m = paxVal.match(/(\d+)/); paxCount = m ? parseInt(m[1],10) : 0; }
        else if(typeof paxVal === 'number') paxCount = paxVal;
        const maxGuests = Math.max(0, paxCount - 1);
        const current = $('#guests-area .guest-item').length;
        if(current >= maxGuests){ alert('Guest count reached the total travellers minus responsible traveller.'); return; }
        addGuest(); updateUnassignedCount();
    });

// refresh assign options when guest name inputs change
$(document).on('input', '.guest-first, .guest-last', function(){ populateGuestAssignOptions(); });

    $('#back-from-step3').on('click', function(){ $('#step3').hide(); $('#step2').show(); });

    $('#auto-assign').on('click', function(){ autoAssignGuests(); updateUnassignedCount(); });
    $('#reset-assign').on('click', function(){ resetAssignments(); updateUnassignedCount(); });
    $('#toggle-assignment').on('click', function(){ $('#assignment-panel').toggle(); });

    $('#to-step4').on('click', function(){
        buildReview(); $('#step3').hide(); $('#step4').show();
    });

    $('#back-to-step3').on('click', function(){ $('#step4').hide(); $('#step3').show(); });

    $('#submit-booking').on('click', function(){ submitBooking(); });
});

function addGuest(){ addGuestWithDefaults($('#guests-area .guest-item').length + 1); }

function addGuestWithDefaults(number, first='', last='', dob=''){
    const idx = $('#guests-area .guest-item').length + 1;
    const html = `
    <div class="card guest-item mb-2 p-3">
        <div class="row">
            <div class="col-md-4"><label>First Name</label><input class="form-control guest-first" name="guests[${idx}][first_name]" value="${first}"></div>
            <div class="col-md-4"><label>Last Name</label><input class="form-control guest-last" name="guests[${idx}][last_name]" value="${last}"></div>
            <div class="col-md-4"><label>DOB</label><input type="date" class="form-control guest-dob" name="guests[${idx}][dob]" value="${dob}"></div>
        </div>
    </div>`;
    $('#guests-area').append(html);
    populateGuestAssignOptions();
}

function fetchRoomsForGroup(groupId){
    $.get("/admin/closed-groups/"+groupId+"/rooms")
    .done(function(resp){
        // render itinerary daywise into step3 area when opened
        // normalize itinerary: API may return array, object or JSON string
        let itRaw = resp.itinerary;
        let itArr = [];
        try{
            if(typeof itRaw === 'string'){
                const parsed = JSON.parse(itRaw);
                if(Array.isArray(parsed)) itArr = parsed;
                else if(parsed && typeof parsed === 'object') itArr = Object.values(parsed);
            } else if(Array.isArray(itRaw)){
                itArr = itRaw;
            } else if(itRaw && typeof itRaw === 'object'){
                itArr = Object.values(itRaw);
            }
        }catch(e){
            console.error('Failed parsing itinerary', e, itRaw);
            itArr = [];
        }
        window._groupItinerary = itArr;

        // normalize keys to strings (API may return numeric keys)
        window._groupRooms = {};
        const rawRooms = resp.rooms || {};
        Object.keys(rawRooms).forEach(k=> window._groupRooms[String(k)] = rawRooms[k]);
        window._groupAccommodations = {};
        const rawAcs = resp.accommodations || {};
        Object.keys(rawAcs).forEach(k=> window._groupAccommodations[String(k)] = rawAcs[k]);
        console.debug('rooms() response', {itinerary: window._groupItinerary, rooms: window._groupRooms, accommodations: window._groupAccommodations});
        renderItineraryRooms();
    }).fail(function(){
        window._groupItinerary = [];
        window._groupRooms = {};
    });
}

function renderItineraryRooms(){
    const it = window._groupItinerary || [];
    // filter out itinerary days that don't have an accommodation set
    const filteredIt = (it || []).filter(function(d){
        if(!d) return false;
        const hasName = d.accommodation_name !== null && d.accommodation_name !== undefined && String(d.accommodation_name).trim() !== '';
        const hasId = d.accommodation !== null && d.accommodation !== undefined && String(d.accommodation).trim() !== '';
        return hasName || hasId;
    });
    // determine how many days to show: prefer group's declared `no_of_days` when available, but never exceed filtered days
    let daysToShow = filteredIt.length;
    try{
        if(window.selectedGroup && window.selectedGroup.no_of_days){
            const nd = Number(window.selectedGroup.no_of_days);
            if(!isNaN(nd) && nd > 0) daysToShow = Math.min(nd, filteredIt.length);
        }
    }catch(e){ /* ignore */ }
    let html = '';
    if(!filteredIt || filteredIt.length===0){
        $('#properties-list').html('<div class="text-muted">No properties found in the selected group itinerary.</div>');
        return;
    }
    // build property cards list for assignment panel
    for(let idx=0; idx<daysToShow; idx++){
        const day = filteredIt[idx] || {};
        // support several itinerary shapes: day.accommodation (id), day.accommodation_id, or day.accommodation.id
        const accomId = day.accommodation ?? day.accommodation_id ?? (day.accommodation && day.accommodation.id) ?? null;
        const accomMeta = (window._groupAccommodations && window._groupAccommodations[accomId]) || {};
        html += `<div class="property-card card mb-3 p-3" data-day-index="${idx}" data-accommodation-id="${accomId}">`;
        html += `<div style="display:flex;justify-content:space-between;align-items:center">`;
        html += `<div><strong>${accomMeta.name || ('Accommodation ' + accomId)}</strong><div style="font-size:12px;color:#6b7280">${accomMeta.place || ''} • Day ${idx+1}</div></div>`;
        html += `<div><button class="btn btn-sm btn-outline add-room-instance" data-day-index="${idx}">+ Add Room</button></div>`;
        html += `</div>`;

        html += `<div class="day-rooms mt-2" data-day-index="${idx}" data-accommodation-id="${accomId}">`;
        const acData = window._groupAccommodations && window._groupAccommodations[accomId] ? window._groupAccommodations[accomId] : null;
        const rooms = (acData && Array.isArray(acData.rooms)) ? acData.rooms : (window._groupRooms[accomId] || []);
        if(rooms.length===0){
            html += `<div class="text-muted">No room types configured for this accommodation.</div>`;
        } else {
            html += renderRoomInstanceHtml(idx, accomId, rooms, 0);
        }
        html += `</div>`;

        html += `</div>`;
    }

    $('#properties-list').html(html);
    // keep a separate itinerary rooms block for payload collection as well
    $('#itinerary-rooms').remove();
    $('#guests-area').after(`<div id="itinerary-rooms" class="mt-3" style="display:none"></div>`);
    // populate guest options
    populateGuestAssignOptions();
}

function renderRoomInstanceHtml(dayIndex, accommodationId, rooms, instanceIndex){
    let ins = `<div class="room-card card p-3 mb-2" data-day-index="${dayIndex}">`;
    ins += `<div style="display:flex;justify-content:space-between;align-items:center">`;
    ins += `<div><strong>Room</strong> <small class="text-muted">(#${instanceIndex+1})</small></div>`;
    ins += `<div><button type="button" class="btn btn-sm btn-outline-danger remove-room-instance">Remove</button></div>`;
    ins += `</div>`;
    ins += `<div class="row mt-2">`;
    ins += `<div class="col-md-4"><label>Room Type</label><select class="form-control room-select">`;
    rooms.forEach(function(r){
        const occupancy = r.capacity ?? r.occupancy ?? 2;
        const label = (r.room_name ? r.room_name : (r.room_type ? (r.room_type + ' Room') : 'Room')) + ' ('+ occupancy + ' pax)';
        ins += `<option value="${r.id}" data-accommodation-id="${r.accommodation_id || ''}" data-occupancy="${occupancy}">${label}</option>`;
    });
    ins += `</select></div>`;
    // derive bed configuration options from room_type
    const bedOptions = ['Standard','Double Bed','Single Bed','Twin Beds','Extra Bed'];
    ins += `<div class="col-md-4"><label>Bed Configuration</label><select class="form-control bed-config">`;
    bedOptions.forEach(function(b){ ins += `<option>${b}</option>`; });
    ins += `</select></div>`;
    // Occupant slots: create placeholder container - actual selects generated by populateGuestAssignOptions() based on occupancy
    const occ = rooms[0]?.occupancy ?? 2;
    ins += `<div class="col-md-4"><label>Occupants</label><div class="occupant-slots" data-occupancy="${occ}">`;
    for(let s=0;s<occ;s++){
        ins += `<div class="mb-1"><select class="form-control guest-assign" data-slot="${s}"><option value="">-- Select Occupant --</option></select></div>`;
    }
    ins += `</div></div>`;
    ins += `</div>`;
    ins += `<div class="mt-2"><small class="text-muted room-occupancy-info">0 / ${occ} assigned</small></div>`;
    ins += `</div>`;
    return ins;
}

// delegate handlers for add/remove room instances and keep guest options updated
$(document).on('click', '.add-room-instance', function(){
    const dayIndex = $(this).data('day-index');
    // find the day-rooms container for this dayIndex (button is outside the container)
    const container = $(`.day-rooms[data-day-index="${dayIndex}"]`).first();
    if(!container || container.length === 0){ console.warn('day-rooms container not found for day', dayIndex); return; }
    const accomId = String(container.data('accommodation-id'));
    const rooms = window._groupRooms[accomId] || [];
    const instanceIndex = container.find('.room-card').length;
    const html = renderRoomInstanceHtml(dayIndex, accomId, rooms, instanceIndex);
    container.append(html);
    populateGuestAssignOptions();
});
$(document).on('click', '.remove-room-instance', function(){
    $(this).closest('.room-card').remove();
    updateUnassignedCount();
});

// when room type changes, adjust occupant slots to match occupancy
$(document).on('change', '.room-select', function(){
    const sel = $(this);
    const card = sel.closest('.room-card');
    const newOcc = Number(sel.find('option:selected').data('occupancy')||2);
    const slotsContainer = card.find('.occupant-slots');
    const prevVals = [];
    slotsContainer.find('.guest-assign').each(function(){ prevVals.push($(this).val()); });
    // rebuild slots
    let html = '';
    for(let s=0;s<newOcc;s++){
        const prev = prevVals[s] || '';
        html += `<div class="mb-1"><select class="form-control guest-assign" data-slot="${s}"><option value="">-- Select Occupant --</option></select></div>`;
    }
    slotsContainer.attr('data-occupancy', newOcc).html(html);
    populateGuestAssignOptions();
    // restore previous values where possible
    slotsContainer.find('.guest-assign').each(function(i){ if(prevVals[i]) $(this).val(prevVals[i]); });
    // update occupancy info
    const assignedCount = card.find('.guest-assign').filter(function(){ return $(this).val(); }).length;
    card.find('.room-occupancy-info').text(assignedCount + ' / ' + newOcc + ' assigned');
    updateUnassignedCount();
});

// when occupant slot changes, update counts and refresh options
$(document).on('change', '.guest-assign', function(){
    const card = $(this).closest('.room-card');
    const occ = card.find('.occupant-slots').data('occupancy') || 0;
    const assignedCount = card.find('.guest-assign').filter(function(){ return $(this).val(); }).length;
    card.find('.room-occupancy-info').text(assignedCount + ' / ' + occ + ' assigned');
    // refresh options (will disable duplicates)
    populateGuestAssignOptions();
    updateUnassignedCount();
});

function populateGuestAssignOptions(){
    const guests = [];
    // include responsible traveller (lead) as an occupant option
    const leadNameRaw = ($('#lead_first_name').val() || '').trim();
    const leadName = leadNameRaw || 'Responsible Traveller';
    guests.push({ idx: 'lead', label: leadName });
    $('#guests-area .guest-item').each(function(i,el){
        const fn = $(el).find('.guest-first').val() || '';
        const ln = $(el).find('.guest-last').val() || '';
        const label = (fn + ' ' + ln).trim() || ('Guest ' + (i+1));
        guests.push({idx: String(i), label: label});
    });
    // enforce uniqueness per day: for each .property-card (day), compute selected values and disable duplicates only inside that card
    $('#properties-list .property-card').each(function(){
        const card = $(this);
        const selects = card.find('.guest-assign');
        const selectedSetDay = new Set();
        selects.each(function(){ const v = $(this).val(); if(v) selectedSetDay.add(String(v)); });

        selects.each(function(){
            const sel = $(this);
            const prev = sel.val() || '';
            sel.empty();
            sel.append(`<option value="">-- Select Occupant --</option>`);
            guests.forEach(function(g){
                const opt = $(`<option value="${g.idx}">${g.label}</option>`);
                if(selectedSetDay.has(String(g.idx)) && String(prev) !== String(g.idx)) opt.prop('disabled', true);
                sel.append(opt);
            });
            if(prev !== undefined && prev !== null) sel.val(prev);
        });
    });
    // any guest-assign selects outside property cards (fallback) — populate normally without cross-day disabling
    $('#itinerary-rooms .guest-assign').each(function(){
        const sel = $(this);
        const prev = sel.val() || '';
        sel.empty();
        sel.append(`<option value="">-- Select Occupant --</option>`);
        guests.forEach(function(g){ sel.append(`<option value="${g.idx}">${g.label}</option>`); });
        if(prev !== undefined && prev !== null) sel.val(prev);
    });
    updateUnassignedCount();
}

function updateUnassignedCount(){
    // total travellers includes responsible traveller + guests
    const total = $('#guests-area .guest-item').length + 1;
    // gather unique selected guest indices across all occupant single-selects
    const unique = new Set();
    $('#properties-list .guest-assign, #itinerary-rooms .guest-assign').each(function(){
        const v = $(this).val(); if(v) unique.add(String(v));
    });
    $('#unassigned-count').text(Math.max(0, total - unique.size));
    $('#advanced-assignment .text-muted').text(unique.size + ' / ' + total + ' Assigned');
}

function autoAssignGuests(){
    // assign unassigned guests into occupant slots (single-selects) in order
    const unassigned = [];
    // include lead plus guests in total order: 'lead', '0', '1', ...
    const guestCount = $('#guests-area .guest-item').length;
    const totalOrder = ['lead'];
    for(let i=0;i<guestCount;i++) totalOrder.push(String(i));

    // collect already assigned
    const assigned = new Set();
    $('#properties-list .guest-assign, #itinerary-rooms .guest-assign').each(function(){ const v=$(this).val(); if(v) assigned.add(String(v)); });
    totalOrder.forEach(function(val){ if(!assigned.has(String(val))) unassigned.push(val); });

    // iterate occupant selects in DOM order and fill with unassigned travellers
    $('#properties-list .guest-assign').each(function(){
        if(unassigned.length===0) return;
        const sel = $(this);
        if(sel.val()) return; // skip already filled
        const next = unassigned.shift();
        sel.val(String(next));
    });
    // update occupancy info text
    $('#properties-list .room-card').each(function(){
        const card = $(this);
        const occ = Number(card.find('.occupant-slots').data('occupancy')||0);
        let assignedCount = 0;
        card.find('.guest-assign').each(function(){ if($(this).val()) assignedCount++; });
        card.find('.room-occupancy-info').text(assignedCount + ' / ' + occ + ' assigned');
    });
    populateGuestAssignOptions();
}

function resetAssignments(){
    $('.guest-assign').each(function(){ $(this).val(''); });
    $('#properties-list .room-card').each(function(){
        const occ = $(this).find('.occupant-slots').data('occupancy') || 0;
        $(this).find('.room-occupancy-info').text('0 / ' + occ + ' assigned');
    });
    updateUnassignedCount();
}

function buildReview(){
    const groupName = selectedGroup && selectedGroup.name ? selectedGroup.name : 'Closed Group';
    const leadName = ($('#lead_first_name').val() || '').trim() || 'Lead Traveller';
    const leadEmail = ($('#lead_email').val() || '').trim() || 'Not provided';
    const leadPhone = ($('#lead_phone').val() || '').trim() || 'Not provided';
    // parse pax value robustly (supports '3 Travellers' labels)
    let pax = 0;
    const paxValRaw = $('#pax').val();
    if (typeof paxValRaw === 'string'){
        const m = String(paxValRaw).match(/(\d+)/);
        pax = m ? parseInt(m[1],10) : 0;
    } else if (typeof paxValRaw === 'number') pax = paxValRaw || 0;
    const leaderFree = $('#tl_free').is(':checked');
    const billedTravellers = Math.max(0, pax - (leaderFree ? 1 : 0));
    // default per-pax group rate (may be overwritten by server price fetch)
    const groupRate = Number(selectedGroup && selectedGroup.price ? selectedGroup.price : 0);
    // Fetch accurate total from server to avoid mismatch with frontend pricing
    let totalAmount = Number(selectedGroup && selectedGroup.price ? selectedGroup.price : 0) * billedTravellers;
    if (selectedGroup && selectedGroup.id) {
        // collect minimal payload (selected rooms + guest assignments) to send so server pricing matches admin selections
        const pricePayload = { pax: pax, selected_rooms: [], guest_assignments: {} };
        // collect selected rooms similarly to submitBooking
        $('#properties-list .room-card').each(function(){
            const card = $(this);
            const dayIndex = card.closest('.property-card').data('day-index');
            const accomId = card.closest('.property-card').data('accommodation-id') || null;
            const roomId = card.find('.room-select').val();
            let checkIn = null, checkOut = null;
            if(selectedGroup && selectedGroup.available_from){
                const base = new Date(selectedGroup.available_from);
                base.setDate(base.getDate() + Number(dayIndex));
                checkIn = base.toISOString().slice(0,10);
                const out = new Date(base); out.setDate(out.getDate() + 1);
                checkOut = out.toISOString().slice(0,10);
            }
            pricePayload.selected_rooms.push({ day_index: dayIndex, accommodation_id: accomId, room_id: roomId, check_in: checkIn, check_out: checkOut });
            const assigned = [];
            card.find('.guest-assign').each(function(){ const v = $(this).val(); if(v) assigned.push(v); });
            const key = `${dayIndex}_${roomId}`;
            pricePayload.guest_assignments[key] = assigned;
        });

        $.get("{{ url('admin/closed-groups') }}/" + selectedGroup.id + "/price", pricePayload)
            .done(function(resp){
                totalAmount = Number(resp.total || totalAmount);
                window.__finalTotalAmount = totalAmount;
                // compute per-pax rate (admin treats all travellers as adults)
                const billed = billedTravellers || 1;
                const perPax = billed > 0 ? (totalAmount / billed) : 0;
                // render breakdown items into invoice table rows
                const tbody = $('.invoice-table tbody').empty();
                if(Array.isArray(resp.items) && resp.items.length){
                    resp.items.forEach(function(it){
                        const desc = it.name || it.type || (groupName + ' Item');
                        const travellers = (it.type === 'Accommodation' || it.type === 'Activity') ? (billedTravellers + ' Pax') : '';
                        const rateText = (it.type === 'Accommodation' || it.type === 'Activity') ? ('$' + ((billedTravellers>0)? ((it.amount / billedTravellers).toFixed(2)) : '0.00')) : '';
                        tbody.append(`<tr><td>${desc}</td><td>${travellers}</td><td>${rateText}</td><td class="amount">$${Number(it.amount||0).toFixed(2)}</td></tr>`);
                    });
                } else {
                    // fallback row
                    $('.invoice-table tbody').append(`<tr><td>${groupName} - Group Package</td><td>${billedTravellers} Pax</td><td>$${perPax.toFixed(2)}</td><td class="amount">$${totalAmount.toFixed(2)}</td></tr>`);
                }
                $('.invoice-total .value').text('$' + (totalAmount.toFixed(2)));
            }).fail(function(){
                window.__finalTotalAmount = totalAmount;
            });
    } else {
        window.__finalTotalAmount = totalAmount;
    }

    const travelerRows = [];
    const leadDetail = leaderFree ? 'Tour Leader - Free' : 'Traveller';
    travelerRows.push(`${leadName} (${leadDetail})`);
    window.__finalTotalAmount = totalAmount;

    $('#guests-area .guest-item').each(function(index, el) {
        const firstName = $(el).find('.guest-first').val() || 'Guest';
        const lastName = $(el).find('.guest-last').val() || '';
        const fullName = `${firstName} ${lastName}`.trim();
        if (fullName) {
            travelerRows.push(`${fullName} (Passenger)`);
        }
    });

    const rosterHtml = travelerRows.map((name, idx) => `<li>${idx + 1}. ${name}</li>`).join('');
    const rowsHtml = [
        {
            description: `${groupName} - Group Package`,
            travellers: `${billedTravellers} Pax`,
            rate: `${groupRate.toFixed(2)}`,
            amount: `${(billedTravellers * groupRate).toFixed(2)}`
        }
    ].map(item => `
        <tr>
            <td>${item.description}</td>
            <td>${item.travellers}</td>
            <td>${item.rate}</td>
            <td class="amount">$${item.amount}</td>
        </tr>
    `).join('');

    $('#review-block').html(`
        <div class="review-success">
            <span class="success-icon">🎉</span>
            <h3>Booking Registration Successful!</h3>
            <p>Your booking has been recorded. Payment must be settled via bank transfer as per the single invoice instructions below.</p>
        </div>

        <div class="review-card review-summary">
            <div class="review-summary-header">
                <div>
                    <h3>${groupName}</h3>
                    <div class="trip-meta">Requested by: ACME Industries Ltd.</div>
                </div>
                <span class="review-status">Pending Bank Transfer</span>
            </div>

            <div class="review-meta">
                <div class="review-meta-row">
                    <strong>Responsible Traveller:</strong>
                    <span>${leadName} (${leadEmail})</span>
                </div>
                <div class="review-meta-row">
                    <strong>Group Size:</strong>
                    <span>${pax} Travellers (${leaderFree ? '1 Tour Leader - Free' : '0 Tour Leader - Free'}, ${Math.max(0, billedTravellers)} Billed Guests)</span>
                </div>
            </div>

            <div class="review-roster">
                <h4>Traveller Roster:</h4>
                <ul>${rosterHtml}</ul>
            </div>
        </div>

        <div class="invoice-box">
            <h4>Single Invoice Summary</h4>
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Billed Travellers</th>
                        <th>Group Rate / Pax</th>
                        <th class="amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHtml}
                </tbody>
            </table>
            <div class="invoice-total">
                <span class="label">Total Invoice Amount:</span>
                <span class="value">$${totalAmount.toFixed(2)}</span>
            </div>
        </div>

        <div class="payment-box">
            <div class="box-header">
                <span class="icon">🏦</span>
                <span>Bank Transfer Payment Instructions (Single Invoice Only)</span>
            </div>
            <div class="payment-grid">
                <div class="item">
                    <span class="label">Bank Name:</span>
                    <span class="value">International Holidays Trust Bank</span>
                </div>
                <div class="item">
                    <span class="label">Account Name:</span>
                    <span class="value">Holidays.io MPO Escrow</span>
                </div>
                <div class="item">
                    <span class="label">Account Number:</span>
                    <span class="value">9028-1123-0982-12</span>
                </div>
                <div class="item">
                    <span class="label">IBAN / Swift Code:</span>
                    <span class="value">IBAN / SWIFT CODE: HTBGZBLXXX</span>
                </div>
                <div class="item">
                    <span class="label">Transfer Reference Code:</span>
                    <span class="value">REF-BK-872417</span>
                </div>
                <div class="item">
                    <span class="label">Amount to Transfer:</span>
                    <span class="value">$${totalAmount.toFixed(2)} USD</span>
                </div>
            </div>
        </div>
    `);
}

function submitBooking(){
    const payload = {
        group_id: $('#group_id').val(),
        lead_first_name: $('#lead_first_name').val(),
        lead_last_name: '',
        lead_email: $('#lead_email').val(),
        lead_phone: $('#lead_phone').val(),
        pax: $('#pax').val(),
        total_amount: window.__finalTotalAmount ?? null,
        _token: '{{ csrf_token() }}',
        guests: []
    };
    $('#guests-area .guest-item').each(function(i,el){
        payload.guests.push({
            first_name: $(el).find('.guest-first').val(),
            last_name: $(el).find('.guest-last').val(),
            dob: $(el).find('.guest-dob').val()
        });
    });

    // collect selected room instances
    payload.selected_rooms = [];
    payload.guest_assignments = {};
    // collect from properties list visible structure
    $('#properties-list .room-card').each(function(){
        const card = $(this);
        const dayIndex = card.closest('.property-card').data('day-index');
        const accomId = card.closest('.property-card').data('accommodation-id') || null;
        const roomId = card.find('.room-select').val();
        // compute check_in/check_out from group's available_from/available_to using dayIndex if available
        let checkIn = null, checkOut = null;
        if(selectedGroup && selectedGroup.available_from){
            const base = new Date(selectedGroup.available_from);
            base.setDate(base.getDate() + Number(dayIndex));
            checkIn = base.toISOString().slice(0,10);
            // assume one night for day index
            const out = new Date(base); out.setDate(out.getDate() + 1);
            checkOut = out.toISOString().slice(0,10);
        }
        payload.selected_rooms.push({ day_index: dayIndex, accommodation_id: accomId, room_id: roomId, check_in: checkIn, check_out: checkOut });
        // collect assigned guests (array of indices)
        const assigned = [];
        card.find('.guest-assign').each(function(){ const v = $(this).val(); if(v) assigned.push(v); });
        const key = `${dayIndex}_${roomId}`;
        payload.guest_assignments[key] = assigned;
    });

    $.ajax({
        url: "{{ route('admin.closed-groups.book.store') }}",
        method: 'POST',
        data: payload,
        dataType: 'json'
    }).done(function(resp){
        if(resp && resp.success){
            alert('Booking created successfully (ID: ' + resp.booking_id + ')');
            location.reload();
        } else {
            alert('Failed to create booking: ' + (resp?.message || 'Unknown error'));
        }
    }).fail(function(xhr){
        let msg = 'Failed to create booking';
        try{
            const j = xhr.responseJSON;
            if(j && j.errors) msg += ': ' + Object.values(j.errors).flat().join('; ');
            else if(j && j.message) msg += ': ' + j.message;
            else msg += ': ' + xhr.responseText;
        }catch(e){ msg += '.'; }
        alert(msg);
    });
}
</script>
@endpush

@endsection
