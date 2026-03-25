@extends('layouts.admin')

@section('content')
<style>
    .main-setion {
    background: linear-gradient(16deg, #fdda65 0%, #4aaee2 100%);
    min-height: 100vh;
}

.col-md-2.list-section {
    display: none !important;
}
</style>
<div class="col-md-4 offset-md-5 form-section">
    <h3 class="mt-0 text-center">Admin Login</h3>
    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary">Login</button>
    </form>
</div>
@endsection