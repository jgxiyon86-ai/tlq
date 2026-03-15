import 'package:animate_do/animate_do.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:jar_app/core/app_colors.dart';
import 'package:jar_app/services/api_service.dart';

class ChallengeHistoryScreen extends StatefulWidget {
  final Map<String, dynamic> challenge;
  const ChallengeHistoryScreen({super.key, required this.challenge});

  @override
  State<ChallengeHistoryScreen> createState() => _ChallengeHistoryScreenState();
}

class _ChallengeHistoryScreenState extends State<ChallengeHistoryScreen> {
  List<dynamic> _entries = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadHistory();
  }

  Future<void> _loadHistory() async {
    try {
      final entries = await ApiService.getChallengeHistory(widget.challenge['id']);
      setState(() {
        _entries = entries;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F5F0),
      appBar: AppBar(
        title: Text('Riwayat Jurnal', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
        backgroundColor: AppColors.emeraldIslamic,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _entries.isEmpty
              ? _buildEmpty()
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _entries.length,
                  itemBuilder: (ctx, i) => _buildHistoryCard(_entries[i]),
                ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Text('📔', style: TextStyle(fontSize: 48)),
          const SizedBox(height: 16),
          Text('Belum ada riwayat jurnal.', 
               style: GoogleFonts.inter(color: Colors.grey, fontSize: 16)),
        ],
      ),
    );
  }

  Widget _buildHistoryCard(Map<String, dynamic> entry) {
    final bool isCompleted = entry['after_berhasil'] != null;
    final content = entry['content'] ?? {};

    return FadeInUp(
      child: Container(
        margin: const EdgeInsets.only(bottom: 16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [BoxShadow(color: Colors.black.withAlpha(5), blurRadius: 10)],
        ),
        child: ExpansionTile(
          leading: CircleAvatar(
            backgroundColor: isCompleted ? AppColors.emeraldIslamic : Colors.grey[200],
            child: Text('${entry['day_number']}', 
                   style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
          title: Text(content['surah_ayah'] ?? 'Ayat Hari Ini', 
                 style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 14)),
          subtitle: Text(isCompleted ? 'Selesai ✓' : 'Belum Selesai', 
                    style: TextStyle(color: isCompleted ? Colors.green : Colors.orange, fontSize: 12)),
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Divider(),
                  _buildDetailRow('Pesan Cinta-Nya', entry['before_pesan'] ?? '-'),
                  const SizedBox(height: 8),
                  _buildDetailRow('Niat/Action', entry['before_action'] ?? '-'),
                  if (isCompleted) ...[
                    const SizedBox(height: 8),
                    _buildDetailRow('Keberhasilan', entry['after_berhasil'] ?? '-'),
                  ],
                ],
              ),
            )
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 11, color: Colors.grey)),
        const SizedBox(height: 2),
        Text(value, style: GoogleFonts.inter(fontSize: 13)),
      ],
    );
  }
}
