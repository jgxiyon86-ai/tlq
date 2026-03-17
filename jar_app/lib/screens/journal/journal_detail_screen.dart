import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:jar_app/core/app_colors.dart';
import 'package:animate_do/animate_do.dart';

class JournalDetailScreen extends StatelessWidget {
  final Map<String, dynamic> entry;
  const JournalDetailScreen({super.key, required this.entry});

  @override
  Widget build(BuildContext context) {
    final content = entry['content'] ?? {};
    final day = entry['day_number'] ?? '?';
    
    return Scaffold(
      backgroundColor: const Color(0xFFF6F5F0),
      appBar: AppBar(
        title: Text('Detail Jurnal Hari $day', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
        backgroundColor: AppColors.emeraldIslamic,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header Card (Ayat)
            FadeInDown(
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [BoxShadow(color: Colors.black.withAlpha(5), blurRadius: 15)],
                ),
                child: Column(
                  children: [
                    Text(
                      content['surah_ayah']?.toString() ?? 'Ayat Pilihan',
                      style: GoogleFonts.inter(
                        fontWeight: FontWeight.bold,
                        color: AppColors.emeraldIslamic,
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      content['arabic_text']?.toString() ?? '',
                      textAlign: TextAlign.right,
                      style: GoogleFonts.amiri(fontSize: 26, height: 2, color: const Color(0xFF1A2E1A)),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      content['translation']?.toString() ?? '',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.inter(fontSize: 14, color: Colors.grey[700], height: 1.6, fontStyle: FontStyle.italic),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Insights & Action Plan
            if (content['insight'] != null || content['action_plan'] != null)
              FadeInLeft(
                child: _buildSectionCard(
                  title: '💡 Insight & What to Do',
                  color: AppColors.goldIslamic,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (content['insight'] != null) ...[
                        Text('Insight:', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.goldIslamic)),
                        const SizedBox(height: 4),
                        Text(content['insight'].toString(), style: GoogleFonts.inter(fontSize: 13, color: Colors.grey[800])),
                        const SizedBox(height: 16),
                      ],
                      if (content['action_plan'] != null) ...[
                        Text('What to Do:', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.emeraldIslamic)),
                        const SizedBox(height: 4),
                        Text(content['action_plan'].toString(), style: GoogleFonts.inter(fontSize: 13, color: Colors.grey[800])),
                      ],
                    ],
                  ),
                ),
              ),
            const SizedBox(height: 16),

            // Before Section
            FadeInLeft(
              delay: const Duration(milliseconds: 200),
              child: _buildSectionCard(
                title: '🌅 Catatan Pagi (Before)',
                color: AppColors.emeraldIslamic,
                child: Column(
                  children: [
                    _buildDetailItem('Pesan Cinta-Nya', entry['before_pesan']),
                    _buildDetailItem('Perasaan', entry['before_perasaan']),
                    _buildDetailItem('Action Plan', entry['before_action']),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // After Section
            FadeInLeft(
              delay: const Duration(milliseconds: 400),
              child: _buildSectionCard(
                title: '🌇 Catatan Sore (After)',
                color: AppColors.goldIslamic,
                child: Column(
                  children: [
                    _buildDetailItem('Keberhasilan', entry['after_berhasil']),
                    _buildDetailItem('Perubahan', entry['after_perubahan']),
                    _buildDetailItem('Perasaan Akhir', entry['after_perasaan']),
                  ],
                ),
              ),
            ),
            
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionCard({required String title, required Color color, required Widget child}) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: color.withAlpha(30)),
        boxShadow: [BoxShadow(color: Colors.black.withAlpha(5), blurRadius: 10)],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 15, color: color)),
          const Divider(height: 24),
          child,
        ],
      ),
    );
  }

  Widget _buildDetailItem(String label, dynamic value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 11, color: Colors.grey)),
          const SizedBox(height: 4),
          Text(value?.toString() ?? '-', style: GoogleFonts.inter(fontSize: 13, color: Colors.black87)),
        ],
      ),
    );
  }
}
