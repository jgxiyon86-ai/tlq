import 'dart:async';
import 'dart:math';
import 'package:animate_do/animate_do.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:jar_app/core/app_colors.dart';
import 'package:jar_app/services/api_service.dart';
import 'package:sensors_plus/sensors_plus.dart';
import 'finished_challenge_screen.dart';
import 'challenge_history_screen.dart';

class ChallengeScreen extends StatefulWidget {
  final Map<String, dynamic> challenge;
  const ChallengeScreen({super.key, required this.challenge});

  @override
  State<ChallengeScreen> createState() => _ChallengeScreenState();
}

class _ChallengeScreenState extends State<ChallengeScreen>
    with SingleTickerProviderStateMixin {

  // --- Entry state ---
  Map<String, dynamic>? _todayEntry;
  bool _isLoading = true;

  // --- Shake / Roll state ---
  bool _isShaking = false;
  bool _isRevealing = false; // loading after shake
  late AnimationController _shakeController;
  StreamSubscription? _accelSub;

  @override
  void initState() {
    super.initState();
    _shakeController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 90),
    );
    _loadTodayEntry();
  }

  @override
  void dispose() {
    _shakeController.dispose();
    _accelSub?.cancel();
    super.dispose();
  }

  // ─────────────────────────────────────────────
  // LOAD today entry (3-priority search)
  // ─────────────────────────────────────────────
  Future<void> _loadTodayEntry() async {
    setState(() => _isLoading = true);
    try {
      final history = await ApiService.getChallengeHistory(widget.challenge['id']);

      Map<String, dynamic>? found;

      // Priority 1: incomplete entry that already has content rolled
      for (var e in history) {
        final m = Map<String, dynamic>.from(e as Map);
        final isCompleted = m['is_completed'];
        final hasContent = m['content'] != null;
        if (hasContent && isCompleted != true && isCompleted != 1) {
          found = m;
          break;
        }
      }

      // Priority 2: today by date
      if (found == null) {
        final today = DateTime.now().toIso8601String().substring(0, 10);
        for (var e in history) {
          final m = Map<String, dynamic>.from(e as Map);
          if (m['entry_date']?.toString().startsWith(today) == true) {
            found = m;
            break;
          }
        }
      }

      // Priority 3: by current_day number
      if (found == null) {
        final day = int.tryParse(widget.challenge['current_day']?.toString() ?? '1') ?? 1;
        for (var e in history) {
          final m = Map<String, dynamic>.from(e as Map);
          if (int.tryParse(m['day_number']?.toString() ?? '0') == day) {
            found = m;
            break;
          }
        }
      }

      if (mounted) setState(() { _todayEntry = found; _isLoading = false; });
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  // ─────────────────────────────────────────────
  // SHAKE / KOCOK Logic
  // ─────────────────────────────────────────────
  void _startListening() {
    _accelSub?.cancel();
    _accelSub = accelerometerEventStream().listen((e) {
      if (_isRevealing || _todayEntry != null) return;
      final mag = sqrt(e.x * e.x + e.y * e.y + e.z * e.z);
      if (mag > 25 && !_isShaking) {
        _onShakeDetected();
      }
    });
  }

  void _startShakeAnimation() {
    setState(() => _isShaking = true);
    _shakeController.repeat(reverse: true);
    _startListening();
  }

  void _stopAndRoll() {
    if (!_isShaking) return;
    _shakeController.stop();
    _accelSub?.cancel();
    setState(() { _isShaking = false; _isRevealing = true; });
    _doRoll();
  }

  void _onShakeDetected() async {
    _startShakeAnimation();
    await Future.delayed(const Duration(milliseconds: 1500));
    _stopAndRoll();
  }

  Future<void> _doRoll() async {
    try {
      await ApiService.rollContent(widget.challenge['id']);
    } catch (e) {
      // Ignore — we reload anyway; existing incomplete entry also fine
    } finally {
      if (mounted) setState(() => _isRevealing = false);
      await _loadTodayEntry();
    }
  }

  // ─────────────────────────────────────────────
  // BEFORE dialog
  // ─────────────────────────────────────────────
  void _showBeforeDialog() {
    final pesanCtrl = TextEditingController();
    final perasaanCtrl = TextEditingController();
    final actionCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _JournalSheet(
        title: '🌅 Catatan Pagi (Before)',
        color: AppColors.emeraldIslamic,
        fields: [
          _Field('Apa pesan cinta-Nya (ayat) yang kamu dapat hari ini?', pesanCtrl),
          _Field('Apa perasaanmu setelah Allah kasih petunjuk ini?', perasaanCtrl),
          _Field('Apa yang akan kamu lakukan? (What to do)', actionCtrl),
        ],
        onSave: () async {
          Navigator.pop(ctx);
          try {
            final result = await ApiService.saveBefore(
              _todayEntry!['id'], pesanCtrl.text, perasaanCtrl.text, actionCtrl.text);
            if (mounted) {
              setState(() { _todayEntry = Map<String, dynamic>.from(result['entry'] as Map); });
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                content: Text('Catatan pagi tersimpan 😊'), backgroundColor: AppColors.emeraldIslamic));
            }
          } catch (e) {
            if (mounted) ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(e.toString().replaceAll('Exception: ', '')), backgroundColor: Colors.red));
          }
        },
      ),
    );
  }

  // ─────────────────────────────────────────────
  // AFTER dialog
  // ─────────────────────────────────────────────
  void _showAfterDialog() {
    final berhasilCtrl = TextEditingController();
    final perubahanCtrl = TextEditingController();
    final perasaanCtrl = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _JournalSheet(
        title: '🌇 Catatan Sore (After)',
        color: AppColors.goldIslamic,
        fields: [
          _Field('Apa yang hari ini berhasil kamu lakukan?', berhasilCtrl),
          _Field('Perubahan apa yang kamu rasakan hari ini?', perubahanCtrl),
          _Field('Apa perasaanmu setelah menghidupkan ayat-Nya?', perasaanCtrl),
        ],
        onSave: () async {
          Navigator.pop(ctx);
          try {
            final result = await ApiService.saveAfter(
              _todayEntry!['id'], berhasilCtrl.text, perubahanCtrl.text, perasaanCtrl.text);
            if (mounted) {
              if (result['challenge']?['is_completed'] == true) {
                Navigator.pushReplacement(context, MaterialPageRoute(
                  builder: (_) => FinishedChallengeScreen(challenge: result['challenge']),
                ));
              } else {
                setState(() { _todayEntry = Map<String, dynamic>.from(result['entry'] as Map); });
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                  content: Text('MasyaAllah! Hari ini selesai 🎉'), backgroundColor: AppColors.goldIslamic));
              }
            }
          } catch (e) {
            if (mounted) ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(e.toString().replaceAll('Exception: ', '')), backgroundColor: Colors.red));
          }
        },
      ),
    );
  }

  // ─────────────────────────────────────────────
  // BUILD
  // ─────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final challenge = widget.challenge;
    final seriesName = challenge['series']?['name']?.toString() ?? 'TLQ';
    final currentDay = int.tryParse(challenge['current_day']?.toString() ?? '1') ?? 1;
    final totalDays = int.tryParse(challenge['total_days']?.toString() ?? '40') ?? 40;
    final progress = totalDays > 0 ? (currentDay / totalDays).clamp(0.0, 1.0) : 0.0;

    final hasBefore = _todayEntry?['before_pesan'] != null;
    final hasAfter = _todayEntry?['after_berhasil'] != null;
    final hasEntry = _todayEntry != null && _todayEntry!['content'] != null;

    return Scaffold(
      backgroundColor: const Color(0xFFF6F5F0),
      body: CustomScrollView(
        slivers: [
          // ── App Bar ──
          SliverAppBar(
            expandedHeight: 200,
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
                    padding: const EdgeInsets.fromLTRB(24, 0, 24, 24),
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
                              onPressed: () => Navigator.push(context, MaterialPageRoute(
                                builder: (_) => ChallengeHistoryScreen(challenge: widget.challenge),
                              )),
                            ),
                          ],
                        ),
                        const Spacer(),
                        Text('📖 Tantangan $totalDays Hari',
                            style: GoogleFonts.inter(color: Colors.white70, fontSize: 13)),
                        const SizedBox(height: 4),
                        Text(seriesName,
                            style: GoogleFonts.inter(color: Colors.white, fontSize: 26, fontWeight: FontWeight.bold)),
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
                                  value: progress,
                                  backgroundColor: Colors.white24,
                                  valueColor: const AlwaysStoppedAnimation<Color>(AppColors.goldIslamic),
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

          // ── Body ──
          if (_isLoading)
            const SliverFillRemaining(
              child: Center(child: CircularProgressIndicator()),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.all(20),
              sliver: SliverList(
                delegate: SliverChildListDelegate([

                  // ══ STATE 1: Belum kocok hari ini ══
                  if (!hasEntry && !_isRevealing)
                    _buildGreetingAndKocok(seriesName, currentDay),

                  // ══ STATE 2: Sedang loading setelah kocok ══
                  if (_isRevealing)
                    _buildRevealingLoader(),

                  // ══ STATE 3: Sudah ada ayat ══
                  if (hasEntry) ...[
                    _buildAyatCard(),
                    const SizedBox(height: 16),

                    // Before card
                    _buildJournalCard(
                      emoji: '🌅',
                      title: 'Catatan Pagi (Before)',
                      subtitle: hasBefore
                          ? '"${_todayEntry!['before_pesan']}"'
                          : 'Tuliskan pesanmu setelah mendapat ayat ini',
                      color: AppColors.emeraldIslamic,
                      isDone: hasBefore,
                      isLocked: false,
                      onTap: hasBefore ? null : _showBeforeDialog,
                    ),
                    const SizedBox(height: 12),

                    // After card
                    _buildJournalCard(
                      emoji: '🌇',
                      title: 'Catatan Sore (After)',
                      subtitle: hasAfter
                          ? '"${_todayEntry!['after_berhasil']}"'
                          : hasBefore
                              ? 'Waktunya menuliskan refleksi harimu!'
                              : '🔒 Isi Catatan Pagi terlebih dahulu',
                      color: AppColors.goldIslamic,
                      isDone: hasAfter,
                      isLocked: !hasBefore,
                      onTap: (hasBefore && !hasAfter) ? _showAfterDialog : null,
                    ),

                    if (hasAfter) ...[
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
                                            color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
                                    Text('MasyaAllah! Terus semangat ya 💪',
                                        style: GoogleFonts.inter(color: Colors.white70, fontSize: 12)),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ],

                  const SizedBox(height: 60),
                ]),
              ),
            ),
        ],
      ),
    );
  }

  // ─────────────────────────────────────────────
  // Widget: Greeting + Kocok button
  // ─────────────────────────────────────────────
  Widget _buildGreetingAndKocok(String seriesName, int day) {
    final color = AppColors.emeraldIslamic;
    return FadeInUp(
      child: Column(
        children: [
          const SizedBox(height: 16),
          // Salam card
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(28),
              boxShadow: [BoxShadow(color: Colors.black.withAlpha(10), blurRadius: 20)],
            ),
            child: Column(
              children: [
                Text('السَّلاَمُ عَلَيْكُمْ',
                    style: GoogleFonts.amiri(fontSize: 28, color: color, height: 1.5)),
                const SizedBox(height: 12),
                Text('Hari ke-$day dari tantangan $seriesName kamu.',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.inter(fontSize: 15, fontWeight: FontWeight.bold, color: const Color(0xFF1A2E1A))),
                const SizedBox(height: 8),
                Text('Apakah kamu siap menghidupkan Al-Quran hari ini?',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.inter(fontSize: 13, color: Colors.grey[600])),
              ],
            ),
          ),
          const SizedBox(height: 28),
          // Shake jar icon
          AnimatedBuilder(
            animation: _shakeController,
            builder: (_, child) => Transform.translate(
              offset: Offset(_isShaking ? (_shakeController.value * 16 - 8) : 0, 0),
              child: child,
            ),
            child: Icon(Icons.auto_awesome_motion, size: 140, color: color.withAlpha(200)),
          ),
          const SizedBox(height: 12),
          Text(
            _isShaking ? 'Goyang atau tap berhenti...' : 'Goyangkan HP atau tekan tombol',
            style: GoogleFonts.inter(color: Colors.grey[600], fontSize: 13, fontStyle: FontStyle.italic),
          ),
          const SizedBox(height: 24),
          // Kocok button
          GestureDetector(
            onTap: _isShaking ? _stopAndRoll : _startShakeAnimation,
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 300),
              padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 16),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: _isShaking
                      ? [Colors.red.shade400, Colors.red.shade700]
                      : [color, const Color(0xFF0F5132)],
                ),
                borderRadius: BorderRadius.circular(50),
                boxShadow: [
                  BoxShadow(
                    color: (_isShaking ? Colors.red : color).withAlpha(80),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(_isShaking ? Icons.stop_rounded : Icons.shuffle_rounded,
                      color: Colors.white, size: 22),
                  const SizedBox(width: 10),
                  Text(
                    _isShaking ? 'Tap untuk Berhenti & Ambil Ayat' : 'Kocok Ayat Hari Ini',
                    style: GoogleFonts.inter(
                        color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          Text('⏰ Pengingat dikirim pukul 05:00 pagi',
              style: GoogleFonts.inter(fontSize: 11, color: Colors.grey[400])),
        ],
      ),
    );
  }

  // ─────────────────────────────────────────────
  // Widget: Revealing loader
  // ─────────────────────────────────────────────
  Widget _buildRevealingLoader() {
    return const SizedBox(
      height: 200,
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(color: AppColors.emeraldIslamic),
            SizedBox(height: 16),
            Text('Membuka gulungan kertas ayat...'),
          ],
        ),
      ),
    );
  }

  // ─────────────────────────────────────────────
  // Widget: Ayat card
  // ─────────────────────────────────────────────
  Widget _buildAyatCard() {
    final content = _todayEntry!['content'] as Map? ?? {};
    return FadeInDown(
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: AppColors.emeraldIslamic.withAlpha(15),
          borderRadius: BorderRadius.circular(24),
          border: Border.all(color: AppColors.emeraldIslamic.withAlpha(60)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              content['surah_ayah']?.toString() ?? '',
              style: GoogleFonts.inter(
                  fontWeight: FontWeight.bold, color: AppColors.emeraldIslamic, fontSize: 14),
            ),
            const SizedBox(height: 12),
            Text(
              content['arabic_text']?.toString() ?? '',
              textAlign: TextAlign.right,
              style: GoogleFonts.amiri(fontSize: 24, height: 2),
            ),
            const SizedBox(height: 12),
            Text(
              content['translation']?.toString() ?? '',
              style: GoogleFonts.inter(fontSize: 13, color: Colors.grey[700], height: 1.6),
            ),
            if (content['insight'] != null) ...[
              const Divider(height: 28),
              Row(
                children: [
                  const Icon(Icons.lightbulb_outline, size: 16, color: AppColors.goldIslamic),
                  const SizedBox(width: 6),
                  Text('Insight', style: GoogleFonts.inter(
                      fontWeight: FontWeight.bold, fontSize: 12, color: AppColors.goldIslamic)),
                ],
              ),
              const SizedBox(height: 6),
              Text(content['insight']?.toString() ?? '',
                  style: GoogleFonts.inter(fontSize: 12, color: Colors.grey[700])),
            ],
          ],
        ),
      ),
    );
  }

  // ─────────────────────────────────────────────
  // Widget: Journal card (Before / After)
  // ─────────────────────────────────────────────
  Widget _buildJournalCard({
    required String emoji,
    required String title,
    required String subtitle,
    required Color color,
    required bool isDone,
    required bool isLocked,
    VoidCallback? onTap,
  }) {
    return FadeInLeft(
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isDone ? color.withAlpha(80) : (isLocked ? Colors.grey.shade200 : Colors.transparent),
          ),
          boxShadow: [BoxShadow(color: Colors.black.withAlpha(8), blurRadius: 12)],
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: isDone ? color.withAlpha(20) : (isLocked ? Colors.grey.shade100 : color.withAlpha(15)),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Center(child: Text(emoji, style: const TextStyle(fontSize: 22))),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(title,
                            style: GoogleFonts.inter(
                                fontWeight: FontWeight.bold,
                                fontSize: 14,
                                color: isLocked ? Colors.grey : const Color(0xFF1A2E1A))),
                      ),
                      if (isDone) Icon(Icons.check_circle_rounded, color: color, size: 20),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(subtitle,
                      style: GoogleFonts.inter(
                          fontSize: 12,
                          color: isDone ? color : (isLocked ? Colors.grey.shade400 : Colors.grey[600]),
                          fontStyle: isDone ? FontStyle.italic : FontStyle.normal)),
                  if (!isDone && !isLocked && onTap != null) ...[
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: onTap,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: color,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: Text('Isi Sekarang', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ═══════════════════════════════════════
// Journal Bottom Sheet
// ═══════════════════════════════════════
class _Field {
  final String hint;
  final TextEditingController ctrl;
  _Field(this.hint, this.ctrl);
}

class _JournalSheet extends StatelessWidget {
  final String title;
  final Color color;
  final List<_Field> fields;
  final VoidCallback onSave;

  const _JournalSheet({
    required this.title,
    required this.color,
    required this.fields,
    required this.onSave,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
        ),
        padding: const EdgeInsets.all(24),
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40, height: 4,
                  decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(2)),
                ),
              ),
              const SizedBox(height: 16),
              Text(title, style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 18, color: color)),
              const SizedBox(height: 20),
              ...fields.map((f) => Padding(
                padding: const EdgeInsets.only(bottom: 14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(f.hint, style: GoogleFonts.inter(fontSize: 12, color: Colors.grey[600])),
                    const SizedBox(height: 6),
                    TextField(
                      controller: f.ctrl,
                      maxLines: 3,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide: BorderSide(color: color, width: 2),
                        ),
                        contentPadding: const EdgeInsets.all(12),
                      ),
                    ),
                  ],
                ),
              )),
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: onSave,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: color,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  ),
                  child: Text('Simpan Jurnal', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 16)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
