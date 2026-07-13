@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h3>Hotel / City Mapping</h3>
        <a href="{{ route('admin.places.create') }}" class="btn btn-primary">Add Hotel / City Mapping</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Hotel / City Mapping</th>
                    <th>Route Region</th>
                    <th>Active</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($places as $place)
                    <tr>
                        <td>{{ $place->place_name }}</td>
                        <td>{{ $place->route_region }}</td>
                        <td>{{ $place->is_active ? 'Yes' : 'No' }}</td>
                        <td>{{ $place->created_at?->format('Y-m-d') }}</td>
                        <td>{{ $place->updated_at?->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('admin.places.edit', $place->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.places.destroy', $place->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this Hotel / City Mapping?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No Hotel / City Mapping records available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
