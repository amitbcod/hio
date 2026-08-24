@extends('layouts.admin')

@php $sidebar = 'admin.packages._steps_sidebar'; $currentStep = 6; @endphp

@section('content')
<div class="container mt-4 mb-5">
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px; overflow:hidden;">
        <div class="card-body p-4">
            <h2 class="mb-1 fw-bold" style="font-size:2rem; color:#1f2a37;">Step 6: Day-wise Itinerary</h2>
            <p class="mb-0 text-muted">Provide a detailed description of the itinerary for each day to display on the frontend</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.packages.step6.store', $package->id) }}">
        @csrf

        <div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden;">
            <div class="card-body p-4">
                <div class="mb-3">
                    <a class="btn btn-success">Step 6: Day-wise Itinerary</a>
                </div>

                @for($i = 0; $i < $days; $i++)
                    @php $val = old('day_descriptions.' . $i, $dayDescriptions[$i] ?? ''); @endphp
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Day {{ $i + 1 }} Description *</label>
                        <textarea name="day_descriptions[{{ $i }}]" rows="4" class="form-control" placeholder="Describe the itinerary, activities and sightseeing highlights for Day {{ $i + 1 }}...">{{ $val }}</textarea>
                    </div>
                @endfor

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.packages.step5', $package->id) }}" class="btn btn-outline-secondary">Back</a>
                    <div>
                        <button type="submit" class="btn btn-success">Next: Payment & Policies</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
