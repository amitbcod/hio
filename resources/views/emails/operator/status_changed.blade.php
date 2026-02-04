<p>Hi {{ $operator->full_name }},</p>

<p>Your account status for {{ $operator->business_legal_name ?? 'the business' }} has changed from <strong>{{ $oldStatus }}</strong> to <strong>{{ $newStatus }}</strong>.</p>

<p>If you have any questions, please contact the owner or support team.</p>

<p>Regards,<br/>Operator Portal</p>