@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-3xl p-10 shadow-sm border border-gray-100">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Daftar Semua Lisensi</h2>
            <p class="text-gray-500">Total: {{ $licenses->total() }} Jar</p>
        </div>
        <div class="flex items-center space-x-4">
            <button onclick="document.getElementById('modal-generate').classList.remove('hidden')" class="bg-gold-islamic text-white px-5 py-2.5 rounded-lg hover:bg-amber-700 shadow-sm transition-all font-semibold flex items-center space-x-2 text-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span class="hidden md:inline">Buat QR Baru</span>
            </button>

            <!-- Filter by Series -->
            <form method="GET" action="{{ route('admin.licenses') }}" class="flex items-center space-x-2">
                <select name="series_id" onchange="this.form.submit()" class="border-gray-300 rounded-lg text-sm focus:ring-emerald-islamic focus:border-emerald-islamic">
                    <option value="">Semua Series</option>
                    @foreach($series as $s)
                        <option value="{{ $s->id }}" {{ request('series_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>

                <select name="per_page" onchange="this.form.submit()" class="border-gray-300 rounded-lg text-sm focus:ring-emerald-islamic focus:border-emerald-islamic hidden md:block">
                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20 Baris</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                    <option value="120" {{ request('per_page') == 120 ? 'selected' : '' }}>120 Baris (~1 Lembar A4)</option>
                    <option value="240" {{ request('per_page') == 240 ? 'selected' : '' }}>240 Baris (~2 Lembar A4)</option>
                    <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500 Baris</option>
                </select>
            </form>

            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-emerald-islamic flex items-center space-x-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Bulk Print Form -->
    <form method="POST" action="{{ route('admin.licenses.bulk-print') }}" target="_blank">
        @csrf
        <div class="mb-4">
            <button type="submit" class="bg-emerald-islamic text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-emerald-600 transition shadow-sm">
                🖨️ Cetak QR Terpilih (Gabungan)
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-xs uppercase text-gray-400 border-b border-gray-50">
                        <th class="py-4 font-medium pl-2">
                            <input type="checkbox" id="selectAll" class="rounded text-emerald-islamic focus:ring-emerald-islamic border-gray-300">
                        </th>
                        <th class="py-4 font-medium relative group cursor-help">
                            License Key
                            <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block w-48 bg-gray-800 text-white text-xs rounded p-2 shadow-lg">Kode unik yang discan pada botol Jar</div>
                        </th>
                        <th class="py-4 font-medium">Series</th>
                        <th class="py-4 font-medium text-center">Telah Dicetak</th>
                        <th class="py-4 font-medium">Status</th>
                        <th class="py-4 font-medium hidden md:table-cell">Dibuat</th>
                        <th class="py-4 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @if(count($licenses) > 0)
                        @foreach($licenses as $license)
                        <tr class="text-sm text-gray-600 hover:bg-gray-50 transition items-center">
                            <td class="py-4 pl-2">
                                <input type="checkbox" name="license_ids[]" value="{{ $license->id }}" class="license-checkbox rounded text-emerald-islamic focus:ring-emerald-islamic border-gray-300">
                            </td>
                            <td class="py-4 font-mono font-semibold">{{ $license->license_key }}</td>
                            <td class="py-4">
                                <span class="px-3 py-1 rounded-full text-white text-xs shadow-sm" style="background-color: {{ $license->series->color_hex }}">
                                    {{ $license->series->name }}
                                </span>
                            </td>
                            <td class="py-4 text-center">
                                @if($license->print_count > 0)
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $license->print_count }}x</span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="py-4">
                                @if($license->is_activated)
                                    <span class="flex items-center text-green-600 font-medium"><span class="w-2 h-2 bg-green-500 rounded-full mr-2 shadow-sm"></span> Aktif</span>
                                @else
                                    <span class="flex items-center text-gray-400"><span class="w-2 h-2 bg-gray-300 rounded-full mr-2"></span> Belum Aktif</span>
                                @endif
                            </td>
                            <td class="py-4 hidden md:table-cell text-gray-400 text-xs">{{ $license->created_at->format('d M Y') }}</td>
                            <td class="py-4">
                                <a href="{{ route('admin.licenses.print', $license->id) }}" target="_blank" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-xs font-semibold inline-flex items-center space-x-1 hover:bg-gray-200 transition">
                                    <span>Preview 1x</span>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-400">Belum ada data lisensi dengan filter tersebut.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </form>

    <div class="mt-8">
        {{ $licenses->links() }}
    </div>
</div>

<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.license-checkbox');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });
</script>

<!-- Modal Generate -->
<div id="modal-generate" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-6 bg-emerald-950/40 backdrop-blur-sm">
    <div class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl animate-scale-up">
        <h3 class="text-xl font-bold text-gray-800 mb-6">Generate Lisensi Baru</h3>
        <form action="{{ route('admin.licenses.generate') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Series TLQ</label>
                <select name="series_id" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none">
                    @foreach($series as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Jar (Satu QR per Jar)</label>
                <input type="number" name="count" value="1" min="1" max="50" class="w-full bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none">
                <p class="text-xs text-gray-400 mt-2">Maksimal 50 jar per generate untuk performa.</p>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="document.getElementById('modal-generate').classList.add('hidden')" class="flex-1 px-6 py-3 rounded-full border border-gray-100 text-gray-500 font-semibold hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="flex-1 px-6 py-3 rounded-full bg-emerald-islamic text-white font-semibold hover:bg-emerald-900 shadow-lg shadow-emerald-900/20 transition">Proses</button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes scale-up { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    .animate-scale-up { animation: scale-up 0.2s ease-out; }
</style>
@endsection
