<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print QR - {{ $license->license_key }}</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 20px; }
        .label { 
            width: 150px; 
            padding: 10px; 
            border: 1px dashed #ccc; 
            display: inline-block;
            border-radius: 10px;
        }
        .qr svg { width: 100px; height: 100px; }
        .code { font-weight: bold; font-size: 14px; margin-top: 5px; color: #064E3B; }
        .series { font-size: 10px; text-transform: uppercase; color: #B45309; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()">Cetak Label</button>
    </div>

    <div class="label">
        <div class="series">{{ $license->series->name }}</div>
        <div class="qr">
            {!! QrCode::size(100)->generate($license->license_key) !!}
        </div>
        <div class="code">{{ $license->license_key }}</div>
        <div style="font-size: 8px; margin-top: 2px;">Scan to Activate TLQ Jar</div>
    </div>
</body>
</html>
