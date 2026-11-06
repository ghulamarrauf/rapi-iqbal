<!DOCTYPE html>
<html lang="id">
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Halo {{ $name }},</p>
    <p>Gunakan kode berikut untuk mengatur ulang password akun RAPI-Sekolah Anda:</p>
    <p style="font-size: 28px; font-weight: bold; letter-spacing: 6px; margin: 16px 0;">{{ $code }}</p>
    <p>Kode berlaku selama {{ $ttlMinutes }} menit. Jangan bagikan kode ini kepada siapa pun.</p>
    <p style="color: #6b7280; font-size: 13px;">Jika Anda tidak merasa meminta reset password, abaikan email ini.</p>
</body>
</html>
