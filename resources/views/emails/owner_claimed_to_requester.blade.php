<p>Hello,</p>
<p>The owner <strong>{{ $verification->owner_full_name ?? $verification->owner_email }}</strong> has claimed the business "<strong>{{ $verification->business->legal_name }}</strong>" and created an owner account.</p>
<p>The business will remain pending until an admin approves it. You will be notified once the business is activated.</p>
<p>Regards,<br>Holidays.io Team</p>