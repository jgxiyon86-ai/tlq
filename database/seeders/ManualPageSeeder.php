<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ManualPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $series = \App\Models\Series::all();

        foreach ($series as $s) {
            // Page 1: How to use
            \App\Models\ManualPage::create([
                'series_id' => $s->id,
                'page_number' => 1,
                'title' => 'Cara Menggunakan TLQ Jar',
                'content' => '1. Niatkan karena Allah.\n2. Duduk dengan tenang.\n3. Ambil satu gulungan secara acak.'
            ]);

            // Page 2: Dua
            \App\Models\ManualPage::create([
                'series_id' => $s->id,
                'page_number' => 2,
                'title' => 'Doa Sebelum Membaca',
                'content' => 'اللَّهُمَّ انْفَعْنِي بِمَا عَلَّمْتَنِي وَعَلِّمْنِي مَا يَنْفَعُنِي وَزِدْنِي عِلْمًا\n"Ya Allah, berilah manfaat atas apa yang Engkau ajarkan kepadaku, dan ajarkanlah kepadaku apa yang bermanfaat bagiku, dan tambahkanlah ilmu kepadaku."'
            ]);
            
            // Page 3: Closing
            \App\Models\ManualPage::create([
                'series_id' => $s->id,
                'page_number' => 3,
                'title' => 'Siap Berinteraksi?',
                'content' => 'Tutup panduan ini dan silakan goyang HP Anda untuk mendapatkan pesan cinta dari Al-Quran hari ini.'
            ]);
        }
    }
}
