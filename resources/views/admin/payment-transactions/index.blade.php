@extends('admin.layout')

@section('title', 'Payment Transactions | Admin')

@section('content')
<div class="admin-content">
    <div class="admin-header">
        <h1>Payment Transactions</h1>
        <p>Manage and monitor payment transactions</p>
    </div>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Transaction Ref</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Booking</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->id }}</td>
                            <td>{{ $transaction->transaction_ref }}</td>
                            <td>{{ $transaction->amount }} {{ $transaction->booking?->currency ?? 'USD' }}</td>
                            <td>{{ ucfirst($transaction->method) }}</td>
                            <td>
                                <span class="badge badge-{{ $transaction->status === 'paid' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>
                                @if($transaction->booking)
                                    <a href="{{ route('admin.accommodation.booking.details', $transaction->booking->id) }}" target="_blank">
                                        {{ $transaction->booking->booking_reference }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.payment-transactions.show', $transaction) }}" class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No payment transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}
    </div>
</div>
@endsection