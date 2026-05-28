@extends($layout)

@section('title', 'Manage Shareable Cart')

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
    <div class="mb-4">
        <h1>Manage Shared Cart</h1>
        <p class="text-muted">Review items, generate the public link, and send it to your customer.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card p-4 mb-4">
        <h2 class="h5">{{ $sharedCart->title }}</h2>
        <p class="mb-2"><strong>Status:</strong> {{ $sharedCart->status }}</p>
        <p class="mb-2"><strong>Expires:</strong> {{ optional($sharedCart->expires_at)->format('Y-m-d') ?? 'Never' }}</p>
        <p class="mb-2"><strong>Items:</strong> {{ count($sharedCart->items ?? []) }}</p>

        <div class="input-group mt-3">
            <input type="text" id="shareableLink" class="form-control" readonly value="{{ route('frontend.booking.shared', $sharedCart->token) }}">
            <button type="button" class="btn btn-outline-secondary" onclick="copySharedLink()">Copy Link</button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card p-4 mb-4">
                <h3 class="h5 mb-3">Shared Cart Items</h3>

                @if(empty($sharedCart->items))
                    <div class="alert alert-warning">No items have been added yet.</div>
                @else
                    <div class="list-group">
                        @foreach($sharedCart->items as $item)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <h5 class="mb-1">{{ $item['title'] ?? ucfirst($item['type']) }}</h5>
                                        <p class="mb-1 small text-muted">{{ ucfirst($item['type']) }} · {{ $item['currency'] ?? 'USD' }} {{ number_format($item['total_price'] ?? 0, 2) }}</p>
                                        <p class="mb-1 small">{{ $item['check_in_display'] ?? $item['check_in'] ?? '' }} @if(!empty($item['check_out_display'])) → {{ $item['check_out_display'] }}@endif</p>
                                    </div>
                                    <form method="POST" action="{{ route($routePrefix . '.shared-carts.items.remove', ['sharedCart' => $sharedCart, 'itemKey' => $item['cart_key']]) }}" onsubmit="return confirm('Remove this item from the shared cart?');">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card p-4">
                <h3 class="h5 mb-3">Add Item</h3>
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route($routePrefix . '.shared-carts.items.store', $sharedCart) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Item Type</label>
                        <select name="type" id="cartItemType" class="form-select" required>
                            <option value="accommodation" selected>Accommodation</option>
                            <option value="activity">Activity</option>
                        </select>
                    </div>

                    <div class="mb-3 item-group item-accommodation">
                        <label class="form-label">Accommodation</label>
                        <select name="accommodation_id" class="form-select">
                            <option value="">Select a property</option>
                            @foreach($accommodations as $accommodation)
                                <option value="{{ $accommodation->id }}">{{ $accommodation->property_name ?? 'Property #' . $accommodation->id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 item-group item-activity" style="display: none;">
                        <label class="form-label">Activity</label>
                        <select name="activity_id" class="form-select">
                            <option value="">Select an activity</option>
                            @foreach($activities as $activity)
                                <option value="{{ $activity->id }}">{{ $activity->activity_name ?? 'Activity #' . $activity->id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Item Title</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="Optional heading for the item">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Check-in / Activity Date</label>
                        <input type="date" name="check_in" value="{{ old('check_in') }}" class="form-control" required>
                    </div>

                    <div class="mb-3 item-group item-accommodation">
                        <label class="form-label">Check-out</label>
                        <input type="date" name="check_out" value="{{ old('check_out') }}" class="form-control">
                    </div>

                    <div class="mb-3 item-group item-accommodation">
                        <label class="form-label">Nights</label>
                        <input type="number" name="nights" value="{{ old('nights', 1) }}" min="1" class="form-control">
                    </div>

                    <div class="mb-3 item-group item-accommodation">
                        <label class="form-label">Room Name</label>
                        <input type="text" name="room_name" value="{{ old('room_name', 'Standard Room') }}" class="form-control">
                    </div>

                    <div class="mb-3 item-group item-activity">
                        <label class="form-label">Variant Name</label>
                        <input type="text" name="variant_name" value="{{ old('variant_name') }}" class="form-control" placeholder="Optional activity variant">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adults</label>
                        <input type="number" name="adults" value="{{ old('adults', 1) }}" min="1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Children</label>
                        <input type="number" name="children" value="{{ old('children', 0) }}" min="0" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Infants</label>
                        <input type="number" name="infants" value="{{ old('infants', 0) }}" min="0" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Price</label>
                        <input type="number" step="0.01" name="total_price" value="{{ old('total_price', 0) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Currency</label>
                        <input type="text" name="currency" value="{{ old('currency', 'USD') }}" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Item</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateItemFields() {
    const type = document.getElementById('cartItemType').value;
    document.querySelectorAll('.item-group').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.item-' + type).forEach(el => el.style.display = 'block');
}

document.getElementById('cartItemType').addEventListener('change', updateItemFields);
updateItemFields();

function copySharedLink() {
    const input = document.getElementById('shareableLink');
    navigator.clipboard.writeText(input.value).then(function() {
        alert('Link copied to clipboard.');
    }, function() {
        prompt('Copy the link manually:', input.value);
    });
}
</script>
@endpush
@if($routePrefix === 'operator')
        </div>
    </div>
</div>
@endif
