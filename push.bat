@echo off
echo [1/3] Membersihkan Cache Laravel...
php artisan optimize:clear

echo [2/3] Menambahkan Perubahan ke Git...
git add -A

echo [3/3] Mengirim Perubahan ke GitHub...
git commit -m "Update Keamanan: RBAC Monitoring, Islamic Error Pages, Login Audit & Block"
git push origin main

echo.
echo ===========================================
echo ALHAMDULILLAH, UPDATE BERHASIL DIKIRIM!
echo Sekarang jalankan ./deploy.sh di VPS Bapak.
echo ===========================================
pause
