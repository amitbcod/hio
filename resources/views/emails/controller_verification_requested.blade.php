<p>Hello {{ $verification->owner_full_name ?? $verification->owner_email }},</p>

<p>A request has been made to link a user to your business "<strong>{{ $verification->business->legal_name }}</strong>" on Holidays.io.</p>

<p>Please review and confirm the request by visiting the following link (valid until {{ $verification->expires_at }}):</p>

<p><a href="{{ $url }}">{{ $url }}</a></p>

<p>If you did not expect this request, you may ignore this email or contact support.</p>

<p>Regards,<br>Holidays.io Team</p>
