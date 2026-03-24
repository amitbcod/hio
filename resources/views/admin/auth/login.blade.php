@extends('layouts.admin')

@section('content')
<div class="col-md-4 offset-md-5 form-section">
    <h3 class="mt-0">Admin Login</h3>
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