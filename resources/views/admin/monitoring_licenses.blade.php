@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <div class="flex justify-between items-center bg-white p-6 rounded-3xl shadow-sm border border-gray-100 mb-8">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Monitor <span class="text-amber-600">Lisensi</span></h2>
            <p class="text-gray-500 text-sm mt-1">Pengawasan aktivasi dan perpindahan hak milik Jar.</p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.dashboard') }}" class="p-3 bg-gray-50 rounded-2xl text-gray-500 hover:text-amber-600 transition shadow-inner">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-amber-500 to-amber-700 rounded-3xl p-6 text-white shadow-xl shadow-amber-100">
            <p class="text-white/70 text-sm font-medium uppercase tracking-wider">Total Lisensi</p>
            <h4 class="text-3xl font-black mt-1">{{ number_format($totalLicenses) }}</h4>
        </div>
        
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
            <p class="text-gray-400 text-sm font-medium uppercase tracking-wider">Teraktivasi</p>
            <h4 class="text-3xl font-black mt-1 text-gray-900">{{ number_format($activatedLicenses) }}</h4>
            <span class="text-xs font-bold text-emerald-600">{{ $activationRate }}% Usage Rate</span>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm">
            <p class="text-gray-400 text-sm font-medium uppercase tracking-wider">Transfer Pending</p>
            <h4 class="text-3xl font-black mt-1 text-gray-900">{{ $pendingTransfers }}</h4>
            <span class="text-xs font-bold text-amber-600">Menunggu Approval</span>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm text-center flex flex-col justify-center">
            <a href="{{ route('admin.licenses') }}" class="py-3 px-4 bg-emerald-islamic text-white rounded-2xl font-bold hover:bg-emerald-900 transition text-sm">
                Kelola Lisensi
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- List: Transfer Requests -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                Riwayat Transfer ({{ $transferRequests->total() }})
            </h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-gray-400 border-b border-gray-50 uppercase text-xs">
                            <th class="py-4 font-medium">Key / Series</th>
                            <th class="py-4 font-medium">Dari -> Ke</th>
                            <th class="py-4 font-medium text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($transferRequests as $t)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-4">
                                <span class="font-bold text-gray-800">{{ $t->license->license_key }}</span><br>
                                <span class="text-[10px] text-gray-400">{{ $t->license->series->name }}</span>
                            </td>
                            <td class="py-4 text-xs">
                                <div class="flex flex-col">
                                    <span class="text-gray-500">Dari: {{ $t->owner->email ?? '-' }}</span>
                                    <span class="text-emerald-600">Ke: {{ $t->requester->email ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="py-4 text-center">
                                @if($t->status === 'approved')
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px] font-bold">DONE</span>
                                @elseif($t->status === 'rejected')
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[10px] font-bold">FAIL</span>
                                @else
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-[10px] font-bold">WAIT</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-10 text-center text-gray-400">Belum ada aktivitas transfer.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6 shadow-sm p-4 bg-gray-50 rounded-2xl">
                {{ $transferRequests->appends(request()->except('transfers_page'))->links() }}
            </div>
        </div>

        <!-- Sidebar: Recent Activations -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Aktivasi Terbaru</h3>
            <div class="space-y-6">
                @forelse($recentActivations as $ra)
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-2xl border border-transparent hover:border-emerald-100 transition">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-sm shadow-emerald-200"></div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-gray-800 leading-none">{{ $ra->user->name ?? 'User' }}</p>
                        <p class="text-[10px] text-gray-500 mt-1">{{ $ra->series->name }} • {{ $ra->activated_at ? $ra->activated_at->diffForHumans() : '-' }}</p>
                    </div>
                </div>
                @empty
                <p class="text-center text-gray-400 text-xs py-10">Belum ada aktivasi.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
