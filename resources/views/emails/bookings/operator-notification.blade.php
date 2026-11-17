<!doctype html>
<html lang="en">
<body style="margin:0;background:#f4f6f8;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:680px;margin:24px auto;background:#ffffff;border-radius:8px;overflow:hidden;">
        <div style="padding:20px 28px;background:#102a43;color:#ffffff;">
            <strong>{{ config('app.name') }}</strong>
        </div>
        <div style="padding:28px;">
            <h1 style="font-size:22px;margin-top:0;">New booking for your service</h1>
            <p>Hello {{ $recipientName }},</p>
            <p>A {{ strtolower($bookingType) }} booking includes service(s) assigned to you. Current booking status: <strong>{{ $bookingStatus }}</strong>.</p>
            <p><strong>Booking reference:</strong> {{ $bookingReference }}<br>
                <strong>{{ ucfirst($bookingType) }}:</strong> {{ $bookingName }}</p>
            @if ($startDate)
                <p><strong>Travel dates:</strong> {{ $startDate }}@if ($endDate && $endDate !== $startDate) – {{ $endDate }}@endif</p>
            @endif
            @if ($traveller)
                <h2 style="font-size:17px;">Traveller contact</h2>
                <p>
                    {{ $traveller['name'] ?? 'Traveller' }}<br>
                    @if (!empty($traveller['email'])){{ $traveller['email'] }}<br>@endif
                    @if (!empty($traveller['phone'])){{ $traveller['phone'] }}<br>@endif
                    Travellers: {{ $traveller['passengers'] ?? 1 }}
                </p>
            @endif
            <h2 style="font-size:17px;">Your assigned service booking(s)</h2>
            @foreach ($services as $service)
                <div style="padding:12px 0;border-top:1px solid #e5e7eb;">
                    <strong>{{ $service['type'] }}: {{ $service['name'] }}</strong><br>
                    Reference: {{ $service['reference'] }}<br>
                    @if ($service['date'])Date: {{ $service['date'] }}<br>@endif
                    Status: {{ $service['status'] }}
                    @foreach ($service['details'] as $label => $value)
                        <br>{{ $label }}: {{ $value }}
                    @endforeach
                </div>
            @endforeach
            <p>For assistance, contact <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.</p>
            <p>Regards,<br>{{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
