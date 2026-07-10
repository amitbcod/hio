@extends('layouts.app')

@section('title', 'Transport Setup | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            @include('operator.transport._steps_wizard_sidebar', ['step' => $step ?? 6])
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                <h2 style="font-weight:700;margin:0;">Step 6: {{ $title ?? 'Service Description' }}</h2>
                <p style="margin:8px 0 0 0;color:#666;">{{ $description ?? 'Describe your transport services and highlight what makes them unique.' }}</p>
            </div>

            <form method="POST" action="{{ route('operator.transport.service-description.save') }}">
                @csrf
                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    <div class="mb-3">
                        <label class="form-label">Service Description</label>
                        <textarea name="service_description" class="form-control" rows="6">{{ old('service_description', data_get($transportSettings, 'service_description')) }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save & Finish</button>
            </form>
        </div>
    </div>
</div>
@endsection
