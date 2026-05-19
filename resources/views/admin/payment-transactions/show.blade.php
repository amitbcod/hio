@extends('layouts.admin')

@section('title', 'Payment Transaction Details | Admin')

@section('content')
<div class="admin-content">
    <div class="admin-header">
        <h1>Payment Transaction Details</h1>
        <p>Transaction: {{ $transaction->transaction_ref }}</p>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="admin-card">
                <h3>Transaction Information</h3>
                <div class="row">
                    <div class="col-md-6">
                        <strong>ID:</strong> {{ $transaction->id }}<br>
                        <strong>Transaction Ref:</strong> {{ $transaction->transaction_ref }}<br>
                        <strong>Amount:</strong> {{ $transaction->amount }} {{ $transaction->booking?->currency ?? 'USD' }}<br>
                        <strong>Method:</strong> {{ ucfirst($transaction->method) }}<br>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <span class="badge badge-{{ $transaction->status === 'paid' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                            {{ ucfirst($transaction->status) }}
                        </span><br>
                        <strong>Created:</strong> {{ $transaction->created_at->format('M d, Y H:i:s') }}<br>
                        <strong>Updated:</strong> {{ $transaction->updated_at->format('M d, Y H:i:s') }}<br>
                    </div>
                </div>
            </div>

            @if($transaction->booking)
                @php
                    $booking = $transaction->booking;
                    $bookingRoute = null;
                    if ($booking->getTable() === 'accommodation_bookings') {
                        $bookingRoute = route('admin.accommodation.booking.details', $booking->id);
                    } elseif ($booking->getTable() === 'activity_bookings') {
                        $bookingRoute = route('admin.activity.booking.details', $booking->id);
                    }
                @endphp

                <div class="admin-card">
                    <h3>Related Booking</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Booking ID:</strong> {{ $booking->id }}<br>
                            <strong>Trip ID:</strong> {{ $booking->trip_id ?? 'N/A' }}<br>
                            <strong>Operator ID:</strong> {{ $booking->operator_id ?? 'N/A' }}<br>
                            <strong>Total Amount:</strong> {{ $booking->total_amount ?? 'N/A' }}<br>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong> {{ ucfirst($booking->status ?? 'N/A') }}<br>
                            <strong>Created:</strong> {{ optional($booking->created_at)->format('M d, Y H:i') ?? 'N/A' }}<br>
                            <strong>Updated:</strong> {{ optional($booking->updated_at)->format('M d, Y H:i') ?? 'N/A' }}<br>
                        </div>
                    </div>
                    @if($bookingRoute)
                        <div class="mt-3">
                            <a href="{{ $bookingRoute }}" class="btn btn-primary" target="_blank">View Booking Details</a>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="admin-card">
                <h3>Gateway Callbacks</h3>
                <p>Check callback status from Againgency payment gateway.</p>
                <button id="loadCallbacksBtn" class="btn btn-secondary" onclick="loadCallbacks()">
                    <i class="fa fa-refresh"></i> Load Callbacks
                </button>
                <div id="callbacksResult" class="mt-3" style="display: none;">
                    <div id="callbacksLoading" class="text-center">
                        <i class="fa fa-spinner fa-spin"></i> Loading...
                    </div>
                    <div id="callbacksContent"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadCallbacks() {
    const btn = document.getElementById('loadCallbacksBtn');
    const result = document.getElementById('callbacksResult');
    const loading = document.getElementById('callbacksLoading');
    const content = document.getElementById('callbacksContent');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading...';
    result.style.display = 'block';
    loading.style.display = 'block';
    content.innerHTML = '';

    fetch('{{ route("admin.payment-transactions.callbacks", $transaction) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        loading.style.display = 'none';

        if (data.success) {
            displayCallbacks(data.callbacks);
        } else {
            content.innerHTML = '<div class="alert alert-danger">' + (data.error || 'Failed to load callbacks') + '</div>';
        }
    })
    .catch(error => {
        loading.style.display = 'none';
        content.innerHTML = '<div class="alert alert-danger">Network error: ' + error.message + '</div>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-refresh"></i> Load Callbacks';
    });
}

function displayCallbacks(callbacks) {
    const content = document.getElementById('callbacksContent');

    if (!callbacks.payload) {
        content.innerHTML = '<div class="alert alert-warning">No callback data available</div>';
        return;
    }

    let html = '';

    // PMS Callbacks
    if (callbacks.payload.pms_callbacks && callbacks.payload.pms_callbacks.length > 0) {
        html += '<h5>PMS Callbacks</h5>';
        html += '<table class="table table-sm">';
        html += '<thead><tr><th>Service</th><th>State</th><th>Expected</th><th>In Progress</th></tr></thead><tbody>';

        callbacks.payload.pms_callbacks.forEach(callback => {
            html += '<tr>';
            html += '<td>' + callback.service + '</td>';
            html += '<td>' + callback.callback_state + '</td>';
            html += '<td>' + callback.awaited_callback_state + '</td>';
            html += '<td>' + (callback.callback_in_progress ? 'Yes' : 'No') + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
    }

    // Timeline Callbacks
    if (callbacks.payload.timeline_callbacks && callbacks.payload.timeline_callbacks.length > 0) {
        html += '<h5>Timeline Callbacks</h5>';
        html += '<table class="table table-sm">';
        html += '<thead><tr><th>ID</th><th>Created</th><th>Sent</th><th>Sending</th><th>Body</th></tr></thead><tbody>';

        callbacks.payload.timeline_callbacks.forEach(callback => {
            html += '<tr>';
            html += '<td>' + callback.callback_id + '</td>';
            html += '<td>' + new Date(callback.created_at).toLocaleString() + '</td>';
            html += '<td>' + (callback.sent ? 'Yes' : 'No') + '</td>';
            html += '<td>' + (callback.sending ? 'Yes' : 'No') + '</td>';
            html += '<td><small>' + callback.body.substring(0, 100) + '...</small></td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
    }

    if (html === '') {
        html = '<div class="alert alert-info">No callbacks found</div>';
    }

    content.innerHTML = html;
}
</script>
@endsection