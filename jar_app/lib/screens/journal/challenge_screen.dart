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
  int _currentDay = 1;
  int _debtDays = 0;
  bool _isCatchUpMode = false;
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
    _currentDay = int.tryParse(widget.challenge['current_day']?.toString() ?? '1') ?? 1;
    _debtDays = int.tryParse(widget.challenge['debt_days']?.toString() ?? '0') ?? 0;
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
  Future<void> _loadTodayEntry({bool background = false}) async {
    if (!mounted) return;
    if (!background) setState(() => _isLoading = true);
    
    // 1. Initial local load to avoid flicker
    if (_todayEntry == null && widget.challenge['today_entry'] != null) {
      _todayEntry = Map<String, dynamic>.from(widget.challenge['today_entry'] as Map);
    }
    
    try {
      final responseData = await ApiService.getChallengeHistory(widget.challenge['id']);
      final List<dynamic> history = (responseData['entries'] as List?) ?? [];
      final Map<String, dynamic>? challengeData = responseData['challenge'] as Map<String, dynamic>?;

      int currentDayChallenge = challengeData != null 
          ? (int.tryParse(challengeData['current_day'].toString()) ?? 1)
          : (int.tryParse(widget.challenge['current_day'].toString()) ?? 1);
      
      String todayStr = DateTime.now().toIso8601String().substring(0, 10);

      Map<String, dynamic>? found;
      
      // Priority 1: Server explicitly tells us which one is today
      if (responseData['today_entry'] != null) {
          found = Map<String, dynamic>.from(responseData['today_entry'] as Map);
      } 
      
      // Priority 2: Fallback (Legacy) - search by date
      if (found == null) {
          for (var e in history) {
              final m = Map<String, dynamic>.from(e as Map);
              if (m['entry_date']?.toString().startsWith(todayStr) == true) {
                  found = m;
                  break;
              }
          }
      }

      if (mounted) {
        setState(() {
          // IMPORTANT: Do not overwrite if we just rolled offline successfully
          final bool isOfflinePlaceholder = _todayEntry?['is_offline_placeholder'] == true;
          if (isOfflinePlaceholder && found == null) {
            // Keep the placeholder
          } else {
            _todayEntry = found;
          }

          if (found != null) {
            _currentDay = int.tryParse(found['day_number']?.toString() ?? '1') ?? 1;
          } else if (!isOfflinePlaceholder) {
            _currentDay = currentDayChallenge;
          }
          
          if (challengeData != null) {
             _debtDays = int.tryParse(challengeData['debt_days']?.toString() ?? '0') ?? 0;
          } else if (!isOfflinePlaceholder) {
             _debtDays = int.tryParse(widget.challenge['debt_days']?.toString() ?? '0') ?? 0;
          }
          _isLoading = false;
        });
      }
    } catch (e) {
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
    final bool catchUp = _isCatchUpMode;
    final int? specificDay = (_todayEntry?['day_number'] != null) ? int.tryParse(_todayEntry!['day_number'].toString()) : null;
    
    try {
      final seriesId = int.tryParse(widget.challenge['series_id']?.toString() ?? '0') ?? 0;
      final res = await ApiService.rollContent(
        widget.challenge['id'], 
        seriesId, 
        isCatchUp: catchUp,
        dayNumber: catchUp ? specificDay : null,
      );
      
      if (res['error'] == true) {
         if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message'])));
         setState(() { _isRevealing = false; _isShaking = false; });
         return;
      }

      if (mounted) {
        setState(() {
          _todayEntry = res['entry'];
          _isCatchUpMode = catchUp;
          _isRevealing = false;
          
          if (res['challenge'] != null) {
            _debtDays = int.tryParse(res['challenge']['debt_days']?.toString() ?? '0') ?? 0;
            _currentDay = int.tryParse(res['challenge']['current_day']?.toString() ?? '1') ?? 1;
          }
          
          if (res['offline'] == true) {
             ScaffoldMessenger.of(context).showSnackBar(
               const SnackBar(
                 content: Text('Alhamdulillah, ayat pilihan untukmu telah siap'),
                 backgroundColor: Colors.green,
                 duration: Duration(seconds: 4),
               )
             );
          }
        });
        // _triggerConfetti(); // Assuming this method exists elsewhere or will be added
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isRevealing = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.toString().replaceAll('Exception: ', '')), backgroundColor: Colors.red));
      }
    } finally {
      // ONLY background load if it wasn't an offline success (to avoid flicker/reversion)
      // Actually, since we now update the local history cache in ApiService, background load is safe!
      await _loadTodayEntry(background: true);
    }
  }

  // ─────────────────────────────────────────────
  // BEFORE dialog
  // ─────────────────────────────────────────────
  void _showBeforeDialog() {
    final pesanCtrl = TextEditingController(text: _todayEntry?['before_pesan']);
    final perasaanCtrl = TextEditingController(text: _todayEntry?['before_perasaan']);
    final actionCtrl = TextEditingController(text: _todayEntry?['before_action']);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _JournalSheet(
        title: (_todayEntry?['before_pesan'] != null) ? '✏️ Edit Catatan Pagi' : '🌅 Catatan Pagi (Before)',
        color: AppColors.emeraldIslamic,
        fields: [
          _Field('Apa pesan cinta-Nya (ayat) yang kamu dapat hari ini?', pesanCtrl),
          _Field('Apa perasaanmu setelah Allah kasih petunjuk ini?', perasaanCtrl),
          _Field('Apa yang akan kamu lakukan? (What to do)', actionCtrl),
        ],
        onSave: () async {
          if (pesanCtrl.text.trim().isEmpty && perasaanCtrl.text.trim().isEmpty && actionCtrl.text.trim().isEmpty) {
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
              content: Text('Tuliskan setidaknya satu hal sebelum menyimpan 😊'),
              backgroundColor: Colors.orange,
            ));
            return;
          }

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
    final berhasilCtrl = TextEditingController(text: _todayEntry?['after_berhasil']);
    final perubahanCtrl = TextEditingController(text: _todayEntry?['after_perubahan']);
    final perasaanCtrl = TextEditingController(text: _todayEntry?['after_perasaan']);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _JournalSheet(
        title: (_todayEntry?['after_berhasil'] != null) ? '✏️ Edit Catatan Sore' : '🌇 Catatan Sore (After)',
        color: AppColors.goldIslamic,
        fields: [
          _Field('Apa yang hari ini berhasil kamu lakukan?', berhasilCtrl),
          _Field('Perubahan apa yang kamu rasakan hari ini?', perubahanCtrl),
          _Field('Apa perasaanmu setelah menghidupkan ayat-Nya?', perasaanCtrl),
        ],
        onSave: () async {
          if (berhasilCtrl.text.trim().isEmpty && perubahanCtrl.text.trim().isEmpty && perasaanCtrl.text.trim().isEmpty) {
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
              content: Text('Tuliskan setidaknya satu hal sebelum menyimpan 😊'),
              backgroundColor: Colors.orange,
            ));
            return;
          }

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
                setState(() { 
                  _todayEntry = Map<String, dynamic>.from(result['entry'] as Map);
                  _currentDay = int.tryParse(result['challenge']?['current_day']?.toString() ?? '1') ?? 1;
                  _debtDays = int.tryParse(result['challenge']?['debt_days']?.toString() ?? '0') ?? 0;
                  if (_isCatchUpMode && _debtDays <= 0) _isCatchUpMode = false;
                });
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
    final currentDay = _currentDay;
    final totalDays = int.tryParse(challenge['total_days']?.toString() ?? '40') ?? 40;
    final progress = totalDays > 0 ? (currentDay / totalDays).clamp(0.0, 1.0) : 0.0;

    final hasBefore = (_todayEntry?['before_pesan'] != null && _todayEntry?['before_pesan'] != '') ||
                       (_todayEntry?['before_perasaan'] != null && _todayEntry?['before_perasaan'] != '') ||
                       (_todayEntry?['before_action'] != null && _todayEntry?['before_action'] != '');
    final hasAfter = (_todayEntry?['after_berhasil'] != null && _todayEntry?['after_berhasil'] != '') ||
                      (_todayEntry?['after_perubahan'] != null && _todayEntry?['after_perubahan'] != '') ||
                      (_todayEntry?['after_perasaan'] != null && _todayEntry?['after_perasaan'] != '');
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
                              onPressed: () async {
                                final result = await Navigator.push(context, MaterialPageRoute(
                                  builder: (_) => ChallengeHistoryScreen(challenge: widget.challenge),
                                ));
                                if (result != null && result is Map<String, dynamic>) {
                                  setState(() {
                                    _todayEntry = Map<String, dynamic>.from(result as Map);
                                    _isCatchUpMode = (result['status'] == 'missed');
                                    _currentDay = int.tryParse(result['day_number']?.toString() ?? '1') ?? 1;
                                  });
                                }
                              },
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

                  // ══ CATCH UP ALERT (Always visible if debt exists) ══
                  if (_debtDays > 0 && !_isCatchUpMode)
                    FadeInDown(
                      child: _buildBanner(
                        emoji: '⚡',
                        title: 'Ada $_debtDays hari tertinggal.',
                        label: 'KEJAR SEKARANG',
                        onTap: () {
                           // Open history directly to let user pick which day to tebus
                            Navigator.push(context, MaterialPageRoute(
                              builder: (_) => ChallengeHistoryScreen(challenge: widget.challenge),
                            )).then((result) {
                              if (result != null && result is Map<String, dynamic>) {
                                setState(() {
                                  _todayEntry = Map<String, dynamic>.from(result as Map);
                                  _isCatchUpMode = (result['status'] == 'missed');
                                  _currentDay = int.tryParse(result['day_number']?.toString() ?? '1') ?? 1;
                                });
                              }
                            });
                        },
                      ),
                    ),

                  if (_isCatchUpMode)
                    FadeInDown(
                      child: _buildBanner(
                        emoji: '⏳',
                        title: 'Mode Kejar: Mengisi Hari $_currentDay',
                        color: Colors.orange,
                        label: 'KEMBALI KE HARI INI',
                        onTap: () {
                          setState(() => _isCatchUpMode = false);
                          _loadTodayEntry();
                        },
                      ),
                    ),

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

                    // Before card — selalu bisa diedit selama ada entry
                    _buildJournalCard(
                      emoji: '🌅',
                      title: 'Catatan Pagi (Before)',
                      subtitle: hasBefore
                          ? '✏️ Tap untuk mengubah: "${_todayEntry!['before_pesan']}"'
                          : 'Tuliskan pesanmu setelah mendapat ayat ini',
                      color: AppColors.emeraldIslamic,
                      isDone: hasBefore,
                      isLocked: false,
                      onTap: _showBeforeDialog,
                    ),
                    const SizedBox(height: 12),

                    // After card — bisa diedit setelah Before diisi
                    _buildJournalCard(
                      emoji: '🌇',
                      title: 'Catatan Sore (After)',
                      subtitle: hasAfter
                          ? '✏️ Tap untuk mengubah: "${_todayEntry!['after_berhasil']}"'
                          : hasBefore
                              ? 'Waktunya menuliskan refleksi harimu!'
                              : '🔒 Isi Catatan Pagi terlebih dahulu',
                      color: AppColors.goldIslamic,
                      isDone: hasAfter,
                      isLocked: !hasBefore,
                      onTap: hasBefore ? _showAfterDialog : null,
                    ),

                    if (hasAfter) ...[
                      const SizedBox(height: 24),
                      FadeInUp(
                        child: Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [AppColors.emeraldIslamic, Color(0xFF0A3D2E)],
                            ),
                            borderRadius: BorderRadius.circular(24),
                          ),
                          child: Column(
                            children: [
                              Row(
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
                                        Text(currentDay == totalDays 
                                            ? 'Alhamdulillah, perjalananmu tuntas! MasyaAllah 💪'
                                            : 'MasyaAllah! Terus semangat ya 💪',
                                            style: GoogleFonts.inter(color: Colors.white70, fontSize: 12)),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                              if (currentDay == totalDays) ...[
                                const SizedBox(height: 16),
                                SizedBox(
                                  width: double.infinity,
                                  child: ElevatedButton(
                                    onPressed: () {
                                      Navigator.pushReplacement(context, MaterialPageRoute(
                                        builder: (_) => FinishedChallengeScreen(challenge: widget.challenge),
                                      ));
                                    },
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: AppColors.goldIslamic,
                                      foregroundColor: Colors.white,
                                      elevation: 0,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                                    ),
                                    child: const Text('Lanjutkan ke Penutupan'),
                                  ),
                                ),
                              ],
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
  Widget _buildBanner({
    required String emoji, 
    required String title, 
    required String label, 
    required VoidCallback onTap,
    Color color = Colors.amber,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: color.withAlpha(20),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withAlpha(50)),
      ),
      child: Row(
        children: [
          Text(emoji, style: const TextStyle(fontSize: 18)),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              title,
              style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.bold, color: color.withAlpha(200)),
            ),
          ),
          TextButton(
            onPressed: onTap,
            child: Text(label, style: TextStyle(fontWeight: FontWeight.w900, color: color, fontSize: 10)),
          ),
        ],
      ),
    );
  }

  Widget _buildGreetingAndKocok(String seriesName, int day) {
    final color = _isCatchUpMode ? Colors.amber.shade700 : AppColors.emeraldIslamic;
    return FadeInUp(
      child: Column(
        children: [
          if (_isCatchUpMode)
            Padding(
              padding: const EdgeInsets.only(bottom: 16),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.amber,
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [BoxShadow(color: Colors.amber.withAlpha(50), blurRadius: 10)],
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Text('⚡', style: TextStyle(fontSize: 14)),
                    const SizedBox(width: 8),
                    Text('MODE KEJAR KETINGGALAN', 
                      style: GoogleFonts.inter(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 10)),
                    const SizedBox(width: 8),
                    GestureDetector(
                      onTap: () => setState(() => _isCatchUpMode = false),
                      child: const Icon(Icons.cancel, color: Colors.white70, size: 16),
                    ),
                  ],
                ),
              ),
            ),
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
            _isShaking ? 'Berdoa & tap berhenti...' : 'Goyangkan botol atau tekan tombol',
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
                  Icon(_isShaking ? Icons.stop_rounded : Icons.menu_book_rounded,
                      color: Colors.white, size: 22),
                  const SizedBox(width: 10),
                  Text(
                    _isShaking ? 'Bismillah, Ambil Sekarang' : 'Jemput Ibrah Hari Ini',
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
            Text('Membuka hikmah untukmu...'),
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
            if (content['action_plan'] != null) ...[
              const SizedBox(height: 16),
              Row(
                children: [
                   const Icon(Icons.task_alt, size: 16, color: AppColors.emeraldIslamic),
                  const SizedBox(width: 6),
                  Text('What to Do', style: GoogleFonts.inter(
                      fontWeight: FontWeight.bold, fontSize: 12, color: AppColors.emeraldIslamic)),
                ],
              ),
              const SizedBox(height: 6),
              Text(content['action_plan']?.toString() ?? '',
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
