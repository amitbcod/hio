<p>Admin,</p>
<p>The owner <strong>{{ $verification->owner_full_name ?? $verification->owner_email }}</strong> has claimed the business "<strong>{{ $verification->business->legal_name }}</strong>" (Business ID: {{ $verification->business->business_id }}).</p>
<p>Please review and approve the business in the admin dashboard: <a href="{{ url('/admin/dashboard') }}">Admin Dashboard</a></p>
<p>Regards,<br>Holidays.io System</p>