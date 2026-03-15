<!DOCTYPE html>
<html>
<head>
    <title>Transfer Lisensi Jar TLQ</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
        <h2 style="color: #064E35;">Permintaan Transfer Lisensi</h2>
        <p>Assalamu'alaikum {{ $transferRequest->owner->name }},</p>
        <p>Pengguna dengan email <strong>{{ $transferRequest->requester->email }}</strong> meminta untuk mengambil alih lisensi Jar <strong>{{ $transferRequest->license->series->name }}</strong> ({{ $transferRequest->license->license_key }}) yang saat ini terdaftar di akun Anda.</p>
        
        <p>Apakah Anda bersedia melepaskan lisensi ini agar dapat digunakan oleh beliau?</p>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="{{ route('license.transfer.action', ['token' => $transferRequest->token, 'action' => 'approve']) }}" 
               style="background-color: #059669; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-right: 10px;">
                Ya, Setujui Transfer
            </a>
            
            <a href="{{ route('license.transfer.action', ['token' => $transferRequest->token, 'action' => 'reject']) }}" 
               style="background-color: #DC2626; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Tolak
            </a>
        </div>
        
        <p style="margin-top: 30px; font-size: 12px; color: #666;">
            Jika Anda tidak merasa memiliki Jar tersebut atau tidak ingin memindahkan lisensi, silakan abaikan email ini atau klik tombol Tolak.
        </p>
        <p>Terima kasih,<br>Tim TLQ</p>
    </div>
</body>
</html>
