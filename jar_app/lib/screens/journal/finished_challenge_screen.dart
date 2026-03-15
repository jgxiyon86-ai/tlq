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
          duration: const Duration(milliseconds: 600),
          curve: Curves.easeInOutBack,
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
          _buildCelebrationStep(),
        ],
      ),
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
                    Text('Alhamdulillah!',
                        style: GoogleFonts.inter(
                            color: AppColors.emeraldIslamic,
                            fontSize: 28,
                            fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Text(
                        'Kamu telah menyelesaikan tantangan ini. Mari catat perubahan yang Allah berikan dalam hidupmu.',
                        style: GoogleFonts.inter(color: Colors.grey[600], fontSize: 14)),
                  ],
                ),
              ),
              const SizedBox(height: 32),
              
              // Table Header
              Container(
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                decoration: BoxDecoration(
                  color: AppColors.emeraldIslamic,
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                ),
                child: Row(
                  children: [
                    Expanded(
                      flex: 2,
                      child: Text('ASPEK HIDUP',
                          style: GoogleFonts.inter(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 12)),
                    ),
                    Expanded(
                      flex: 3,
                      child: Text('PERUBAHAN YANG ALLAH BERIKAN',
                          style: GoogleFonts.inter(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 12)),
                    ),
                  ],
                ),
              ),

              // Table Body
              ..._controllers.entries.map((entry) {
                return Container(
                  decoration: BoxDecoration(
                    border: Border(
                      left: BorderSide(color: Colors.grey.shade200),
                      right: BorderSide(color: Colors.grey.shade200),
                      bottom: BorderSide(color: Colors.grey.shade200),
                    ),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        flex: 2,
                        child: Container(
                          padding: const EdgeInsets.all(16),
                          color: Colors.grey.shade50,
                          child: Text(entry.key,
                              style: GoogleFonts.inter(
                                  fontWeight: FontWeight.w600,
                                  fontSize: 13,
                                  color: AppColors.emeraldIslamic)),
                        ),
                      ),
                      Expanded(
                        flex: 3,
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                          child: TextField(
                            controller: entry.value,
                            maxLines: null,
                            decoration: const InputDecoration(
                              hintText: 'Tulis di sini...',
                              hintStyle: TextStyle(fontSize: 12),
                              border: InputBorder.none,
                            ),
                            style: GoogleFonts.inter(fontSize: 13),
                          ),
                        ),
                      ),
                    ],
                  ),
                );
              }).toList(),

              const SizedBox(height: 32),
              
              FadeInUp(
                child: SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _isSaving ? null : _saveAndFinish,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.emeraldIslamic,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 18),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
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
            child: Text('Barakallah!',
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
                '${widget.challenge['total_days']} hari bukan akhir, tapi awal hidup baru bersama Qur\'an.',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(
                    fontSize: 18,
                    height: 1.5,
                    color: AppColors.emeraldIslamic,
                    fontWeight: FontWeight.w600)),
          ),
          const SizedBox(height: 12),
          FadeInUp(
            delay: const Duration(milliseconds: 900),
            child: Text(
                'Jadilah terus The Living Quran yang menghidupkan Al Quran dalam setiap langkahmu.',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(
                    fontSize: 14,
                    height: 1.5,
                    color: Colors.grey[600])),
          ),
          const SizedBox(height: 60),
          FadeInUp(
            delay: const Duration(milliseconds: 1100),
            child: ElevatedButton(
              onPressed: () => Navigator.pop(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.emeraldIslamic,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 16),
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
