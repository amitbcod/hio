@extends('layouts.admin')

@section('content')
<div class="col-md-8 offset-md-2">
    <h3 class="mt-4">Add Hotel / City Mapping</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.places.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Hotel / City Mapping</label>
            <input type="text" name="place_name" class="form-control" value="{{ old('place_name') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Route Region</label>
            <select name="route_region" class="form-control" required>
                <option value="">Select region</option>
                @foreach($regions as $region)
                    <option value="{{ $region }}" {{ old('route_region') === $region ? 'selected' : '' }}>{{ $region }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
        <button type="submit" class="btn btn-primary">Save Hotel / City Mapping</button>
        <a href="{{ route('admin.places.index') }}" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
@endsection
