<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syafahullah, Halaman Tidak Ditemukan - TLQ Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #FDFBF7; }
        .islamic-pattern { background-image: url('https://www.transparenttextures.com/patterns/islamic-art.png'); opacity: 0.05; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative p-6">
    <div class="fixed inset-0 islamic-pattern pointer-events-none"></div>

    <div class="max-w-md w-full text-center space-y-8 animate-in fade-in zoom-in duration-700">
        <div class="w-24 h-24 bg-amber-500 rounded-[2rem] flex items-center justify-center mx-auto shadow-2xl shadow-amber-200 -rotate-3">
             <span class="text-4xl">🕌</span>
        </div>

        <div class="bg-white p-10 rounded-[3rem] shadow-sm border border-amber-50 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-amber-500 via-emerald-400 to-amber-500"></div>
            
            <h1 class="text-6xl font-black text-amber-900 mb-2">404</h1>
            <p class="text-xl font-bold text-gray-800 tracking-tight">Afwan, Ya Akhy/Ukhty...</p>
            <p class="text-gray-500 text-sm mt-4 leading-relaxed italic">
                "Halaman yang antum cari tidak ditemukan (hilang dari pandangan)."
            </p>

            <div class="mt-8">
                <a href="{{ url('/admin') }}" class="inline-flex items-center bg-gray-900 text-white px-8 py-4 rounded-2xl text-[10px] font-black tracking-[0.2em] uppercase hover:bg-amber-600 transition-all shadow-lg shadow-gray-200">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali Ke Dashboard
                </a>
            </div>
        </div>

        <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest italic">InSyaAllah Khair</p>
    </div>
</body>
</html>
