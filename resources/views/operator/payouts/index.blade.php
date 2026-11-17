@extends('operator.layout')

@section('title', 'Payouts')

@section('content')
<div class="card">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div><h4 class="mb-1">Operator payouts</h4><p class="text-muted mb-0">Payout statements and historical agreement snapshots (USD).</p></div>
        @if($canRequest)
            <form method="POST" action="{{ route('operator.payouts.store') }}">@csrf<button class="btn btn-primary">Request eligible payout</button></form>
        @endif
    </div>
    @if(session('success'))<div class="alert alert-success mx-3">{{ session('success') }}</div>@endif
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Reference</th><th>Period</th><th>Schedule</th><th>Bookings</th><th>Gross USD</th><th>Commission USD</th><th>Net USD</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($payouts as $payout)
            <tr>
                <td>{{ $payout->reference }}</td><td>{{ $payout->period_start?->format('Y-m-d') }} – {{ $payout->period_end?->format('Y-m-d') }}</td>
                <td>{{ $payout->payment_schedule }}</td><td>{{ $payout->items->count() }}</td>
                <td>{{ number_format((float) $payout->gross_amount, 2) }}</td><td>{{ number_format((float) $payout->operator_commission_amount, 2) }}</td>
                <td>{{ number_format((float) $payout->net_amount, 2) }}</td><td>{{ $payout->status }}</td>
                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('operator.payouts.show', $payout) }}">Details</a></td>
            </tr>
        @empty<tr><td colspan="9" class="text-center text-muted py-4">No payout statements found.</td></tr>@endforelse
        </tbody>
    </table></div>
    <div class="card-body">{{ $payouts->links() }}</div>
</div>
@endsection
