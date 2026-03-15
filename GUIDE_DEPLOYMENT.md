# Panduan Deployment & Operasional TLQ (The Living Quran)

Dokumen ini berisi informasi penting mengenai konfigurasi server, aplikasi Android, dan pengelolaan data untuk sistem TLQ.

## 1. Akses Sistem
*   **Domain**: `https://tlq.pondokalima.com`
*   **Admin Panel**: `https://tlq.pondokalima.com/login`
*   **Default Admin**: `admin@tlq.com` / `admin123`
*   **Link Download APK**: `https://tlq.pondokalima.com/TLQ_v3.apk`

---

## 2. Struktur Folder Produksi
Aplikasi menggunakan folder `public` sebagai root. Jika hosting menggunakan cPanel/Shared Hosting, pastikan file `.htaccess` di root folder berisi:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

---

## 3. Konfigurasi Sosial Login (Google)
Fitur login Google menggunakan Google Cloud Console Project: **thelivingquran**.

### Konfigurasi `.env` Server:
Pastikan variabel berikut terisi di file `.env` server:
```env
GOOGLE_CLIENT_ID=861818693354-lj32u37920uuu1rm7lvcfli4ph59vni3.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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T
```

### Konfigurasi Android (Google Console):
Aplikasi Android sudah didaftarkan dengan:
*   **Package Name**: `com.tlq.jar_app`
*   **SHA-1 Fingerprint**: `B6:FF:16:60:4C:30:9D:4D:FA:3A:65:B9:A1:3E:49:95:7B:F9:88:46` (Debug Key laptop saat ini).
*   **Status**: In Production.

---

## 4. Pengelolaan Data (Seeding)
Untuk memasukkan data awal (Admin & 38 Content Al-Quran) ke database server baru, jalankan perintah:
```bash
php artisan db:seed --class=ProductionDataSeeder
```

---

## 5. Pemeliharaan Rutin (SSH Commands)
Setiap kali ada update dari GitHub, jalankan perintah ini di SSH server:
```bash
# Ambil kode terbaru
git pull origin main

# Update konfigurasi (jika ada perubahan .env)
php artisan config:cache
php artisan route:cache

# Link storage (pastikan folder public/storage terhubung)
php artisan storage:link
```

---

## 6. Build Android (Update APK)
Jika ingin melakukan build ulang APK di laptop:
1. Pastikan `baseUrl` di `lib/services/api_service.dart` adalah `https://tlq.pondokalima.com/api/v1`.
2. Jalankan: `flutter build apk --release`.
3. Copy file `build/app/outputs/flutter-apk/app-release.apk` ke folder `public/` di server dan beri nama `TLQ_v3.apk`.

---

**Kontak Pengembang**: jgxiyon86@gmail.com
**Status Project**: Live / Production
