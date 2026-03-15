@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Monitoring Aktivitas User</h2>
            <p class="text-gray-500 text-sm">Update real-time tantangan dan jurnal pengguna.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-emerald-islamic flex items-center space-x-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali</span>
        </a>
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
                {{ $activeChallengesList->links() }}
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
                            <span class="text-[10px] text-gray-400 px-2 py-0.5 bg-gray-100 rounded-full">Hari {{ $e->day_number }}</span>
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
                {{ $recentJournalEntries->links() }}
            </div>
        </div>
    <div class="mt-8 bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            Monitoring Transfer Lisensi ({{ $transferRequests->total() }})
        </h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-50 uppercase text-xs">
                        <th class="py-4 font-medium">Jar/Series</th>
                        <th class="py-4 font-medium">Dari (Pemilik)</th>
                        <th class="py-4 font-medium">Ke (Peminta)</th>
                        <th class="py-4 font-medium">Status</th>
                        <th class="py-4 font-medium">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transferRequests as $t)
                    <tr>
                        <td class="py-4">
                            <span class="font-bold text-gray-800">{{ $t->license->license_key }}</span><br>
                            <span class="text-[10px] text-gray-400">{{ $t->license->series->name }}</span>
                        </td>
                        <td class="py-4 text-xs">
                            <span class="font-semibold text-gray-700">{{ $t->owner->name }}</span><br>
                            <span class="text-gray-400">{{ $t->owner->email }}</span>
                        </td>
                        <td class="py-4 text-xs">
                            <span class="font-semibold text-gray-700">{{ $t->requester->name }}</span><br>
                            <span class="text-gray-400">{{ $t->requester->email }}</span>
                        </td>
                        <td class="py-4">
                            @if($t->status === 'approved')
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-bold">SETUJU</span>
                            @elseif($t->status === 'rejected')
                                <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-bold">DITOLAK</span>
                            @else
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-[10px] font-bold">PENDING</span>
                            @endif
                        </td>
                        <td class="py-4 text-gray-400 text-[10px]">{{ $t->created_at->format('d/m/y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-400">Belum ada riwayat transfer lisensi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $transferRequests->links() }}
        </div>
    </div>
</div>
@endsection
