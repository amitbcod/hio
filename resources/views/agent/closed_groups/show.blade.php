@extends('layouts.agent')

@section('title', 'Group Booking Details | Holidays.io')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $booking->trip?->title ?? 'Group Booking' }}</h1>
        <p class="text-muted mb-0">Booking {{ $booking->bookingRef?->booking_ref_code ?? 'reference pending' }}</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('agent.closed-groups.index') }}">Back to bookings</a>
</div>

@if($booking->agent_verification_status === 'verified')
    <div class="alert alert-success">This booking has been approved by Admin.</div>
@else
    <div class="alert alert-warning">This booking is pending Admin review. You may submit bank-transfer proof; Admin will verify the payment after reviewing the booking.</div>
@endif

@php($payment = $booking->payments->firstWhere('booking_ref_id', $booking->booking_ref_id))
<section class="bg-white border rounded p-4 mb-4">
    <h2 class="h5 mb-3">Bank transfer proof</h2>
    @if($payment?->settlement_status === 'verified_settled')
        <div class="alert alert-success mb-0">
            Payment verified and settled{{ $payment->verified_at ? ' on ' . $payment->verified_at->format('d/m/Y H:i') : '' }}.
        </div>
    @elseif($payment?->bank_transfer_receipt_path)
        <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0">
            <span>
                Proof submitted and awaiting Admin verification.
                Transfer date: {{ $payment->bank_transfer_date?->format('d/m/Y') ?? 'N/A' }}.
            </span>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('agent.closed-groups.bank-transfer-proof.download', $booking->id) }}">Download submitted proof</a>
        </div>
    @elseif($payment)
        <p class="text-muted">Upload the bank-transfer receipt for this booking. Accepted formats: PDF, PNG, JPG or JPEG; maximum 5 MB.</p>
        <form method="POST" enctype="multipart/form-data" action="{{ route('agent.closed-groups.bank-transfer-proof', $booking->id) }}" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label" for="bank_transfer_date">Date of Transfer <span class="text-danger">*</span></label>
                <input class="form-control" id="bank_transfer_date" name="bank_transfer_date" type="date" value="{{ old('bank_transfer_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="receipt">Upload Bank Transfer Receipt <span class="text-danger">*</span></label>
                <input class="form-control" id="receipt" name="receipt" type="file" accept=".pdf,.png,.jpg,.jpeg" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Submit Bank Transfer Proof</button>
            </div>
        </form>
    @else
        <div class="alert alert-secondary mb-0">Payment details are not available for this booking yet.</div>
    @endif
</section>

@php($lead = $booking->trip?->travellers?->firstWhere('relationship', 'lead'))
<section class="bg-white border rounded p-4 mb-4">
    <h2 class="h5 mb-3">Traveller and booking details</h2>
    <dl class="row mb-0">
        <dt class="col-sm-4">Traveller</dt>
        <dd class="col-sm-8">{{ $lead?->name ?? 'N/A' }}</dd>
        <dt class="col-sm-4">Email</dt>
        <dd class="col-sm-8">{{ $lead?->email ?? 'N/A' }}</dd>
        <dt class="col-sm-4">Phone</dt>
        <dd class="col-sm-8">{{ $lead?->phone ?? 'N/A' }}</dd>
        <dt class="col-sm-4">Passport Number</dt>
        <dd class="col-sm-8">{{ $lead?->passport_number ?? 'N/A' }}</dd>
        <dt class="col-sm-4">Created</dt>
        <dd class="col-sm-8">{{ $booking->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</dd>
        <dt class="col-sm-4">Booking Status</dt>
        <dd class="col-sm-8">{{ ucfirst($booking->status ?? 'pending') }}</dd>
        <dt class="col-sm-4">Total Amount</dt>
        <dd class="col-sm-8">USD {{ number_format((float) $booking->total_amount, 2) }}</dd>
    </dl>
</section>

<section class="bg-white border rounded p-4">
    <h2 class="h5 mb-3">Traveller roster</h2>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Name</th><th>Relationship</th><th>Email</th><th>Phone</th></tr></thead>
            <tbody>
                @forelse($booking->trip?->travellers ?? [] as $traveller)
                    <tr>
                        <td>{{ $traveller->name }}</td>
                        <td>{{ ucfirst($traveller->relationship ?? 'guest') }}</td>
                        <td>{{ $traveller->email ?? '—' }}</td>
                        <td>{{ $traveller->phone ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">Traveller details are unavailable.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
