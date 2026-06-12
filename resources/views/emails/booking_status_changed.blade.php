<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking {{ $status }} Notification</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 0; background: #f6f7f9;">
    <div style="max-width: 680px; margin: 0 auto; padding: 24px;">
        <div style="background: #ffffff; border: 1px solid #e4e7eb; border-radius: 12px; overflow: hidden;">
            <div style="background: #667eea; color: #ffffff; padding: 24px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px;">Booking {{ $status }}</h1>
                <p style="margin: 8px 0 0; color: rgba(255,255,255,0.85);">{{ $serviceType }} update from Holidays.io</p>
            </div>

            <div style="padding: 24px;">
                @if($recipientType === 'customer')
                    <p style="font-size: 16px; margin-bottom: 18px;">Hello,</p>
                    <p style="font-size: 15px; color: #4a5568; margin-bottom: 20px;">
                        Your {{ strtolower($serviceType) }} booking has been <strong>{{ strtolower($status) }}</strong> by the operator.
                    </p>
                @else
                    <p style="font-size: 16px; margin-bottom: 18px;">Hello Admin,</p>
                    <p style="font-size: 15px; color: #4a5568; margin-bottom: 20px;">
                        An operator has updated a {{ strtolower($serviceType) }} booking status to <strong>{{ strtolower($status) }}</strong>.
                    </p>
                @endif

                <div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 22px;">
                    <p style="margin: 0 0 10px; font-size: 15px;"><strong>Booking reference:</strong> {{ $bookingReference }}</p>
                    <p style="margin: 0 0 10px; font-size: 15px;"><strong>Service type:</strong> {{ $serviceType }}</p>
                    <p style="margin: 0 0 10px; font-size: 15px;"><strong>Service name:</strong> {{ $serviceName }}</p>
                    @if($bookingDate)
                        <p style="margin: 0 0 10px; font-size: 15px;"><strong>Booked on:</strong> {{ $bookingDate }}</p>
                    @endif
                    @if($bookingAmount && $bookingCurrency)
                        <p style="margin: 0 0 10px; font-size: 15px;"><strong>Amount:</strong> {{ $bookingCurrency }} {{ number_format($bookingAmount, 2) }}</p>
                    @endif
                    @if($guestEmail)
                        <p style="margin: 0 0 10px; font-size: 15px;"><strong>Customer email:</strong> {{ $guestEmail }}</p>
                    @endif
                </div>

                <p style="font-size: 15px; color: #4a5568; margin-bottom: 20px;">
                    If you have any questions or require further assistance, please contact the Holidays.io support team.
                </p>

                <p style="font-size: 15px; color: #4a5568; margin-bottom: 0;">
                    Thank you,<br>
                    The Holidays.io Team
                </p>
            </div>
        </div>

        <div style="margin-top: 16px; text-align: center; font-size: 13px; color: #999;">
            <p style="margin: 0;">This email is intended for notification purposes only.</p>
        </div>
    </div>
</body>
</html>
