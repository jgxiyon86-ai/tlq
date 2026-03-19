@extends('layouts.admin')

@section('content')
<div class="max-w-xl mx-auto space-y-8 animate-in fade-in duration-700">
    <div class="text-center">
        <div class="w-16 h-16 bg-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-white shadow-xl rotate-3">
             <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
        </div>
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Kemanan <span class="text-emerald-600">Akun</span></h2>
        <p class="text-gray-500 text-sm mt-1">Ganti password antum secara mandiri di sini.</p>
    </div>

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border-l-4 border-rose-500 text-rose-700 text-xs">
        <ul class="list-disc ml-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
        <form action="{{ route('admin.profile.password.update') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Password Baru (min 8 car)</label>
                <div class="relative group">
                    <input type="password" name="password" required 
                           class="w-full pl-5 pr-5 py-4 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500/20 transition-all font-medium">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Konfirmasi Password Baru</label>
                <div class="relative group">
                    <input type="password" name="password_confirmation" required 
                           class="w-full pl-5 pr-5 py-4 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-emerald-500/20 transition-all font-medium">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-gray-900 text-white font-black py-4 rounded-2xl hover:bg-emerald-600 transition-all shadow-lg uppercase text-[10px] tracking-[0.2em] flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Simpan Perubahan Password
                </button>
            </div>
        </form>
    </div>

    <p class="text-center text-gray-400 text-[10px] font-bold uppercase tracking-widest">
        Jagalah Amanah & Password Antum
    </p>
</div>
@endsection
