@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Countries</h1>
        <a href="{{ route('admin.countries.create') }}" class="btn btn-primary">Add Country</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead><tr><th>Country</th><th>ISO code</th><th>Currency</th><th>Timezone</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($countries as $country)
                    <tr>
                        <td>{{ $country->name }}</td>
                        <td>{{ $country->iso_code }}</td>
                        <td>{{ $country->currency_code ?: '—' }}</td>
                        <td>{{ $country->time_zone ?: '—' }}</td>
                        <td>{{ $country->is_active ? 'Active' : 'Inactive' }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.countries.edit', $country) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No countries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $countries->links() }}
</div>
@endsection
