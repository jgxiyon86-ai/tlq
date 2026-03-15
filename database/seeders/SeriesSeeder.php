<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $series = [
            [
                'name'        => 'Miracle',
                'slug'        => 'miracle',
                'color_hex'   => '#3730A3', // Biru Dongker / Ungu Gelap (kartu biru gelap)
                'description' => 'Keajaiban-keajaiban Al-Quran untuk mempertebal iman.',
            ],
            [
                'name'        => 'Marriage',
                'slug'        => 'married',
                'color_hex'   => '#D97706', // Kuning Emas (kartu kuning)
                'description' => 'Membangun keluarga sakinah, mawaddah, wa rahmah.',
            ],
            [
                'name'        => 'Parenting',
                'slug'        => 'parenting',
                'color_hex'   => '#EA580C', // Oranye Hangat
                'description' => 'Panduan mendidik anak sesuai nilai-nilai Al-Quran.',
            ],
            [
                'name'        => 'Huffaz',
                'slug'        => 'huffaz',
                'color_hex'   => '#0284C7', // Biru Langit (kartu biru muda)
                'description' => 'Motivasi dan panduan bagi para penjaga Al-Quran.',
            ],
            [
                'name'        => 'Healing',
                'slug'        => 'healing',
                'color_hex'   => '#E63995', // Gradasi multi-emosi (Pink sebagai base)
                'description' => 'Menemukan ketenangan dan pemulihan jiwa melalui Al-Quran.',
            ],
        ];

        foreach ($series as $s) {
            \App\Models\Series::updateOrCreate(['slug' => $s['slug']], $s);
        }
    }
}
