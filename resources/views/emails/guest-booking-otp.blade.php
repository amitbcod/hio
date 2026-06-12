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
        .otp-code {
            background: #f0f7f7;
            border: 2px solid #19b5b5;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #19b5b5;
        }
        .button {
            display: inline-block;
            background: #19b5b5;
            color: white;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            margin: 20px 0;
            font-weight: 600;
        }
        .button:hover {
            background: #138080;
        }
        .details {
            background: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #19b5b5;
            margin: 20px 0;
        }
        .details p {
            margin: 8px 0;
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
            <h1>Booking Confirmation</h1>

            <p>Hello {{ $firstName }},</p>

            <p>Thank you for your booking! Your reservation has been confirmed.</p>

            <div class="details">
                <p><strong>Booking Details</strong></p>
                <p><strong>Booking Reference:</strong> {{ $bookingRef }}</p>
                <p><strong>Property:</strong> {{ $accommodationName }}</p>
                <p><strong>Check-in:</strong> {{ $checkInDate }}</p>
                <p><strong>Check-out:</strong> {{ $checkOutDate }}</p>
            </div>

            <h2>Access Your Trip Details</h2>

            <p>To view and manage your booking, use this one-time verification link:</p>

            <!-- <div class="otp-code">{{ $otp }}</div> -->

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ $tripUrl }}" class="button">View Your Booking</a>
            </p>

            <p><strong>Your Email:</strong> {{ $email }}</p>
            

            <p style="color: #999; font-size: 12px;">
                This verification link will expire in 15 minutes and can be used one time only to access your guest trip details.
            </p>
            <p style="color: #999; font-size: 12px;">
                You can access your guest trip bookings and manage them by generating a new verification link 
                <a href="{{ $verificationUrl }}" style="color:#007bff;">click here</a>.
            </p>
            <div class="footer">
                <p><strong>Questions?</strong></p>
                <p>If you have any questions about your booking, please contact us at support@holidaysio.com</p>
                <p>Thanks,<br>{{ config('app.name') }} Team</p>
            </div>
        </div>
    </div>
</body>
</html>

