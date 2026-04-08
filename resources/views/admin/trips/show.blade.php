@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Trip Details - ID: {{ $trip->id }}</h3>
                    <a href="{{ route('admin.trips.index') }}" class="btn btn-secondary">Back</a>
                </div>
                <div class="card-body">
                    <h4>Traveller: {{ $trip->traveler->full_name }}</h4>
                    <p>Title: {{ $trip->title }}</p>
                    <p>Dates: {{ $trip->start_date ? $trip->start_date->format('d/m/Y') : 'N/A' }} - {{ $trip->end_date ? $trip->end_date->format('d/m/Y') : 'N/A' }}</p>
                    <p>Status: {{ $trip->status }}</p>

                    <h5>Travellers in Party</h5>
                    <ul>
                        @foreach($trip->travellers as $traveller)
                        <li>{{ $traveller->name }} ({{ $traveller->relationship }})</li>
                        @endforeach
                    </ul>

                    <h5>Bookings</h5>
                    @foreach($trip->bookings as $booking)
                    <div class="border p-2 mb-2">
                        <p>Booking ID: {{ $booking->id }}, Status: {{ $booking->status }}, Total: ${{ $booking->total_amount }}</p>
                        <h6>Line Items</h6>
                        <ul>
                            @foreach($booking->lineItems as $bli)
                            <li>{{ $bli->service_type }}: {{ $bli->quantity }} x ${{ $bli->price }}
                                <br>Travellers: @foreach($bli->travellers as $t) {{ $t->name }} @endforeach
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
