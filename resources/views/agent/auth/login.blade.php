@extends('layouts.agent-auth')

@section('title', 'Agent Login')

@section('content')
<h1>Agent Login</h1>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('agent.login.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
        <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
        <input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <button class="btn btn-primary" type="submit">Sign in</button>
        <a href="{{ route('agent.register') }}">Register your agency</a>
    </div>
</form>
@endsection
