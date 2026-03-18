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

  // Controllers for the reflection form
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

  // ─── Does the server say reflections are already saved? ───────────────────
  bool get _alreadySaved =>
      widget.challenge['has_reflections'] == true ||
      widget.challenge['has_reflections'] == 1;

  @override
  void initState() {
    super.initState();
    // Pre-fill controllers if reflections already exist (for re-editing if needed)
    final saved = widget.challenge['final_reflections'];
    if (saved != null && saved is Map) {
      for (var key in _controllers.keys) {
        _controllers[key]?.text = saved[key]?.toString() ?? '';
      }
    }
  }

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

  // ═══════════════════════════════════════════════════════════════
  // BUILD
  // ═══════════════════════════════════════════════════════════════
  @override
  Widget build(BuildContext context) {
    // ── SMART VIEW: If already saved → jump straight to full report ──
    if (_alreadySaved) {
      return Scaffold(
        backgroundColor: const Color(0xFFF9FAFB),
        appBar: AppBar(
          title: Text('Laporan Perjalanan', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
          backgroundColor: Colors.white,
          foregroundColor: AppColors.emeraldIslamic,
          elevation: 0,
        ),
        body: _buildFullReport(),
      );
    }

    // ── FORM VIEW: New submission ──
    return Scaffold(
      backgroundColor: Colors.white,
      body: PageView(
        controller: _pageController,
        physics: const NeverScrollableScrollPhysics(),
        onPageChanged: (idx) => setState(() => _currentPage = idx),
        children: [
          _buildReflectionsStep(),
          _buildSummaryStep(),
          _buildCelebrationStep(),
        ],
      ),
    );
  }

  // ═══════════════════════════════════════════════════════════════
  // FULL REPORT VIEW (when already saved)
  // ═══════════════════════════════════════════════════════════════
  Widget _buildFullReport() {
    final stats = _calculateStats();
    final entries = widget.challenge['journal_entries'] as List? ?? [];
    final reflections = widget.challenge['final_reflections'] as Map? ?? {};
    final seriesName = widget.challenge['series']?['name']?.toString() ?? '';
    final totalDays = widget.challenge['total_days']?.toString() ?? '-';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Header ──────────────────────────────────────────
          FadeInDown(
            child: Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [AppColors.emeraldIslamic, Color(0xFF0A3D2E)],
                ),
                borderRadius: BorderRadius.circular(28),
              ),
              child: Column(
                children: [
                  const Text('🏆', style: TextStyle(fontSize: 48)),
                  const SizedBox(height: 12),
                  Text('Barakallah Fiikum!',
                      style: GoogleFonts.inter(
                          color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 6),
                  Text('Perjalanan $totalDays hari • $seriesName',
                      style: GoogleFonts.inter(color: Colors.white70, fontSize: 13)),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),

          // ── Rapotan Mujahadah Stats ─────────────────────────
          FadeInUp(
            delay: const Duration(milliseconds: 200),
            child: _sectionTitle('📊 Rapotan Amal'),
          ),
          FadeInUp(
            delay: const Duration(milliseconds: 300),
            child: Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                boxShadow: [BoxShadow(color: Colors.black.withAlpha(5), blurRadius: 20)],
              ),
              child: Column(
                children: [
                  _statRow('Hari Mujahadah', '${stats['onTime']} Hari', 'Tepat Waktu (Disiplin)', AppColors.emeraldIslamic, Icons.verified_user),
                  const Divider(height: 28),
                  _statRow('Hari Perjuangan', '${stats['catchUp']} Hari', 'Mode Kejar (Ijtihad)', Colors.amber.shade700, Icons.flash_on),
                  const Divider(height: 28),
                  _statRow('Hari Terlewat', '${stats['missed']} Hari', 'Bahan Muhasabah Diri', Colors.red.shade400, Icons.hourglass_empty),
                ],
              ),
            ),
          ),
          const SizedBox(height: 28),

          // ── Perubahan Indah ─────────────────────────────────
          if (reflections.isNotEmpty) ...[
            FadeInUp(child: _sectionTitle('💚 Perubahan yang Allah Berikan')),
            const SizedBox(height: 12),
            ...reflections.entries.where((e) => e.value.toString().trim().isNotEmpty).map((e) {
              return FadeInLeft(
                child: Container(
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: AppColors.emeraldIslamic.withAlpha(30)),
                    boxShadow: [BoxShadow(color: Colors.black.withAlpha(3), blurRadius: 10)],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(e.key.toUpperCase(),
                          style: GoogleFonts.inter(
                              fontSize: 10,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 1,
                              color: AppColors.emeraldIslamic)),
                      const SizedBox(height: 6),
                      Text(e.value.toString(),
                          style: GoogleFonts.inter(fontSize: 14, height: 1.6, color: const Color(0xFF1A2E1A))),
                    ],
                  ),
                ),
              );
            }).toList(),
            const SizedBox(height: 24),
          ],

          // ── Riwayat Semua Ayat & Catatan ───────────────────
          FadeInUp(child: _sectionTitle('📖 Riwayat Ayat & Catatan Harian')),
          const SizedBox(height: 12),
          ...List.generate(entries.length, (idx) {
            final entry = entries[idx] as Map;
            final content = entry['content'] as Map? ?? {};
            final dayNum = entry['day_number']?.toString() ?? '-';
            final hasBefore = entry['before_pesan'] != null && entry['before_pesan'].toString().trim().isNotEmpty;
            final hasAfter = entry['after_berhasil'] != null && entry['after_berhasil'].toString().trim().isNotEmpty;
            final isFilled = entry['is_completed'] == true || entry['is_completed'] == 1;

            return FadeInUp(
              delay: Duration(milliseconds: 100 * idx),
              child: Container(
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: isFilled ? AppColors.emeraldIslamic.withAlpha(40) : Colors.grey.shade100),
                  boxShadow: [BoxShadow(color: Colors.black.withAlpha(4), blurRadius: 12)],
                ),
                child: Theme(
                  data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
                  child: ExpansionTile(
                    initiallyExpanded: false,
                    leading: Container(
                      width: 36, height: 36,
                      decoration: BoxDecoration(
                        color: isFilled ? AppColors.emeraldIslamic.withAlpha(20) : Colors.grey.shade100,
                        shape: BoxShape.circle,
                      ),
                      child: Center(
                        child: Text(dayNum,
                            style: GoogleFonts.inter(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: isFilled ? AppColors.emeraldIslamic : Colors.grey)),
                      ),
                    ),
                    title: Text(
                      content['surah_ayah']?.toString() ?? (isFilled ? 'Hari $dayNum' : 'Hari $dayNum — Belum diisi'),
                      style: GoogleFonts.inter(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: isFilled ? const Color(0xFF1A2E1A) : Colors.grey),
                    ),
                    subtitle: isFilled
                        ? Text(
                            (hasBefore ? '🌅 Before  ' : '') + (hasAfter ? '🌇 After' : ''),
                            style: GoogleFonts.inter(fontSize: 11, color: Colors.grey[500]),
                          )
                        : null,
                    children: isFilled ? [
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (content['arabic_text'] != null) ...[
                              Text(content['arabic_text'].toString(),
                                  textAlign: TextAlign.right,
                                  style: GoogleFonts.amiri(fontSize: 20, height: 2)),
                              const SizedBox(height: 8),
                              Text(content['translation']?.toString() ?? '',
                                  style: GoogleFonts.inter(fontSize: 12, color: Colors.grey[700], height: 1.5)),
                              if (content['insight'] != null) ...[
                                const SizedBox(height: 10),
                                _miniChip('💡 Insight', content['insight'].toString(), AppColors.goldIslamic),
                              ],
                            ],
                            if (hasBefore) ...[
                              const Divider(height: 24),
                              _miniChip('🌅 Before', entry['before_pesan'].toString(), AppColors.emeraldIslamic),
                            ],
                            if (hasAfter) ...[
                              const SizedBox(height: 8),
                              _miniChip('🌇 After', entry['after_berhasil'].toString(), AppColors.goldIslamic),
                            ],
                          ],
                        ),
                      ),
                    ] : [],
                  ),
                ),
              ),
            );
          }),

          const SizedBox(height: 24),
          // ── Closing ─────────────────────────────────────────
          FadeInUp(
            child: Container(
              padding: const EdgeInsets.all(28),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(28),
                boxShadow: [BoxShadow(color: Colors.black.withAlpha(5), blurRadius: 20)],
              ),
              child: Column(
                children: [
                  Text('آَمِينَ يَا رَبَّ الْعَالَمِيْن',
                      style: GoogleFonts.amiri(fontSize: 26, color: AppColors.goldIslamic)),
                  const SizedBox(height: 16),
                  Text(
                    'Syukron atas mujahadahmu. Semoga tiap langkah kakimu kini selalu dibimbing oleh Al-Quran. Teruslah istiqomah, Sobat TLQ. 💚',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.inter(fontSize: 13, height: 1.8, color: Colors.grey[600]),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 28),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () => Navigator.pop(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.emeraldIslamic,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 20),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
              ),
              child: Text('Kembali ke Dashboard', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
            ),
          ),
          const SizedBox(height: 40),
        ],
      ),
    );
  }

  Widget _sectionTitle(String text) => Padding(
    padding: const EdgeInsets.only(bottom: 12),
    child: Text(text, style: GoogleFonts.inter(fontSize: 15, fontWeight: FontWeight.w800, color: const Color(0xFF1A2E1A))),
  );

  Widget _miniChip(String label, String value, Color color) => Container(
    padding: const EdgeInsets.all(12),
    decoration: BoxDecoration(
      color: color.withAlpha(10),
      borderRadius: BorderRadius.circular(12),
      border: Border.all(color: color.withAlpha(40)),
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: GoogleFonts.inter(fontSize: 10, fontWeight: FontWeight.bold, color: color)),
        const SizedBox(height: 4),
        Text(value, style: GoogleFonts.inter(fontSize: 12, height: 1.5, color: const Color(0xFF1A2E1A))),
      ],
    ),
  );

  Widget _statRow(String label, String value, String sub, Color color, IconData icon) {
    return Row(
      children: [
        Container(
          width: 44, height: 44,
          decoration: BoxDecoration(color: color.withAlpha(20), shape: BoxShape.circle),
          child: Icon(icon, color: color, size: 22),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label, style: GoogleFonts.inter(fontSize: 12, color: Colors.grey[500], fontWeight: FontWeight.bold)),
              Text(sub, style: GoogleFonts.inter(fontSize: 10, color: Colors.grey[400])),
            ],
          ),
        ),
        Text(value, style: GoogleFonts.inter(fontSize: 17, fontWeight: FontWeight.w900, color: color)),
      ],
    );
  }

  // ═══════════════════════════════════════════════════════════════
  // FORM FLOW (PageView: Step 1 → Step 2 → Step 3)
  // ═══════════════════════════════════════════════════════════════
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
                        style: GoogleFonts.inter(color: AppColors.emeraldIslamic, fontSize: 28, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Text('Mari sejenak menoleh ke belakang, perubahan indah apa yang Allah titipkan selama perjalanan ini?',
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
                          style: GoogleFonts.inter(fontWeight: FontWeight.w900, fontSize: 10, letterSpacing: 1, color: AppColors.emeraldIslamic)),
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
                    _statRow('Hari Mujahadah', '${stats['onTime']} Hari', 'Tepat Waktu (Disiplin)', AppColors.emeraldIslamic, Icons.verified_user),
                    const Divider(height: 40),
                    _statRow('Hari Perjuangan', '${stats['catchUp']} Hari', 'Mode Kejar (Ijtihad)', Colors.amber.shade700, Icons.flash_on),
                    const Divider(height: 40),
                    _statRow('Hari Terlewat', '${stats['missed']} Hari', 'Butuh Perbaikan Niat', Colors.red.shade400, Icons.hourglass_empty),
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
                          'Total $total hari telah kamu lalui bersama Al-Quran. Setiap detikmu adalah saksi di akhirat kelak.',
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
              width: 120, height: 120,
              decoration: BoxDecoration(color: AppColors.goldIslamic.withAlpha(20), shape: BoxShape.circle),
              child: const Center(child: Text('🏆', style: TextStyle(fontSize: 60))),
            ),
          ),
          const SizedBox(height: 40),
          FadeInUp(
            delay: const Duration(milliseconds: 500),
            child: Text('Barakallah Fiikum!',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(fontSize: 32, fontWeight: FontWeight.bold, color: AppColors.goldIslamic)),
          ),
          const SizedBox(height: 20),
          FadeInUp(
            delay: const Duration(milliseconds: 700),
            child: Text(
                'Perjalanan ${widget.challenge['total_days']} hari ini bukanlah akhir, melainkan pintu gerbang menuju hidup yang lebih tenang bersama Cahaya Al-Quran.',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(fontSize: 16, height: 1.6, color: AppColors.emeraldIslamic, fontWeight: FontWeight.w600)),
          ),
          const SizedBox(height: 16),
          FadeInUp(
            delay: const Duration(milliseconds: 900),
            child: Text(
                'Syukron atas mujahadah (kesungguhan) kamu menghidupkan ayat-ayat Nya. Teruslah istiqomah, Sobat TLQ.',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(fontSize: 13, height: 1.8, color: Colors.grey[600])),
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
