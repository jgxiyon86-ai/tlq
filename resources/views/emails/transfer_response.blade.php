<!DOCTYPE html>
<html>
<head>
    <title>TLQ License Transfer</title>
</head>
<body style="font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background-color: #f6f5f0;">
    <div style="text-align: center; padding: 40px; background: white; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 400px;">
        <div style="font-size: 50px; margin-bottom: 20px;">
            @if($status == 'success') ✅ @else ℹ️ @endif
        </div>
        <h2 style="color: #064E35; margin-bottom: 10px;">{{ $title }}</h2>
        <p style="color: #666; line-height: 1.5;">{{ $message }}</p>
        <div style="margin-top: 30px;">
            <p style="font-size: 14px; color: #999;">Terima kasih atas kerja samanya dalam menghidupkan Al-Quran.</p>
        </div>
    </div>
</body>
</html>
