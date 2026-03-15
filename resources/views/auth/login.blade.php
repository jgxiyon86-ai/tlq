<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TLQ Jar Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #FDFBF7; }
        .bg-emerald-islamic { background-color: #064E3B; }
        .bg-gold-islamic { background-color: #B45309; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 relative">
    <div class="fixed inset-0 opacity-5 pointer-events-none" style="background-image: url('https://www.transparenttextures.com/patterns/islamic-art.png');"></div>

    <div class="w-full max-w-md bg-white rounded-[2.5rem] p-10 shadow-2xl relative z-10 border border-emerald-50">
        <div class="text-center mb-10">
            <div class="w-20 h-20 rounded-3xl overflow-hidden flex items-center justify-center mx-auto mb-6 shadow-xl shadow-emerald-900/30 border-4 border-amber-400">
                <img src="/logo_tlq.png" alt="TLQ Logo" class="w-full h-full object-cover">
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Assalamu'alaikum</h1>
            <p class="text-gray-500 mt-2">Selamat datang di Panel Admin TLQ Jar</p>
        </div>

        @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-50 text-red-600 text-sm border border-red-100">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition" placeholder="admin@tlq.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition" placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-emerald-islamic text-white py-4 rounded-2xl font-bold text-lg hover:bg-emerald-900 shadow-xl shadow-emerald-900/20 transition-all active:scale-[0.98]">
                Masuk ke Dashboard
            </button>
        </form>

        <p class="text-center text-xs text-gray-400 mt-10 uppercase tracking-widest">Premium Spiritual Companion</p>
    </div>
</body>
</html>
