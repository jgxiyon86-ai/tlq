@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">Tambah Konten Baru</h2>
        
        <form action="{{ route('admin.contents.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor</label>
                    <input type="number" name="number" placeholder="0" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Series TLQ</label>
                    <select name="series_id" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition">
                        @foreach($series as $s)
                        <option value="{{ $s->id }}" {{ request('series_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Surah & Ayat</label>
                    <input type="text" name="surah_ayah" placeholder="Misal: Al-Baqarah: 183" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Teks Arab</label>
                <textarea name="arabic_text" rows="3" dir="rtl" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition text-2xl font-serif" placeholder="كتب عليكم الصيام..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Terjemahan</label>
                <textarea name="translation" rows="3" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition" placeholder="Diwajibkan atas kamu berpuasa..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Insight (Pencerahan)</label>
                <textarea name="insight" rows="3" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition" placeholder="Intisari dari ayat ini adalah..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Action Plan (Tindakan Nyata)</label>
                <textarea name="action_plan" rows="3" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition" placeholder="Tindakan yang perlu dilakukan hari ini..."></textarea>
            </div>

            <div class="flex space-x-4 pt-4">
                <a href="{{ route('admin.contents.index') }}" class="flex-1 px-8 py-4 rounded-2xl border border-gray-100 text-gray-500 font-bold hover:bg-gray-50 text-center">Batal</a>
                <button type="submit" class="flex-1 bg-emerald-islamic text-white py-4 rounded-2xl font-bold shadow-xl shadow-emerald-900/20 hover:bg-emerald-900 transition-all">Simpan Konten</button>
            </div>
        </form>
    </div>
</div>
@endsection
