@extends('layouts.app')

@section('title', 'Transport Management | Operator Dashboard')

@section('content')
<div class="container mt-0">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @include('operator.registration._sidebar_main')
        </div>
        <div class="col-md-9 my-pro">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;margin-top:40px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h2 style="font-weight:700;margin:0;">Transport Management</h2>
                        <p style="margin:6px 0 0 0;color:#666;">Manage your transport services.</p>
                    </div>
                    <div>
                        <a href="{{ route('operator.transport.create') }}" class="btn" style="background:#19b5b5;color:#fff;padding:8px 14px;border-radius:6px;border:none;">+ Add Vehicle</a>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div style="background:#e8f5e9;border:1px solid #66bb6a;border-radius:8px;padding:12px;margin-bottom:12px;color:#2e7d32;">{{ session('success') }}</div>
            @endif

            @if ($transports->count() > 0)
                <div style="background:#fff;border-radius:12px;padding:12px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Type</th>
                                    <th>Capacity</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transports as $transport)
                                    <tr>
                                        <td>{{ $transport->vehicle_name }}</td>
                                        <td>{{ $transport->vehicle_type }}</td>
                                        <td>{{ $transport->seating_capacity }}</td>
                                        <td>{{ ucfirst($transport->status ?? 'draft') }}</td>
                                        <td>{{ optional($transport->created_at)->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('operator.transport.show', $transport->id) }}" class="btn btn-sm btn-info">View</a>
                                            <a href="{{ route('operator.transport.edit', $transport->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $transports->links() }}</div>
                </div>
            @else
                <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
                    <div class="alert" style="background:transparent;color:#666;margin:0;">No transport records found. <a href="{{ route('operator.transport.create') }}">Create your first vehicle</a>.</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
