@extends('layouts.mpo-auth')

@section('content')
<section class="admin-mpo-auth-panel">
    <h1>MPO Login</h1>
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info" role="alert">{{ session('info') }}</div>
    @endif
    <form method="POST" action="{{ route('mpo.login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="mpo-email">Email</label>
            <input id="mpo-email" name="email" type="email" class="form-control" value="{{ old('email') }}" autocomplete="username" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="mpo-password">Password</label>
            <input id="mpo-password" name="password" type="password" class="form-control" autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary" type="submit">Login</button>
    </form>
</section>
@endsection
