<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to Holidays.io</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #333; line-height: 1.6;">

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 20px; text-align: center; border-radius: 8px 8px 0 0;">
    <h1 style="color: white; margin: 0; font-size: 28px;">Welcome to Holidays.io!</h1>
    <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 16px;">Your travel adventure starts here</p>
</div>

<div style="background: white; padding: 40px 30px; border: 1px solid #e0e0e0; border-top: none;">

    <p style="font-size: 16px; margin-bottom: 20px;">Hi <strong>{{ $name }}</strong>,</p>

    <p style="font-size: 15px; color: #555; margin-bottom: 20px;">
        Thank you for creating your Holidays.io account! We're excited to have you join our travel community.
    </p>

    <p style="font-size: 15px; color: #555; margin-bottom: 20px;">
        With your new account, you can now:
    </p>

    <ul style="font-size: 15px; color: #555; margin-bottom: 25px; padding-left: 20px;">
        <li style="margin-bottom: 10px;">Browse and book amazing accommodations worldwide</li>
        <li style="margin-bottom: 10px;">Discover exciting activities and experiences</li>
        <li style="margin-bottom: 10px;">Manage your trips and bookings in one place</li>
        <li style="margin-bottom: 10px;">Track your travel history and preferences</li>
        <li>Receive exclusive offers and travel deals</li>
    </ul>

    <div style="margin: 30px 0; text-align: center;">
        <a href="{{ $browseUrl }}" style="display: inline-block; background: #667eea; color: white; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600; margin-right: 10px; margin-bottom: 10px;">Start Exploring</a>
        <a href="{{ $profileUrl }}" style="display: inline-block; background: #f0f0f0; color: #333; padding: 14px 32px; text-decoration: none; border-radius: 6px; font-weight: 600;">Complete Your Profile</a>
    </div>

    <p style="font-size: 15px; color: #555; margin-top: 30px; margin-bottom: 20px;">
        Your account is set up and ready to go. If you have any questions or need assistance, please don't hesitate to contact our support team at <a href="mailto:info@holidays.io" style="color: #667eea; text-decoration: none;">info@holidays.io</a>.
    </p>

    <p style="font-size: 15px; color: #555; margin-bottom: 30px;">
        Happy travels!<br>
        <strong>The Holidays.io Team</strong>
    </p>

</div>

<div style="background: #f9f9f9; padding: 25px 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 8px 8px; font-size: 13px; color: #888;">
    <p style="margin: 0 0 10px 0;">
        If you did not create this account, please <a href="mailto:info@holidays.io" style="color: #667eea; text-decoration: none;">contact us</a> immediately.
    </p>
    <p style="margin: 0;">
        Account email: <strong>{{ $email }}</strong>
    </p>
</div>

</body>
</html>
