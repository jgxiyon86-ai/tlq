import 'dart:async';
import 'package:flutter/material.dart';
import 'package:animate_do/animate_do.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:jar_app/core/app_colors.dart';
import 'package:jar_app/services/api_service.dart';
import 'finished_challenge_screen.dart';
import 'challenge_history_screen.dart';

class ChallengeScreen extends StatefulWidget {
  final Map<String, dynamic> challenge;
  const ChallengeScreen({super.key, required this.challenge});

  @override
  State<ChallengeScreen> createState() => _ChallengeScreenState();
}

class _ChallengeScreenState extends State<ChallengeScreen>
    with TickerProviderStateMixin {
  Map<String, dynamic>? _todayEntry;
  bool _isLoading = false;
  bool _isRolling = false;

  // Gacha animation
  Timer? _rollTimer;
  String _displayAyah = 'Bismillah, mari kita jemput pesan cinta-Nya...';
  late AnimationController _pulseController;

  @override
  void initState() {
    super.initState();
    _pulseController =
        AnimationController(vsync: this, duration: const Duration(milliseconds: 600))
          ..repeat(reverse: true);
    _loadTodayEntry();
  }

  @override
  void dispose() {
    _rollTimer?.cancel();
    _pulseController.dispose();
    super.dispose();
  }

  Future<void> _loadTodayEntry() async {
    setState(() => _isLoading = true);
    final history = await ApiService.getChallengeHistory(widget.challenge['id']);
    final day = widget.challenge['current_day'];
    final entry = history.firstWhere(
      (e) => e['day_number'] == day,
      orElse: () => null,
    );
    setState(() {
      _todayEntry = entry;
      _isLoading = false;
    });
  }

  // --- GACHA ANIMATION ---
  final List<String> _shuffleTexts = [
    'Sedang mengacak hikmah...',
    'Menunggu petunjuk terbaik...',
    'Mempersiapkan pesan cinta-Nya...',
    'Bismillah, mohon petunjuk-Mu...',
    'Hati yang bersih menerima cahaya...',
  ];

  void _startGacha() {
    if (_isRolling) {
      // STOP: lock the ayat
      _rollTimer?.cancel();
      setState(() => _isRolling = false);
      _doRollContent();
    } else {
      // START: animate rolling
      setState(() {
        _isRolling = true;
        _displayAyah = _shuffleTexts[0];
      });
      int i = 0;
      _rollTimer = Timer.periodic(const Duration(milliseconds: 150), (_) {
        i = (i + 1) % _shuffleTexts.length;
        if (mounted) setState(() => _displayAyah = _shuffleTexts[i]);
      });
    }
  }

  Future<void> _doRollContent() async {
    setState(() => _isLoading = true);
    try {
      final result = await ApiService.rollContent(widget.challenge['id']);
      setState(() {
        _todayEntry = result['entry'];
        _isLoading = false;
      });
      if (mounted && result['message'] != null) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(result['message']),
          backgroundColor: result['already_done_today'] == true ? Colors.orange : AppColors.emeraldIslamic,
        ));
      }
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        String msg = e.toString().replaceAll('Exception: ', '');
        ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(msg), backgroundColor: Colors.orange));
      }
    }
  }

  // --- BEFORE & AFTER FORMS ---
  void _showBeforeDialog() {
    final pesanCtrl = TextEditingController();
    final perasaanCtrl = TextEditingController();
    final actionCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _JournalForm(
        title: '🌅 Catatan Pagi (Before)',
        color: AppColors.emeraldIslamic,
        fields: [
          _FieldConfig('Apa pesan cintaNya (ayat) yang kamu dapat hari ini?', pesanCtrl),
          _FieldConfig('Apa perasaanmu setelah Allah kasih petunjuk ini?', perasaanCtrl),
          _FieldConfig('Apa yang akan kamu lakukan? (What to do)', actionCtrl),
        ],
        onSave: () async {
          Navigator.pop(ctx);
          final result = await ApiService.saveBefore(
            _todayEntry!['id'],
            pesanCtrl.text,
            perasaanCtrl.text,
            actionCtrl.text,
          );
          if (mounted) {
            setState(() {
               _todayEntry = result['entry'];
            });
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
              content: Text('Catatan pagi tersimpan! Sampai sore ya 😊'),
              backgroundColor: AppColors.emeraldIslamic,
            ));
          }
        },
      ),
    );
  }

  void _showAfterDialog() {
    final berhasilCtrl = TextEditingController();
    final perubahanCtrl = TextEditingController();
    final perasaanCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _JournalForm(
        title: '🌇 Catatan Sore (After)',
        color: AppColors.goldIslamic,
        fields: [
          _FieldConfig('Apa yang hari ini berhasil kamu lakukan?', berhasilCtrl),
          _FieldConfig('Perubahan apa yang kamu rasakan hari ini?', perubahanCtrl),
          _FieldConfig('Apa perasaanmu setelah menghidupkan ayatNya?', perasaanCtrl),
        ],
        onSave: () async {
          Navigator.pop(ctx);
          final result = await ApiService.saveAfter(
            _todayEntry!['id'],
            berhasilCtrl.text,
            perubahanCtrl.text,
            perasaanCtrl.text,
          );
          
          if (mounted) {
            setState(() {
               _todayEntry = result['entry'];
            });
            if (result['challenge']['is_completed'] == true) {
              Navigator.pushReplacement(
                context,
                MaterialPageRoute(
                  builder: (context) => FinishedChallengeScreen(challenge: result['challenge']),
                ),
              );
            } else {
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                content: Text('MasyaAllah! Hari ini selesai, satu langkah lebih dekat!'),
                backgroundColor: AppColors.goldIslamic,
              ));
            }
          }
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final challenge = widget.challenge;
    final currentDay = challenge['current_day'] as int;
    final totalDays = challenge['total_days'] as int;
    final seriesName = challenge['series']?['name'] ?? 'TLQ';
    final hasBefore = _todayEntry?['before_pesan'] != null;
    final hasAfter = _todayEntry?['after_berhasil'] != null;
    final hasEntry = _todayEntry != null;

    return Scaffold(
      backgroundColor: const Color(0xFFF6F5F0),
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 220,
            pinned: true,
            backgroundColor: AppColors.emeraldIslamic,
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [AppColors.emeraldIslamic, Color(0xFF0A3D2E)],
                  ),
                ),
                child: SafeArea(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            IconButton(
                              icon: const Icon(Icons.arrow_back, color: Colors.white),
                              onPressed: () => Navigator.pop(context),
                            ),
                            IconButton(
                              icon: const Icon(Icons.history, color: Colors.white),
                              onPressed: () => Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => ChallengeHistoryScreen(challenge: widget.challenge),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const Spacer(),
                        Text('📖 Tantangan 40 Hari',
                            style: GoogleFonts.inter(color: Colors.white70, fontSize: 13)),
                        const SizedBox(height: 4),
                        Text(seriesName,
                            style: GoogleFonts.inter(
                                color: Colors.white, fontSize: 26, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Text('Hari ke-$currentDay dari $totalDays',
                                style: GoogleFonts.inter(color: Colors.white70, fontSize: 13)),
                            const SizedBox(width: 12),
                            Expanded(
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(10),
                                child: LinearProgressIndicator(
                                  value: currentDay / totalDays,
                                  backgroundColor: Colors.white24,
                                  valueColor:
                                      const AlwaysStoppedAnimation<Color>(AppColors.goldIslamic),
                                  minHeight: 8,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
          SliverPadding(
            padding: const EdgeInsets.all(20),
            sliver: SliverList(
              delegate: SliverChildListDelegate([
                // ACTIVATION WARNING
                if (widget.challenge['has_license'] == false)
                  _buildActivationWarning(),

                // GACHA SECTION
                if (!hasEntry) ...[
                  FadeInUp(
                    child: Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(28),
                        boxShadow: [BoxShadow(color: Colors.black.withAlpha(10), blurRadius: 20)],
                      ),
                      child: Column(
                        children: [
                          Text('Hari ke-$currentDay',
                              style: GoogleFonts.inter(
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.emeraldIslamic,
                                  fontSize: 16)),
                          const SizedBox(height: 16),
                          AnimatedBuilder(
                            animation: _pulseController,
                            builder: (_, __) => Opacity(
                              opacity: _isRolling
                                  ? 0.4 + (_pulseController.value * 0.6)
                                  : 1.0,
                              child: Text(
                                _isRolling ? _displayAyah : 'Acak Ayat untuk Hari Ini',
                                textAlign: TextAlign.center,
                                style: GoogleFonts.inter(
                                  fontSize: _isRolling ? 13 : 15,
                                  fontStyle: _isRolling ? FontStyle.italic : FontStyle.normal,
                                  color: Colors.grey[700],
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 20),
                          GestureDetector(
                            onTap: _isLoading ? null : _startGacha,
                            child: Column(
                              children: [
                                Stack(
                                  alignment: Alignment.center,
                                  children: [
                                    if (!_isRolling && !hasEntry)
                                      Pulse(
                                        infinite: true,
                                        child: Container(
                                          width: 100,
                                          height: 100,
                                          decoration: BoxDecoration(
                                            color: AppColors.emeraldIslamic.withAlpha(20),
                                            shape: BoxShape.circle,
                                          ),
                                        ),
                                      ),
                                    AnimatedContainer(
                                      duration: const Duration(milliseconds: 300),
                                      width: 80,
                                      height: 80,
                                      decoration: BoxDecoration(
                                        gradient: LinearGradient(
                                          colors: _isRolling
                                              ? [Colors.red.shade400, Colors.red.shade700]
                                              : [AppColors.emeraldIslamic, const Color(0xFF0F5132)],
                                        ),
                                        shape: BoxShape.circle,
                                        boxShadow: [
                                          BoxShadow(
                                            color: (_isRolling ? Colors.red : AppColors.emeraldIslamic)
                                                .withAlpha(80),
                                            blurRadius: 20,
                                            offset: const Offset(0, 8),
                                          ),
                                        ],
                                      ),
                                      child: Icon(
                                        _isRolling ? Icons.stop_rounded : Icons.play_arrow_rounded,
                                        color: Colors.white,
                                        size: 40,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 16),
                                Text(
                                  _isRolling ? 'TAP UNTUK BERHENTI' : 'KLIK UNTUK MULAI',
                                  style: GoogleFonts.inter(
                                      color: _isRolling ? Colors.red : AppColors.emeraldIslamic,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 12,
                                      letterSpacing: 1.2),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],

                // TODAY'S AYAT
                if (hasEntry) ...[
                  FadeInDown(
                    child: Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: AppColors.emeraldIslamic.withAlpha(15),
                        borderRadius: BorderRadius.circular(24),
                        border: Border.all(color: AppColors.emeraldIslamic.withAlpha(60)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            _todayEntry?['content']?['surah_ayah'] ?? '',
                            style: GoogleFonts.inter(
                                fontWeight: FontWeight.bold,
                                color: AppColors.emeraldIslamic,
                                fontSize: 14),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            _todayEntry?['content']?['arabic_text'] ?? '',
                            textAlign: TextAlign.right,
                            style: GoogleFonts.amiri(fontSize: 22, height: 2),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            _todayEntry?['content']?['translation'] ?? '',
                            style: GoogleFonts.inter(fontSize: 13, color: Colors.grey[700]),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // BEFORE CARD
                  _SectionCard(
                    emoji: '🌅',
                    title: 'Catatan Pagi (Before)',
                    color: AppColors.emeraldIslamic,
                    isDone: hasBefore,
                    content: hasBefore
                        ? '"${_todayEntry!['before_pesan']}"'
                        : 'Isi catatan pagi untuk memulai harimu dengan niat yang kuat.',
                    onTap: hasBefore ? null : _showBeforeDialog,
                    buttonLabel: 'Isi Sekarang',
                  ),
                  const SizedBox(height: 12),

                  // AFTER CARD
                  _SectionCard(
                    emoji: '🌇',
                    title: 'Catatan Sore (After)',
                    color: AppColors.goldIslamic,
                    isDone: hasAfter,
                    isLocked: !hasBefore,
                    content: hasAfter
                        ? '"${_todayEntry!['after_berhasil']}"'
                        : hasBefore
                            ? 'Waktunya menuliskan perubahan yang kamu rasakan hari ini!'
                            : '🔒 Isi catatan Pagi terlebih dahulu.',
                    onTap: (hasBefore && !hasAfter) ? _showAfterDialog : null,
                    buttonLabel: 'Isi Sekarang',
                  ),
                ],

                if (hasEntry && hasAfter) ...[
                  const SizedBox(height: 24),
                  FadeInUp(
                    child: Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [AppColors.goldIslamic, Color(0xFFD4A017)],
                        ),
                        borderRadius: BorderRadius.circular(24),
                      ),
                      child: Row(
                        children: [
                          const Text('🎉', style: TextStyle(fontSize: 32)),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Hari $currentDay Selesai!',
                                    style: GoogleFonts.inter(
                                        color: Colors.white,
                                        fontWeight: FontWeight.bold,
                                        fontSize: 16)),
                                Text('MasyaAllah! $currentDay hari lebih dekat dengan-Nya.',
                                    style: GoogleFonts.inter(
                                        color: Colors.white70, fontSize: 12)),
                              ],
                            ),
                          )
                        ],
                      ),
                    ),
                  ),
                ],

                const SizedBox(height: 60),
              ]),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActivationWarning() {
    return FadeInDown(
      child: Container(
        margin: const EdgeInsets.only(bottom: 20),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.orange.shade50,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: Colors.orange.shade200),
        ),
        child: Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Colors.orange.shade800, size: 28),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Belum Teraktivasi',
                    style: GoogleFonts.inter(
                      fontWeight: FontWeight.bold,
                      color: Colors.orange.shade900,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Silahkan hubungi Distributor TLQ anda (08995295781) untuk mendapatkan kode aktivasi.',
                    style: GoogleFonts.inter(
                      color: Colors.orange.shade800,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ---- HELPER WIDGETS ----

class _FieldConfig {
  final String hint;
  final TextEditingController controller;
  _FieldConfig(this.hint, this.controller);
}

class _JournalForm extends StatelessWidget {
  final String title;
  final Color color;
  final List<_FieldConfig> fields;
  final VoidCallback onSave;

  const _JournalForm(
      {required this.title,
      required this.color,
      required this.fields,
      required this.onSave});

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.92,
      minChildSize: 0.5,
      maxChildSize: 0.95,
      builder: (_, ctrl) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(30)),
        ),
        child: ListView(
          controller: ctrl,
          padding: EdgeInsets.only(
              left: 24,
              right: 24,
              top: 16,
              bottom: MediaQuery.of(context).viewInsets.bottom + 24),
          children: [
            Center(
              child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                      color: Colors.grey[200], borderRadius: BorderRadius.circular(10))),
            ),
            const SizedBox(height: 20),
            Text(title,
                style: GoogleFonts.inter(
                    fontWeight: FontWeight.bold, fontSize: 18, color: color)),
            const SizedBox(height: 20),
            ...fields.map((f) => Padding(
                  padding: const EdgeInsets.only(bottom: 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(f.hint,
                          style: GoogleFonts.inter(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: Colors.grey[700])),
                      const SizedBox(height: 8),
                      TextField(
                        controller: f.controller,
                        maxLines: 3,
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: Colors.grey[50],
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(color: color.withAlpha(80)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(color: color, width: 2),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(color: Colors.grey.shade200),
                          ),
                        ),
                      ),
                    ],
                  ),
                )),
            const SizedBox(height: 8),
            ElevatedButton(
              onPressed: onSave,
              style: ElevatedButton.styleFrom(
                backgroundColor: color,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 18),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              ),
              child: Text('Simpan Catatan',
                  style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 15)),
            ),
          ],
        ),
      ),
    );
  }
}

class _SectionCard extends StatelessWidget {
  final String emoji;
  final String title;
  final Color color;
  final bool isDone;
  final bool isLocked;
  final String content;
  final VoidCallback? onTap;
  final String buttonLabel;

  const _SectionCard({
    required this.emoji,
    required this.title,
    required this.color,
    required this.isDone,
    this.isLocked = false,
    required this.content,
    this.onTap,
    required this.buttonLabel,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: isDone
            ? Border.all(color: color.withAlpha(100), width: 2)
            : null,
        boxShadow: [BoxShadow(color: Colors.black.withAlpha(8), blurRadius: 15)],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(emoji, style: const TextStyle(fontSize: 22)),
              const SizedBox(width: 8),
              Text(title,
                  style: GoogleFonts.inter(
                      fontWeight: FontWeight.bold, fontSize: 14, color: color)),
              const Spacer(),
              if (isDone)
                Icon(Icons.check_circle_rounded, color: color, size: 22),
              if (isLocked)
                const Icon(Icons.lock_outline, color: Colors.grey, size: 20),
            ],
          ),
          const SizedBox(height: 10),
          Text(content,
              style: GoogleFonts.inter(fontSize: 13, color: Colors.grey[600])),
          if (!isDone && !isLocked && onTap != null) ...[
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: onTap,
                style: ElevatedButton.styleFrom(
                  backgroundColor: color,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14)),
                ),
                child: Text(buttonLabel,
                    style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
