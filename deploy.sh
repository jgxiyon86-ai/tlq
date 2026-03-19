#!/bin/bash

echo "-------------------------------------------"
echo "        TLQ JAR ADMIN DEPLOYMENT          "
echo "-------------------------------------------"

# 1. Tarik update terbaru dari GitHub
echo "[1/4] Git Pull dari GitHub..."
git pull origin main

# 2. Update Database (jika ada kolom baru)
echo "[2/4] Menjalankan Migration Database..."
php artisan migrate --force

# 3. Bersihkan Cache & Optimasi
echo "[3/4] Membersihkan Cache & Optimize..."
php artisan optimize:clear

# 4. Beri izin folder storage
echo "[4/4] Memberi Izin Folder Storage & Cache..."
chmod -R 775 storage bootstrap/cache

echo "-------------------------------------------"
echo "  ALHAMDULILLAH, VPS BERHASIL DI-UPDATE!   "
echo "-------------------------------------------"
