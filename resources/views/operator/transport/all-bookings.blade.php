@extends('layouts.app')

@section('title', 'All Transport Bookings | Operator')

@section('content')
<div class="container mt-0">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @include('operator.registration._sidebar_main')
        </div>
        <div class="col-md-9 my-pro">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;margin-top:40px;">
                <h2 style="margin:0;font-weight:700;">All Transport Bookings</h2>
                <p style="margin:6px 0 0 0;color:#666;">Bookings across all your transport services.</p>
            </div>

            @if($bookings->isEmpty())
                <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                    <div class="alert" style="background:transparent;color:#666;margin:0;">No bookings found.</div>
                </div>
            @else
                <div style="background:#fff;border-radius:12px;padding:12px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Service</th>
                                    <th>Guest</th>
                                    <th>Passengers</th>
                                    <th>Pickup</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Booked</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookings as $booking)
                                    <tr>
                                        <td>{{ $booking->booking_reference }}</td>
                                        <td>{{ optional($booking->transport)->vehicle_name }}</td>
                                        <td>{{ $booking->guest_name ?? ($booking->traveler_first_name.' '.$booking->traveler_last_name) }}</td>
                                        <td>{{ $booking->total_passengers ?? $booking->adults }}</td>
                                        <td>{{ optional($booking->pickup_date)->format('M d, Y') }} {{ $booking->pickup_time }}</td>
                                        <td>{{ $booking->currency ?? 'USD' }} {{ number_format($booking->total_amount, 2) }}</td>
                                        <td>{{ ucfirst($booking->booking_status ?? 'pending') }}</td>
                                        <td>{{ optional($booking->booked_at)->format('M d, Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('operator.transport.booking.details', [$booking->transport_id, $booking->id]) }}" class="btn btn-sm btn-primary">Details</a>
                                            @php
                                                $viewAssigned = $booking->pickup_driver_id && (! $booking->return_date || $booking->return_driver_id);
                                            @endphp
                                            <button class="btn btn-sm btn-info" onclick="openAssignDriverModal({{ $booking->id }})">
                                                {{ $viewAssigned ? 'View Assigned Drivers' : 'Assign Driver' }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $bookings->links() }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Assign Driver Modal -->
<div class="modal fade" id="assignDriverModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Drivers to Booking</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeAssignDriverModal()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignDriverForm">
                    @csrf
                    <div class="form-group">
                        <label for="pickupDriverSelect">Pickup Driver <span style="color: red;">*</span></label>
                        <select id="pickupDriverSelect" name="pickup_driver_id" class="form-control" required>
                            <option value="">Select pickup driver</option>
                        </select>
                    </div>
                    <div id="returnDriverGroup" class="form-group" style="display: none;">
                        <label for="returnDriverSelect">Return Driver</label>
                        <select id="returnDriverSelect" name="return_driver_id" class="form-control">
                            <option value="">Select return driver</option>
                        </select>
                        <small class="form-text text-muted">Assign a separate return driver if this booking includes a return journey.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeAssignDriverModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveDriverAssignment()">Assign Drivers</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentBookingId = null;
const getDriversUrlTemplate = "{{ route('operator.transport.booking.get-drivers', ['booking' => 'BOOKING_ID']) }}";
const assignDriversUrlTemplate = "{{ route('operator.transport.booking.assign-drivers', ['booking' => 'BOOKING_ID']) }}";

function openAssignDriverModal(bookingId) {
    currentBookingId = bookingId;
    const url = getDriversUrlTemplate.replace('BOOKING_ID', bookingId);

    fetch(url, {
        credentials: 'same-origin'
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.drivers) {
                populateDriverSelects(data.drivers, data.assigned_pickup_driver_id, data.assigned_return_driver_id, data.has_return_journey);
                $('#assignDriverModal').modal('show');
            } else {
                throw new Error('No drivers payload');
            }
        })
        .catch(error => {
            console.error('Error fetching drivers:', error);
            alert('Error loading drivers');
        });
}

function populateDriverSelects(drivers, selectedPickupId = null, selectedReturnId = null, hasReturnJourney = false) {
    const pickupSelect = document.getElementById('pickupDriverSelect');
    const returnSelect = document.getElementById('returnDriverSelect');
    const returnGroup = document.getElementById('returnDriverGroup');
    pickupSelect.innerHTML = '<option value="">Select pickup driver</option>';
    returnSelect.innerHTML = '<option value="">Select return driver</option>';

    drivers.forEach(driver => {
        const optionA = document.createElement('option');
        optionA.value = driver.id;
        optionA.text = `${driver.driver_name} (${driver.driver_phone || 'N/A'})`;
        if (driver.id === selectedPickupId) {
            optionA.selected = true;
        }
        pickupSelect.appendChild(optionA);

        const optionB = document.createElement('option');
        optionB.value = driver.id;
        optionB.text = `${driver.driver_name} (${driver.driver_phone || 'N/A'})`;
        if (driver.id === selectedReturnId) {
            optionB.selected = true;
        }
        returnSelect.appendChild(optionB);
    });

    if (hasReturnJourney) {
        returnGroup.style.display = 'block';
    } else {
        returnGroup.style.display = 'none';
        returnSelect.value = '';
    }
}

function closeAssignDriverModal() {
    $('#assignDriverModal').modal('hide');
}

function saveDriverAssignment() {
    const pickupDriverId = document.getElementById('pickupDriverSelect').value;
    const returnDriverId = document.getElementById('returnDriverSelect').value || null;

    if (!pickupDriverId) {
        alert('Please select a pickup driver');
        return;
    }

    const url = assignDriversUrlTemplate.replace('BOOKING_ID', currentBookingId);

    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            pickup_driver_id: pickupDriverId,
            return_driver_id: returnDriverId,
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(body => {
                throw new Error(body.error || body.message || `HTTP ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Drivers assigned successfully');
            closeAssignDriverModal();
            location.reload();
        } else {
            throw new Error(data.error || data.message || 'Unknown error');
        }
    })
    .catch(error => {
        console.error('Error assigning drivers:', error);
        alert('Error assigning drivers: ' + error.message);
    });
}
</script>

@endsection
