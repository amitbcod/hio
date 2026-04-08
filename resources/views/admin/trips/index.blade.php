@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Trips Management</h3>
                    <a href="{{ route('admin.trips.create') }}" class="btn btn-primary float-right">Create Trip</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Trip ID</th>
                                <th>Traveller</th>
                                <th>Title</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trips as $trip)
                            <tr>
                                <td>{{ $trip->id }}</td>
                                <td>{{ $trip->traveler->full_name ?? 'N/A' }}</td>
                                <td>{{ $trip->title }}</td>
                                <td>{{ $trip->start_date ? $trip->start_date->format('d/m/Y') : 'N/A' }} - {{ $trip->end_date ? $trip->end_date->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $trip->status }}</td>
                                <td>
                                    <a href="{{ route('admin.trips.show', $trip) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('admin.trips.edit', $trip) }}" class="btn btn-sm btn-warning">Edit</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $trips->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
