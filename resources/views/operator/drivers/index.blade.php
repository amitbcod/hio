@extends('layouts.app')

@section('title', 'Driver Management | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            @include('operator.drivers._sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h2 style="font-weight:700;margin:0;">Driver Management</h2>
                    <p style="margin:6px 0 0 0;color:#666;">Manage and track your drivers</p>
                </div>
                <div>
                    <a href="{{ route('operator.drivers.create') }}" class="btn" style="background:#19b5b5;color:#fff;padding:8px 14px;border-radius:6px;border:none;">+ Add New Driver</a>
                </div>
            </div>

            @if (session('success'))
                <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:12px;margin-bottom:12px;color:#2e7d32;">{{ session('success') }}</div>
            @endif

            @if ($drivers->count() > 0)
                <div style="background:#fff;border-radius:12px;padding:12px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Driver</th>
                                    <th>Mobile</th>
                                    <th>License</th>
                                    <th>Expiry</th>
                                    <th>Status</th>
                                    <th>Trips</th>
                                    <th>Rating</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($drivers as $driver)
                                    <tr>
                                        <td>
                                            <strong>{{ $driver->driver_name }}</strong><br>
                                            <small class="text-muted">{{ $driver->driver_id }}</small>
                                        </td>
                                        <td>{{ $driver->driver_mobile_no ?? 'N/A' }}</td>
                                        <td>{{ $driver->driver_license_no }}</td>
                                        <td>
                                            @if ($driver->license_expiry_date)
                                                <span style="font-size:13px;">{{ \Illuminate\Support\Carbon::parse($driver->license_expiry_date)->format('M d, Y') }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $driver->driver_status }}</td>
                                        <td>{{ $driver->total_trips ?? 0 }}</td>
                                        <td>{{ number_format($driver->average_rating ?? 0, 1) }}</td>
                                        <td>
                                            <a href="{{ route('operator.drivers.show', $driver->id) }}" class="btn btn-sm btn-info">View</a>
                                            <a href="{{ route('operator.drivers.edit', $driver->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('operator.drivers.destroy', $driver->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $drivers->links() }}</div>
                </div>
            @else
                <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                    <div class="alert" style="background:transparent;color:#666;margin:0;">No drivers found. <a href="{{ route('operator.drivers.create') }}">Add your first driver</a>.</div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
