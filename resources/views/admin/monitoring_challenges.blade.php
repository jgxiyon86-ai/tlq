@extends('layouts.admin')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-8 rounded-[2rem] shadow-sm border border-emerald-50 mb-8 overflow-hidden relative">
        <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
            <svg class="w-24 h-24 text-emerald-900" fill="currentColor" viewBox="0 0 24 24"><path d="M12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm0,18a8,8,0,1,1,8-8A8,8,0,0,1,12,20ZM12,6a6,6,0,1,0,6,6A6,6,0,0,0,12,6Zm0,10a4,4,0,1,1,4-4A4,4,0,0,1,12,16Z"/></svg>
        </div>
        <div class="relative z-10">
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Monitor <span class="text-emerald-600">User</span></h2>
            <p class="text-gray-500 text-sm mt-1">Pengawasan khidmah aktivitas harian User Al-Quran.</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4 w-full md:w-auto mt-6 md:mt-0 relative z-10">
            <!-- AJAX Search -->
            <div class="relative group w-full sm:w-72">
                <input type="text" id="ajax-search" placeholder="Cari user aktif..." 
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500/20 transition-all font-medium"
                    oninput="searchUsers(this.value)">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" id="search-icon">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <!-- Dropdown hasil pencarian -->
                <div id="search-results" class="hidden absolute top-14 left-0 right-0 bg-white rounded-3xl shadow-2xl border border-gray-100 z-50 max-h-72 overflow-y-auto"></div>
            </div>

            <div id="live-indicator" class="flex items-center bg-emerald-50 text-emerald-700 px-5 py-2.5 rounded-2xl text-[10px] font-black tracking-widest uppercase">
                <span class="relative flex h-2 w-2 mr-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                LIVE AKTIVITAS
            </div>

            <button onclick="openCreateModal()" class="flex items-center bg-gray-900 text-white px-6 py-3 rounded-2xl text-[10px] font-black tracking-widest uppercase hover:bg-emerald-600 transition-colors shadow-lg shadow-gray-200">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Tantangan
            </button>
        </div>
    </div>

    <!-- 1. Stats Summary (Islamic Tone Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-emerald-600 via-emerald-700 to-emerald-900 rounded-[2rem] p-7 text-white shadow-2xl shadow-emerald-200/50 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M13,3H11V21h2V3M5,3H3V21h2V3M21,3H19V21h2V3Z"/></svg>
            </div>
            <div class="flex justify-between items-start relative z-10">
                <div class="p-2.5 bg-white/20 rounded-2xl backdrop-blur-md">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <span class="text-[10px] font-black bg-emerald-400/30 px-3 py-1 rounded-full border border-white/10">{{ $completionRate }}% KHATAM</span>
            </div>
            <p class="text-emerald-100 text-xs mt-6 font-bold uppercase tracking-[0.2em]">User Aktif</p>
            <h4 class="text-4xl font-black mt-1">{{ number_format($totalActive) }}</h4>
        </div>
        
        <div class="bg-white rounded-[2rem] p-7 border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div class="p-2.5 bg-amber-50 rounded-2xl text-amber-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <span class="text-[10px] font-black text-amber-600 bg-amber-50 px-3 py-1 rounded-full uppercase tracking-widest">MASYAALLAH</span>
            </div>
            <p class="text-gray-400 text-xs mt-6 font-bold uppercase tracking-[0.2em]">Jurnal Terukir</p>
            <h4 class="text-4xl font-black mt-1 text-gray-900">{{ number_format($recentJournalEntries->total()) }}</h4>
        </div>

        <div class="bg-white rounded-[2rem] p-7 border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div class="p-2.5 bg-blue-50 rounded-2xl text-blue-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-black text-blue-600 uppercase tracking-tighter">Dalam Usaha</span>
                </div>
            </div>
            <p class="text-gray-400 text-xs mt-6 font-bold uppercase tracking-[0.2em]">Istiqomah (5m)</p>
            <h4 class="text-4xl font-black mt-1 text-gray-900">{{ number_format($liveUsersCount) }} <span class="text-sm font-normal text-gray-400">Jiwa</span></h4>
        </div>

        <div class="bg-white rounded-[2rem] p-7 border border-gray-100 shadow-sm hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div class="p-2.5 bg-rose-50 rounded-2xl text-rose-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-[10px] font-black text-rose-600 bg-rose-50 px-3 py-1 rounded-full tracking-widest">BUTUH SAPAAN</span>
            </div>
            <p class="text-gray-400 text-xs mt-6 font-bold uppercase tracking-[0.2em]">Berhenti > 3 Hari</p>
            <h4 class="text-4xl font-black mt-1 text-gray-900">{{ number_format($anomaliesCount) }}</h4>
        </div>
    </div>

    @if($searchQuery)
    <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-2xl flex items-center justify-between">
        <p class="text-emerald-800 text-sm font-medium">
            <span class="font-black italic">Hasil Pencarian:</span> "{{ $searchQuery }}" 
            <span class="ml-2 opacity-60">(Ditemukan {{ $activeChallengesList->total() }} Tantangan & {{ $recentJournalEntries->total() }} Jurnal)</span>
        </p>
        <a href="{{ route('admin.monitoring.challenges') }}" class="text-xs font-black text-emerald-600 hover:underline">RESET FILTER</a>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- ── KIRI: User Aktif (AJAX Interactive) ───────────────────────────────────────── -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-black text-gray-800 flex items-center tracking-tight">
                    <div class="w-8 h-8 mr-3 bg-emerald-600 rounded-xl flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    User Aktif ({{ $activeChallengesList->total() }})
                </h3>
                <span class="text-[10px] text-gray-400 font-bold">klik user untuk lihat detail</span>
            </div>

            <div class="space-y-3" id="user-list">
                @forelse($activeChallengesList as $c)
                <div class="rounded-3xl border border-gray-100 overflow-hidden" id="user-block-{{ $c->id }}">
                    <!-- User Row (klik untuk load tantangan) -->
                    <div class="flex items-center justify-between p-4 hover:bg-emerald-50/50 cursor-pointer transition group"
                         onclick="toggleChallenges({{ $c->user->id ?? 0 }}, {{ $c->id }}, this)">
                        <div class="flex items-center">
                            <div class="relative">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white font-black text-sm shadow" 
                                     style="background-color: {{ optional($c->series)->color_hex ?? '#064E3B' }}">
                                    {{ strtoupper(substr(optional($c->user)->name ?? '?', 0, 1)) }}
                                </div>
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-white rounded-full flex items-center justify-center shadow">
                                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                                </span>
                            </div>
                            <div class="ml-3">
                                <p class="font-black text-gray-800 text-sm">{{ optional($c->user)->name ?? '-' }}</p>
                                <p class="text-[10px] text-gray-400 font-medium">{{ optional($c->user)->email ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                @if($c->is_completed)
                                    <span class="text-[10px] font-black text-gray-400 bg-gray-100 px-2 py-0.5 rounded-lg">SELESAI</span>
                                @else
                                    <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-lg">HARI KE-{{ $c->current_day }}</span>
                                    @php
                                        $startDate = $c->started_at ?? $c->created_at;
                                        $debt = $startDate ? max(0, min($c->total_days, $startDate->copy()->startOfDay()->diffInDays(now()->startOfDay()) + 1) - $c->current_day) : 0;
                                    @endphp
                                    @if($debt > 0)
                                        <p class="text-[9px] font-black text-rose-500 text-right">{{ $debt }}H Tunggakan</p>
                                    @endif
                                @endif
                                <p class="text-[9px] text-gray-300">{{ optional($c->series)->name }} • {{ $c->total_days }}H</p>
                            </div>
                            <!-- Expand icon -->
                            <svg class="w-4 h-4 text-gray-300 transition-transform duration-300 expand-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Challenges dropdown (hidden, loaded via AJAX) -->
                    <div class="challenges-panel hidden bg-gray-50 border-t border-gray-100 px-4 py-3" id="challenges-{{ $c->user->id ?? 0 }}">
                        <div class="loading-indicator text-center py-4">
                            <div class="inline-block w-5 h-5 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                            <p class="text-xs text-gray-400 mt-2">Memuat tantangan...</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="py-16 text-center">
                    <p class="text-gray-400 font-bold text-sm">Belum ada user aktif.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-6 pt-4 border-t border-gray-50">
                {{ $activeChallengesList->links() }}
            </div>
        </div>

        <!-- ── KANAN: Panel Detail Journal (AJAX) + Tantangan Selesai ─────────────────── -->
        <div class="space-y-8">

            <!-- Journal Detail Panel (default kosong) -->
            <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100" id="journal-panel">
                <h3 class="text-xl font-black text-gray-800 flex items-center tracking-tight mb-6">
                    <div class="w-8 h-8 mr-3 bg-amber-500 rounded-xl flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-7.714 2.143L11 21l-2.286-6.857L1 12l7.714-2.143L11 3z"></path></svg>
                    </div>
                    <span id="journal-panel-title">Jurnal Istiqomah</span>
                </h3>
                <div id="journal-content">
                    <div class="py-16 text-center">
                        <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-amber-300">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg>
                        </div>
                        <p class="text-gray-400 font-bold text-sm">Pilih user, lalu pilih tantangan<br>untuk melihat jurnal istiqomah.</p>
                    </div>
                </div>
            </div>

            <!-- Tantangan Selesai -->
            <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-black text-gray-800 flex items-center tracking-tight mb-6">
                    <div class="w-8 h-8 mr-3 bg-yellow-500 rounded-xl flex items-center justify-center text-white">
                        🏆
                    </div>
                    Tantangan Selesai ({{ $completedChallengesList->total() }})
                </h3>
                <div class="space-y-3">
                    @forelse($completedChallengesList as $c)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:bg-yellow-50/50 transition cursor-pointer"
                         onclick="loadJournals({{ $c->id }}, '{{ addslashes(optional($c->user)->name) }} — {{ addslashes(optional($c->series)->name) }} (Selesai)')">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-sm" 
                                 style="background:{{ optional($c->series)->color_hex ?? '#D97706' }}">
                                {{ strtoupper(substr(optional($c->user)->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="ml-3">
                                <p class="font-black text-gray-800 text-sm">{{ optional($c->user)->name ?? '-' }}</p>
                                <p class="text-[10px] text-gray-400">{{ optional($c->series)->name }} • {{ $c->total_days }} Hari</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-black text-yellow-700 bg-yellow-100 px-2 py-1 rounded-lg">✅ KHATAM</span>
                            <p class="text-[9px] text-gray-300 mt-1">{{ $c->updated_at->translatedFormat('d M Y') }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-400 text-sm py-8">Belum ada tantangan selesai.</p>
                    @endforelse
                </div>
                <div class="mt-4 pt-4 border-t border-gray-50">{{ $completedChallengesList->links() }}</div>
            </div>
        </div>
    </div>

                    <div class="ml-5 flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-black text-gray-800 text-sm tracking-tight capitalize">{{ optional($e->user)->name ?? 'User' }}</p>
                                <p class="text-[10px] font-black {{ $e->content ? 'text-emerald-600' : 'text-gray-400' }} mt-0.5 tracking-widest">
                                    {{ optional($e->content)->surah_ayah ?? 'BELUM DIKOCOK' }}
                                </p>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-[10px] font-black text-amber-700 px-3 py-1 bg-amber-100 rounded-full mb-1">HARI {{ $e->day_number }}</span>
                                @if($e->is_catch_up)
                                    <span class="text-[8px] font-black text-rose-600 flex items-center bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-100 uppercase tracking-widest">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3V1L8 11h2v8l3-10h-2V3z"></path></svg>
                                        Akselerasi
                                    </span>
                                @endif
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 line-clamp-2 italic leading-relaxed">
                            "{{ $e->insight_text ?? (optional($e->content)->translation_text ?? 'Menunggu ayat terekam...') }}"
                        </p>
                        <div class="flex items-center mt-3 pt-3 border-t border-gray-100/50">
                            <svg class="w-3 h-3 text-gray-300 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-[0.1em]">{{ $e->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-24">
                    <p class="text-gray-400 font-bold uppercase tracking-widest text-xs">Belum ada aktivitas terekam.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-8 pt-6 border-t border-gray-50">
                {{ $recentJournalEntries->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Challenge -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeCreateModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 p-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black text-gray-900 tracking-tight" id="modal-title">Mulai <span class="text-emerald-600">Tantangan</span></h3>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('admin.monitoring.challenges.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih User</label>
                    <select name="user_id" required class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500/20 transition-all font-medium appearance-none">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Seri Jar</label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($series as $s)
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="series_id" value="{{ $s->id }}" class="peer hidden" required>
                            <div class="p-3 border-2 border-gray-50 rounded-2xl text-center transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 group-hover:bg-gray-50">
                                <p class="text-xs font-black text-gray-700">{{ $s->name }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Durasi</label>
                        <select name="total_days" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500/20 transition-all font-medium">
                            <option value="40">40 Hari (Default)</option>
                            <option value="7">7 Hari</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tanggal Mulai</label>
                        <input type="date" name="started_at" value="{{ date('Y-m-d') }}" required
                            class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500/20 transition-all font-medium">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-emerald-600 text-white font-black py-4 rounded-2xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-200 uppercase text-[10px] tracking-[0.2em]">
                        Konfirmasi & Mulai Takdir
                    </button>
                    <p class="text-[9px] text-gray-400 text-center mt-3 font-medium">
                        Catatan: Menghidupkan tantangan baru akan menghapus tantangan aktif <br> user tersebut pada seri yang sama (Bypass Mode).
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const AJAX_BASE = '{{ url("admin/ajax") }}';
    const CSRF = '{{ csrf_token() }}';

    // ── AJAX Search User ────────────────────────────────────────
    let searchTimeout;
    function searchUsers(q) {
        clearTimeout(searchTimeout);
        const box = document.getElementById('search-results');
        if (!q.trim()) { box.classList.add('hidden'); return; }
        box.classList.remove('hidden');
        box.innerHTML = '<p class="text-xs text-gray-400 p-4">Mencari...</p>';
        searchTimeout = setTimeout(async () => {
            const res = await fetch(`${AJAX_BASE}/users/search?q=${encodeURIComponent(q)}`);
            const users = (await res.json());
            if (!users.length) {
                box.innerHTML = '<p class="text-xs text-gray-400 p-4">User tidak ditemukan.</p>';
                return;
            }
            box.innerHTML = users.map(u => `
                <div class="px-4 py-3 hover:bg-emerald-50 cursor-pointer flex items-center gap-3 transition" onclick="loadUserChallenges(${u.id}, '${u.name.replace(/'/g,"&apos;")}')">
                    <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-sm">${u.name[0].toUpperCase()}</div>
                    <div>
                        <p class="font-bold text-sm text-gray-800">${u.name}</p>
                        <p class="text-xs text-gray-400">${u.email} • ${u.challenges_count} tantangan</p>
                    </div>
                </div>`).join('');
        }, 400);
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#ajax-search') && !e.target.closest('#search-results')) {
            document.getElementById('search-results').classList.add('hidden');
        }
    });

    // ── Toggle Challenges for a User ───────────────────────────
    let loadedUsers = {};
    function toggleChallenges(userId, blockId, el) {
        const panel = document.getElementById(`challenges-${userId}`);
        const icon  = el.querySelector('.expand-icon');
        const isOpen = !panel.classList.contains('hidden');

        if (isOpen) {
            panel.classList.add('hidden');
            icon.style.transform = '';
            return;
        }

        panel.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';

        if (loadedUsers[userId]) return; // already loaded
        loadedUsers[userId] = true;
        loadUserChallenges(userId, null, panel);
    }

    async function loadUserChallenges(userId, name, panelEl) {
        if (!panelEl) {
            // triggered from search — try to find or create a temp panel
            panelEl = document.getElementById(`challenges-${userId}`);
            if (!panelEl) {
                // Scroll to journal panel and load directly
                await loadJournals(null, name, userId);
                return;
            }
            panelEl.classList.remove('hidden');
        }
        panelEl.innerHTML = `<div class="loading-indicator text-center py-4"><div class="inline-block w-5 h-5 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div><p class="text-xs text-gray-400 mt-2">Memuat tantangan...</p></div>`;
        const res = await fetch(`${AJAX_BASE}/users/${userId}/challenges`);
        const data = await res.json();
        if (!data.challenges.length) {
            panelEl.innerHTML = '<p class="text-xs text-gray-400 py-4 text-center">Tidak ada tantangan.</p>';
            return;
        }
        panelEl.innerHTML = data.challenges.map(c => `
            <div class="flex items-center justify-between px-3 py-2.5 hover:bg-emerald-100/50 rounded-2xl cursor-pointer transition mb-1"
                 onclick="loadJournals(${c.id}, '${(c.series?.name || 'Seri').replace(/'/g, "&apos;")} — Hari ${c.current_day}/${c.total_days}')">
                <div>
                    <p class="text-sm font-black text-gray-700">${c.series?.name || 'Seri'}</p>
                    <p class="text-[10px] text-gray-400">${c.total_days} Hari • ${c.is_completed ? '✅ Selesai' : 'Hari ' + c.current_day}</p>
                </div>
                <svg class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>`).join('');
    }

    // ── Load Journal Entries for a Challenge ───────────────────
    async function loadJournals(challengeId, label) {
        const panel  = document.getElementById('journal-panel');
        const title  = document.getElementById('journal-panel-title');
        const content= document.getElementById('journal-content');
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        title.textContent = label || 'Jurnal Istiqomah';
        content.innerHTML = `<div class="py-8 text-center"><div class="inline-block w-6 h-6 border-2 border-amber-400 border-t-transparent rounded-full animate-spin"></div><p class="text-xs text-gray-400 mt-2">Memuat jurnal...</p></div>`;

        const res  = await fetch(`${AJAX_BASE}/challenges/${challengeId}/journals`);
        const data = await res.json();
        if (!data.entries.length) {
            content.innerHTML = '<p class="text-gray-400 text-sm text-center py-8">Tidak ada jurnal.</p>';
            return;
        }
        content.innerHTML = data.entries.map(e => {
            const hasBefore = e.before_pesan;
            const hasAfter  = e.after_berhasil;
            const isDone    = e.is_completed;
            return `<div class="border border-gray-100 rounded-2xl mb-3 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 cursor-pointer ${isDone ? 'bg-emerald-50' : 'bg-gray-50'}" onclick="this.nextElementSibling.classList.toggle('hidden')">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-black ${isDone ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-500'}">${e.day_number}</span>
                        <div>
                            <p class="text-xs font-black text-gray-700">${e.content?.surah_ayah || (isDone ? 'Hari '+e.day_number : 'Belum dijemput')}</p>
                            <p class="text-[9px] text-gray-400">${hasBefore ? '🌅 Before ' : ''}${hasAfter ? '🌇 After' : ''}</p>
                        </div>
                    </div>
                    <span class="text-[9px] font-black ${e.is_catch_up ? 'text-amber-600 bg-amber-100' : 'text-gray-400'} px-2 py-0.5 rounded">${e.is_catch_up ? 'KEJAR' : ''}</span>
                </div>
                <div class="hidden px-4 py-3 space-y-2 bg-white">
                    ${e.content?.arabic_text ? `<p class="text-right font-amiri text-lg text-gray-800">${e.content.arabic_text}</p><p class="text-xs text-gray-500 italic">${e.content.translation || ''}</p>` : ''}
                    ${hasBefore ? `<div class="bg-emerald-50 rounded-xl p-3"><p class="text-[10px] font-black text-emerald-700 mb-1">🌅 BEFORE</p><p class="text-xs text-gray-700">${e.before_pesan}</p></div>` : ''}
                    ${hasAfter  ? `<div class="bg-amber-50 rounded-xl p-3"><p class="text-[10px] font-black text-amber-700 mb-1">🌇 AFTER</p><p class="text-xs text-gray-700">${e.after_berhasil}</p></div>` : ''}
                </div>
            </div>`;
        }).join('');
    }

    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>

<style>
/* Animasi halus untuk pagination */
.pagination { @apply flex space-x-2; }
.page-item { @apply rounded-xl overflow-hidden shadow-sm; }
.page-link { @apply !px-4 !py-2 !bg-white !border-gray-100 !text-gray-600 !font-black !text-xs hover:!bg-emerald-50 hover:!text-emerald-600 transition-colors; }
.active .page-link { @apply !bg-emerald-600 !text-white !border-emerald-600; }
</style>

@if(session('success'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        title: 'Barakallah!',
        text: "{{ session('success') }}",
        icon: 'success',
        confirmButtonText: 'Alhamdulillah',
        confirmButtonColor: '#059669',
        customClass: {
            popup: 'rounded-[2rem]',
            confirmButton: 'rounded-xl px-8 font-black uppercase text-xs tracking-widest'
        }
    });
</script>
@endif
@endsection
