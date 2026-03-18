import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:animate_do/animate_do.dart';
import 'package:jar_app/core/app_colors.dart';
import 'package:jar_app/services/api_service.dart';

class FinishedChallengeScreen extends StatefulWidget {
  final Map<String, dynamic> challenge;
  const FinishedChallengeScreen({super.key, required this.challenge});

  @override
  State<FinishedChallengeScreen> createState() => _FinishedChallengeScreenState();
}

class _FinishedChallengeScreenState extends State<FinishedChallengeScreen> {
  final PageController _pageController = PageController();
  int _currentPage = 0;

  final Map<String, TextEditingController> _controllers = {
    'Spiritual': TextEditingController(),
    'Financial': TextEditingController(),
    'Hubungan': TextEditingController(),
    'Kesehatan': TextEditingController(),
    'Emosi & Mental': TextEditingController(),
    'Karir & Bisnis': TextEditingController(),
    'Lainnya': TextEditingController(),
  };

  bool _isSaving = false;

  @override
  void dispose() {
    for (var ctrl in _controllers.values) {
      ctrl.dispose();
    }
    _pageController.dispose();
    super.dispose();
  }

  Future<void> _saveAndFinish() async {
    setState(() => _isSaving = true);
    try {
      final reflections = _controllers.map((key, value) => MapEntry(key, value.text));
      await ApiService.saveFinalReflections(widget.challenge['id'], reflections);
      
      if (mounted) {
        _pageController.nextPage(
          duration: const Duration(milliseconds: 800),
          curve: Curves.fastOutSlowIn,
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString()), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  Map<String, int> _calculateStats() {
    final entries = widget.challenge['journal_entries'] as List? ?? [];
    final totalDays = int.tryParse(widget.challenge['total_days']?.toString() ?? '0') ?? 0;
    
    int onTime = 0;
    int catchUp = 0;

    for (var e in entries) {
      if (e['is_completed'] == true || e['is_completed'] == 1) {
        if (e['is_catch_up'] == true || e['is_catch_up'] == 1) {
          catchUp++;
        } else {
          onTime++;
        }
      }
    }

    int missed = totalDays - (onTime + catchUp);
    return {'onTime': onTime, 'catchUp': catchUp, 'missed': (missed < 0 ? 0 : missed)};
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: PageView(
        controller: _pageController,
        physics: const NeverScrollableScrollPhysics(),
        onPageChanged: (idx) => setState(() => _currentPage = idx),
        children: [
          _buildReflectionsStep(),
          _buildSummaryStep(), // New Stats Page
          _buildCelebrationStep(),
        ],
      ),
    );
  }

  Widget _buildSummaryStep() {
    final stats = _calculateStats();
    final total = stats['onTime']! + stats['catchUp']!;
    
    return Container(
      color: const Color(0xFFF9FAFB),
      child: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(32),
          child: Column(
            children: [
              FadeInDown(
                child: Text('Muhasabah Perjalanan ✨',
                  style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.emeraldIslamic, letterSpacing: 1.5)),
              ),
              const SizedBox(height: 12),
              FadeInDown(
                delay: const Duration(milliseconds: 200),
                child: Text('Rapotan Amalmu',
                    style: GoogleFonts.inter(fontSize: 28, fontWeight: FontWeight.bold, color: const Color(0xFF1A2E1A))),
              ),
              const SizedBox(height: 40),
              
              // Statistics Box
              Container(
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(32),
                  boxShadow: [BoxShadow(color: Colors.black.withAlpha(5), blurRadius: 30)],
                  border: Border.all(color: Colors.grey.shade100),
                ),
                child: Column(
                  children: [
                    _buildStatRow('Hari Mujahadah', '${stats['onTime']} Hari', 'Tepat Waktu (Disiplin)', AppColors.emeraldIslamic, Icons.verified_user),
                    const Divider(height: 40),
                    _buildStatRow('Hari Perjuangan', '${stats['catchUp']} Hari', 'Mode Kejar (Ijtihad)', Colors.amber.shade700, Icons.flash_on),
                    const Divider(height: 40),
                    _buildStatRow('Hari Terlewat', '${stats['missed']} Hari', 'Butuh Perbaikan Niat', Colors.red.shade400, Icons.hourglass_empty),
                  ],
                ),
              ),
              
              const SizedBox(height: 40),
              FadeInUp(
                child: Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: AppColors.emeraldIslamic.withAlpha(10),
                    borderRadius: BorderRadius.circular(24),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.info_outline, color: AppColors.emeraldIslamic),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Text(
                          'Total $total hari telah kamu lalui bersama Al-Quran kali ini. Setiap detikmu adalah saksi di akhirat kelak.',
                          style: GoogleFonts.inter(fontSize: 12, color: AppColors.emeraldIslamic, fontWeight: FontWeight.w600, height: 1.5),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 60),
              FadeInUp(
                child: SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () => _pageController.nextPage(duration: const Duration(milliseconds: 600), curve: Curves.easeInOut),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.goldIslamic,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 20),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                      elevation: 0,
                    ),
                    child: Text('Lihat Pesan Untukmu ➔', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatRow(String label, String value, String sub, Color color, IconData icon) {
    return Row(
      children: [
        Container(
          width: 48, height: 48,
          decoration: BoxDecoration(color: color.withAlpha(20), shape: BoxShape.circle),
          child: Icon(icon, color: color, size: 24),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: GoogleFonts.inter(fontSize: 12, color: Colors.grey[500], fontWeight: FontWeight.bold)),
              const SizedBox(height: 2),
              Text(sub, style: GoogleFonts.inter(fontSize: 10, color: Colors.grey[400])),
            ],
          ),
        ),
        Text(value, style: GoogleFonts.inter(fontSize: 18, fontWeight: FontWeight.w900, color: color)),
      ],
    );
  }

  Widget _buildReflectionsStep() {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [Color(0xFFFDFBF7), Colors.white],
        ),
      ),
      child: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              FadeInDown(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Masha Allah!',
                        style: GoogleFonts.inter(
                            color: AppColors.emeraldIslamic,
                            fontSize: 28,
                            fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Text(
                        'Mari sejenak menoleh ke belakang, perubahan indah apa yang Allah titipkan selama perjalanan ini?',
                        style: GoogleFonts.inter(color: Colors.grey[600], fontSize: 14)),
                  ],
                ),
              ),
              const SizedBox(height: 32),
              
              ..._controllers.entries.map((entry) {
                return Container(
                  margin: const EdgeInsets.only(bottom: 16),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(color: Colors.grey.shade100),
                    boxShadow: [BoxShadow(color: Colors.black.withAlpha(2), blurRadius: 10)],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(entry.key.toUpperCase(),
                          style: GoogleFonts.inter(
                              fontWeight: FontWeight.w900,
                              fontSize: 10,
                              letterSpacing: 1,
                              color: AppColors.emeraldIslamic)),
                      TextField(
                        controller: entry.value,
                        maxLines: null,
                        decoration: const InputDecoration(
                          hintText: 'Perubahan yang dirasakan...',
                          hintStyle: TextStyle(fontSize: 13, color: Colors.grey),
                          border: InputBorder.none,
                          contentPadding: EdgeInsets.only(top: 8),
                        ),
                        style: GoogleFonts.inter(fontSize: 14, height: 1.5),
                      ),
                    ],
                  ),
                );
              }).toList(),

              const SizedBox(height: 24),
              FadeInUp(
                child: SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _isSaving ? null : _saveAndFinish,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.emeraldIslamic,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 20),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
                      elevation: 0,
                    ),
                    child: _isSaving
                      ? const CircularProgressIndicator(color: Colors.white)
                      : Text('Simpan & Lanjutkan',
                          style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 16)),
                  ),
                ),
              ),
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildCelebrationStep() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(40),
      color: Colors.white,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          ZoomIn(
            duration: const Duration(seconds: 1),
            child: Container(
              width: 120,
              height: 120,
              decoration: BoxDecoration(
                color: AppColors.goldIslamic.withAlpha(20),
                shape: BoxShape.circle,
              ),
              child: const Center(
                child: Text('🏆', style: TextStyle(fontSize: 60)),
              ),
            ),
          ),
          const SizedBox(height: 40),
          FadeInUp(
            delay: const Duration(milliseconds: 500),
            child: Text('Barakallah Fiikum!',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(
                    fontSize: 32,
                    fontWeight: FontWeight.bold,
                    color: AppColors.goldIslamic)),
          ),
          const SizedBox(height: 20),
          FadeInUp(
            delay: const Duration(milliseconds: 700),
            child: Text(
                'Perjalanan ${widget.challenge['total_days']} hari ini bukanlah akhir, melainkan pintu gerbang menuju hidup yang lebih tenang bersama Cahaya Al-Quran.',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(
                    fontSize: 16,
                    height: 1.6,
                    color: AppColors.emeraldIslamic,
                    fontWeight: FontWeight.w600)),
          ),
          const SizedBox(height: 16),
          FadeInUp(
            delay: const Duration(milliseconds: 900),
            child: Text(
                'Syukron atas mujahadah (kesungguhan) kamu dalam menghidupkan ayat-ayat Nya. Semoga tiap langkah kakimu kini selalu dibimbing oleh Al-Quran. Teruslah istiqomah, Sobat TLQ.',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(
                    fontSize: 13,
                    height: 1.8,
                    color: Colors.grey[600])),
          ),

          const SizedBox(height: 30),
          FadeInUp(
            delay: const Duration(milliseconds: 1100),
            child: Text('آَمِينَ يَا رَبَّ الْعَالَمِيْن',
                style: GoogleFonts.amiri(fontSize: 28, color: AppColors.goldIslamic)),
          ),
          const SizedBox(height: 60),
          FadeInUp(
            delay: const Duration(milliseconds: 1100),
            child: ElevatedButton(
              onPressed: () => Navigator.pop(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.emeraldIslamic,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 50, vertical: 20),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
              ),
              child: const Text('Ke Dashboard Utama'),
            ),
          ),
        ],
      ),
    );
  }
}
