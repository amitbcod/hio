@extends('layouts.mpo-auth')

@section('title', 'Set MPO Password | Holidays.io')

@section('content')
<section class="admin-mpo-auth-panel">
    <h1>Set your MPO password</h1>
    <p>Choose a password for the MPO account registered to {{ $administrator->email }}.</p>

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('mpo.activation.update', [$administrator, $token]) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="password">New password</label>
            <input class="form-control" id="password" type="password" name="password" minlength="8" autocomplete="new-password" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password_confirmation">Confirm new password</label>
            <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
        </div>
        <button class="btn btn-primary" type="submit">Set password</button>
    </form>
</section>
@endsection
