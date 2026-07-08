<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
<h2>Verify Your Email</h2>
<p>Hello {{ $user->name }},</p>
<p>Thank you for registering.</p>
<p>Please verify your email address by clicking the link below:</p>
<p>
    <a href="{{ config('app.url') }}/user/verify-email/{{ $token }}">
        Verify Email
    </a>
</p>
<p>This link will expire in {{ $expirationMinutes }} minutes.</p>
<p>If you did not create an account, please ignore this email.</p>
</body>
</html>
