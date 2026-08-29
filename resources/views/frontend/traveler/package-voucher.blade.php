<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Voucher</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 32px;
            color: #1f2937;
            background: #f9fafb;
        }
        .card {
            max-width: 760px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.04);
        }
        h1 {
            margin: 0 0 8px;
            font-size: 28px;
            color: #0f172a;
        }
        .muted {
            color: #6b7280;
        }
        .meta {
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }
        .meta-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 14px;
            background: #f8fafc;
        }
        .label {
            display: block;
            font-size: 11px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
        }
        .value {
            font-size: 16px;
            font-weight: 600;
        }
        ul {
            margin: 18px 0 0 18px;
            padding: 0;
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Package Voucher</h1>
        <div class="muted">{{ $trip->name ?? 'Trip' }} · {{ $packageLineItem->booking_reference ?? 'Package Booking' }}</div>

        <div class="meta">
            <div class="meta-item">
                <span class="label">Package</span>
                <span class="value">{{ $package?->name ?? 'Package' }}</span>
            </div>
            <div class="meta-item">
                <span class="label">Travelers</span>
                <span class="value">{{ count($travellerNames ?? []) }}</span>
            </div>
            <div class="meta-item">
                <span class="label">Trip</span>
                <span class="value">{{ $trip->id ?? 'N/A' }}</span>
            </div>
        </div>

        <ul>
            @forelse($travellerNames ?? [] as $travellerName)
                <li>{{ $travellerName }}</li>
            @empty
                <li>No travelers attached to this package.</li>
            @endforelse
        </ul>
    </div>
</body>
</html>
