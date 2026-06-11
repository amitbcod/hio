<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #f9f9f9;
        }
        .content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background: #1659c2;
            color: white;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            margin: 20px 0;
            font-weight: 600;
        }
        .button:hover {
            background: #0f4eb1;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h1>Password reset request</h1>
            <p>Hello {{ $name }},</p>
            <p>We received a request to reset your Holidays.io password. Click the button below to set a new password for your traveler account.</p>
            <p style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button">Reset your password</a>
            </p>
            <p>If you did not request a password reset, you can safely ignore this email.</p>
            <p>If the button does not work, copy and paste the following link into your browser:</p>
            <p><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
            <div class="footer">
                <p>Questions? Contact us at <a href="mailto:info@holidays.io">info@holidays.io</a>.</p>
                <p>Thank you,<br>{{ config('app.name') }} Team</p>
            </div>
        </div>
    </div>
</body>
</html>
