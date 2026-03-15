@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">Edit Konten Jar</h2>
        
        <form action="{{ route('admin.contents.update', $content->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Series TLQ</label>
                    <select name="series_id" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition">
                        @foreach($series as $s)
                        <option value="{{ $s->id }}" {{ $content->series_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Surah & Ayat</label>
                    <input type="text" name="surah_ayah" value="{{ old('surah_ayah', $content->surah_ayah) }}" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Teks Arab</label>
                <textarea name="arabic_text" rows="3" dir="rtl" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition text-2xl font-serif">{{ old('arabic_text', $content->arabic_text) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Terjemahan</label>
                <textarea name="translation" rows="3" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition">{{ old('translation', $content->translation) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Insight (Pencerahan)</label>
                <textarea name="insight" rows="3" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition">{{ old('insight', $content->insight) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Action Plan (Tindakan Nyata)</label>
                <textarea name="action_plan" rows="3" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition">{{ old('action_plan', $content->action_plan) }}</textarea>
            </div>

            <div class="flex space-x-4 pt-4">
                <a href="{{ route('admin.contents.index') }}" class="flex-1 px-8 py-4 rounded-2xl border border-gray-100 text-gray-500 font-bold hover:bg-gray-50 text-center flex items-center justify-center">Batal</a>
                <button type="submit" class="flex-1 bg-emerald-islamic text-white py-4 rounded-2xl font-bold shadow-xl shadow-emerald-900/20 hover:bg-emerald-900 transition-all">Perbarui Konten</button>
            </div>
        </form>
    </div>
</div>
@endsection
