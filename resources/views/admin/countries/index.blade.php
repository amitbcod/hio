@extends('layouts.admin')

@section('content')
<style>
    .countries-table {
        table-layout: fixed;
        width: 100%;
    }

    .countries-table th,
    .countries-table td {
        vertical-align: middle;
    }

    .countries-table .country-name {
        width: 22%;
    }

    .countries-table .country-iso {
        width: 9%;
    }

    .countries-table .country-currency {
        width: 12%;
    }

    .countries-table .country-timezone {
        width: 35%;
    }

    .countries-table .country-status {
        width: 12%;
    }

    .countries-table .country-actions {
        width: 10%;
        text-align: center;
        white-space: nowrap;
    }

    .countries-table .timezone-value {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Countries</h1>
        <a href="{{ route('admin.countries.create') }}" class="btn btn-primary">Add Country</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="table-responsive">
        <table class="table table-striped align-middle countries-table">
            <thead>
                <tr>
                    <th class="country-name">Country</th>
                    <th class="country-iso">ISO code</th>
                    <th class="country-currency">Currency</th>
                    <th class="country-timezone">Timezone</th>
                    <th class="country-status">Status</th>
                    <th class="country-actions"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($countries as $country)
                    @php
                        $timezones = collect(explode(',', (string) $country->time_zone))
                            ->map(fn ($timezone) => trim($timezone))
                            ->filter()
                            ->values();
                        $timezoneSummary = $timezones->first();
                        if ($timezones->count() > 1) {
                            $timezoneSummary .= ' (+' . ($timezones->count() - 1) . ' more)';
                        }
                    @endphp
                    <tr>
                        <td class="country-name text-break">{{ $country->name }}</td>
                        <td class="country-iso">{{ $country->iso_code }}</td>
                        <td class="country-currency">{{ $country->currency_code ?: '—' }}</td>
                        <td class="country-timezone" title="{{ $timezones->join(', ') ?: '—' }}">
                            <span class="timezone-value">{{ $timezoneSummary ?: '—' }}</span>
                        </td>
                        <td class="country-status">{{ $country->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="country-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.countries.edit', $country) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No countries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $countries->links('pagination::bootstrap-5') }}
</div>
@endsection
