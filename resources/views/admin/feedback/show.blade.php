@extends('layouts.admin')

@section('content')
<div class="col-md-10 offset-md-1">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h3>Feedback Detail</h3>
        <a href="{{ route('admin.feedback.index') }}" class="btn btn-secondary btn-sm">Back to Feedback List</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Traveler</h5>
            <p class="small">
                Name: {{ optional($review->trip->traveler)->full_name ?? optional($review->trip->traveler)->email ?? 'N/A' }}
                &nbsp; | &nbsp;
                Email: {{ optional($review->trip->traveler)->email ?? 'N/A' }}
            </p>

            <h5>Overall Rating</h5>
            <p>Stars: {{ $review->overall_rating ?? 'N/A' }} <span class="text-muted">(1=Poor, 5=Excellent)</span></p>

            <h5>Trip Comments</h5>
            @php
                $overallReview = json_decode($review->overall_review, true) ?: [];
            @endphp
            <p><strong>How did you hear about us?</strong><br>{{ $overallReview['hear_about_us'] ?? 'N/A' }}</p>
            <p><strong>Other comments:</strong><br>{{ $overallReview['trip_comments'] ?? 'N/A' }}</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Accommodation Reviews</h5>
            @php
                $accommodations = $review->items->where('service_type', 'accommodation');
            @endphp
            @if($accommodations->isEmpty())
                <p>No accommodation reviews submitted.</p>
            @else
                @foreach($accommodations as $item)
                    @php
                        $booking = optional($review->trip->accommodationBookings)->firstWhere('id', $item->service_id);
                        $serviceName = optional(optional($booking)->accommodation)->property_name ?? ('Accommodation #' . $item->service_id);
                        $criteria = is_array($item->criteria) ? $item->criteria : [];
                        $statusColor = $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : 'warning');
                    @endphp
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6>Accommodation: {{ $serviceName }}</h6>
                            <span class="badge badge-{{ $statusColor }}">{{ ucfirst($item->status) }}</span>
                        </div>
                        <table class="table table-sm mb-3">
                            <tbody>
                                @foreach($criteria as $key => $value)
                                    <tr>
                                        <td>{{ ucwords(str_replace(['_','-'], ' ', $key)) }}</td>
                                        <td>{{ $value ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td>Comments</td>
                                    <td>{{ $item->review ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="btn-group" role="group">
                            @if($item->status !== 'approved')
                                <form action="{{ route('admin.feedback.item.update-status', $item) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                            @endif
                            @if($item->status !== 'rejected')
                                <form action="{{ route('admin.feedback.item.update-status', $item) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                            @endif
                            @if($item->status !== 'pending')
                                <form action="{{ route('admin.feedback.item.update-status', $item) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="pending">
                                    <button type="submit" class="btn btn-sm btn-secondary">Mark Pending</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Activity Reviews</h5>
            @php
                $activities = $review->items->where('service_type', 'activity');
            @endphp
            @if($activities->isEmpty())
                <p>No activity reviews submitted.</p>
            @else
                @foreach($activities as $item)
                    @php
                        $booking = optional($review->trip->activityBookings)->firstWhere('id', $item->service_id);
                        $serviceName = optional(optional($booking)->activity)->activity_name ?? ('Activity #' . $item->service_id);
                        $criteria = is_array($item->criteria) ? $item->criteria : [];
                        $statusColor = $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : 'warning');
                    @endphp
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6>Activity: {{ $serviceName }}</h6>
                            <span class="badge badge-{{ $statusColor }}">{{ ucfirst($item->status) }}</span>
                        </div>
                        <table class="table table-sm mb-3">
                            <tbody>
                                @foreach($criteria as $key => $value)
                                    <tr>
                                        <td>{{ ucwords(str_replace(['_','-'], ' ', $key)) }}</td>
                                        <td>{{ $value ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td>Comments</td>
                                    <td>{{ $item->review ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="btn-group" role="group">
                            @if($item->status !== 'approved')
                                <form action="{{ route('admin.feedback.item.update-status', $item) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                            @endif
                            @if($item->status !== 'rejected')
                                <form action="{{ route('admin.feedback.item.update-status', $item) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                            @endif
                            @if($item->status !== 'pending')
                                <form action="{{ route('admin.feedback.item.update-status', $item) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="pending">
                                    <button type="submit" class="btn btn-sm btn-secondary">Mark Pending</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
