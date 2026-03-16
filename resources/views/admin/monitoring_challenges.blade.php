@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex justify-between items-center bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mb-8">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Monitor <span class="text-emerald-600">Tantangan</span></h2>
            <p class="text-gray-500 text-sm mt-1">Pengawasan real-time aktivitas harian pengguna.</p>
        </div>
        <div class="flex items-center space-x-4">
            <div id="live-indicator" class="flex items-center bg-emerald-50 text-emerald-700 px-4 py-2 rounded-full text-xs font-bold animate-pulse">
                <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span>
                LIVE UPDATING
            </div>
            <a href="{{ route('admin.dashboard') }}" class="p-3 bg-gray-50 rounded-2xl text-gray-500 hover:text-emerald-600 transition shadow-inner">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
        </div>
    </div>

    <!-- 1. Stats Summary (Command Center Mode) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-3xl p-6 text-white shadow-xl shadow-emerald-100">
            <div class="flex justify-between items-start">
                <div class="p-2 bg-white/20 rounded-xl">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <span class="text-xs font-bold bg-white/20 px-2 py-1 rounded-full">{{ $completionRate }}% Success</span>
            </div>
            <p class="text-white/70 text-sm mt-4 font-medium uppercase tracking-wider">Tantangan Aktif</p>
            <h4 class="text-3xl font-black mt-1">{{ number_format($totalActive) }}</h4>
        </div>
        
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start">
                <div class="p-2 bg-amber-50 rounded-xl text-amber-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <span class="text-xs font-bold text-amber-600">Total Riwayat</span>
            </div>
            <p class="text-gray-400 text-sm mt-4 font-medium uppercase tracking-wider">Total Jurnal Dunia</p>
            <h4 class="text-3xl font-black mt-1 text-gray-900">{{ number_format($recentJournalEntries->total()) }}</h4>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start">
                <div class="p-2 bg-blue-50 rounded-xl text-blue-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <p class="text-gray-400 text-sm mt-4 font-medium uppercase tracking-wider">Aktivitas Terkini</p>
            <h4 class="text-3xl font-black mt-1 text-gray-900">{{ number_format($liveUsersCount) }} <span class="text-xs font-normal text-gray-400">User</span></h4>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
            <div class="flex justify-between items-start">
                <div class="p-2 bg-red-50 rounded-xl text-red-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-xs font-bold text-red-600">Stuck</span>
            </div>
            <p class="text-gray-400 text-sm mt-4 font-medium uppercase tracking-wider">Berhenti > 3 Hari</p>
            <h4 class="text-3xl font-black mt-1 text-gray-900">{{ number_format($anomaliesCount) }} <span class="text-xs font-normal text-gray-400">Alerts</span></h4>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- List: Tantangan Aktif -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Tantangan Berjalan ({{ $activeChallengesList->total() }})
                </h3>
            </div>
            
            <div class="space-y-4">
                @forelse($activeChallengesList as $c)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl hover:bg-white border border-transparent hover:border-gray-100 transition">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold" style="background-color: {{ $c->series->color_hex ?? '#064E3B' }}">
                            {{ strtoupper(substr($c->series->name, 0, 1)) }}
                        </div>
                        <div class="ml-4">
                            <p class="font-bold text-gray-800 text-sm">{{ $c->user->name }}</p>
                            <p class="text-[10px] text-gray-500">{{ $c->series->name }} ({{ $c->is_seven_days ? '7' : '40' }} Hari)</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-emerald-600">Hari ke-{{ $c->current_day }}</p>
                        <p class="text-[10px] text-gray-400">{{ $c->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="py-20 text-center">
                    <p class="text-gray-400 text-sm">Belum ada tantangan yang sedang berjalan.</p>
                </div>
                @endforelse
            </div>
            
            <div class="mt-6">
                {{ $activeChallengesList->appends(request()->except('challenges_page'))->links() }}
            </div>
        </div>

        <!-- List: Jurnal Terbaru -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    Riwayat Jurnal ({{ $recentJournalEntries->total() }})
                </h3>
            </div>
            
            <div class="space-y-4">
                @forelse($recentJournalEntries as $e)
                <div class="flex items-start p-4 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition rounded-xl">
                    <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <div class="flex justify-between items-start">
                            <p class="font-bold text-gray-800 text-sm">{{ $e->user->name }}</p>
                            <div class="flex flex-col items-end">
                                <span class="text-[10px] text-gray-400 px-2 py-0.5 bg-gray-100 rounded-full mb-1">Hari {{ $e->day_number }}</span>
                                @if($e->is_catch_up)
                                    <span class="text-[8px] font-black text-amber-600 flex items-center bg-amber-50 px-1.5 py-0.5 rounded-lg border border-amber-100 uppercase tracking-tighter">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3V1L8 11h2v8l3-10h-2V3z"></path></svg>
                                        Akselerasi
                                    </span>
                                @endif
                            </div>
                        </div>
                        <p class="text-xs text-emerald-600 italic mt-1 line-clamp-1">"{{ $e->content->surah_ayah }}"</p>
                        <p class="text-[10px] text-gray-400 mt-1">{{ $e->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-10">
                    <p class="text-gray-400">Belum ada aktivitas jurnal.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $recentJournalEntries->appends(request()->except('journals_page'))->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
