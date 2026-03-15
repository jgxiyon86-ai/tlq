# TLQ App (The Living Quran) - Handoff Document

## 🎯 Objective
Mengimplementasikan fitur **Challenge & Journal 40 Hari** di dalam aplikasi TLQ. Fitur ini memungkinkan pengguna untuk melakukan refleksi jurnal (Catatan Pagi & Catatan Sore) berdasarkan ayat yang didapat dari "Gacha" atau acak ayat (terkait dengan lisensi Jar yang mereka miliki).

Terdapat 5 jenis Series Jar:
1. Miracle
2. Parenting
3. Huffaz
4. Marriage
5. Healing (Baru ditambahkan)

---

## ✅ Apa yang Sudah Selesai Dikerjakan

### Backend (Laravel)
1. **Database & Migration:**
   - Tabel `challenges` (menyimpan progress 40 hari per user per series).
   - Tabel `journal_entries` (menyimpan form Before & After harian lengkap dengan ayat hasil Gacha).
2. **Model & Seeder:**
   - Model `Challenge` dan `JournalEntry` dengan relasi yang sesuai.
   - `SeriesSeeder` diperbarui: Menambahkan series **Healing** dan menyesuaikan kode warna (*Hex Colors*) untuk kelima series agar cocok dengan produk fisik tutup botol.
3. **API Endpoints (ChallengeController):**
   - `POST /api/v1/challenges/activate`: Memulai tantangan 40 hari (syarat: punya lisensi Jar tersebut).
   - `GET /api/v1/challenges`: Menampilkan tantangan yang sedang aktif.
   - `POST /api/v1/challenges/{id}/roll`: Melakukan Acak Ayat / Gacha untuk hari itu. (Otomatis terkunci untuk hari tersebut).
   - `POST /api/v1/journal/{id}/before`: Menyimpan catatan pagi (Pesan cintaNya, perasaan, apa yang akan dilakukan).
   - `POST /api/v1/journal/{id}/after`: Menyimpan catatan sore (Apa yang berhasil, perubahan, perasaan). Otomatis memajukan progres ke hari berikutnya (Day +1).
   - `GET /api/v1/challenges/{id}/history`: Melihat riwayat jurnal selama 40 hari.
4. **Auth Controller:**
   - Menambahkan fitur ganti password (`changePassword`) khusus untuk user yang login menggunakan Email standar (User Google disembunyikan/ditolak agar tidak error).

### Frontend (Flutter)
1. **Integrasi API (`api_service.dart`):**
   - Semua endpoint baru (Challenge, Journal, Change Password) sudah tersambung di Service.
2. **Layar Utama (`home_screen.dart`):**
   - Rombak total UI Rak Botol (Grid 2 kolom) menampilkan 5 macam Series (Miracle, Marriage, Parenting, Huffaz, Healing).
   - Menyesuaikan warna dinamis. Khusus "Healing" memiliki gradasi pelangi (5 warna) untuk representasi stabilitas multi-emosi.
   - Status botol: Cekungan menyala jika "Aktif ✓" dan abu-abu ber-gembok bila belum punya ("Terkunci").
   - Menambahkan seksi "Tantangan 40 Hari" di bagian bawah beserta tombol "+ Mulai" yang memunculkan dialog daftar Jar aktif untuk dijadikan tantangan.
   - Label teks di pojok atas diubah menjadi "The Living Quran" (dari tulisan Arab sebelumnya).
3. **Layar Profil (`profile_screen.dart`):**
   - Layar baru diakses dengan Icon User di Home Screen.
   - Menampilkan Nama, Email, dan inisial huruf di Avatar.
   - Menampilkan Form Ganti Password (khusus login email).
   - Tombol Keluar / Logout.
4. **Layar Jurnal / Challenge (`challenge_screen.dart`):**
   - UI elegan dengan Header memuat nama Series, progres Bar (Hari ke-X dari 40).
   - Animasi **Gacha Acak Ayat** dengan tombol Start -> Stop.
   - Kartu "Catatan Pagi (Before)" dan "Catatan Sore (After)".
   - Bottom sheet interaktif untuk pengisian jurnal harian.
5. **Aset Ilustrasi (AI Gen):**
   - Telah dibuat 5 gambar AI beresolusi tinggi (circular frame) untuk menggantikan Icon Botol generik pada Home Screen. Gambar-gambar ini memiliki desain berdasarkan gambar referensi tutup botol yang dikirimkan. (Khusus Healing bebas dari lambang salib).

---

## 🚀 Langkah Selanjutnya (Next Steps)

Jika Anda melanjutkan pengerjaan dengan asisten AI lain, berikan prompt berikut atau tunjukkan file ini agar asisten tahu persis dari mana harus melanjutkan:

1. **Memasang Gambar AI ke Flutter:**
   - 5 gambar hasil AI untuk icon Jar sudah selesai di-generate (tersimpan di riwayat Artifact sesi sebelumnya).
   - **Tugas:** Unduh gambar-gambar tersebut, masukkan ke dalam folder `assets/images/` di project Flutter.
   - Daftarkan di `pubspec.yaml`.
   - Modifikasi fungsi `_buildJarCard` di `home_screen.dart` untuk mengganti penggunaan `Icon(Icons.local_drink_rounded)` menjadi gambar aset `Image.asset(...)`.
2. **Fitur Pengingat (Reminder Notifications):**
   - **Tugas:** Implementasikan Local Push Notification (misalnya dengan package `flutter_local_notifications`).
   - Jadwalkan pengingat pagi (Cth: jam 06:00 pagi) "MasyaAllah, ayo buka The Living Quran dan acak ayat hari ini!"
   - Jadwalkan pengingat sore (Cth: jam 17:00 sore) "Alhamdulillah hari ini hampir selesai, yuk isi Catatan Soremu!"
3. **Final Testing & Deploy:**
   - Lakukan tes kelancaran (End-to-End Test) mulai dari user Scan QR Jar baru -> Mulai Challenge -> Acak Ayat -> Isi Pagi -> Isi Sore -> Harinya berganti -> dst sampai 40 Hari.
   - Jika sudah stabil, siapkan Build rilis (`flutter build apk` / `aab`).

---
_Catatan: Seluler pengguna harus menjalankan perintah `C:\src\flutter\bin\flutter.bat devices` atau mengkonfigurasi IP jika di Debug via Wi-Fi Android._
