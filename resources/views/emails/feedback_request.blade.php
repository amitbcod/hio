<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Please review your trip</title>
  <style>
    body { font-family: sans-serif; }
    .btn { display:inline-block; padding:10px 16px; background:#007bff; color:#fff; text-decoration:none; border-radius:4px; }
  </style>
</head>
<body>
  <p>Hi {{ $trip->traveler->name ?? 'Traveler' }},</p>
  <p>Thanks for completing your trip (ID: {{ $trip->id }}). We would appreciate your feedback.</p>
  <p><a class="btn" href="{{ $url }}">Leave feedback for trip #{{ $trip->id }}</a></p>
  <p>If the button doesn't work, open this link in your browser:</p>
  <p>{{ $url }}</p>
  <p>Thanks,<br/>The Team</p>
</body>
</html>
