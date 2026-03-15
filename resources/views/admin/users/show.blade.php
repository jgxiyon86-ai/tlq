@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- User Profile Header -->
    <div class="bg-emerald-islamic rounded-t-[2.5rem] p-10 text-white relative overflow-hidden">
        <div class="absolute inset-0 islamic-pattern opacity-10"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6">
            <div class="w-20 h-20 bg-amber-400 rounded-2xl flex items-center justify-center text-emerald-900 text-3xl font-bold">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-3xl font-bold">{{ $user->name }}</h2>
                <p class="opacity-80">{{ $user->email }}</p>
                <p class="text-xs mt-2 bg-emerald-950/30 inline-block px-3 py-1 rounded-full">User ID: #{{ $user->id }}</p>
            </div>
            <div class="md:ml-auto text-center md:text-right">
                <p class="text-3xl font-bold text-amber-400">{{ $user->licenses->count() }}</p>
                <p class="text-xs uppercase tracking-widest opacity-70">Total Koleksi Jar</p>
            </div>
        </div>
    </div>

    <!-- Collection List -->
    <div class="bg-white rounded-b-[2.5rem] p-10 shadow-lg border border-gray-100">
        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            <svg class="h-6 w-6 mr-2 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Koleksi Jar Aktif
        </h3>

        @if($user->licenses->isEmpty())
        <div class="text-center py-20 bg-gray-50 rounded-3xl border border-dashed border-gray-200">
            <p class="text-gray-400 italic">Belum ada Jar yang diaktifkan oleh pengguna ini.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($user->licenses as $license)
            <div class="p-6 rounded-3xl border border-gray-100 hover:border-emerald-100 transition shadow-sm bg-gray-50/50 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-md text-xs font-bold" style="background-color: {{ $license->series->color_hex }}">
                    JAR
                </div>
                <div>
                    <h4 class="font-bold text-gray-800">Series {{ $license->series->name }}</h4>
                    <p class="text-xs font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded mt-1 inline-block">{{ $license->license_key }}</p>
                    <p class="text-[10px] text-gray-400 mt-2 italic items-center flex">
                        <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Aktif: {{ $license->activated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="mt-10 pt-10 border-t border-gray-50 text-center">
            <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-emerald-islamic transition font-medium"> Kembali ke Daftar Pengguna </a>
        </div>
    </div>
</div>
@endsection
