@extends('admin.layout')

@section('title', 'Booking References')

@section('content')
    <div class="wrap">
        <h1>Booking References</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Trip ID</th>
                    <th>Total Amount</th>
                    <th>Payment Transaction</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookingRefs as $br)
                    <tr>
                        <td>{{ $br->id }}</td>
                        <td>{{ $br->booking_ref_code }}</td>
                        <td>{{ $br->trip_id }}</td>
                        <td>{{ $br->total_amount }}</td>
                        <td>{{ $br->payment_transaction_id }}</td>
                        <td>
                            <a href="{{ route('admin.booking_refs.show', $br) }}" class="btn btn-sm btn-primary">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
