@extends($layout)

@section('title', 'Create Shareable Cart')

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
        <h1>Create Shareable Cart</h1>
        <p class="text-muted">Add a title and optional expiration date for your shared cart link.</p>
        <p class="small text-muted mb-0">After creation you will be redirected to the frontend so you can add items directly through the regular booking flow.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card p-4">
        <form method="POST" action="{{ route($routePrefix . '.shared-carts.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Cart Title</label>
                <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
                <div class="form-text">Give this shared cart a short title for your customer.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Expires At</label>
                <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="form-control">
                <div class="form-text">Optional expiry date after which the link will no longer work.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Shareable Cart</button>
                <a href="{{ route($routePrefix . '.shared-carts.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </form>
    </div>
</div>
@if($routePrefix === 'operator')
        </div>
    </div>
</div>
@endif
@endsection
