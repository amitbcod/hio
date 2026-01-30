@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Operator Profile</h2>
    <div class="card mb-3">
        <div class="card-body">
            <h5>Operator: {{ $operator->full_name ?? $operator->operator_id }}</h5>
            <p>Email: {{ $operator->email }}</p>
            <p>Status: {{ $operator->account_status ?? '' }}</p>
        </div>
    </div>
    @if($profile)
    <div class="card mb-3">
        <div class="card-body">
            <h5>Business Legal Name: {{ $profile->business_legal_name }}</h5>
            <p>Country: {{ $progress->country_of_operation ?? '' }}</p>
            <p>Trading Name: {{ $profile->trading_name }}</p>
            <!-- Add more profile fields as needed -->
        </div>
    </div>
    @endif
    @if($progress)
    <div class="card">
        <div class="card-body">
            <h5>Registration Progress</h5>
            <p>Current Step: {{ $progress->current_step }}</p>
            <p>Registration Complete: {{ $progress->registration_complete ? 'Yes' : 'No' }}</p>
        </div>
    </div>
    @endif
</div>
@endsection
