@foreach($contents as $content)
<tr class="text-sm text-gray-600 hover:bg-gray-50 transition">
    <td class="py-4 text-gray-400 text-xs">
        {{ $content->number }}
    </td>
    <td class="py-4">
        <span class="px-3 py-1 rounded-full text-white text-xs" style="background-color: {{ $content->series->color_hex }}">
            {{ $content->series->name }}
        </span>
    </td>
    <td class="py-4 font-semibold">{{ $content->surah_ayah }}</td>
    <td class="py-4 truncate max-w-xs">{{ \Illuminate\Support\Str::limit($content->insight, 50) }}</td>
    <td class="py-4 flex space-x-3">
        <a href="{{ route('admin.contents.edit', $content->id) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
        <form action="{{ route('admin.contents.destroy', $content->id) }}" method="POST" onsubmit="return confirm('Hapus konten ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
        </form>
    </td>
</tr>
@endforeach

@if($contents->isEmpty())
<tr>
    <td colspan="5" class="py-10 text-center text-gray-400">Tidak ada konten yang ditemukan.</td>
</tr>
@endif
