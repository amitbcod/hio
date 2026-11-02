@extends('layouts.mpo-portal')

@section('title', $definition['name'])

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="text-muted mb-0">{{ $definition['description'] }}</p>
        </div>
        <span class="badge text-bg-secondary">
            {{ isset($definition['booking_type']) ? 'Booking access' : 'Read only' }}
        </span>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        @foreach($definition['columns'] as $column)
                            <th scope="col">{{ \Illuminate\Support\Str::headline($column) }}</th>
                        @endforeach
                        @if(isset($definition['booking_type']))
                            <th scope="col">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            @foreach($definition['columns'] as $column)
                                <td>{{ $record[$column] ?? '—' }}</td>
                            @endforeach
                            @if(isset($definition['booking_type']))
                                <td>
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ route('mpo.operations.booking.show', ['serviceType' => $definition['booking_type'], 'booking' => $record['id']]) }}">
                                        View / Manage
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-muted py-4" colspan="{{ count($definition['columns']) + (isset($definition['booking_type']) ? 1 : 0) }}">
                                No records found for this country.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $records->links() }}</div>
    </div>
@endsection
