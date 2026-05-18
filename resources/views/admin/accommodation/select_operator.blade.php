@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="page-header mb-4">
        <h1>Select Operator or Business</h1>
        <p class="text-muted">Choose the operator/business you want to manage accommodations and activities for.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Operator</th>
                        <th>Business</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($operators as $operator)
                        <tr>
                            <td>{{ $operator->full_name }}</td>
                            <td>{{ optional($operator->business)->legal_name ?? 'N/A' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $operator->account_status)) }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.accommodation.set-operator') }}">
                                    @csrf
                                    <input type="hidden" name="operator_id" value="{{ $operator->id }}">
                                    <button type="submit" class="btn btn-primary btn-sm">Select</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $operators->links() }}
    </div>
</div>
@endsection
