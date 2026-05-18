@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Operators</h3>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="mb-3">
        <a href="{{ route('admin.operators.create') }}" class="btn btn-primary">Create Operator</a>
    </div>
    <table class="table table-striped">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Business</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse($operators as $op)
                <tr>
                    <td>{{ $op->id }}</td>
                    <td>{{ $op->full_name }}</td>
                    <td>{{ $op->email }}</td>
                    <td>{{ optional($op->business)->legal_name ?? '—' }}</td>
                    <td>{{ $op->account_status }}</td>
                    <td>
                        <a href="{{ route('admin.operators.edit', $op) }}" class="btn btn-sm btn-secondary">Edit</a>
                        <form method="POST" action="{{ route('admin.operators.select', $op) }}" style="display:inline; margin-left:4px;">
                            @csrf
                            <button class="btn btn-sm btn-info">Select</button>
                        </form>
                        <form method="POST" action="{{ route('admin.operators.destroy', $op) }}" style="display:inline; margin-left:4px;" onsubmit="return confirm('Delete operator?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No operators found.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $operators->links() }}
</div>
@endsection