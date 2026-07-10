@extends('layouts.app')

@section('title', 'Transport Setup | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            @include('operator.transport._steps_wizard_sidebar', ['step' => $step ?? 4])
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                <h2 style="font-weight:700;margin:0;">Step 4: {{ $title ?? 'Reservation and Communication' }}</h2>
                <p style="margin:8px 0 0 0;color:#666;">{{ $description ?? 'Set reservation and communication preferences.' }}</p>
            </div>

            <form method="POST" action="{{ route('operator.transport.reservation-and-communication.save') }}">
                @csrf
                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Reservation Contact Name</label>
                            <input type="text" name="reservation_and_communication[reservation_contact_name]" class="form-control" required value="{{ old('reservation_and_communication.reservation_contact_name', data_get($transportSettings, 'reservation_and_communication.reservation_contact_name')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reservation Email</label>
                            <input type="email" name="reservation_and_communication[reservation_email]" class="form-control" required value="{{ old('reservation_and_communication.reservation_email', data_get($transportSettings, 'reservation_and_communication.reservation_email')) }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Reservation Phone</label>
                            <input type="text" name="reservation_and_communication[reservation_phone]" class="form-control" value="{{ old('reservation_and_communication.reservation_phone', data_get($transportSettings, 'reservation_and_communication.reservation_phone')) }}">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save & Finish</button>
            </form>
        </div>
    </div>
</div>
@endsection
