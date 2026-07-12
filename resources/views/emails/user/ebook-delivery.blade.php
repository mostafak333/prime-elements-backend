<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
<h2>Your Ebook is Ready</h2>
<p>Hello {{ $user->name }},</p>
<p>Thank you for your purchase. Your ebook "{{ $productName }}" is now available for download.</p>
<p>
    <a href="{{ $downloadUrl }}">
        Download Your Ebook
    </a>
</p>
<p>This download link will expire on {{ $expiresAt }}.</p>
<p>If you have any issues, please contact support.</p>
</body>
</html>
