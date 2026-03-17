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
            <!-- Search Form -->
            <form action="{{ route('admin.monitoring.challenges') }}" method="GET" class="relative group w-full sm:w-64">
                <input type="text" name="q" value="{{ $searchQuery ?? '' }}" 
                    placeholder="Cari Nama / Konten..." 
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500/20 transition-all font-medium">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </form>

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
        <!-- List: Tantangan Aktif -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-black text-gray-800 flex items-center tracking-tight">
                    <div class="w-8 h-8 mr-3 bg-emerald-600 rounded-xl flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    User Aktif ({{ $activeChallengesList->total() }})
                </h3>
            </div>
            
            <div class="space-y-4">
                @forelse($activeChallengesList as $c)
                <div class="flex items-center justify-between p-5 bg-gray-50/50 rounded-3xl hover:bg-white border border-transparent hover:border-emerald-100 transition shadow-sm hover:shadow-md group">
                    <div class="flex items-center">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white font-black text-lg shadow-lg group-hover:rotate-6 transition-transform" style="background-color: {{ optional($c->series)->color_hex ?? '#064E3B' }}">
                                {{ strtoupper(substr(optional($c->series)->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-white rounded-full flex items-center justify-center shadow-sm">
                                <span class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="font-black text-gray-800 text-sm">{{ optional($c->user)->name ?? 'User Tidak Dikenal' }}</p>
                            <p class="text-[10px] font-bold text-gray-400 flex items-center space-x-2">
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    {{ optional($c->series)->name ?? 'Seri Tidak Ada' }}
                                </span>
                                <span class="opacity-30">|</span>
                                <span class="bg-gray-100 px-2 py-0.5 rounded text-gray-600 font-black tracking-tighter">{{ $c->total_days }} HARI</span>
                                <span class="opacity-30">|</span>
                                <span class="text-emerald-500/70">{{ optional($c->user)->email ?? '-' }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="text-right">
                            <p class="text-sm font-black text-emerald-600 tracking-tighter">HARI KE-{{ $c->current_day }}</p>
                            <p class="text-[10px] font-bold text-gray-400 italic">Mulai {{ $c->started_at ? $c->started_at->translatedFormat('d M') : ($c->created_at ? $c->created_at->translatedFormat('d M') : '-') }}</p>
                        </div>
                        
                        <form action="{{ route('admin.monitoring.challenges.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Hapus paksa tantangan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-gray-300 hover:text-rose-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="py-24 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-gray-500 font-bold">Afwan, User belum ditemukan.</p>
                </div>
                @endforelse
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-50">
                {{ $activeChallengesList->links() }}
            </div>
        </div>

        <!-- List: Jurnal Terbaru -->
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-xl font-black text-gray-800 flex items-center tracking-tight">
                    <div class="w-8 h-8 mr-3 bg-amber-500 rounded-xl flex items-center justify-center text-white">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-7.714 2.143L11 21l-2.286-6.857L1 12l7.714-2.143L11 3z"></path></svg>
                    </div>
                    Jurnal Istiqomah ({{ $recentJournalEntries->total() }})
                </h3>
            </div>
            
            <div class="space-y-4">
                @forelse($recentJournalEntries as $e)
                <div class="flex items-start p-5 bg-gray-50/30 rounded-3xl border border-transparent hover:border-amber-100 hover:bg-white transition group">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 outline-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </div>
                    <div class="ml-5 flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-black text-gray-800 text-sm tracking-tight capitalize">{{ $e->user->name }}</p>
                                <p class="text-[10px] font-black text-emerald-600 mt-0.5 tracking-widest">{{ $e->content->surah_ayah }}</p>
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
                        <p class="text-xs text-gray-500 mt-2 line-clamp-2 italic leading-relaxed">"{{ $e->insight_text ?? $e->content->translation_text }}"</p>
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
