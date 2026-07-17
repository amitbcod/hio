@extends('layouts.app')

@section('title', 'Transport Step 2 - Car Rental Pricing | Operator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3 net-section">
            @php $currentStep = '2b'; @endphp
            @include('operator.transport._steps_sidebar')
        </div>
        <div class="col-md-9">
            <div style="background:#fff;border-radius:16px;padding:16px;box-shadow:0 2px 16px rgba(0,0,0,0.07);margin-bottom:16px;">
                <h2 style="font-weight:700;margin:0;">Step 2-B: Car Rental Pricing</h2>
                <p style="margin:8px 0 0 0;color:#666;">Set per-hour and block pricing for car rental services for this vehicle.</p>
            </div>

            <form method="POST" action="{{ route('operator.transport.step2.car_rental.save', $transport->id) }}">
                @csrf
                <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.04);margin-bottom:16px;">
                    @php
                        $prices = old('car_rental_prices', $transport->car_rental_prices ?? []);
                    @endphp

                    <div class="mb-3 row">
                        <label class="col-md-3 form-label">Per Hour Price (USD)</label>
                        <div class="col-md-9"><input type="number" step="0.01" min="0" name="car_rental_prices[per_hour]" class="form-control" value="{{ $prices['per_hour'] ?? '' }}"></div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-3 form-label">4 Hour Price (USD)</label>
                        <div class="col-md-9"><input type="number" step="0.01" min="0" name="car_rental_prices[per_4h]" class="form-control" value="{{ $prices['per_4h'] ?? '' }}"></div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-3 form-label">8 Hour Price (USD)</label>
                        <div class="col-md-9"><input type="number" step="0.01" min="0" name="car_rental_prices[per_8h]" class="form-control" value="{{ $prices['per_8h'] ?? '' }}"></div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-3 form-label">12 Hour Price (USD)</label>
                        <div class="col-md-9"><input type="number" step="0.01" min="0" name="car_rental_prices[per_12h]" class="form-control" value="{{ $prices['per_12h'] ?? '' }}"></div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-3 form-label">24 Hour Price (USD)</label>
                        <div class="col-md-9"><input type="number" step="0.01" min="0" name="car_rental_prices[per_24h]" class="form-control" value="{{ $prices['per_24h'] ?? '' }}"></div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Seasonal Pricing</label>
                        <p class="text-muted small mb-2">If a seasonal date range matches the booking date, those values override the default prices. Otherwise the current base prices are used.</p>
                        <div id="car-rental-seasonal-list">
                            @php $seasonalEntries = $prices['seasonal'] ?? []; @endphp
                            @foreach($seasonalEntries as $seasonIndex => $season)
                                <div class="season-row mb-2">
                                    <div class="row gx-2">
                                        <div class="col-md-2"><input type="date" name="car_rental_prices[seasonal][{{ $seasonIndex }}][start]" class="form-control" value="{{ $season['start'] ?? $season['start_date'] ?? '' }}"></div>
                                        <div class="col-md-2"><input type="date" name="car_rental_prices[seasonal][{{ $seasonIndex }}][end]" class="form-control" value="{{ $season['end'] ?? $season['end_date'] ?? '' }}"></div>
                                        <div class="col-md-2"><input type="number" step="0.01" min="0" name="car_rental_prices[seasonal][{{ $seasonIndex }}][per_hour]" class="form-control" placeholder="Per hour" value="{{ $season['per_hour'] ?? '' }}"></div>
                                        <div class="col-md-2"><input type="number" step="0.01" min="0" name="car_rental_prices[seasonal][{{ $seasonIndex }}][per_4h]" class="form-control" placeholder="4h" value="{{ $season['per_4h'] ?? '' }}"></div>
                                        <div class="col-md-2"><input type="number" step="0.01" min="0" name="car_rental_prices[seasonal][{{ $seasonIndex }}][per_8h]" class="form-control" placeholder="8h" value="{{ $season['per_8h'] ?? '' }}"></div>
                                        <div class="col-md-2 d-flex align-items-center"><button type="button" class="btn btn-sm btn-danger w-100" onclick="this.closest('.season-row').remove();">Remove</button></div>
                                    </div>
                                    <div class="row gx-2 mt-2">
                                        <div class="col-md-3"><input type="number" step="0.01" min="0" name="car_rental_prices[seasonal][{{ $seasonIndex }}][per_12h]" class="form-control" placeholder="12h" value="{{ $season['per_12h'] ?? '' }}"></div>
                                        <div class="col-md-3"><input type="number" step="0.01" min="0" name="car_rental_prices[seasonal][{{ $seasonIndex }}][per_24h]" class="form-control" placeholder="24h" value="{{ $season['per_24h'] ?? '' }}"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-secondary" onclick="addCarRentalSeason();">Add Seasonal Price</button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn" style="background:#19b5b5;color:#fff;padding:10px 20px;border-radius:4px;border:none;">Save Car Rental Prices</button>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
function addCarRentalSeason() {
    const container = document.getElementById('car-rental-seasonal-list');
    if (!container) {
        return;
    }

    const seasonCount = container.querySelectorAll('.season-row').length;
    const row = document.createElement('div');
    row.className = 'season-row mb-2';
    row.innerHTML = `
        <div class="row gx-2">
            <div class="col-md-2"><input type="date" name="car_rental_prices[seasonal][${seasonCount}][start]" class="form-control"></div>
            <div class="col-md-2"><input type="date" name="car_rental_prices[seasonal][${seasonCount}][end]" class="form-control"></div>
            <div class="col-md-2"><input type="number" step="0.01" min="0" name="car_rental_prices[seasonal][${seasonCount}][per_hour]" class="form-control" placeholder="Per hour"></div>
            <div class="col-md-2"><input type="number" step="0.01" min="0" name="car_rental_prices[seasonal][${seasonCount}][per_4h]" class="form-control" placeholder="4h"></div>
            <div class="col-md-2"><input type="number" step="0.01" min="0" name="car_rental_prices[seasonal][${seasonCount}][per_8h]" class="form-control" placeholder="8h"></div>
            <div class="col-md-2 d-flex align-items-center"><button type="button" class="btn btn-sm btn-danger w-100" onclick="this.closest('.season-row').remove();">Remove</button></div>
        </div>
        <div class="row gx-2 mt-2">
            <div class="col-md-3"><input type="number" step="0.01" min="0" name="car_rental_prices[seasonal][${seasonCount}][per_12h]" class="form-control" placeholder="12h"></div>
            <div class="col-md-3"><input type="number" step="0.01" min="0" name="car_rental_prices[seasonal][${seasonCount}][per_24h]" class="form-control" placeholder="24h"></div>
        </div>
    `;
    container.appendChild(row);
}
</script>
@endpush

@endsection
