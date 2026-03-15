@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">Edit Halaman Panduan</h2>
        
        <form action="{{ route('admin.manual-pages.update', $manualPage->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Series TLQ</label>
                    <select name="series_id" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition">
                        @foreach($series as $s)
                        <option value="{{ $s->id }}" {{ $manualPage->series_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Halaman Ke-</label>
                    <input type="number" name="page_number" value="{{ old('page_number', $manualPage->page_number) }}" placeholder="Misal: 1" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Judul Halaman (Opsional)</label>
                <input type="text" name="title" value="{{ old('title', $manualPage->title) }}" placeholder="Misal: Doa Sebelum Membaca" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Isi Konten (Doa/Cara Baca)</label>
                <textarea name="content" rows="6" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition" placeholder="Tuliskan petunjuk atau doa di sini...">{{ old('content', $manualPage->content) }}</textarea>
            </div>

            <div class="flex space-x-4 pt-4">
                <a href="{{ route('admin.manual-pages.index') }}" class="flex-1 px-8 py-4 rounded-2xl border border-gray-100 text-gray-500 font-bold hover:bg-gray-50 text-center flex items-center justify-center">Batal</a>
                <button type="submit" class="flex-1 bg-emerald-islamic text-white py-4 rounded-2xl font-bold shadow-xl shadow-emerald-900/20 hover:bg-emerald-900 transition-all">Perbarui Halaman</button>
            </div>
        </form>
    </div>
</div>
@endsection
