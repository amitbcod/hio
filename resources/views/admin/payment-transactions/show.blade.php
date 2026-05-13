@extends('admin.layout')

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
            <div class="admin-card">
                <h3>Related Booking</h3>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Booking Reference:</strong> {{ $transaction->booking->booking_reference }}<br>
                        <strong>Type:</strong> {{ ucfirst($transaction->booking->getTable() === 'accommodation_bookings' ? 'Accommodation' : 'Activity') }}<br>
                        <strong>Guest Name:</strong> {{ $transaction->booking->guest_name }}<br>
                        <strong>Guest Email:</strong> {{ $transaction->booking->guest_email }}<br>
                    </div>
                    <div class="col-md-6">
                        <strong>Total Amount:</strong> {{ $transaction->booking->total_amount }} {{ $transaction->booking->currency }}<br>
                        <strong>Booking Status:</strong> {{ $transaction->booking->booking_status }}<br>
                        <strong>Booked At:</strong> {{ $transaction->booking->booked_at->format('M d, Y H:i') }}<br>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.accommodation.booking.details', $transaction->booking->id) }}" class="btn btn-primary" target="_blank">View Booking Details</a>
                </div>
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