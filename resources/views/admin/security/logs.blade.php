@extends('layouts.admin')

@section('content')
<div class="space-y-8 animate-in fade-in duration-700">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-8 rounded-[2rem] shadow-sm border border-rose-50 mb-8 overflow-hidden relative">
        <div class="relative z-10">
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Pusat <span class="text-rose-600">Keamanan</span></h2>
            <p class="text-gray-500 text-sm mt-1">Audit log login dan manajemen akun yang ditangguhkan.</p>
        </div>
    </div>

    <!-- ── BLOCKED USERS SECTION ── -->
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
        <h3 class="text-xl font-black text-gray-800 flex items-center tracking-tight mb-6 uppercase">
            <div class="w-8 h-8 mr-3 bg-rose-600 rounded-xl flex items-center justify-center text-white text-xs">🚫</div>
            Akun Terblokir ({{ count($blockedUsers) }})
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($blockedUsers as $u)
            <div class="p-5 bg-rose-50 rounded-[2rem] border border-rose-100 flex justify-between items-center group hover:bg-white transition-all shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-rose-600 text-white rounded-xl flex items-center justify-center font-black">
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-black text-gray-800">{{ $u->name }}</p>
                        <p class="text-[10px] text-rose-400 font-bold uppercase tracking-tighter">5x Gagal Login</p>
                    </div>
                </div>
                <form action="{{ route('admin.users.unblock', $u->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-white text-emerald-600 text-[10px] font-black rounded-xl border border-emerald-100 hover:bg-emerald-600 hover:text-white transition shadow-sm uppercase tracking-widest">
                        Pulihkan
                    </button>
                </form>
            </div>
            @empty
            <div class="col-span-full py-8 text-center text-gray-400 font-bold italic text-sm">
                Alhamdulillah, tidak ada akun yang sedang terblokir.
            </div>
            @endforelse
        </div>
    </div>

    <!-- ── LOGIN ATTEMPTS LOG ── -->
    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 overflow-hidden">
        <h3 class="text-xl font-black text-gray-800 flex items-center tracking-tight mb-6 uppercase">
            <div class="w-8 h-8 mr-3 bg-gray-900 rounded-xl flex items-center justify-center text-white text-xs">📑</div>
            Audit Log Percobaan Login
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Email</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">IP Address</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-bold text-gray-700">{{ $log->email }}</td>
                        <td class="px-6 py-4 text-xs font-mono text-gray-400">{{ $log->ip_address }}</td>
                        <td class="px-6 py-4">
                            @if($log->is_successful)
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black rounded-full uppercase border border-emerald-100">BERHASIL</span>
                            @else
                                <span class="px-3 py-1 bg-rose-50 text-rose-600 text-[9px] font-black rounded-full uppercase border border-rose-100">GAGAL</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-[10px] text-gray-400">
                            {{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y, H:i') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
