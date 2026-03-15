<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contents = [
            // Miracle
            [
                'series_id' => 1,
                'surah_ayah' => 'Al-Baqarah: 164',
                'arabic_text' => 'إِنَّ فِي خَلْقِ السَّمَاوَاتِ وَالْأَرْضِ...',
                'translation' => 'Sesungguhnya dalam penciptaan langit dan bumi...',
                'insight' => 'Ayat ini mengajak kita merenungi keteraturan alam semesta sebagai bukti kebesaran Ilahi.',
                'action_plan' => 'Luangkan waktu 5 menit hari ini untuk menatap langit/pohon dan mengucap Subhanallah.'
            ],
            // Parenting
            [
                'series_id' => 2,
                'surah_ayah' => 'Luqman: 13',
                'arabic_text' => 'وَإِذْ قَالَ لُقْمَانُ لِابْنِهِ وَهُوَ يَعِظُهُ يَا بُنَيَّ لَا تُشْرِكْ بِاللَّهِ...',
                'translation' => 'Dan ingatlah ketika Luqman berkata kepada anaknya...',
                'insight' => 'Pilar utama mendidik anak adalah menanamkan Tauhid dengan penuh kasih sayang.',
                'action_plan' => 'Berikan pelukan hangat pada sikecil dan bisikkan satu kalimat motivasi Islami.'
            ],
            // Huffaz
            [
                'series_id' => 3,
                'surah_ayah' => 'Al-Qamar: 17',
                'arabic_text' => 'وَلَقَدْ يَسَّرْنَا الْقُرْآنَ لِلذِّكْرِ فَهَلْ مِن مُّدَّكِرٍ',
                'translation' => 'Dan sesungguhnya telah Kami mudahkan Al-Quran untuk pelajaran...',
                'insight' => 'Al-Quran dijamin mudah jika kita memiliki tekad dan keistiqomahan.',
                'action_plan' => 'Murojaah 5 ayat terakhir yang dihafal sebelum tidur malam ini.'
            ],
            // Married
            [
                'series_id' => 4,
                'surah_ayah' => 'Ar-Rum: 21',
                'arabic_text' => 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا...',
                'translation' => 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu isteri-isteri...',
                'insight' => 'Pasangan adalah ketenangan (Sakinah). Kunci rumah tangga adalah kasih sayang (Mawaddah & Rahmah).',
                'action_plan' => 'Ucapkan terima kasih atas hal kecil yang dilakukan pasangan hari ini.'
            ],
        ];

        foreach ($contents as $c) {
            \App\Models\Content::create($c);
        }
    }
}
