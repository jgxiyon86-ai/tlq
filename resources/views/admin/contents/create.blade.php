@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">Tambah Konten Baru</h2>
        
        <!-- OCR Scanner Section -->
        <div class="mb-8 p-6 bg-amber-islamic/10 border border-amber-islamic/30 rounded-2xl">
            <h3 class="text-lg font-bold text-emerald-900 mb-2 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                AI Auto-Ketik (Dari Foto)
            </h3>
            <p class="text-sm text-gray-600 mb-4">Foto gulungan kertas secara bergantian. Sistem akan otomatis mengisi kolom-kolom di bawah sesuai jenis posisinya.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Tombol Depan -->
                <label class="cursor-pointer bg-white border-2 border-dashed border-emerald-400 rounded-xl p-4 text-center hover:bg-emerald-50 transition relative overflow-hidden group">
                    <span class="text-emerald-700 font-bold flex flex-col items-center justify-center">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                        1. Scan Bagian Depan
                        <span class="text-xs font-normal text-emerald-600 mt-1">(Ayat & Terjemah)</span>
                    </span>
                    <input type="file" accept="image/*" capture="environment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="processImage(this, 'front')">
                </label>

                <!-- Tombol Belakang -->
                <label class="cursor-pointer bg-white border-2 border-dashed border-amber-400 rounded-xl p-4 text-center hover:bg-amber-50 transition relative overflow-hidden group">
                    <span class="text-amber-700 font-bold flex flex-col items-center justify-center">
                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        2. Scan Bagian Belakang
                        <span class="text-xs font-normal text-amber-600 mt-1">(Insight & What to do)</span>
                    </span>
                    <input type="file" accept="image/*" capture="environment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="processImage(this, 'back')">
                </label>
            </div>
            
            <div id="ocr-loading" class="hidden text-center text-sm font-bold text-amber-600 mt-4">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-amber-600 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span id="ocr-status">AI sedang mengeja teks foto...</span>
            </div>
        </div>
        <form action="{{ route('admin.contents.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Series TLQ</label>
                    <select name="series_id" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-emerald-500 outline-none transition">
                        @foreach($series as $s)
                        <option value="{{ $s->id }}" {{ request('series_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
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

<!-- Tesseract OCR Library -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
    function compressImage(file, callback) {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = event => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                const MAX_WIDTH = 1000; // Optimal resolution for OCR
                
                if (width > MAX_WIDTH) {
                    height = Math.round((height *= MAX_WIDTH / width));
                    width = MAX_WIDTH;
                }
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // --- IMAGE PREPROCESSING UNTUK OCR (Ver 2.0 yang Lebih Halus) ---
                const imageData = ctx.getImageData(0, 0, width, height);
                const data = imageData.data;
                let totalBrightness = 0;

                // 1. Hitung rata-rata kecerahan gambar
                for (let i = 0; i < data.length; i += 4) {
                    let r = data[i], g = data[i+1], b = data[i+2];
                    let brightness = (r * 299 + g * 587 + b * 114) / 1000;
                    totalBrightness += brightness;
                }
                let avgBrightness = totalBrightness / (width * height);
                
                // Jika gambar dominan gelap (seperti kartu biru tua Anda),
                let shouldInvert = avgBrightness < 127;

                // 2. Terapkan filter Hitam Putih (Grayscale) & Invert HARF (Lebih halus)
                for (let i = 0; i < data.length; i += 4) {
                    let r = data[i], g = data[i+1], b = data[i+2];
                    let grayscale = (r * 0.3) + (g * 0.59) + (b * 0.11);
                    
                    if (shouldInvert) {
                        grayscale = 255 - grayscale; // Balik warna agar huruf jadi gelap, latar jadi terang
                    }
                    
                    // Pertajam kontras LEBIH HALUS tanpa merusak bentuk huruf
                    if(grayscale > 200) grayscale = 255;
                    else if(grayscale < 80) grayscale = 0;
                    else {
                        // linear stretch untuk tengah-tengah
                        grayscale = (grayscale - 80) * (255 / (200 - 80));
                    }

                    data[i] = grayscale;
                    data[i+1] = grayscale;
                    data[i+2] = grayscale;
                }
                ctx.putImageData(imageData, 0, 0);

                // Kembalikan gambar kualitas tinggi (1.0 = Max) agar huruf tidak burem
                callback(canvas.toDataURL('image/jpeg', 1.0));
            }
        };
    }

    async function processImage(input, type) {
        if (!input.files || input.files.length === 0) return;
        
        const file = input.files[0];
        const loadingStr = document.getElementById('ocr-loading');
        const statusStr = document.getElementById('ocr-status');
        
        // Target inputs
        const targetSurah = document.querySelector('input[name="surah_ayah"]');
        const targetTranslation = document.querySelector('textarea[name="translation"]');
        const targetInsight = document.querySelector('textarea[name="insight"]');
        const targetAction = document.querySelector('textarea[name="action_plan"]');

        loadingStr.classList.remove('hidden');
        statusStr.innerText = type === 'front' ? "Mengecilkan & Membaca Ayat..." : "Mengecilkan & Membaca Insight...";

        // Compress image first to speed up Tesseract drastically
        compressImage(file, async (compressedDataUrl) => {
            try {
                statusStr.innerText = "Mengekstrak Teks... (Tunggu Sebentar)";
                const worker = await Tesseract.createWorker('ind');
                const ret = await worker.recognize(compressedDataUrl);
                const rawText = ret.data.text;
                await worker.terminate();

            // Auto-Parsing Logic
            if (type === 'front') {
                // Parsing logic for Bagian Depan
                // Mengharapkan format: "QS. NamaSurat: Ayat" lalu terjemahannya.
                const lines = rawText.split('\n').map(l => l.trim()).filter(l => l.length > 0);
                
                // Cari line pertama yang mengandung indikasi surah (QS, Q5, OS, O5, etc atau format xx:yy)
                let qsIndex = lines.findIndex(l => {
                    const up = l.toUpperCase();
                    return up.includes('QS') || up.includes('Q5') || up.includes('OS') || l.match(/\d+:\d+/) || up.includes('AL-');
                });
                
                if (qsIndex !== -1) {
                    targetSurah.value = lines[qsIndex].replace(/Q5/ig, 'QS').replace(/OS/ig, 'QS');
                    
                    // Sisa baris di bawahnya anggap terjemahan (mengabaikan no hal di awal/akhir)
                    let transLines = lines.slice(qsIndex + 1);
                    
                    // Bersihkan nomor halaman acak yang sering muncul sendirian dari OCR
                    transLines = transLines.filter(l => !l.match(/^\s*\d+\s*$/)); 
                    
                    // Buang tulisan watermark "The Living Quran" jika OCR iseng membacanya
                    transLines = transLines.filter(l => !l.toUpperCase().includes('THE LIVING') && !l.toUpperCase().includes('LIVING QURAN'));

                    targetTranslation.value = transLines.join(' ');
                } else {
                    // Fallback kasar: Kalau tidak ketemu cirinya, anggap baris 1 (atau 2) itu surah
                    let possibleSurahIndex = lines.length > 2 ? 1 : 0; // Skip watermark on line 0 if exist
                    targetSurah.value = lines[possibleSurahIndex];
                    
                    let transLines = lines.slice(possibleSurahIndex + 1).filter(l => !l.match(/^\s*\d+\s*$/));
                    targetTranslation.value = transLines.join(' ');
                }
            } else if (type === 'back') {
                // Parsing logic for Bagian Belakang
                // Mengharapkan kata kunci "Insight" dan "What to do"
                let text = rawText.replace(/\\n/g, ' ');

                // Pencarian Regex kasar
                let insightMatch = rawText.match(/Insight\s*:?([\s\S]*?)(?=What to do:|What to do\s*:|$)/i);
                let actionMatch = rawText.match(/What to do\s*:?([\s\S]*)/i);

                if (insightMatch && insightMatch[1]) {
                    targetInsight.value = insightMatch[1].replace(/\n/g, ' ').trim();
                }

                if (actionMatch && actionMatch[1]) {
                    targetAction.value = actionMatch[1].replace(/\n/g, ' ').trim();
                }

                if (!insightMatch && !actionMatch) {
                   targetInsight.value = rawText.trim();
                   alert("Format Insight/Action tidak terbaca sempurna. Semuanya masuk ke insight.");
                }
            }
            
            loadingStr.classList.add('hidden');
            
            // Add tiny animation to show success
            if(type === 'front') {
                targetSurah.parentElement.classList.add('animate-pulse');
                targetTranslation.parentElement.classList.add('animate-pulse');
                setTimeout(() => {
                    targetSurah.parentElement.classList.remove('animate-pulse');
                    targetTranslation.parentElement.classList.remove('animate-pulse');
                }, 1000);
            } else {
                targetInsight.parentElement.classList.add('animate-pulse');
                targetAction.parentElement.classList.add('animate-pulse');
                setTimeout(() => {
                    targetInsight.parentElement.classList.remove('animate-pulse');
                    targetAction.parentElement.classList.remove('animate-pulse');
                }, 1000);
            }

        } catch (err) {
            console.error(err);
            alert("Maaf, gagal memproses foto. Pastikan pencahayaan cukup dan foto fokus.");
            loadingStr.classList.add('hidden');
        }
        });
    }
</script>
@endsection
