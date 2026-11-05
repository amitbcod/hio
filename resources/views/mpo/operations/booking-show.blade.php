@extends('layouts.mpo-portal')

@section('title', $module['name'])

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1">Booking Details</h2>
            <p class="text-muted mb-0">Booking {{ $booking->booking_reference ?? '#'.$booking->getKey() }}</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('mpo.operations.module', ['module' => $module['module']]) }}">Back to bookings</a>
    </div>

    @include('shared.booking-postponement', [
        'booking' => $booking,
        'serviceType' => $serviceType,
        'postponementActions' => $postponementActions,
        'isAdmin' => $isMpoOwner,
        'isMpoStaff' => ! $isMpoOwner,
    ])

    <div class="card mb-4">
        <div class="card-header">Booking information</div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <tbody>
                    @foreach($module['columns'] as $column)
                        <tr>
                            <th scope="row">{{ \Illuminate\Support\Str::headline($column) }}</th>
                            <td>{{ $booking->getAttribute($column) ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        @php
            $statusOptions = $serviceType === 'transport'
                ? ['Confirmed' => 'confirm', 'Scheduled' => 'process', 'Completed' => 'process', 'Cancelled' => 'cancel']
                : ['Confirmed' => 'confirm', 'Cancelled' => 'cancel'];
        @endphp
        @foreach($statusOptions as $status => $action)
            @if($permissions->can($staff, $module['module'], $action))
                <form method="POST" action="{{ route('mpo.operations.booking.status', ['serviceType' => $serviceType, 'booking' => $booking->getKey()]) }}">
                    @csrf
                    <input type="hidden" name="booking_status" value="{{ $status }}">
                    <button class="btn {{ $status === 'Cancelled' ? 'btn-outline-danger' : 'btn-outline-primary' }}" type="submit"
                            @disabled($booking->booking_status === $status || $booking->booking_status === 'Cancelled')>
                        {{ $status }}
                    </button>
                </form>
            @endif
        @endforeach

        @if($serviceType === 'transport' && ($permissions->can($staff, $module['module'], 'assign') || $permissions->can($staff, $module['module'], 'reassign')))
            <section class="card w-100">
                <div class="card-header">Driver and vehicle assignment</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('mpo.operations.transport.assign', ['booking' => $booking->getKey()]) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="pickup_driver_id">Pickup driver</label>
                                <select class="form-select" name="pickup_driver_id" id="pickup_driver_id">
                                    <option value="">No change</option>
                                    @foreach($assignmentDrivers as $driver)
                                        <option value="{{ $driver->id }}" @selected((int) $booking->pickup_driver_id === (int) $driver->id)>{{ $driver->driver_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($booking->return_date)
                                <div class="col-md-4">
                                    <label class="form-label" for="return_driver_id">Return driver</label>
                                    <select class="form-select" name="return_driver_id" id="return_driver_id">
                                        <option value="">No change</option>
                                        @foreach($assignmentDrivers as $driver)
                                            <option value="{{ $driver->id }}" @selected((int) $booking->return_driver_id === (int) $driver->id)>{{ $driver->driver_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-4">
                                <label class="form-label" for="vehicle_id">Vehicle</label>
                                <select class="form-select" name="vehicle_id" id="vehicle_id">
                                    <option value="">No change</option>
                                    @foreach($assignmentVehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" @selected((int) $booking->transport_vehicle_id === (int) $vehicle->id)>{{ $vehicle->license_number }} ({{ $vehicle->registration_number }})</option>
                                    @endforeach
                                    <option value="other">Other vehicle</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="other_vehicle_name">Other vehicle name</label>
                                <input class="form-control" id="other_vehicle_name" name="other_vehicle_name" value="{{ $booking->other_vehicle_name }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="other_vehicle_license_number">Other vehicle license</label>
                                <input class="form-control" id="other_vehicle_license_number" name="other_vehicle_license_number" value="{{ $booking->other_vehicle_license_number }}">
                            </div>
                            @if($booking->transport_vehicle_id || $booking->pickup_driver_id || $booking->return_driver_id || $booking->other_vehicle_license_number)
                                <div class="col-12">
                                    <label class="form-label" for="reason">Reason for reassignment</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="2"></textarea>
                                </div>
                            @endif
                        </div>
                        <button class="btn btn-outline-primary mt-3" type="submit">
                            {{ $booking->transport_vehicle_id || $booking->pickup_driver_id ? 'Save reassignment' : 'Assign driver / vehicle' }}
                        </button>
                    </form>
                </div>
            </section>
        @endif
    </div>
@endsection
