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
      final responseData = await ApiService.getChallengeHistory(widget.challenge['id']);
      final List<dynamic> entries = (responseData['entries'] as List?) ?? [];
      final Map<String, dynamic>? challengeData = responseData['challenge'] as Map<String, dynamic>?;

      int currentDay = challengeData != null 
          ? (int.tryParse(challengeData['current_day'].toString()) ?? 1)
          : (int.tryParse(widget.challenge['current_day']?.toString() ?? '1') ?? 1);
          
      int totalDays = challengeData != null
          ? (int.tryParse(challengeData['total_days'].toString()) ?? 40)
          : (int.tryParse(widget.challenge['total_days']?.toString() ?? '40') ?? 40);
      
      // Map existing entries to their day_number for quick lookup
      final entryMap = <int, dynamic>{};
      for (var e in entries) {
        final day = int.tryParse(e['day_number']?.toString() ?? '0') ?? 0;
        if (day > 0) entryMap[day] = e;
      }

      // Generate a full list of all days
      List<dynamic> fullHistory = [];
      for (int i = 1; i <= totalDays; i++) {
        if (entryMap.containsKey(i)) {
          fullHistory.add(entryMap[i]);
        } else if (i < currentDay) {
          // MISSED DAY
          fullHistory.add({'day_number': i, 'status': 'missed'});
        } else {
          // FUTURE / LOCKED DAY
          fullHistory.add({'day_number': i, 'status': 'locked'});
        }
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
      return FadeInUp(
        child: Container(
          margin: const EdgeInsets.only(bottom: 16),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.red.withAlpha(10),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Colors.red.withAlpha(30)),
          ),
          child: Row(
            children: [
              CircleAvatar(
                backgroundColor: Colors.red.withAlpha(20),
                child: Text('${entry['day_number']}', 
                       style: const TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
              ),
              const SizedBox(width: 16),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Hari ${entry['day_number']}', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
                  Text('Terlewat / Tidak Diisi ❌', style: GoogleFonts.inter(color: Colors.red, fontSize: 12)),
                ],
              )
            ],
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
