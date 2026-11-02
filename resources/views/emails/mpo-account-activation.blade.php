<p>Hello {{ $administrator->name }},</p>

<p>A Holidays.io MPO administrator account has been created for you.</p>

<p>Your login email is <strong>{{ $administrator->email }}</strong>. Use the secure link below to set or replace your password. This link expires in 24 hours.</p>

<p><a href="{{ $activationUrl }}">Set up your MPO password</a></p>

<p>If you were not expecting this account, please contact the Holidays.io administrator. Do not share this link.</p>
