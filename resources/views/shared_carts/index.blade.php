@extends($layout)

@section('title', 'Shareable Cart Links')

@section('content')
@if($routePrefix === 'operator')
<div class="container-fluid">
    <div class="row">
        <div id="sidebar" class="col-md-3 net-section">
            @include('operator.registration._sidebar_main')
        </div>
        <div class="col-md-9" style="margin-top: 30px;">
@endif
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Shareable Cart Links</h1>
            <p class="text-muted">Create public cart links for customers to load curated travel items and continue to checkout.</p>
        </div>
        <a href="{{ route($routePrefix . '.shared-carts.create') }}" class="btn btn-primary">Create New Shareable Cart</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($sharedCarts->isEmpty())
        <div class="card p-4">
            <p class="mb-0">No shareable carts have been created yet. Click the button above to create one.</p>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Expires</th>
                            <th>Link</th>
                            <!-- <th>Actions</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sharedCarts as $cart)
                            <tr>
                                <td>{{ $cart->title }}</td>
                                <td>{{ $cart->status }}</td>
                                <td>{{ count($cart->items ?? []) }}</td>
                                <td>{{ optional($cart->expires_at)->format('Y-m-d') ?? 'Never' }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('frontend.booking.shared', $cart->token) }}" target="_blank">Open</a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('{{ route('frontend.booking.shared', $cart->token) }}')">Copy</button>
                                    </div>
                                </td>
                                <!-- <td>
                                    <a href="{{ route($routePrefix . '.shared-carts.show', $cart) }}" class="btn btn-sm btn-secondary">Manage</a>
                                </td> -->
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@if($routePrefix === 'operator')
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Link copied to clipboard.');
    }, function() {
        prompt('Copy the link manually:', text);
    });
}
</script>
@endpush
