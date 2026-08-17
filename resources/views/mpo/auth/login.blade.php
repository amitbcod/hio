@extends('layouts.app')

@section('content')
<div class="container">
    <h1>MPO Login</h1>
    <form method="POST" action="{{ route('mpo.login') }}">
        @csrf
        <div>
            <label>Email</label>
            <input name="email" type="email" required>
        </div>
        <div>
            <label>Password</label>
            <input name="password" type="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
</div>
@endsection
