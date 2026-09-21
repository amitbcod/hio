@extends('admin.layout')

@section('title', 'Booking Reference Detail')

@section('content')
    <div class="wrap">
        <h1>Booking Reference: {{ $bookingRef->booking_ref_code }}</h1>
        <p><strong>ID:</strong> {{ $bookingRef->id }}</p>
        <p><strong>Trip ID:</strong> {{ $bookingRef->trip_id }}</p>
        <p><strong>Total:</strong> {{ $bookingRef->total_amount }}</p>
        <p><strong>Payment Transaction:</strong> {{ $bookingRef->payment_transaction_id }}</p>

        <h3>Linked Service Bookings</h3>
        <ul>
            @foreach(\App\Models\AccommodationBooking::where('booking_ref_id', $bookingRef->id)->get() as $b)
                <li>Accommodation: ID {{ $b->id }} - Ref {{ $b->booking_reference }}</li>
            @endforeach
            @foreach(\App\Models\ActivityBooking::where('booking_ref_id', $bookingRef->id)->get() as $b)
                <li>Activity: ID {{ $b->id }} - Ref {{ $b->booking_reference }}</li>
            @endforeach
            @foreach(\App\Models\TransportBooking::where('booking_ref_id', $bookingRef->id)->get() as $b)
                <li>Transport: ID {{ $b->id }} - Ref {{ $b->booking_reference }}</li>
            @endforeach
        </ul>
    </div>
@endsection
