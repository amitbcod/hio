@extends('layouts.admin')

@section('content')
<div class="container mt-5">
    <h2>Packages</h2>
    <p><a href="{{ route('admin.packages.create') }}" class="btn btn-primary">Create New Package</a></p>
    @if($packages->count())
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Days</th>
                    <th>Available From</th>
                    <th>Available To</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($packages as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ $p->name }}</td>
                    <td>
                        <span class="badge {{ $p->status === 'published' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ ucfirst($p->status ?? 'draft') }}
                        </span>
                    </td>
                    <td>{{ $p->no_of_days }}</td>
                    <td>{{ optional($p->available_from)->toDateString() }}</td>
                    <td>{{ optional($p->available_to)->toDateString() }}</td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('admin.packages.step2', $p->id) }}" class="btn btn-sm btn-secondary">Edit Itinerary</a>
                        <a href="{{ route('admin.packages.index') }}?duplicate={{ $p->id }}" class="btn btn-sm btn-light">Duplicate</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $packages->links() }}
    @else
        <p>No packages yet.</p>
    @endif
</div>
@endsection
