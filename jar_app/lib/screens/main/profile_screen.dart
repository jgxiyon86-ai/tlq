import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:jar_app/core/app_colors.dart';
import 'package:jar_app/screens/auth/login_screen.dart';
import 'package:jar_app/services/api_service.dart';
import 'package:jar_app/services/notification_service.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  Map<String, dynamic> _user = {};
  bool _isGoogleUser = false;
  bool _isLoading = false;

  final _nameCtrl = TextEditingController();
  final _nicknameCtrl = TextEditingController();
  final _oldPassCtrl = TextEditingController();
  final _newPassCtrl = TextEditingController();
  final _confirmPassCtrl = TextEditingController();
  List<dynamic> _licenses = [];
  
  TimeOfDay _morningTime = const TimeOfDay(hour: 5, minute: 0);
  TimeOfDay _eveningTime = const TimeOfDay(hour: 17, minute: 0);


  @override
  void initState() {
    super.initState();
    _loadUser();
    _loadLicenses();
    _loadNotificationSettings();
  }

  Future<void> _loadNotificationSettings() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _morningTime = TimeOfDay(
        hour: prefs.getInt('notif_morning_hour') ?? 5,
        minute: prefs.getInt('notif_morning_minute') ?? 0,
      );
      _eveningTime = TimeOfDay(
        hour: prefs.getInt('notif_evening_hour') ?? 17,
        minute: prefs.getInt('notif_evening_minute') ?? 0,
      );
    });
  }

  Future<void> _saveNotificationSettings() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt('notif_morning_hour', _morningTime.hour);
    await prefs.setInt('notif_morning_minute', _morningTime.minute);
    await prefs.setInt('notif_evening_hour', _eveningTime.hour);
    await prefs.setInt('notif_evening_minute', _eveningTime.minute);
    
    // Reschedule notifications with new times
    await NotificationService.scheduleDailyReminders();
    _showSnack('Jadwal pengingat berhasil diperbarui!', AppColors.emeraldIslamic);
  }

  Future<void> _selectTime(BuildContext context, bool isMorning) async {
    final TimeOfDay? picked = await showTimePicker(
      context: context,
      initialTime: isMorning ? _morningTime : _eveningTime,
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: AppColors.emeraldIslamic,
              onPrimary: Colors.white,
              onSurface: AppColors.textDark,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() {
        if (isMorning) {
          _morningTime = picked;
        } else {
          _eveningTime = picked;
        }
      });
      _saveNotificationSettings();
    }
  }


  Future<void> _loadLicenses() async {
    try {
      final data = await ApiService.getDashboard();
      if (mounted) {
        setState(() {
          _licenses = data['jars'] ?? [];
        });
      }
    } catch (_) {}
  }

  Future<void> _releaseLicense(String key) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Lepas Lisensi?'),
        content: const Text('Jar ini akan bisa diaktifkan kembali oleh orang lain. Anda akan kehilangan akses ke Jar ini.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Ya, Lepaskan', style: TextStyle(color: Colors.red))),
        ],
      ),
    );

    if (confirmed == true) {
      setState(() => _isLoading = true);
      try {
        await ApiService.releaseJar(key);
        _showSnack('Lisensi berhasil dilepaskan!', AppColors.emeraldIslamic);
        await _loadLicenses();
      } catch (e) {
        _showSnack(e.toString(), Colors.red);
      } finally {
        if (mounted) setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _showTransferDialog(String key) async {
    final emailCtrl = TextEditingController();
    final result = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Text('Pindah Lisensi', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('Masukkan email akun tujuan untuk memindahkan Jar ini secara langsung.'),
            const SizedBox(height: 16),
            TextField(
              controller: emailCtrl,
              decoration: InputDecoration(
                labelText: 'Email Tujuan',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
              ),
              keyboardType: TextInputType.emailAddress,
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Pindahkan'),
          ),
        ],
      ),
    );

    if (result == true && emailCtrl.text.isNotEmpty) {
      setState(() => _isLoading = true);
      try {
        final res = await ApiService.transferJar(key, emailCtrl.text.trim());
        _showSnack(res['message'] ?? 'Berhasil dipindahkan!', AppColors.emeraldIslamic);
        await _loadLicenses();
      } catch (e) {
        _showSnack(e.toString(), Colors.red);
      } finally {
        if (mounted) setState(() => _isLoading = false);
      }
    }
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _nicknameCtrl.dispose();
    _oldPassCtrl.dispose();
    _newPassCtrl.dispose();
    _confirmPassCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userStr = prefs.getString('user');
    if (userStr != null) {
      final user = json.decode(userStr) as Map<String, dynamic>;
      setState(() {
        _user = user;
        // Jika login via Google, google_id tidak null dan password bisa null
        _isGoogleUser = user['google_id'] != null;
        _nameCtrl.text = user['name'] ?? '';
        _nicknameCtrl.text = user['nickname'] ?? '';
      });
    }
  }

  Future<void> _updateProfile() async {
    if (_nameCtrl.text.trim().isEmpty) {
      _showSnack('Nama lengkap tidak boleh kosong!', Colors.red);
      return;
    }

    setState(() => _isLoading = true);
    try {
      await ApiService.updateProfile(_nameCtrl.text, _nicknameCtrl.text);
      _showSnack('Profil berhasil diperbarui!', AppColors.emeraldIslamic);
      await _loadUser(); // Muat ulang profil agar tampilan berubah
    } catch (e) {
      _showSnack(e.toString(), Colors.red);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _changePassword() async {
    if (_newPassCtrl.text != _confirmPassCtrl.text) {
      _showSnack('Password baru tidak cocok!', Colors.red);
      return;
    }
    if (_newPassCtrl.text.length < 6) {
      _showSnack('Password minimal 6 karakter.', Colors.red);
      return;
    }

    setState(() => _isLoading = true);
    try {
      await ApiService.changePassword(
        _oldPassCtrl.text,
        _newPassCtrl.text,
      );
      _showSnack('Password berhasil diganti!', AppColors.emeraldIslamic);
      _oldPassCtrl.clear();
      _newPassCtrl.clear();
      _confirmPassCtrl.clear();
    } catch (e) {
      _showSnack(e.toString(), Colors.red);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _syncManualData() async {
    setState(() => _isLoading = true);
    try {
      await ApiService.getDashboard();
      _showSnack('Data offline berhasil diperbarui!', AppColors.emeraldIslamic);
    } catch (e) {
      _showSnack('Gagal sinkronisasi: ${e.toString()}', Colors.red);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showSnack(String msg, Color color) {
    if (!mounted) return;
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(msg), backgroundColor: color));
  }

  Future<void> _logout() async {
    setState(() => _isLoading = true);
    try {
      // CLEAR EVERYTHING LOCALLY FIRST
      final prefs = await SharedPreferences.getInstance();
      await prefs.clear();
      
      // Call API (silent)
      try {
        await ApiService.logout();
      } catch (_) {}

      if (mounted) {
        Navigator.pushAndRemoveUntil(
          context,
          MaterialPageRoute(builder: (context) => const LoginScreen()),
          (route) => false,
        );
      }
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final initial = (_user['name'] ?? 'U').substring(0, 1).toUpperCase();

    return Scaffold(
      backgroundColor: const Color(0xFFF6F5F0),
      appBar: AppBar(
        backgroundColor: AppColors.emeraldIslamic,
        foregroundColor: Colors.white,
        title: Text('Profil Saya',
            style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Avatar
            const SizedBox(height: 16),
            CircleAvatar(
              radius: 50,
              backgroundColor: AppColors.emeraldIslamic,
              child: Text(initial,
                  style: GoogleFonts.inter(
                      fontSize: 36,
                      color: Colors.white,
                      fontWeight: FontWeight.bold)),
            ),
            const SizedBox(height: 12),
            Text(_user['name'] ?? '',
                style: GoogleFonts.inter(
                    fontSize: 20, fontWeight: FontWeight.bold)),
            const SizedBox(height: 4),
            Text(_user['email'] ?? _user['phone'] ?? '',
                style: GoogleFonts.inter(color: Colors.grey[600])),
            if (_isGoogleUser) ...[
              const SizedBox(height: 8),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                decoration: BoxDecoration(
                  color: Colors.blue.shade50,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: Colors.blue.shade200),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.g_mobiledata,
                        color: Colors.blue, size: 18),
                    const SizedBox(width: 4),
                    Text('Login via Google',
                        style: GoogleFonts.inter(
                            color: Colors.blue,
                            fontSize: 12,
                            fontWeight: FontWeight.w600)),
                  ],
                ),
              ),
            ],

            const SizedBox(height: 28),

            // Profile Form (For all users)
            _SectionCard(
              title: '👤 Informasi Pribadi',
              child: Column(
                children: [
                  _buildInput('Nama Lengkap', _nameCtrl),
                  const SizedBox(height: 12),
                  _buildInput('Nama Panggilan (Contoh: "Ayah")', _nicknameCtrl),
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: _isLoading ? null : _updateProfile,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.emeraldIslamic,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14)),
                      ),
                      child: _isLoading
                          ? const SizedBox(
                              height: 18,
                              width: 18,
                              child: CircularProgressIndicator(
                                  color: Colors.white, strokeWidth: 2))
                          : Text('Simpan Profil',
                              style: GoogleFonts.inter(
                                  fontWeight: FontWeight.bold)),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Notification Settings
            _SectionCard(
              title: '🔔 Notifikasi & Pengingat',
              child: Column(
                children: [
                   Text(
                    'Atur jadwal pengingat harian sesuai kenyamanan Anda di lokasi Anda saat ini.',
                    style: GoogleFonts.inter(fontSize: 12, color: Colors.grey[600]),
                  ),
                  const SizedBox(height: 16),
                  _buildTimeTile(
                    '🌅 Pengingat Pagi', 
                    'Untuk memulai tantangan & ambil ayat',
                    _morningTime,
                    () => _selectTime(context, true),
                  ),
                  const Divider(height: 24, thickness: 0.5),
                  _buildTimeTile(
                    '🌇 Pengingat Sore', 
                    'Untuk mengisi catatan perubahan (After)',
                    _eveningTime,
                    () => _selectTime(context, false),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),


            const SizedBox(height: 20),

            const SizedBox(height: 28),

            // Profile Form has been moved above

            // Change password section (hidden for Google users)
            if (!_isGoogleUser) ...[
              _SectionCard(
                title: '🔒 Ganti Password',
                child: Column(
                  children: [
                    _buildInput('Password Lama', _oldPassCtrl, obscure: true),
                    const SizedBox(height: 12),
                    _buildInput('Password Baru', _newPassCtrl, obscure: true),
                    const SizedBox(height: 12),
                    _buildInput('Konfirmasi Password Baru', _confirmPassCtrl,
                        obscure: true),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _changePassword,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.emeraldIslamic,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(14)),
                        ),
                        child: _isLoading
                            ? const SizedBox(
                                height: 18,
                                width: 18,
                                child: CircularProgressIndicator(
                                    color: Colors.white, strokeWidth: 2))
                            : Text('Simpan Password',
                                style: GoogleFonts.inter(
                                    fontWeight: FontWeight.bold)),
                      ),
                    ),
                  ],
                ),
              ),
            ] else ...[
              _SectionCard(
                title: '🔒 Password',
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  child: Row(
                    children: [
                      const Icon(Icons.info_outline,
                          color: Colors.blue, size: 18),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Akun ini menggunakan login Google. Untuk mengganti password, silakan atur melalui akun Google Anda.',
                          style: GoogleFonts.inter(
                              color: Colors.grey[600], fontSize: 13),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],


            // Logout button
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _logout,
                icon: const Icon(Icons.logout, color: Colors.red),
                label: Text('Keluar',
                    style:
                        GoogleFonts.inter(color: Colors.red, fontWeight: FontWeight.bold)),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  side: const BorderSide(color: Colors.red),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14)),
                ),
              ),
            ),

            const SizedBox(height: 24),

            // My Licenses Section
            _SectionCard(
              title: 'Lisensi Saya (Botol Jar)',
              child: _licenses.isEmpty
                  ? Text('Belum ada Jar aktif.', style: GoogleFonts.inter(color: Colors.grey, fontSize: 13))
                  : Column(
                      children: _licenses.map((l) => ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: const CircleAvatar(
                          backgroundColor: AppColors.emeraldIslamic,
                          child: Icon(Icons.inventory_2, color: Colors.white, size: 20),
                        ),
                        title: Text(l['series']?['name'] ?? 'TLQ Jar', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 14)),
                        subtitle: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(l['license_key'], style: GoogleFonts.inter(fontSize: 12, color: Colors.grey)),
                            const SizedBox(height: 2),
                            Text('Terhubung Ke: ${l['user']?['email'] ?? 'Akun Saya'}', 
                                style: GoogleFonts.inter(fontSize: 11, color: AppColors.emeraldIslamic, fontWeight: FontWeight.w500)),
                            if (l['device_id'] != null)
                              Text('ID Perangkat: ${l['device_id']}', 
                                  style: GoogleFonts.inter(fontSize: 10, color: Colors.grey.shade500)),
                          ],
                        ),
                        trailing: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            TextButton(
                              onPressed: () => _showTransferDialog(l['license_key']),
                              child: const Text('Pindah', style: TextStyle(color: Colors.blue, fontSize: 12)),
                            ),
                            TextButton(
                              onPressed: () => _releaseLicense(l['license_key']),
                              child: const Text('Lepas', style: TextStyle(color: Colors.red, fontSize: 12)),
                            ),
                          ],
                        ),
                      )).toList(),
                    ),
            ),

            const SizedBox(height: 40),
            Center(
              child: Column(
                children: [
                  Text('The Living Quran © 2025',
                      style: GoogleFonts.inter(color: Colors.grey[400], fontSize: 12)),
                  const SizedBox(height: 4),
                  Text('Versi 1.0.0',
                      style: GoogleFonts.inter(color: Colors.grey[400], fontSize: 11, fontWeight: FontWeight.bold)),
                ],
              ),
            ),
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }

  Widget _buildTimeTile(String title, String subtitle, TimeOfDay time, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 8.0),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 14)),
                  Text(subtitle, style: GoogleFonts.inter(fontSize: 11, color: Colors.grey)),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              decoration: BoxDecoration(
                color: AppColors.emeraldIslamic.withOpacity(0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                time.format(context),
                style: GoogleFonts.inter(
                  fontWeight: FontWeight.bold, 
                  color: AppColors.emeraldIslamic,
                  fontSize: 16
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInput(String label, TextEditingController ctrl,

      {bool obscure = false}) {
    return TextField(
      controller: ctrl,
      obscureText: obscure,
      decoration: InputDecoration(
        labelText: label,
        labelStyle: GoogleFonts.inter(fontSize: 13),
        filled: true,
        fillColor: Colors.grey.shade50,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: Colors.grey.shade200),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide(color: Colors.grey.shade200),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide:
              const BorderSide(color: AppColors.emeraldIslamic, width: 2),
        ),
      ),
    );
  }
}

class _SectionCard extends StatelessWidget {
  final String title;
  final Widget child;
  const _SectionCard({required this.title, required this.child});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(color: Colors.black.withAlpha(8), blurRadius: 15)
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title,
              style: GoogleFonts.inter(
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                  color: AppColors.textDark)),
          const SizedBox(height: 16),
          child,
        ],
      ),
    );
  }
}
