@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Manage Operators</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($operators->isEmpty())
        <div class="alert alert-info">No operators for your business.</div>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Current Status</th>
                    <th>Change Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($operators as $op)
                <tr>
                    <td>{{ $op->full_name }}</td>
                    <td>{{ $op->email }}</td>
                    <td>{{ $op->account_status }}</td>
                    <td>
                        <form method="POST" action="{{ route('operator.manage.operators.update_status', $op->id) }}" class="d-inline">
                            @csrf
                            <div class="input-group">
                                <select name="status" class="form-select">
                                    <option value="pending_verification" {{ $op->account_status === 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
                                    <option value="active" {{ $op->account_status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ $op->account_status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="archived" {{ $op->account_status === 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                                <button class="btn btn-primary" type="submit">Update</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection