@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Travellers</h3>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <table class="table table-striped">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse($travellers as $traveller)
                <tr>
                    <td>{{ $traveller->id }}</td>
                    <td>{{ optional($traveller->profile)->first_name }} {{ optional($traveller->profile)->last_name }}</td>
                    <td>{{ $traveller->email }}</td>
                    <td>{{ optional($traveller->profile)->phone ?? '—' }}</td>
                    <td>
                        @if($traveller->account_suspended)
                            <span class="badge bg-danger">Suspended</span>
                        @else
                            <span class="badge bg-success">Active</span>
                        @endif
                    </td>
                    <td>{{ $traveller->created_at->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ route('admin.travellers.edit', $traveller) }}" class="btn btn-sm btn-secondary">Edit</a>
                        <form method="POST" action="{{ route('admin.travellers.suspend', $traveller) }}" style="display:inline;">
                            @csrf
                            <button class="btn btn-sm {{ $traveller->account_suspended ? 'btn-success' : 'btn-warning' }}">
                                {{ $traveller->account_suspended ? 'Activate' : 'Suspend' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No travellers found.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $travellers->links() }}
</div>
@endsection