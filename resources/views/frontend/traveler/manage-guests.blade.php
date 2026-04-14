@extends('frontend.layout')

@section('title', 'Manage Guests | ' . $trip->trip_name)
@section('meta_description', 'Manage guest details for your booking.')

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

        @php $bookedCount = ($booking->adults ?? 0) + ($booking->children ?? 0); @endphp
        <div class="manage-guests-card" style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
            <form method="POST" action="{{ route('traveler.trip.booking.update-guests', ['trip' => $trip->id, 'booking' => $booking->id]) }}">
                @csrf
                <p style="margin-bottom: 20px; font-weight: 600;">Booked: {{ $bookedCount }} &nbsp;|&nbsp; Added: <span id="added-count">{{ $booking->guests->count() }}</span></p>
                <div id="guests-list">
                    @foreach($booking->guests as $index => $guest)
                    <div class="guest-item" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 10px;">Guest {{ $index + 1 }}</h4>
                        <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div class="form-group">
                                <label for="guests_{{ $index }}_first_name">First Name <span class="req">*</span></label>
                                <input type="text" id="guests_{{ $index }}_first_name" name="guests[{{ $index }}][first_name]" value="{{ $guest->first_name }}" required class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="guests_{{ $index }}_middle_name">Middle Name</label>
                                <input type="text" id="guests_{{ $index }}_middle_name" name="guests[{{ $index }}][middle_name]" value="{{ $guest->middle_name }}" class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="guests_{{ $index }}_last_name">Last Name <span class="req">*</span></label>
                                <input type="text" id="guests_{{ $index }}_last_name" name="guests[{{ $index }}][last_name]" value="{{ $guest->last_name }}" required class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="guests_{{ $index }}_dob">Date of Birth <span class="req">*</span></label>
                                <input type="date" id="guests_{{ $index }}_dob" name="guests[{{ $index }}][dob]" value="{{ $guest->dob ? \Carbon\Carbon::parse($guest->dob)->format('Y-m-d') : '' }}" required class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="guests_{{ $index }}_gender">Gender</label>
                                <select id="guests_{{ $index }}_gender" name="guests[{{ $index }}][gender]" class="form-input">
                                    <option value="">Select</option>
                                    <option value="male" @selected($guest->gender === 'male')>Male</option>
                                    <option value="female" @selected($guest->gender === 'female')>Female</option>
                                    <option value="non_binary" @selected($guest->gender === 'non_binary')>Non-binary</option>
                                    <option value="other" @selected($guest->gender === 'other')>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="guests_{{ $index }}_nationality">Nationality <span class="req">*</span></label>
                                <select id="guests_{{ $index }}_nationality" name="guests[{{ $index }}][nationality]" required class="form-input">
                                    <option value="">Select nationality</option>
                                    @foreach($countries ?? [] as $country)
                                        <option value="{{ $country }}" @selected(trim($guest->nationality) === trim($country))>{{ $country }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="guests_{{ $index }}_passport_number">Passport Number</label>
                                <input type="text" id="guests_{{ $index }}_passport_number" name="guests[{{ $index }}][passport_number]" value="{{ $guest->passport_number }}" class="form-input">
                            </div>
                            <div class="form-group form-group--full">
                                <label for="guests_{{ $index }}_notes">Notes</label>
                                <textarea id="guests_{{ $index }}_notes" name="guests[{{ $index }}][notes]" rows="2" class="form-input form-textarea">{{ $guest->notes }}</textarea>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger remove-guest" data-index="{{ $index }}" style="margin-top: 10px;">Remove Guest</button>
                    </div>
                    @endforeach
                </div>
                <button type="button" id="add-guest-btn" class="btn btn-secondary" style="margin-right: 10px;">Add Guest</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>

        <a href="{{ route('traveler.trip.detail', $trip) }}" class="btn btn-secondary">&larr; Back to Trip</a>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let guestIndex = {{ $booking->guests->count() }};
    const maxGuests = {{ $bookedCount }};
    const addGuestBtn = document.getElementById('add-guest-btn');
    const addedCountEl = document.getElementById('added-count');

    updateAddedCount();

    addGuestBtn.addEventListener('click', function() {
        if (document.querySelectorAll('#guests-list .guest-item').length >= maxGuests) {
            alert('You can only add up to ' + maxGuests + ' guest details for this booking.');
            return;
        }
        addGuestForm();
        updateAddedCount();
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-guest')) {
            e.target.closest('.guest-item').remove();
            updateAddedCount();
        }
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        const currentCount = document.querySelectorAll('#guests-list .guest-item').length;
        if (currentCount > maxGuests) {
            e.preventDefault();
            alert('You cannot save more guest entries than the booked total (' + maxGuests + ').');
        }
    });

    function updateAddedCount() {
        const currentCount = document.querySelectorAll('#guests-list .guest-item').length;
        addedCountEl.textContent = currentCount;
        if (currentCount >= maxGuests) {
            addGuestBtn.disabled = true;
            addGuestBtn.style.opacity = '0.6';
            addGuestBtn.style.cursor = 'not-allowed';
        } else {
            addGuestBtn.disabled = false;
            addGuestBtn.style.opacity = '';
            addGuestBtn.style.cursor = '';
        }
    }

    function addGuestForm() {
        const container = document.getElementById('guests-list');
        const guestItem = document.createElement('div');
        guestItem.className = 'guest-item';
        guestItem.style.border = '1px solid #ddd';
        guestItem.style.borderRadius = '8px';
        guestItem.style.padding = '15px';
        guestItem.style.marginBottom = '15px';
        guestItem.innerHTML = `
            <h4 style="margin-bottom: 10px;">Guest ${guestIndex + 1}</h4>
            <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label for="guests_${guestIndex}_first_name">First Name <span class="req">*</span></label>
                    <input type="text" id="guests_${guestIndex}_first_name" name="guests[${guestIndex}][first_name]" value="" required class="form-input">
                </div>
                <div class="form-group">
                    <label for="guests_${guestIndex}_middle_name">Middle Name</label>
                    <input type="text" id="guests_${guestIndex}_middle_name" name="guests[${guestIndex}][middle_name]" value="" class="form-input">
                </div>
                <div class="form-group">
                    <label for="guests_${guestIndex}_last_name">Last Name <span class="req">*</span></label>
                    <input type="text" id="guests_${guestIndex}_last_name" name="guests[${guestIndex}][last_name]" value="" required class="form-input">
                </div>
                <div class="form-group">
                    <label for="guests_${guestIndex}_dob">Date of Birth <span class="req">*</span></label>
                    <input type="date" id="guests_${guestIndex}_dob" name="guests[${guestIndex}][dob]" value="" required class="form-input">
                </div>
                <div class="form-group">
                    <label for="guests_${guestIndex}_gender">Gender</label>
                    <select id="guests_${guestIndex}_gender" name="guests[${guestIndex}][gender]" class="form-input">
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="non_binary">Non-binary</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="guests_${guestIndex}_nationality">Nationality <span class="req">*</span></label>
                    <select id="guests_${guestIndex}_nationality" name="guests[${guestIndex}][nationality]" required class="form-input">
                        <option value="">Select nationality</option>
                        @foreach($countries ?? [] as $country)
                            <option value="{{ $country }}">{{ $country }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="guests_${guestIndex}_passport_number">Passport Number</label>
                    <input type="text" id="guests_${guestIndex}_passport_number" name="guests[${guestIndex}][passport_number]" value="" class="form-input">
                </div>
                <div class="form-group form-group--full">
                    <label for="guests_${guestIndex}_notes">Notes</label>
                    <textarea id="guests_${guestIndex}_notes" name="guests[${guestIndex}][notes]" rows="2" class="form-input form-textarea"></textarea>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-danger remove-guest" data-index="${guestIndex}" style="margin-top: 10px;">Remove Guest</button>
        `;
        container.appendChild(guestItem);
        guestIndex++;
    }
});
</script>
@endsection