<p>Dear Admin,</p>

<p>An operator has requested approval for their business:</p>
<ul>
    <li><strong>Operator:</strong> {{ $operator->full_name }} ({{ $operator->email }})</li>
    <li><strong>Business:</strong> {{ optional($operator->business)->legal_name ?? 'N/A' }}</li>
</ul>

<p>Please review and take action in the admin dashboard:</p>
<p><a href="{{ config('app.url') }}/admin">Go to Admin Dashboard</a></p>

<p>Regards,<br>HIO System</p>