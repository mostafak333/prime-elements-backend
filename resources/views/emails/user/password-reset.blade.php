<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
<h2>Password Reset</h2>
<p>Hello {{ $user->name }},</p>
<p>We received a request to reset your password.</p>
<p>Click the link below to reset your password:</p>
<p>
    <a href="{{ config('app.url') }}/user/reset-password?token={{ $token }}">
        Reset Password
    </a>
</p>
<p>This link will expire in {{ $expirationMinutes }} minutes.</p>
<p>If you did not request this reset, please ignore this email.</p>
</body>
</html>
