<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PrimeElements</title>
</head>

<body
    style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #fcfbf7; color: #2c1654; margin: 0; padding: 30px;">

    <div
        style="max-width: 600px; margin: auto; background: #ffffff; padding: 40px; border: 1px solid #eae8df; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">

        <!-- Header / Logo Area -->
        <div style="border-bottom: 2px solid #2c1654; padding-bottom: 20px; margin-bottom: 30px; text-align: center;">
            <h2 style="color: #2c1654; margin: 0; font-size: 24px; font-weight: 800;">PrimeElements</h2>
            <p style="font-size: 12px; color: #666; margin: 4px 0 0;">Learning & Educational Bookstore</p>
        </div>

        <!-- Main Title & Greeting -->
        <h2 style="color: #2c1654; font-size: 20px; margin-top: 0;">Password Reset Request 🔒</h2>
        <p style="font-size: 14px; color: #444; line-height: 1.5;">Hello <strong>{{ $user->name }}</strong>,</p>
        <p style="font-size: 14px; color: #444; line-height: 1.5;">We received a request to reset the password for your
            PrimeElements account. Click the button below to choose a new password:</p>

        <!-- Reset Button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.frontend_url') }}/reset-password?token={{ $token }}"
                style="background-color: #2c1654; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: bold; display: inline-block;">
                Reset Password
            </a>
        </div>

        <!-- Info Notice -->
        <div style="background: #fcfbf7; padding: 14px; border-radius: 8px; border: 1px solid #f0efeb; margin: 25px 0;">
            <p style="font-size: 13px; color: #555; margin: 0; line-height: 1.4;">
                This password reset link will expire in <strong>{{ $expirationMinutes }} minutes</strong>.
            </p>
        </div>

        <p style="font-size: 13px; color: #666; margin-top: 30px; line-height: 1.4;">
            If you did not request a password reset, please ignore this email or contact support if you have concerns.
        </p>

        <!-- Footer -->
        <div
            style="margin-top: 40px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #eae8df; padding-top: 20px;">
            <p style="margin: 0 0 5px;">Your trusted online bookstore for educational and general books.</p>
            <p style="margin: 0;">© 2026 PrimeElements. All rights reserved.</p>
        </div>

    </div>

</body>

</html>