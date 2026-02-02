<p>Hello {{ $verification->owner_full_name ?? $verification->owner_email }},</p>

<p>A request has been made to link a user to your business "<strong>{{ $verification->business->legal_name }}</strong>" on Holidays.io.</p>

@if(isset($requester) && $requester)
    <p><strong>Requester details:</strong></p>
    <ul>
        <li>Name: {{ $requester->full_name ?? 'N/A' }}</li>
        <li>Email: {{ $requester->email ?? 'N/A' }}</li>
    </ul>
@endif

<p>Please review and confirm the request by visiting the following link (valid until {{ $verification->expires_at }}):</p>

<p><a href="{{ $url }}" style="display:inline-block;padding:10px 16px;background:#28a745;color:#fff;border-radius:4px;text-decoration:none;">Review & Approve</a></p>

<p>If you did not expect this request, you may ignore this email or contact support.</p>

<p>Regards,<br>Holidays.io Team</p>
