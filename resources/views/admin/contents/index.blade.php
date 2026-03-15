@extends('layouts.admin')

@section('content')
<div class="bg-white rounded-3xl p-10 shadow-sm border border-gray-100">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Konten Al-Quran</h2>
            <p class="text-gray-500">Daftar ayat, insight, dan aksi untuk setiap jar.</p>
        </div>
        <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
            <div class="relative">
                <input type="text" id="search-content" placeholder="Cari ayat, insight..." class="bg-gray-50 border border-gray-100 rounded-full pl-10 pr-6 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition w-full md:w-64">
                <svg class="h-5 w-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <a href="{{ route('admin.contents.create') }}" id="btn-tambah-konten" class="bg-emerald-islamic text-white px-8 py-2.5 rounded-full hover:bg-emerald-900 shadow-lg shadow-emerald-900/20 transition-all font-semibold flex items-center space-x-2 text-sm justify-center">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Tambah Konten</span>
            </a>
        </div>
    </div>

    <!-- Pilihan Series (Model Foto 2) -->
    <div class="mb-8">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Pilih Series & Tambah Konten</p>
        <div class="grid grid-cols-3 sm:grid-cols-5 gap-4">
            <!-- Tombol Semua Series -->
            <button onclick="selectSeries('')" class="series-btn active border border-gray-100 rounded-2xl p-4 flex flex-col items-center justify-center text-center transition hover:shadow-md bg-white hover:border-gray-300" data-id="">
                <div class="w-12 h-12 rounded-full mb-3 flex items-center justify-center bg-gray-100 text-gray-500 font-bold border-2 border-transparent">
                    All
                </div>
                <span class="text-sm font-semibold text-gray-800">Semua</span>
            </button>

            @foreach($series as $s)
            <button onclick="selectSeries({{ $s->id }})" class="series-btn border border-gray-100 rounded-2xl p-4 flex flex-col items-center justify-center text-center transition hover:shadow-md bg-white hover:border-gray-300 {{ request('series_id') == $s->id ? 'active ring-2 ring-emerald-500' : '' }}" data-id="{{ $s->id }}">
                <div class="w-12 h-12 rounded-full mb-3 flex items-center justify-center text-white font-bold transition transform group-hover:scale-105" style="background-color: {{ $s->color_hex }}">
                    {{ strtoupper(substr($s->name, 0, 1)) }}
                </div>
                <span class="text-sm font-semibold text-gray-800">{{ $s->name }}</span>
            </button>
            @endforeach
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-xs uppercase text-gray-400 border-b border-gray-50">
                    <th class="py-4 font-medium">Series</th>
                    <th class="py-4 font-medium">Ayat</th>
                    <th class="py-4 font-medium">Insight Preview</th>
                    <th class="py-4 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50" id="table-body">
                @include('admin.contents._table')
            </tbody>
        </table>
    </div>

    <div class="mt-8" id="pagination-links">
        {{ $contents->links() }}
    </div>
</div>

<script>
    let currentSeriesId = '{{ request('series_id') }}';
    let currentSearch = '';

    // Update link "Tambah Konten" agar sesuai dengan series yang aktif
    function updateCreateButton() {
        const btn = document.getElementById('btn-tambah-konten');
        if (currentSeriesId) {
            btn.href = `{{ route('admin.contents.create') }}?series_id=${currentSeriesId}`;
        } else {
            btn.href = `{{ route('admin.contents.create') }}`;
        }
    }

    // Pindah style tombol filter (Grid Series)
    function updateGridButtons() {
        document.querySelectorAll('.series-btn').forEach(btn => {
            btn.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50');
            if (btn.dataset.id == currentSeriesId) {
                btn.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50');
            }
        });
    }

    function selectSeries(id) {
        currentSeriesId = id;
        updateGridButtons();
        updateCreateButton();
        fetchContent();
    }

    // Panggil fetchContent saat mengetik di search bar (dikasih delay via debounce)
    let searchTimeout = null;
    document.getElementById('search-content').addEventListener('input', function(e) {
        currentSearch = e.target.value;
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchContent();
        }, 500); // Tunggu 500ms setelah user berhenti ngetik
    });

    // Panggil AJAX untuk refresh tabel
    function fetchContent(pageUrl = null) {
        document.getElementById('table-body').style.opacity = '0.5'; // Soft loading effect
        
        let url = pageUrl || `{{ route('admin.contents.index') }}?series_id=${currentSeriesId}&search=${encodeURIComponent(currentSearch)}`;
        
        // Ensure parameters are preserved in pagination links
        if (pageUrl) {
            const urlObj = new URL(url, window.location.origin);
            urlObj.searchParams.set('series_id', currentSeriesId);
            urlObj.searchParams.set('search', currentSearch);
            url = urlObj.toString();
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('table-body').innerHTML = data.html;
            document.getElementById('pagination-links').innerHTML = data.pagination;
            document.getElementById('table-body').style.opacity = '1';
        });
    }

    // Tangani klik pagination secara dinamis dengan AJAX juga
    document.addEventListener('click', function(e) {
        let link = e.target.closest('#pagination-links a');
        if (link) {
            e.preventDefault();
            fetchContent(link.href);
        }
    });

    // Init state pas reload
    updateCreateButton();
    updateGridButtons();
</script>
@endsection
