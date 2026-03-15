<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak QR Code Gabungan (2x2 cm)</title>
    <style>
        body { 
            margin: 0; 
            padding: 0; 
            background: #f3f4f6; 
            font-family: sans-serif; 
        }
        .controls {
            text-align: center;
            padding: 20px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .btn-print {
            padding: 10px 24px;
            font-size: 16px;
            cursor: pointer;
            background: #059669; /* emerald-600 */
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .btn-print:hover { background: #047857; }
        
        .page { 
            width: 210mm; /* A4 width */
            min-height: 297mm; /* A4 height */
            padding: 10mm;
            margin: auto;
            background: white;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 2mm; /* Space between stickers for easier cutting */
            justify-content: flex-start;
        }
        
        /* 2x2 cm sticker size */
        .qr-item {
            width: 20mm;
            height: 20mm;
            border: 0.5px dashed #ccc; /* Garis potong (cut line) */
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #fff;
            page-break-inside: avoid;
        }
        
        .qr-wrapper {
            width: 13mm;
            height: 13mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qr-wrapper svg {
            width: 100% !important;
            height: 100% !important;
        }
        
        .text-series {
            font-size: 5px;
            font-weight: bold;
            margin-top: 1mm;
            text-align: center;
            line-height: 1;
            text-transform: uppercase;
        }
        
        @media print {
            body { 
                background: none; 
            }
            .controls, .no-print { 
                display: none; 
            }
            .page { 
                padding: 5mm; /* Minimum margin printable area */
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="controls no-print">
        <h2 style="margin-top:0;">Preview Cetak QR - Ukuran 2x2 cm</h2>
        <p style="color: #666; font-size: 14px; margin-bottom: 20px;">Pastikan setting printer Anda: <b>Paper Size: A4</b>, <b>Scale: 100% / Default</b>, dan <b>Margins: Minimum / None</b>.</p>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan ke PDF Sekarang</button>
    </div>

    <!-- Halaman Cetak -->
    <div class="page text-center">
        <div class="grid">
            @foreach($licenses as $license)
                <div class="qr-item">
                    <div class="qr-wrapper">
                        {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->margin(0)->generate($license->license_key) !!}
                    </div>
                    <div class="text-series" style="color: {{ $license->series->color_hex }}">
                        {{ $license->series->name }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    <script>
        // Once the print dialog is closed, user can refresh parent or close this tab
        window.onafterprint = function() {
            if(window.opener) {
                window.opener.location.reload();
            }
        };
    </script>
</body>
</html>
