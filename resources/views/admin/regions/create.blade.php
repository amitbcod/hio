@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Create Region</h1>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.regions.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Region Name</label>
                <input name="name" class="form-control" value="{{ old('name') }}" required />
            </div>
            <button class="btn btn-primary">Create</button>
            <a href="{{ route('admin.regions.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
