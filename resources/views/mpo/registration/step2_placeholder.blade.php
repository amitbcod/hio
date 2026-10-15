@extends('layouts.mpo-auth')

@section('content')
<section class="admin-mpo-auth-panel">
    <h1>MPO Account Status</h1>

    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info" role="status">{{ session('info') }}</div>
    @endif

    <p>Your MPO account workflow has returned to this status page.</p>
    <a class="btn btn-primary" href="{{ route('mpo.login') }}">Continue to MPO login</a>
</section>
@endsection
