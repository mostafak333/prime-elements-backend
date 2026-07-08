<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
<h2>Admin Invitation</h2>
<p>Hello {{ $admin->name }},</p>
<p>You have been invited to join the admin panel.</p>
<p>Please set your password using the link below:</p>
<p>
    <a href="{{ config('app.url') }}/admin/set-password?token={{ $token }}">
        Set Your Password
    </a>
</p>
<p>This link will expire in {{ $expirationMinutes }} minutes.</p>
<p>If you did not expect this invitation, please ignore this email.</p>
</body>
</html>
