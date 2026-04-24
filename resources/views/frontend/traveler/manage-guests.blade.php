@extends('frontend.layout')

@section('title', 'Manage Guests | ' . $trip->trip_name)
@section('meta_description', 'Manage guest details for your booking.')

@push('styles')
<style>
    /* Modal Overlay */
    .guest-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 999;
    }

    .guest-modal-overlay.show {
        display: block !important;
    }

    /* Modal Wrapper */
    .guest-modal-wrapper {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        background: transparent;
    }

    .guest-modal-wrapper.show {
        display: flex !important;
    }

    /* Modal Box */
    .guest-modal-box {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        margin: auto;
    }

    .guest-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #eee;
    }

    .guest-modal-header h2 {
        margin: 0;
        font-size: 20px;
        color: #333;
    }

    .guest-modal-close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #666;
        padding: 0;
        width: 30px;
        height: 30px;
    }

    .guest-modal-close:hover {
        color: #333;
    }

    .guest-modal-body {
        padding: 20px;
        overflow-y: auto;
        flex: 1;
    }

    .guest-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px;
        border-top: 1px solid #eee;
        background: #f9f9f9;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
    }

    .btn-primary {
        background-color: #0066cc;
        color: white;
    }

    .btn-primary:hover {
        background-color: #0052a3;
    }

    .btn-secondary {
        background-color: #f0f0f0;
        color: #333;
    }

    .btn-secondary:hover {
        background-color: #e0e0e0;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #444;
        margin-bottom: 6px;
    }

    .req {
        color: #e53e3e;
    }

    .form-input {
        width: 100%;
        padding: 11px 14px;
        border: 1.5px solid #ddd;
        border-radius: 10px;
        font-size: 14px;
        font-family: inherit;
        color: #1a1a2e;
        outline: none;
        transition: border-color .2s;
        box-sizing: border-box;
    }

    .form-input:focus {
        border-color: #1a1a2e;
    }

    .btn-edit-guest:hover {
        color: #0052a3;
    }

    .btn-remove-guest:hover {
        color: #c82333;
    }

    @media (max-width: 768px) {
        .guest-modal-box {
            width: 95%;
            max-width: none;
        }
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<section class="page-section manage-guests-section">
    <div class="wrap">
        <div class="page-header">
            <div class="breadcrumbs">
                <a href="{{ route('traveler.trips') }}">Trips</a>
                <span>/</span>
                <a href="{{ route('traveler.trip.detail', $trip) }}">{{ $trip->trip_name }}</a>
                <span>/</span>
                <span>Manage Guests</span>
            </div>
            <h1>Manage Guests for {{ $booking->booking_reference }}</h1>
            <p class="page-subtitle">Update guest details for this booking.</p>
        </div>

        @php $bookedCount = ($booking->adults ?? 0) + ($booking->children ?? 0); $addedCount = $booking->guests->count(); $canDownload = $bookedCount == $addedCount; @endphp
        <div class="manage-guests-card" style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
            <form id="manageGuestsForm" method="POST" action="{{ route('traveler.trip.booking.update-guests', ['trip' => $trip->id, 'booking' => $booking->id]) }}">
                @csrf
                <p style="margin-bottom: 20px; font-weight: 600;">Booked: {{ $bookedCount }} &nbsp;|&nbsp; Added: <span id="added-count">{{ $booking->guests->count() }}</span></p>

                <div class="saved-guests-panel" style="border: 1px solid #dcdcdc; border-radius: 10px; padding: 18px; margin-bottom: 20px; background: #fafafa;">
                    <h3 style="margin-top: 0;">Use Saved Guest Details</h3>
                    @if($savedGuests->isEmpty())
                        <p style="margin: 0 0 10px; color: #555;">No saved guest profiles found. Add a new guest below.</p>
                    @else
                        <div class="saved-guest-list" style="display: grid; gap: 10px;">
                            @foreach($savedGuests as $guest)
                                <div class="saved-guest-checkbox" style="display: grid; grid-template-columns: auto 1fr auto; gap: 10px; align-items: center; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0;">
                                        <input type="checkbox" class="saved-guest-checkbox-input" data-relation="{{ $guest->relation ?? 'other' }}" data-gender="{{ $guest->gender ?? '' }}" data-first-name="{{ $guest->first_name }}" data-middle-name="{{ $guest->middle_name ?? '' }}" data-last-name="{{ $guest->last_name }}" data-dob="{{ $guest->dob }}" data-nationality="{{ $guest->nationality ?? '' }}" data-passport="{{ $guest->passport_number ?? '' }}" data-notes="{{ $guest->notes ?? '' }}">
                                        <div>
                                            <strong>{{ trim($guest->first_name . ' ' . ($guest->last_name ?? '')) }}</strong><br>
                                            <small>{{ $guest->nationality ?? 'Unknown' }} · {{ $guest->dob ? \Carbon\Carbon::parse($guest->dob)->format('d/m/Y') : 'No DOB' }}</small>
                                        </div>
                                    </label>
                                    @if($booking instanceof \App\Models\ActivityBooking && $activityTimeSlots->isNotEmpty())
                                        <select class="saved-guest-time-slot form-input" disabled style="max-width: 220px;">
                                            <option value="">Select time slot</option>
                                            @foreach($activityTimeSlots as $slot)
                                                <option value="{{ $slot->timeslot_id }}">{{ $slot->start_time }} - {{ $slot->end_time }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-saved-guests-btn" class="btn btn-primary" style="margin-top: 12px;">Add selected saved guests</button>
                    @endif
                </div>

                <div id="guests-list" style="margin-bottom: 20px;">
                    @foreach($booking->guests as $index => $guest)
                    <div class="guest-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; background: #f9f9f9;">
                        <div class="guest-item-info">
                            <span class="guest-item-name" style="font-weight: 500; color: #333; display: block;">{{ trim($guest->first_name . ' ' . ($guest->last_name ?? '')) }}</span>
                            <span class="guest-item-age" style="font-size: 12px; color: #666; display: block;">{{ $guest->nationality ?? 'Unknown' }} · {{ $guest->dob ? \Carbon\Carbon::parse($guest->dob)->format('d/m/Y') : 'No DOB' }}</span>
                            @if($booking instanceof \App\Models\ActivityBooking && isset($booking->participant_time_slots[$guest->guest_number ?? ($index + 1)]))
                                @php
                                    $timeSlotId = $booking->participant_time_slots[$guest->guest_number ?? ($index + 1)];
                                    $timeSlot = $activityTimeSlots->where('timeslot_id', $timeSlotId)->first();
                                @endphp
                                @if($timeSlot)
                                    <span class="guest-item-timeslot" style="font-size: 12px; color: #007bff; display: block;">Time Slot: {{ $timeSlot->start_time }} - {{ $timeSlot->end_time }}</span>
                                @endif
                            @endif
                        </div>
                        <div class="guest-item-actions" style="display: flex; gap: 10px; align-items: center;">
                            <!-- <button type="button" class="btn-edit-guest" data-index="{{ $index }}" style="background: none; border: none; cursor: pointer; font-size: 14px; color: #0066cc; padding: 0;">
                                <i class="fa-solid fa-pencil"></i> Edit
                            </button> -->
                            @if ($booking instanceof \App\Models\ActivityBooking && isset($guest->id) && $canDownload)
                                @php
                                    $hasTimeSlot = isset($booking->participant_time_slots[$guest->guest_number ?? ($index + 1)]) && !empty($booking->participant_time_slots[$guest->guest_number ?? ($index + 1)]);
                                @endphp
                                @if($hasTimeSlot)
                                    <a href="{{ route('traveler.trip.booking.download-voucher', ['trip' => $trip->id, 'booking' => $booking->id, 'guest' => $guest->id]) }}" target="_blank" style="font-size: 14px; color: #007bff; text-decoration: none; display: inline-flex; align-items: center;">
                                        <i class="fa-solid fa-download"></i> Download Voucher
                                    </a>
                                @else
                                    <span style="font-size: 12px; color: #dc3545;">Time slot required for voucher</span>
                                @endif
                            @endif
                            <button type="button" class="btn-remove-guest" data-index="{{ $index }}" style="background: none; border: none; cursor: pointer; font-size: 14px; color: #dc3545; padding: 0;">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div id="guest-form-inputs" style="display: none;">
                    <!-- Guest forms will be generated here by JavaScript when needed -->
                </div>

                <button type="button" id="add-guest-btn" class="btn btn-secondary" style="margin-right: 10px;">+ Add New Guest</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>

            @if ($canDownload && ($booking instanceof \App\Models\ActivityBooking || $booking instanceof \App\Models\AccommodationBooking))
                <!-- <a href="{{ route('traveler.trip.booking.download-voucher', ['trip' => $trip->id, 'booking' => $booking->id]) }}" target="_blank" class="btn btn-primary" style="margin-top: 16px;">Download Voucher</a> -->
            @endif
        </div>

        <a href="{{ route('traveler.trip.detail', $trip) }}" class="btn btn-secondary">&larr; Back to Trip</a>
    </div>
</section>

<div id="modalOverlay" class="guest-modal-overlay" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.35); z-index: 99998;"></div>
<div id="addGuestModal" class="guest-modal-wrapper" style="display:none; position: fixed; inset: 0; z-index: 99999; align-items: center; justify-content: center; transform: none;">
    <div class="guest-modal-box">
        <div class="guest-modal-header">
            <h2>Add Guest</h2>
            <button type="button" class="guest-modal-close" id="closeModalBtn">&times;</button>
        </div>
        <div class="guest-modal-body">
            <form id="addGuestForm">
                <div class="form-grid" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label for="modal_relation">Relationship <span class="req">*</span></label>
                        <select id="modal_relation" name="relation" required class="form-input">
                            <option value="">Select</option>
                            <option value="self">Self</option>
                            <option value="spouse">Spouse</option>
                            <option value="child">Child</option>
                            <option value="friend">Friend</option>
                            <option value="colleague">Colleague</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="modal_gender">Gender</label>
                        <select id="modal_gender" name="gender" class="form-input">
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="non_binary">Non-binary</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 15px; margin-top: 15px;">
                    <div class="form-group">
                        <label for="modal_first_name">First Name <span class="req">*</span></label>
                        <input type="text" id="modal_first_name" name="first_name" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="modal_middle_name">Middle Name</label>
                        <input type="text" id="modal_middle_name" name="middle_name" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="modal_last_name">Last Name <span class="req">*</span></label>
                        <input type="text" id="modal_last_name" name="last_name" required class="form-input">
                    </div>
                </div>
                <div class="form-grid" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 15px; margin-top: 15px;">
                    <div class="form-group">
                        <label for="modal_dob">Date of Birth <span class="req">*</span></label>
                        <input type="date" id="modal_dob" name="dob" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="modal_nationality">Nationality <span class="req">*</span></label>
                        <select id="modal_nationality" name="nationality" required class="form-input">
                            <option value="">Select nationality</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}">{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="modal_passport_number">Passport Number</label>
                        <input type="text" id="modal_passport_number" name="passport_number" class="form-input">
                    </div>
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label for="modal_notes">Notes</label>
                    <textarea id="modal_notes" name="notes" class="form-input" rows="3"></textarea>
                </div>
                @if($booking instanceof \App\Models\ActivityBooking && $activityTimeSlots->isNotEmpty())

                
                <div class="form-group" style="margin-top: 15px;">
                    <label for="modal_time_slot">Activity Time Slot <span class="req">*</span></label>
                    <select id="modal_time_slot" name="time_slot" class="form-input" required>
                        <option value="">Select time slot</option>
                        @foreach($activityTimeSlots as $slot)
                            <option value="{{ $slot->timeslot_id }}">{{ $slot->start_time }} - {{ $slot->end_time }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="form-group" style="margin-top: 15px;">
                    <label class="checkbox-label">
                        <input type="checkbox" id="modal_save_to_list" name="save_to_list">
                        <span>Save this guest to my saved guest list</span>
                    </label>
                </div>
            </form>
        </div>
        <div class="guest-modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelModalBtn">Cancel</button>
            <button type="button" class="btn btn-primary" id="saveGuestBtn">Add Guest</button>
        </div>
    </div>
</div>

<script>

    document.addEventListener('change', function(e) {
    if (e.target.classList.contains('saved-guest-checkbox-input')) {

        const checkbox = e.target;
        const row = checkbox.closest('.saved-guest-checkbox');
        const select = row?.querySelector('.saved-guest-time-slot');

        if (select) {
            select.disabled = !checkbox.checked;

            if (!checkbox.checked) {
                select.value = ''; // reset only when unchecked
            }
        }
    }
});
document.addEventListener('DOMContentLoaded', function() {
    const maxGuests = {{ $bookedCount }};
    const addGuestBtn = document.getElementById('add-guest-btn');
    const addSavedGuestsBtn = document.getElementById('add-saved-guests-btn');
    const addedCountEl = document.getElementById('added-count');
    const guestsList = document.getElementById('guests-list');
    const guestFormInputs = document.getElementById('guest-form-inputs');
    const modalOverlay = document.getElementById('modalOverlay');
    const addGuestModal = document.getElementById('addGuestModal');
    const addGuestForm = document.getElementById('addGuestForm');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const saveGuestBtn = document.getElementById('saveGuestBtn');
    const saveToListCheckbox = document.getElementById('modal_save_to_list');
    const bookingForm = document.getElementById('manageGuestsForm');
    const saveGuestUrl = '{{ route("frontend.booking.save-guest") }}';

    // Data structure to hold guest data
    let guestData = {};
    let editingIndex = null;

    // Initialize with existing guests
    function initializeExistingGuests() {
        const existingItems = guestsList.querySelectorAll('.guest-item');
        existingItems.forEach((item, index) => {
            const nameEl = item.querySelector('.guest-item-name');
            const ageEl = item.querySelector('.guest-item-age');
            if (nameEl && ageEl) {
                const [firstName, ...lastNameParts] = (nameEl.textContent || '').trim().split(' ');
                const lastName = lastNameParts.join(' ') || '';
                
                guestData[index] = {
                    first_name: firstName,
                    last_name: lastName,
                    middle_name: '',
                    dob: '',
                    gender: '',
                    nationality: '',
                    passport_number: '',
                    notes: '',
                    time_slot: '',
                };
            }
        });
    }

    // Load existing guest data from page (if available via data attributes)
    const existingGuestData = @json($booking->guests);
    const activityTimeSlots = @json($activityTimeSlots);
    const isActivityBooking = @json($booking instanceof \App\Models\ActivityBooking);
    const participantTimeSlots = @json($booking->participant_time_slots ?? []);

    existingGuestData.forEach((guest, index) => {
        guestData[index] = {
            relation: guest.relation || 'other',
            gender: guest.gender || '',
            first_name: guest.first_name || '',
            middle_name: guest.middle_name || '',
            last_name: guest.last_name || '',
            dob: guest.dob || '',
            nationality: guest.nationality || '',
            passport_number: guest.passport_number || '',
            notes: guest.notes || '',
            time_slot: isActivityBooking ? (participantTimeSlots[guest.guest_number] || '') : '',
        };
    });

    updateAddedCount();

    function normalizeString(value) {
        return (value || '').toString().trim().toLowerCase();
    }

    function normalizeDob(value) {
        let dob = (value || '').toString().trim();
        if (!dob) {
            return '';
        }
        if (dob.includes('/')) {
            const parts = dob.split('/').map(part => part.trim());
            if (parts.length === 3) {
                return `${parts[2].padStart(4, '0')}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
            }
        }
        if (dob.includes('T')) {
            dob = dob.split('T')[0];
        }
        if (dob.includes(' ')) {
            dob = dob.split(' ')[0];
        }
        return dob;
    }

    function getGuestSignature(guest) {
        return `${normalizeString(guest.first_name)}|${normalizeString(guest.last_name)}|${normalizeDob(guest.dob)}`;
    }

    /*function getSavedGuestTimeSlot(input) {
        const savedGuestRow = input.closest('.saved-guest-checkbox');
        const timeSlotSelect = savedGuestRow?.querySelector('select.saved-guest-time-slot');
        let selectedValue = '';

        if (timeSlotSelect) {
            selectedValue = (timeSlotSelect.value || '').toString().trim();
            input.dataset.timeSlot = selectedValue;
        } else {
            selectedValue = (input.dataset.timeSlot || '').toString().trim();
        }

        return selectedValue;
    }*/

        function getSavedGuestTimeSlot(input) {
            const row = input.closest('.saved-guest-checkbox');
            const select = row?.querySelector('.saved-guest-time-slot');

            if (!select || select.disabled) return '';

            return (select.value || '').trim();
        }

    // Function to update checkbox states for saved guests
    function updateSavedGuestsCheckboxes() {
        const checkboxes = document.querySelectorAll('.saved-guest-checkbox-input');
        const addedKeys = Object.values(guestData).map(getGuestSignature);
        const isFull = Object.keys(guestData).length >= maxGuests;

        checkboxes.forEach(checkbox => {
            const savedKey = getGuestSignature({
                first_name: checkbox.dataset.firstName,
                last_name: checkbox.dataset.lastName,
                dob: checkbox.dataset.dob,
            });
            const isAdded = addedKeys.includes(savedKey);
            checkbox.checked = isAdded;
            checkbox.disabled = !isAdded && isFull;

            const label = checkbox.closest('.saved-guest-checkbox');
            const timeSlotSelect = label?.querySelector('.saved-guest-time-slot');
            if (timeSlotSelect) {
                timeSlotSelect.disabled = !checkbox.checked;
                if (!checkbox.checked) {
                    timeSlotSelect.value = '';
                    delete checkbox.dataset.timeSlot;
                } else {
                    getSavedGuestTimeSlot(checkbox);
                }
            }

            if (label) {
                label.style.opacity = !isAdded && isFull ? '0.6' : '';
                label.style.pointerEvents = !isAdded && isFull ? 'none' : '';
            }
        });

        if (addSavedGuestsBtn) {
            addSavedGuestsBtn.disabled = isFull;
            addSavedGuestsBtn.style.opacity = isFull ? '0.6' : '';
            addSavedGuestsBtn.style.cursor = isFull ? 'not-allowed' : '';
        }
    }

    // Add saved guests
    if (addSavedGuestsBtn) {
        addSavedGuestsBtn.addEventListener('click', function() {
            const checked = Array.from(document.querySelectorAll('.saved-guest-checkbox-input:checked'));
            if (checked.length === 0) {
                alert('Please select at least one saved guest to add.');
                return;
            }

            // Filter out already-added guests
            const newGuests = checked.filter(input => {
                const isAlreadyAdded = Object.values(guestData).some(g => {
                    return getGuestSignature(g) === getGuestSignature({
                        first_name: input.dataset.firstName,
                        last_name: input.dataset.lastName,
                        dob: input.dataset.dob,
                    });
                });
                return !isAlreadyAdded;
            });

            if (newGuests.length === 0) {
                alert('All selected guests are already added.');
                return;
            }

            const currentCount = Object.keys(guestData).length;
            if (currentCount + newGuests.length > maxGuests) {
                alert('You can only add up to ' + maxGuests + ' guests for this booking.');
                return;
            }

            // Ensure time slot is selected for each chosen saved guest
            for (const input of newGuests) {
                 // console.log('input:', input);
                //const selectedValue = getSavedGuestTimeSlot(input);

                const row = input.closest('.saved-guest-checkbox');
                const select = row?.querySelector('.saved-guest-time-slot');

                if (select && select.disabled) {
                    select.disabled = false; // 🔥 force enable before reading
                }

                const selectedValue = getSavedGuestTimeSlot(input);

                if (isActivityBooking && activityTimeSlots.length > 0) {
                    const row = input.closest('.saved-guest-checkbox');
                    const select = row?.querySelector('.saved-guest-time-slot');

                    if (!select || !select.value || select.value === '') {
                        const guestName = `${input.dataset.firstName || ''} ${input.dataset.lastName || ''}`.trim();
                        alert(`Please select a time slot for ${guestName || 'the saved guest'} before adding.`);
                        return;
                    }
                }
            }

            newGuests.forEach(input => {
                const selectedValue = getSavedGuestTimeSlot(input);
                const index = Object.keys(guestData).length;
                guestData[index] = {
                    relation: input.dataset.relation || 'other',
                    gender: input.dataset.gender || '',
                    first_name: input.dataset.firstName || '',
                    middle_name: input.dataset.middleName || '',
                    last_name: input.dataset.lastName || '',
                    dob: input.dataset.dob || '',
                    nationality: input.dataset.nationality || '',
                    passport_number: input.dataset.passport || '',
                    notes: input.dataset.notes || '',
                    time_slot: selectedValue || '',
                };
                appendGuestItem(guestData[index], index);
            });

            updateSavedGuestsCheckboxes();
            updateAddedCount();
        });
    }

    function syncSavedGuestTimeSlots() {
        document.querySelectorAll('.saved-guest-checkbox-input').forEach(input => {
            getSavedGuestTimeSlot(input);
        });
    }

    // Initialize checkboxes on page load
    updateSavedGuestsCheckboxes();
    syncSavedGuestTimeSlots();

    // Enable or disable timeslot select when saved guest checkbox changes
    document.addEventListener('change', function(e) {
        if (e.target.matches('.saved-guest-checkbox-input')) {
            const checkbox = e.target;
            const timeSlotSelect = checkbox.closest('.saved-guest-checkbox')?.querySelector('.saved-guest-time-slot');
            if (timeSlotSelect) {
                timeSlotSelect.disabled = !checkbox.checked;
                if (!checkbox.checked) {
                    timeSlotSelect.value = '';
                    delete checkbox.dataset.timeSlot;
                } else {
                    //checkbox.dataset.timeSlot = getSavedGuestTimeSlot(checkbox);
                }
            }
        }

        if (e.target.matches('.saved-guest-time-slot')) {
            const timeSlotSelect = e.target;
            const checkbox = timeSlotSelect.closest('.saved-guest-checkbox')?.querySelector('.saved-guest-checkbox-input');
            if (checkbox) {
               // checkbox.dataset.timeSlot = getSavedGuestTimeSlot(checkbox);
            }
        }
    });

    // Add guest button
    addGuestBtn.addEventListener('click', function() {
        if (Object.keys(guestData).length >= maxGuests) {
            alert('You can only add up to ' + maxGuests + ' guests for this booking.');
            return;
        }
        editingIndex = null;
        openModal();
    });

    // Edit guest button
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-edit-guest')) {
            e.preventDefault();
            const btn = e.target.closest('.btn-edit-guest');
            const index = parseInt(btn.dataset.index);
            editItemGuest(index);
        }
        if (e.target.closest('.btn-remove-guest')) {
            e.preventDefault();
            const btn = e.target.closest('.btn-remove-guest');
            const index = parseInt(btn.dataset.index);
            removeItemGuest(index);
        }
    });

    closeModalBtn.addEventListener('click', function() {
        closeModal();
    });
    cancelModalBtn.addEventListener('click', function() {
        closeModal();
    });

    saveGuestBtn.addEventListener('click', function() {
        if (!addGuestForm.checkValidity()) {
            addGuestForm.reportValidity();
            return;
        }

        if (editingIndex === null && Object.keys(guestData).length >= maxGuests) {
            alert('You can only add up to ' + maxGuests + ' guests for this booking.');
            closeModal();
            return;
        }

        const guest = {
            relation: document.getElementById('modal_relation').value,
            gender: document.getElementById('modal_gender').value || null,
            first_name: document.getElementById('modal_first_name').value,
            middle_name: document.getElementById('modal_middle_name').value,
            last_name: document.getElementById('modal_last_name').value,
            dob: document.getElementById('modal_dob').value,
            nationality: document.getElementById('modal_nationality').value,
            passport_number: document.getElementById('modal_passport_number').value,
            notes: document.getElementById('modal_notes').value,
            time_slot: isActivityBooking ? document.getElementById('modal_time_slot').value : '',
        };

        const saveToList = saveToListCheckbox?.checked;

        function addGuestToBooking() {
            if (editingIndex !== null) {
                guestData[editingIndex] = guest;
                refreshGuestsList();
            } else {
                const index = Object.keys(guestData).length;
                guestData[index] = guest;
                appendGuestItem(guest, index);
                updateSavedGuestsCheckboxes();
            }
            updateAddedCount();
            closeModal();
        }

        if (saveToList) {
            fetch(saveGuestUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(guest)
            }).then(async response => {
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Failed to save guest');
                }
                addGuestToBooking();
            }).catch(error => {
                console.error('Save guest error:', error);
                alert('Guest was added to booking, but could not be saved to your list.');
                addGuestToBooking();
            });
        } else {
            addGuestToBooking();
        }
    });

    function appendGuestItem(guest, index) {
        const item = document.createElement('div');
        item.className = 'guest-item';
        item.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; background: #f9f9f9;';
        
        const guestName = `${guest.first_name} ${guest.last_name}`.trim();
        const dobFormatted = guest.dob ? new Date(guest.dob).toLocaleDateString('en-GB') : 'No DOB';
        
        let timeSlotHtml = '';
        if (isActivityBooking && guest.time_slot) {
            //const timeSlot = activityTimeSlots.find(slot => slot.timeslot_id == guest.time_slot);
           const timeSlot = activityTimeSlots.find(slot => slot.timeslot_id == guest.time_slot);
            if (timeSlot) {
                timeSlotHtml = `<span class="guest-item-timeslot" style="font-size: 12px; color: #007bff; display: block;">Time Slot: ${timeSlot.start_time} - ${timeSlot.end_time}</span>`;
            }
        }
        
        item.innerHTML = `
            <div class="guest-item-info">
                <span class="guest-item-name" style="font-weight: 500; color: #333; display: block;">${guestName}</span>
                <span class="guest-item-age" style="font-size: 12px; color: #666; display: block;">${guest.nationality || 'Unknown'} · ${dobFormatted}</span>
                ${timeSlotHtml}
            </div>
            <div class="guest-item-actions" style="display: flex; gap: 10px;">
                <button type="button" class="btn-edit-guest" data-index="${index}" style="background: none; border: none; cursor: pointer; font-size: 14px; color: #0066cc; padding: 0;">
                    <i class="fa-solid fa-pencil"></i> Edit
                </button>
                <button type="button" class="btn-remove-guest" data-index="${index}" style="background: none; border: none; cursor: pointer; font-size: 14px; color: #dc3545; padding: 0;">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
            </div>
        `;
        guestsList.appendChild(item);
    }

    function refreshGuestsList() {
        guestsList.innerHTML = '';
        Object.keys(guestData).sort((a, b) => parseInt(a) - parseInt(b)).forEach(index => {
            appendGuestItem(guestData[index], index);
        });
        updateSavedGuestsCheckboxes();
    }

    function updateAddedCount() {
        addedCountEl.textContent = Object.keys(guestData).length;
        if (Object.keys(guestData).length >= maxGuests) {
            addGuestBtn.disabled = true;
            addGuestBtn.style.opacity = '0.6';
            addGuestBtn.style.cursor = 'not-allowed';
        } else {
            addGuestBtn.disabled = false;
            addGuestBtn.style.opacity = '';
            addGuestBtn.style.cursor = '';
        }
    }

    function editItemGuest(index) {
        editingIndex = index;
        const guest = guestData[index];
        document.getElementById('modal_relation').value = guest.relation || '';
        document.getElementById('modal_gender').value = guest.gender || '';
        document.getElementById('modal_first_name').value = guest.first_name || '';
        document.getElementById('modal_middle_name').value = guest.middle_name || '';
        document.getElementById('modal_last_name').value = guest.last_name || '';
        document.getElementById('modal_dob').value = guest.dob || '';
        document.getElementById('modal_nationality').value = guest.nationality || '';
        document.getElementById('modal_passport_number').value = guest.passport_number || '';
        document.getElementById('modal_notes').value = guest.notes || '';
        if (isActivityBooking) {
            document.getElementById('modal_time_slot').value = guest.time_slot || '';
        }
        openModal();
    }

    function removeItemGuest(index) {
        delete guestData[index];
        refreshGuestsList();
        updateAddedCount();
    }

    function openModal() {
        addGuestModal.classList.add('show');
        modalOverlay.classList.add('show');
        addGuestModal.style.display = 'flex';
        addGuestModal.style.position = 'fixed';
        addGuestModal.style.inset = '0';
        addGuestModal.style.transform = 'none';
        addGuestModal.style.alignItems = 'center';
        addGuestModal.style.justifyContent = 'center';
        addGuestModal.style.zIndex = '99999';
        modalOverlay.style.display = 'block';
        modalOverlay.style.position = 'fixed';
        modalOverlay.style.inset = '0';
        modalOverlay.style.zIndex = '99998';
        addGuestForm.reset();
    }

    function closeModal() {
        addGuestModal.classList.remove('show');
        modalOverlay.classList.remove('show');
        addGuestModal.style.display = 'none';
        modalOverlay.style.display = 'none';
        addGuestForm.reset();
        editingIndex = null;
    }

    // Handle form submission
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            // Build hidden form inputs for guests
            guestFormInputs.innerHTML = '';
            Object.keys(guestData).sort((a, b) => parseInt(a) - parseInt(b)).forEach(index => {
                const guest = guestData[index];
                Object.keys(guest).forEach(key => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `guests[${index}][${key}]`;
                    input.value = guest[key] || '';
                    guestFormInputs.appendChild(input);
                });
            });
        });
    }

    // Close modal when clicking overlay
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && addGuestModal.classList.contains('show')) {
            closeModal();
        }
    });
});
</script>

<style>
.guest-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.35);
    z-index: 10;
}
.guest-modal-wrapper {
    position: fixed;
    inset: 0;
    z-index: 20;
    justify-content: center;
    align-items: center;
    display: none;
}
.guest-modal-wrapper.show {
    display: flex;
}
</style>
@endsection