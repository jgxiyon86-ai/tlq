@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-3xl p-10 shadow-sm border border-gray-100">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Panduan Penggunaan (Book Mode)</h2>
            <p class="text-gray-500">Kelola halaman panduan dan doa yang muncul sebelum fitur kocok.</p>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.manual-pages.create') }}" class="mt-4 md:mt-0 bg-emerald-islamic text-white px-8 py-3 rounded-full hover:bg-emerald-900 shadow-lg shadow-emerald-900/20 transition-all font-semibold flex items-center space-x-2">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Tambah Halaman</span>
            </a>

            <!-- Filter by Series -->
            <form method="GET" action="{{ route('admin.manual-pages.index') }}" class="flex items-center space-x-2">
                <select name="series_id" onchange="this.form.submit()" class="border-gray-300 rounded-lg text-sm focus:ring-emerald-islamic focus:border-emerald-islamic">
                    <option value="">Semua Series</option>
                    @foreach($series as $s)
                        <option value="{{ $s->id }}" {{ request('series_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-xs uppercase text-gray-400 border-b border-gray-50">
                    <th class="py-4 font-medium">Series</th>
                    <th class="py-4 font-medium">Hal</th>
                    <th class="py-4 font-medium">Judul</th>
                    <th class="py-4 font-medium">Isi Preview</th>
                    <th class="py-4 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($pages as $page)
                <tr class="text-sm text-gray-600 hover:bg-gray-50 transition">
                    <td class="py-4">
                        <span class="px-3 py-1 rounded-full text-white text-xs" style="background-color: {{ $page->series->color_hex }}">
                            {{ $page->series->name }}
                        </span>
                    </td>
                    <td class="py-4 font-bold">{{ $page->page_number }}</td>
                    <td class="py-4">{{ $page->title }}</td>
                    <td class="py-4 truncate max-w-xs">{{ Str::limit($page->content, 50) }}</td>
                    <td class="py-4 flex space-x-3">
                        <a href="{{ route('admin.manual-pages.edit', $page->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                        <form action="{{ route('admin.manual-pages.destroy', $page->id) }}" method="POST" onsubmit="return confirm('Hapus halaman ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {{ $pages->links() }}
    </div>
</div>
@endsection
