import 'package:animate_do/animate_do.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:jar_app/core/app_colors.dart';
import 'package:jar_app/services/api_service.dart';

import 'package:jar_app/screens/journal/journal_detail_screen.dart';

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
      final responseData = await ApiService.getChallengeHistory(widget.challenge['id']);
      final List<dynamic> entries = (responseData['entries'] as List?) ?? [];
      final Map<String, dynamic>? challengeData = responseData['challenge'] as Map<String, dynamic>?;

      int currentDay = challengeData != null 
          ? (int.tryParse(challengeData['current_day'].toString()) ?? 1)
          : (int.tryParse(widget.challenge['current_day']?.toString() ?? '1') ?? 1);
          
      int totalDays = challengeData != null
          ? (int.tryParse(challengeData['total_days'].toString()) ?? 40)
          : (int.tryParse(widget.challenge['total_days']?.toString() ?? '40') ?? 40);
      
      String todayStr = DateTime.now().toIso8601String().substring(0, 10);

      // Generate a full list of all days
      List<dynamic> fullHistory = [];
      for (var e in entries) {
        final m = Map<String, dynamic>.from(e as Map);
        String entryDate = m['entry_date']?.toString() ?? '';
        if (entryDate.length > 10) entryDate = entryDate.substring(0, 10);
        
        final isCompleted = m['is_completed'] == true || m['is_completed'] == 1 || m['is_completed'] == "1";
        
        if (isCompleted) {
          m['status'] = 'completed';
        } else if (entryDate.compareTo(todayStr) < 0) {
          m['status'] = 'missed'; // Past but not done
        } else if (entryDate == todayStr) {
          m['status'] = 'today'; // Today's slot
        } else {
          m['status'] = 'locked'; // Future slot
        }
        fullHistory.add(m);
      }

      setState(() {
        _entries = fullHistory.reversed.toList(); // Newest first
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
    final status = entry['status'] ?? 'completed';
    final bool isCompleted = entry['after_berhasil'] != null;
    final content = entry['content'] ?? {};

    if (status == 'missed') {
      final bool hasAyat = entry['content_id'] != null;
      return FadeInUp(
        child: InkWell(
          onTap: () async {
            // Navigate back to challenge screen with this specific entry for catching up
            Navigator.pop(context, entry); 
          },
          child: Container(
            margin: const EdgeInsets.only(bottom: 16),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.orange.withAlpha(10),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: Colors.orange.withAlpha(30)),
            ),
            child: Row(
              children: [
                CircleAvatar(
                  backgroundColor: Colors.orange.withAlpha(20),
                  child: Text('${entry['day_number']}', 
                         style: const TextStyle(color: Colors.orange, fontWeight: FontWeight.bold)),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Hari ${entry['day_number']}', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
                      Text(hasAyat ? 'Ayat Tertinggal (Belum Selesai) ⚠️' : 'Ayat Belum Diambil (Tertinggal) ⚠️', 
                           style: GoogleFonts.inter(color: Colors.orange, fontSize: 12)),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right, color: Colors.orange),
              ],
            ),
          ),
        ),
      );
    }

    if (status == 'locked') {
       return FadeInUp(
        child: Container(
          margin: const EdgeInsets.only(bottom: 16),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.grey.withAlpha(10),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Colors.grey.withAlpha(30)),
          ),
          child: Row(
            children: [
              CircleAvatar(
                backgroundColor: Colors.grey[200],
                child: Text('${entry['day_number']}', 
                       style: const TextStyle(color: Colors.grey, fontWeight: FontWeight.bold)),
              ),
              const SizedBox(width: 16),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Hari ${entry['day_number']}', style: GoogleFonts.inter(fontWeight: FontWeight.bold, color: Colors.grey)),
                  Text('Terkunci 🔒', style: GoogleFonts.inter(color: Colors.grey, fontSize: 12)),
                ],
              )
            ],
          ),
        ),
      );
    }

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
            backgroundColor: isCompleted ? AppColors.emeraldIslamic : Colors.orange.withAlpha(40),
            child: Text('${entry['day_number']}', 
                   style: TextStyle(color: isCompleted ? Colors.white : Colors.orange, fontWeight: FontWeight.bold)),
          ),
          title: Text(content['surah_ayah'] ?? 'Ayat Hari Ini', 
                 style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 14)),
          subtitle: Text(isCompleted ? 'Selesai ✓' : 'Belum Selesai ⏳', 
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
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      onPressed: () => Navigator.push(context, MaterialPageRoute(
                        builder: (_) => JournalDetailScreen(entry: entry),
                      )),
                      icon: const Icon(Icons.visibility_outlined, size: 16),
                      label: const Text('LIHAT DETAIL', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.emeraldIslamic,
                        side: const BorderSide(color: AppColors.emeraldIslamic),
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ),
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
