@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <h3 class="mt-4">Feedback Submissions</h3>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>Review ID</th>
                <th>Trip ID</th>
                <th>Traveler</th>
                <th>Overall Rating</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($feedbacks as $feedback)
                <tr>
                    <td>{{ $feedback->id }}</td>
                    <td>{{ $feedback->trip_id }}</td>
                    <td>{{ optional($feedback->trip->traveler)->full_name ?? optional($feedback->trip->traveler)->email ?? 'N/A' }}</td>
                    <td>{{ $feedback->overall_rating ?? 'N/A' }}</td>
                    <td>{{ $feedback->created_at ? $feedback->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                    <td>
                        <a href="{{ route('admin.feedback.show', $feedback) }}" class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No feedback submissions found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
