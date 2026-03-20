@extends('layouts.admin')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-8 rounded-[2rem] shadow-sm border border-emerald-50 mb-8 overflow-hidden relative">
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-50 rounded-full -mr-32 -mt-32 opacity-50"></div>
        <div class="relative z-10">
            <h2 class="text-3xl font-black text-gray-900 tracking-tight text-emerald-900 uppercase">Ahlan wa Sahlan, <span class="text-emerald-600 italic underline">{{ auth()->user()->name }}</span></h2>
            <p class="text-gray-500 text-sm mt-1 uppercase tracking-widest font-bold opacity-60">Dashboard Monitoring TLQ</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3 relative z-10">
            <div class="px-6 py-3 bg-emerald-600 text-white rounded-2xl shadow-lg shadow-emerald-100 flex items-center">
                <span class="text-xs font-black tracking-widest uppercase">{{ date('d M Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Santri -->
        <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-50 group hover:border-emerald-200 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition">👥</div>
                <span class="text-[10px] font-black text-blue-400 bg-blue-50 px-2 py-1 rounded-lg uppercase tracking-widest">Total User</span>
            </div>
            <h3 class="text-4xl font-black text-gray-900 tracking-tighter">{{ number_format($totalUsers) }}</h3>
            <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-widest leading-none">Santri Terdaftar</p>
        </div>

        <!-- Tantangan Aktif -->
        <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-50 group hover:border-amber-200 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition">🔥</div>
                <span class="text-[10px] font-black text-amber-400 bg-amber-50 px-2 py-1 rounded-lg uppercase tracking-widest">Challenge</span>
            </div>
            <h3 class="text-4xl font-black text-gray-900 tracking-tighter">{{ number_format($activeChallenges) }}</h3>
            <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-widest leading-none">Sedang Berjalan</p>
        </div>

        <!-- License Tergenerate -->
        <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-50 group hover:border-purple-200 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition">🔑</div>
                <span class="text-[10px] font-black text-purple-400 bg-purple-50 px-2 py-1 rounded-lg uppercase tracking-widest">Licenses</span>
            </div>
            <h3 class="text-4xl font-black text-gray-900 tracking-tighter">{{ number_format($totalLicenses) }}</h3>
            <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-widest leading-none">Total Generated</p>
        </div>

        <!-- Total Jurnal -->
        <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-50 group hover:border-emerald-200 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition">📖</div>
                <span class="text-[10px] font-black text-emerald-400 bg-emerald-50 px-2 py-1 rounded-lg uppercase tracking-widest">Jurnal</span>
            </div>
            <h3 class="text-4xl font-black text-gray-900 tracking-tighter">{{ number_format($totalJournalEntries) }}</h3>
            <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-widest leading-none">Total Postingan</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-gray-50">
            <h4 class="text-sm font-black text-gray-800 uppercase tracking-[0.2em] mb-8 text-center bg-gray-50 py-2 rounded-xl">📈 Pertumbuhan Santri (7 Hari)</h4>
            <div class="h-64 relative">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>
        <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-gray-50">
            <h4 class="text-sm font-black text-gray-800 uppercase tracking-[0.2em] mb-8 text-center bg-gray-50 py-2 rounded-xl">📊 Aktivitas Jurnal Harian</h4>
            <div class="h-64 relative">
                <canvas id="activityChartDashboard"></canvas>
            </div>
        </div>
    </div>

    <!-- Distribusi Series List -->
    <div class="bg-white rounded-[3rem] p-8 shadow-sm border border-gray-100">
        <h3 class="text-sm font-black text-gray-800 uppercase tracking-[0.2em] mb-8 flex items-center">
            <div class="w-8 h-8 rounded-xl bg-gray-900 text-white flex items-center justify-center mr-3 text-xs">🚀</div>
            Distribusi Cetak Series Lisensi
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
            @foreach($series as $s)
            <div class="bg-gray-50 rounded-[2rem] p-6 flex flex-col items-center text-center group hover:bg-white hover:shadow-xl hover:shadow-gray-100 transition-all border border-transparent hover:border-gray-100">
                <div class="w-14 h-14 rounded-[1.2rem] mb-4 flex items-center justify-center text-xl text-white shadow-lg shadow-gray-200 group-hover:scale-110 transition-transform" style="background-color: {{ $s->color_hex }}">
                    {{ strtoupper(substr($s->name, 0, 1)) }}
                </div>
                <h4 class="font-black text-gray-800 text-sm tracking-tight uppercase">{{ $s->name }}</h4>
                <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-widest">{{ number_format($s->licenses_count) }} Jar</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data dari Controller
        const userLabels = {!! json_encode($chartUserLabels) !!};
        const userData = {!! json_encode($chartUserData) !!};
        const actLabels = {!! json_encode($chartActLabels) !!};
        const actData = {!! json_encode($chartActData) !!};

        // Chart 1: Pendaftaran User
        const ctxUsers = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(ctxUsers, {
            type: 'line',
            data: {
                labels: userLabels,
                datasets: [{
                    label: 'Santri Baru',
                    data: userData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] }, ticks: { font: { size: 10, weight: 'bold' } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } }
                }
            }
        });

        // Chart 2: Aktivitas Jurnal
        const ctxActivity = document.getElementById('activityChartDashboard').getContext('2d');
        new Chart(ctxActivity, {
            type: 'bar',
            data: {
                labels: actLabels,
                datasets: [{
                    label: 'Postingan Jurnal',
                    data: actData,
                    backgroundColor: '#f59e0b',
                    borderRadius: 12,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] }, ticks: { font: { size: 10, weight: 'bold' } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } }
                }
            }
        });
    });
</script>
@endsection
