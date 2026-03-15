@extends('layouts.admin')

@section('content')
<!-- Overview Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Pengguna</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($totalUsers) }}</h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-islamic flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">Challenge Aktif</p>
            <h3 class="text-3xl font-bold text-emerald-600 mt-1">{{ number_format($activeChallenges) }}</h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Lisensi Jar</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($totalLicenses) }}</h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-gray-50 text-gray-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">Entry Jurnal</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($totalJournalEntries) }}</h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
    <!-- Chart: Pertumbuhan User -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-6">Pendaftaran Pengguna Baru (7 Hari)</h3>
        <div class="relative h-64 w-full">
            <canvas id="userChart"></canvas>
        </div>
    </div>

    <!-- Chart: Aktivitas Jurnal -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-6">Aktivitas Mengisi Jurnal (7 Hari)</h3>
        <div class="relative h-64 w-full">
            <canvas id="activityChart"></canvas>
        </div>
    </div>
</div>

<!-- Distribusi Series List -->
<div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
    <h3 class="text-lg font-bold text-gray-800 mb-6">Distribusi Cetak Series Lisensi</h3>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @foreach($series as $s)
        <div class="border border-gray-100 rounded-xl p-4 flex flex-col items-center text-center group hover:shadow-md transition">
            <div class="w-12 h-12 rounded-full mb-3 flex items-center justify-center text-white shadow-sm" style="background-color: {{ $s->color_hex }}">
                {{ strtoupper(substr($s->name, 0, 1)) }}
            </div>
            <h4 class="font-semibold text-gray-800">{{ $s->name }}</h4>
            <p class="text-sm text-gray-500">{{ number_format($s->licenses_count) }} Jar</p>
        </div>
        @endforeach
    </div>
</div>

    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data dari Controller
        const userLabels = {!! json_encode($chartUserLabels) !!};
        const userData = {!! json_encode($chartUserData) !!};
        
        const actLabels = {!! json_encode($chartActLabels) !!};
        const actData = {!! json_encode($chartActData) !!};

        // Chart 1: Pendaftaran User
        const ctxUser = document.getElementById('userChart').getContext('2d');
        new Chart(ctxUser, {
            type: 'line',
            data: {
                labels: userLabels,
                datasets: [{
                    label: 'Pengguna Baru',
                    data: userData,
                    borderColor: '#059669', // emerald
                    backgroundColor: 'rgba(5, 150, 105, 0.1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#059669',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] }, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Chart 2: Aktivitas Jurnal
        const ctxAct = document.getElementById('activityChart').getContext('2d');
        new Chart(ctxAct, {
            type: 'bar',
            data: {
                labels: actLabels,
                datasets: [{
                    label: 'Entry Jurnal Harian',
                    data: actData,
                    backgroundColor: '#d97706', // amber
                    borderRadius: 4,
                    borderWidth: 0,
                    barThickness: 'flex',
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] }, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
</script>
@endsection
