-- Database Guide: Manual Pages (Panduan Pengunaan dalam Aplikasi)
-- File ini berisi panduan yang muncul saat pengguna membuka series di aplikasi JAR.

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE manual_pages;

-- Panduan untuk semua Series (Miracle, Parenting, Huffaz, Marriage, Healing)
-- Series ID dari 1 sampai 5

-- Mengulang untuk setiap Series
INSERT INTO manual_pages (series_id, page_number, title, content, created_at, updated_at) VALUES
-- Series 1 (Miracle)
(1, 1, 'Cara Menggunakan TLQ Jar', '1. Niatkan karena Allah.\n2. Duduk dengan tenang.\n3. Ambil satu gulungan secara acak.', NOW(), NOW()),
(1, 2, 'Doa Sebelum Membaca', 'اللَّهُمَّ انْفَعْنِي بِمَا عَلَّمْتَنِي وَعَلِّمْنِي مَا يَنْفَعُنِي وَزِدْنِي عِلْمًا\n"Ya Allah, berilah manfaat atas apa yang Engkau ajarkan kepadaku, dan ajarkanlah kepadaku apa yang bermanfaat bagiku, dan tambahkanlah ilmu kepadaku."', NOW(), NOW()),
(1, 3, 'Siap Berinteraksi?', 'Tutup panduan ini dan silakan goyang HP Anda untuk mendapatkan pesan cinta dari Al-Quran hari ini.', NOW(), NOW()),

-- Series 2 (Parenting)
(2, 1, 'Cara Menggunakan TLQ Jar', '1. Niatkan karena Allah.\n2. Duduk dengan tenang.\n3. Ambil satu gulungan secara acak.', NOW(), NOW()),
(2, 2, 'Doa Sebelum Membaca', 'اللَّهُمَّ انْفَعْنِي بِمَا عَلَّمْتَنِي وَعَلِّمْنِي مَا يَنْفَعُنِي وَزِدْنِي عِلْمًا\n"Ya Allah, berilah manfaat atas apa yang Engkau ajarkan kepadaku, dan ajarkanlah kepadaku apa yang bermanfaat bagiku, dan tambahkanlah ilmu kepadaku."', NOW(), NOW()),
(2, 3, 'Siap Berinteraksi?', 'Tutup panduan ini dan silakan goyang HP Anda untuk mendapatkan pesan cinta dari Al-Quran hari ini.', NOW(), NOW()),

-- Series 3 (Huffaz)
(3, 1, 'Cara Menggunakan TLQ Jar', '1. Niatkan karena Allah.\n2. Duduk dengan tenang.\n3. Ambil satu gulungan secara acak.', NOW(), NOW()),
(3, 2, 'Doa Sebelum Membaca', 'اللَّهُمَّ انْفَعْنِي بِمَا عَلَّمْتَنِي وَعَلِّمْنِي مَا يَنْفَعُنِي وَزِدْنِي عِلْمًا\n"Ya Allah, berilah manfaat atas apa yang Engkau ajarkan kepadaku, dan ajarkanlah kepadaku apa yang bermanfaat bagiku, dan tambahkanlah ilmu kepadaku."', NOW(), NOW()),
(3, 3, 'Siap Berinteraksi?', 'Tutup panduan ini dan silakan goyang HP Anda untuk mendapatkan pesan cinta dari Al-Quran hari ini.', NOW(), NOW()),

-- Series 4 (Marriage)
(4, 1, 'Cara Menggunakan TLQ Jar', '1. Niatkan karena Allah.\n2. Duduk dengan tenang.\n3. Ambil satu gulungan secara acak.', NOW(), NOW()),
(4, 2, 'Doa Sebelum Membaca', 'اللَّهُمَّ انْفَعْنِي بِمَا عَلَّمْتَنِي وَعَلِّمْنِي مَا يَنْفَعُنِي وَزِدْنِي عِلْمًا\n"Ya Allah, berilah manfaat atas apa yang Engkau ajarkan kepadaku, dan ajarkanlah kepadaku apa yang bermanfaat bagiku, dan tambahkanlah ilmu kepadaku."', NOW(), NOW()),
(4, 3, 'Siap Berinteraksi?', 'Tutup panduan ini dan silakan goyang HP Anda untuk mendapatkan pesan cinta dari Al-Quran hari ini.', NOW(), NOW()),

-- Series 5 (Healing)
(5, 1, 'Cara Menggunakan TLQ Jar', '1. Niatkan karena Allah.\n2. Duduk dengan tenang.\n3. Ambil satu gulungan secara acak.', NOW(), NOW()),
(5, 2, 'Doa Sebelum Membaca', 'اللَّهُمَّ انْفَعْنِي بِمَا عَلَّمْتَنِي وَعَلِّمْنِي مَا يَنْفَعُنِي وَزِدْنِي عِلْمًا\n"Ya Allah, berilah manfaat atas apa yang Engkau ajarkan kepadaku, dan ajarkanlah kepadaku apa yang bermanfaat bagiku, dan tambahkanlah ilmu kepadaku."', NOW(), NOW()),
(5, 3, 'Siap Berinteraksi?', 'Tutup panduan ini dan silakan goyang HP Anda untuk mendapatkan pesan cinta dari Al-Quran hari ini.', NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;
