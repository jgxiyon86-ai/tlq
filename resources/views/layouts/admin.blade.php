<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TLQ Jar Admin - The Living Quran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #FDFBF7;
        }
        .islamic-pattern {
            background-image: url('https://www.transparenttextures.com/patterns/islamic-art.png');
            opacity: 0.05;
        }
        .bg-emerald-islamic { background-color: #064E3B; }
        .text-emerald-islamic { color: #064E3B; }
        .border-emerald-islamic { border-color: #064E3B; }
        .bg-gold-islamic { background-color: #B45309; }
        
        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="min-h-screen relative">
    <div class="fixed inset-0 islamic-pattern pointer-events-none"></div>

    <nav class="bg-emerald-islamic text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full overflow-hidden shadow-inner flex items-center justify-center border-2 border-amber-400">
                    <img src="/logo_tlq.png" alt="TLQ Logo" class="w-full h-full object-cover">
                </div>
                <h1 class="text-xl font-bold tracking-tight">TLQ Jar <span class="text-amber-400 font-light text-sm italic">Admin</span></h1>
            </div>
            <div class="flex items-center space-x-6">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-amber-400 transition {{ request()->routeIs('admin.dashboard') ? 'text-amber-400' : '' }}">Dashboard</a>
                
                <!-- Monitoring Dropdown Submenu -->
                <div class="relative group">
                    <button class="hover:text-amber-400 transition py-4 flex items-center space-x-1 {{ request()->routeIs('admin.monitoring.*') ? 'text-amber-400' : '' }}">
                        <span>Monitoring</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div class="absolute left-0 top-full w-48 hidden group-hover:block z-50 pt-1">
                        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 py-2 animate-fade-in">
                            <a href="{{ route('admin.monitoring.challenges') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition">
                                📊 Monitor Tantangan
                            </a>
                            <a href="{{ route('admin.monitoring.licenses') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50 hover:text-amber-600 transition">
                                🔑 Monitor License
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.licenses') }}" class="hover:text-amber-400 transition {{ request()->routeIs('admin.licenses') ? 'text-amber-400' : '' }}">Licenses</a>
                <a href="{{ route('admin.contents.index') }}" class="hover:text-amber-400 transition">Contents</a>
                <a href="{{ route('admin.manual-pages.index') }}" class="hover:text-amber-400 transition">Guides</a>
                <a href="{{ route('admin.users.index') }}" class="hover:text-amber-400 transition">Users</a>
                <div class="h-6 w-px bg-emerald-800"></div>
                <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="text-sm opacity-80 hover:text-amber-400">Logout</button></form>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-10 relative">
        @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border-l-4 border-green-500 text-green-700 flex items-center space-x-3 shadow-sm animate-fade-in">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-auto py-10 text-center text-gray-400 text-sm">
        <p>&copy; {{ date('Y') }} The Living Quran Jar. Premium Spiritual Companion.</p>
    </footer>
</body>
</html>
