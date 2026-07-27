@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Regions</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a class="btn btn-primary mb-3" href="{{ route('admin.regions.create') }}">Create Region</a>

        @if($regions->count())
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($regions as $region)
                        <tr>
                            <td>{{ $region->name }}</td>
                            <td>
                                <a class="btn btn-sm btn-secondary" href="{{ route('admin.regions.edit', $region->id) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.regions.destroy', $region->id) }}" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Delete region?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No regions found.</p>
        @endif
    </div>
@endsection
