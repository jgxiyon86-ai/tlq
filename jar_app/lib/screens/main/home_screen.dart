import 'package:animate_do/animate_do.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:jar_app/core/app_colors.dart';
import 'package:jar_app/screens/jar/manual_book_screen.dart';
import 'package:jar_app/screens/jar/qr_scanner_screen.dart';
import 'package:jar_app/screens/journal/challenge_screen.dart';
import 'package:jar_app/screens/main/profile_screen.dart';
import 'package:jar_app/services/api_service.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  String _userName = 'Sobat TLQ';
  String _userNickname = 'Sobat TLQ';
  List<dynamic> _myJars = [];
  List<dynamic> _myChallenges = [];
  bool _isLoading = true;

  // All 5 series
  final List<Map<String, dynamic>> _allSeries = [
    {'id': '1', 'name': 'Miracle',   'color': const Color(0xFF3730A3), 'image': 'assets/images/jar_miracle.png'},
    {'id': '4', 'name': 'Marriage',  'color': const Color(0xFFD97706), 'image': 'assets/images/jar_marriage.png'},
    {'id': '2', 'name': 'Parenting', 'color': const Color(0xFFEA580C), 'image': 'assets/images/jar_parenting.png'},
    {'id': '3', 'name': 'Huffaz',    'color': const Color(0xFF0284C7), 'image': 'assets/images/jar_huffaz.png'},
    {'id': '5', 'name': 'Healing',   'color': const Color(0xFFE63995), 'image': 'assets/images/jar_healing.png'},
  ];

  @override
  void initState() {
    super.initState();
    _loadAll();
  }

  Future<void> _loadAll() async {
    setState(() => _isLoading = true);
    try {
      final prefs = await SharedPreferences.getInstance();
      setState(() {
        _userName = prefs.getString('user_name') ?? 'Sobat TLQ';
        _userNickname = prefs.getString('user_nickname') ?? _userName;
      });

      final data = await ApiService.getDashboard();
      
      ApiService.syncOfflineData();
      
      if (mounted) {
        setState(() {
          _myJars = data['jars'];
          // Filter challenges: only show if they have a corresponding JAR license
          final rawChallenges = data['active_challenges'] as List;
          final ownedSeriesIds = _myJars.map((j) => j['series_id'].toString()).toSet();
          
          _myChallenges = rawChallenges
              .where((c) => ownedSeriesIds.contains(c['series_id'].toString()))
              .toList();
          
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F5F0),
      body: RefreshIndicator(
        onRefresh: _loadAll,
        color: AppColors.emeraldIslamic,
        child: CustomScrollView(
          slivers: [
            // Header
            SliverAppBar(
              expandedHeight: 240,
              pinned: true,
              backgroundColor: AppColors.emeraldIslamic,
              actions: [
                IconButton(
                  onPressed: _scanQR,
                  icon: const Icon(Icons.qr_code_scanner, color: Colors.white),
                  tooltip: 'Aktivasi Jar Baru',
                ),
                IconButton(
                  onPressed: () => Navigator.push(context,
                    MaterialPageRoute(builder: (_) => const ProfileScreen())),
                  icon: const Icon(Icons.account_circle_outlined, color: Colors.white),
                ),
              ],
              flexibleSpace: FlexibleSpaceBar(
                background: Container(
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [AppColors.emeraldIslamic, Color(0xFF064E35)],
                    ),
                  ),
                  child: SafeArea(
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(24, 20, 24, 0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          FadeInDown(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Assalamualikum,',
                                  style: GoogleFonts.inter(
                                    color: Colors.white.withAlpha(180),
                                    fontSize: 14,
                                  ),
                                ),
                                Text(
                                  _userNickname,
                                  style: GoogleFonts.inter(
                                    color: Colors.white,
                                    fontSize: 28,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withAlpha(40),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(Icons.auto_awesome, color: AppColors.amberIslamic, size: 14),
                                      const SizedBox(width: 8),
                                      Text(
                                        'Sudahkah hari ini menghidupkan Al-Quran?',
                                        style: GoogleFonts.inter(
                                          color: Colors.white,
                                          fontSize: 11,
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 32),
                          FadeInUp(
                            child: Row(
                              children: [
                                _buildHeaderStat('Aktif', '${_myJars.length} Jar', Icons.bolt),
                                const SizedBox(width: 12),
                                _buildHeaderStat('Tantangan', '${_myChallenges.length} Berjalan', Icons.inventory_2),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),

            if (_isLoading)
              const SliverFillRemaining(
                child: Center(child: CircularProgressIndicator()),
              )
            else ...[
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 24, 20, 0),
                sliver: SliverToBoxAdapter(
                  child: Text('🫙  Botol TLQ Jar',
                      style: GoogleFonts.inter(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                          color: AppColors.textDark)),
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                sliver: SliverGrid(
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    mainAxisSpacing: 14,
                    crossAxisSpacing: 14,
                    childAspectRatio: 0.82,
                  ),
                  delegate: SliverChildBuilderDelegate(
                    (ctx, i) => _buildJarCard(_allSeries[i]),
                    childCount: _allSeries.length,
                  ),
                ),
              ),

              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 28, 20, 0),
                sliver: SliverToBoxAdapter(
                  child: Row(
                    children: [
                      Text('📖  Tantangan Sedang Berjalan',
                          style: GoogleFonts.inter(
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                              color: AppColors.textDark)),
                      const Spacer(),
                      GestureDetector(
                        onTap: _showActivateChallengeDialog,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: AppColors.emeraldIslamic,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text('+ Tantangan',
                              style: GoogleFonts.inter(
                                  color: Colors.white,
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold)),
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              if (_myChallenges.isEmpty)
                SliverPadding(
                  padding: const EdgeInsets.all(20),
                  sliver: SliverToBoxAdapter(
                    child: FadeIn(
                      child: Container(
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(24),
                          border: Border.all(color: Colors.grey.shade100),
                        ),
                        child: Column(
                          children: [
                            const Text('📓', style: TextStyle(fontSize: 40)),
                            const SizedBox(height: 12),
                            Text(
                              _myJars.isEmpty
                                  ? 'Kamu belum memasukkan Botol TLQ.\nTapi kamu tetap bisa memulai tantangan!'
                                  : 'Belum ada tantangan aktif.\nTekan "+ Tantangan" untuk memulai!',
                              textAlign: TextAlign.center,
                              style: GoogleFonts.inter(
                                  color: Colors.grey[600], fontSize: 13),
                            ),
                            if (_myJars.isEmpty) ...[
                              const SizedBox(height: 16),
                            ],
                          ],
                        ),
                      ),
                    ),
                  ),
                )
              else
                SliverPadding(
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
                  sliver: SliverList(
                    delegate: SliverChildBuilderDelegate(
                      (ctx, i) => _buildChallengeCard(_myChallenges[i]),
                      childCount: _myChallenges.length,
                    ),
                  ),
                ),

              const SliverPadding(padding: EdgeInsets.only(bottom: 32)),
            ],
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _scanQR,
        backgroundColor: AppColors.goldIslamic,
        icon: const Icon(Icons.qr_code_scanner, color: Colors.white),
        label: Text('Aktivasi Jar',
            style: GoogleFonts.inter(
                color: Colors.white, fontWeight: FontWeight.bold)),
      ),
    );
  }

  Widget _buildHeaderStat(String label, String value, IconData icon) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white.withAlpha(20),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: Colors.white.withAlpha(30)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: AppColors.amberIslamic, size: 20),
            const SizedBox(height: 12),
            Text(value,
                style: GoogleFonts.inter(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.bold)),
            Text(label,
                style: GoogleFonts.inter(
                    color: Colors.white.withAlpha(160), fontSize: 11)),
          ],
        ),
      ),
    );
  }

  Widget _buildJarCard(Map<String, dynamic> series) {
    final id = series['id'].toString();
    final name = series['name'] as String;
    final color = series['color'] as Color;
    final isOwned = _myJars.any((j) => j['series_id'].toString() == id);
    final isHealing = name == 'Healing';

    return ZoomIn(
      child: GestureDetector(
        onTap: isOwned
            ? () {
                final license = _myJars.firstWhere(
                    (j) => j['series_id'].toString() == id);
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => ManualBookScreen(
                      series: {'id': id, 'name': name, 'color': color},
                      licenseKey: license['license_key'],
                    ),
                  ),
                );
              }
            : () {
                ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                  content: Text('$name belum diaktivasi. Scan QR untuk aktivasi!'),
                  action: SnackBarAction(
                    label: 'Scan',
                    textColor: Colors.yellow,
                    onPressed: _scanQR,
                  ),
                ));
              },
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(28),
            gradient: isHealing && isOwned
                ? const LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [
                      Color(0xFFBEF),
                      Color(0xFFFC8B8B),
                      Color(0xFF90EE90),
                      Color(0xFF87CEEB),
                      Color(0xFF9B89D0),
                    ],
                  )
                : LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: isOwned
                        ? [color.withAlpha(40), color.withAlpha(15)]
                        : [Colors.grey.shade100, Colors.grey.shade50],
                  ),
            border: Border.all(
              color: isOwned ? color.withAlpha(120) : Colors.grey.shade200,
              width: 1.5,
            ),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 70,
                height: 70,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: isOwned ? color.withAlpha(30) : Colors.grey.shade200,
                  image: isOwned
                      ? DecorationImage(
                          image: AssetImage(series['image'] as String),
                          fit: BoxFit.cover,
                        )
                      : null,
                ),
                child: isOwned
                    ? null
                    : Icon(
                        Icons.lock_outline_rounded,
                        size: 36,
                        color: Colors.grey.shade400,
                      ),
              ),
              const SizedBox(height: 12),
              Text(name,
                  style: GoogleFonts.inter(
                      fontWeight: FontWeight.bold,
                      fontSize: 15,
                      color: isOwned ? AppColors.textDark : Colors.grey)),
              const SizedBox(height: 4),
              Text(
                isOwned ? 'Aktif ✓' : 'Terkunci',
                style: GoogleFonts.inter(
                    fontSize: 11,
                    color: isOwned ? color : Colors.grey.shade400,
                    fontWeight: FontWeight.w600),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildChallengeCard(dynamic challengeData) {
    if (challengeData == null || challengeData is! Map) {
      return const SizedBox.shrink();
    }
    
    try {
      final challenge = Map<String, dynamic>.from(challengeData);
      final seriesName = challenge['series']?['name'] ?? 'TLQ';
      final currentDay = int.tryParse(challenge['current_day']?.toString() ?? '1') ?? 1;
      final totalDays = int.tryParse(challenge['total_days']?.toString() ?? '40') ?? 40;
      final progress = totalDays > 0 ? (currentDay / totalDays).clamp(0.0, 1.0) : 0.0;
      final seriesId = challenge['series_id']?.toString() ?? '';
      
      final series = _allSeries.firstWhere(
          (s) => s['id'] == seriesId,
          orElse: () => {'color': AppColors.emeraldIslamic});
      final color = series['color'] as Color;

      return FadeInUp(
      child: GestureDetector(
        onTap: () async {
          await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => ChallengeScreen(challenge: challenge),
            ),
          );
          _loadAll();
        },
        child: Container(
          margin: const EdgeInsets.only(bottom: 14),
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: [BoxShadow(color: Colors.black.withAlpha(8), blurRadius: 15)],
          ),
          child: Row(
            children: [
              Container(
                width: 54,
                height: 54,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: color.withAlpha(25),
                ),
                child: Icon(Icons.menu_book_rounded, color: color, size: 28),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Tantangan $seriesName (${totalDays} Hari)',
                        style: GoogleFonts.inter(
                            fontWeight: FontWeight.bold, fontSize: 14)),
                    const SizedBox(height: 4),
                    Text('Hari ke-$currentDay dari $totalDays hari',
                        style: GoogleFonts.inter(
                            fontSize: 12, color: Colors.grey[600])),
                    const SizedBox(height: 8),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(10),
                      child: LinearProgressIndicator(
                        value: progress,
                        backgroundColor: Colors.grey.shade100,
                        valueColor: AlwaysStoppedAnimation<Color>(color),
                        minHeight: 6,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              Icon(Icons.chevron_right_rounded, color: Colors.grey.shade300),
            ],
          ),
        ),
      ),
    );
    } catch (e) {
      return const SizedBox.shrink();
    }
  }

  void _showActivateChallengeDialog() {
    if (_myJars.isEmpty) {
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          title: Text('Akses Terkunci', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
          content: Text('Anda belum mengaktifkan Jar apapun.\n\nSilahkan hubungi Distributor TLQ anda (08995295781) untuk mendapatkan kode aktivasi atau ketuk "Aktivasi Jar" untuk memindai QR.',
            style: GoogleFonts.inter()),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Tutup')),
          ],
        ),
      );
      return;
    }

    // Only allow selecting series that the user owns a license for
    final ownedSeriesIds = _myJars.map((j) => j['series_id'].toString()).toSet();
    final availableSeries = _allSeries.where((s) => ownedSeriesIds.contains(s['id'].toString())).toList();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Text('Mulai Tantangan Baru',
            style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
        content: SizedBox(
          width: double.maxFinite,
          child: ListView(
            shrinkWrap: true,
            children: availableSeries.map((s) => ListTile(
              leading: CircleAvatar(
                backgroundColor: (s['color'] as Color).withAlpha(40),
                backgroundImage: AssetImage(s['image'] as String),
                radius: 20,
              ),
              title: Text(s['name'] as String),
              onTap: () {
                Navigator.pop(ctx);
                _showChallengeTypeDialog(s);
              },
            )).toList(),
          ),
        ),
      ),
    );
  }

  void _showChallengeTypeDialog(Map<String, dynamic> series) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Text('Pilih Durasi',
            style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.calendar_today, color: Colors.blue),
              title: const Text('Tantangan 7 Hari'),
              onTap: () {
                Navigator.pop(ctx);
                _activateChallenge(int.parse(series['id']), isSevenDays: true);
              },
            ),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.auto_awesome, color: Colors.orange),
              title: const Text('Tantangan 40 Hari'),
              onTap: () {
                Navigator.pop(ctx);
                _activateChallenge(int.parse(series['id']), isSevenDays: false);
              },
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _activateChallenge(int seriesId, {bool confirmed = false, bool isSevenDays = false}) async {
    try {
      final result = await ApiService.activateChallenge(seriesId, confirmed: confirmed, isSevenDays: isSevenDays);
      if (mounted) {
        if (result['needs_confirmation'] == true) {
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (ctx) => AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
              title: Text('Konfirmasi', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
              content: Text(result['message'] ?? 'Lanjutkan?'),
              actions: [
                TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
                ElevatedButton(
                  onPressed: () {
                    Navigator.pop(ctx);
                    _activateChallenge(seriesId, confirmed: true, isSevenDays: isSevenDays);
                  },
                  child: const Text('Ya, Lanjutkan'),
                ),
              ],
            ),
          );
          return;
        }
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result['message'] ?? 'Berhasil!')));
        _loadAll();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString()), backgroundColor: Colors.red));
      }
    }
  }

  Future<void> _scanQR() async {
    final result = await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const QRScannerScreen()),
    );
    if (result != null && result is String) {
      _activateJar(result);
    }
  }

  Future<void> _activateJar(String key) async {
    setState(() => _isLoading = true);
    try {
      final res = await ApiService.activateJar(key);
      if (res['can_request_transfer'] == true) {
        if (mounted) setState(() => _isLoading = false);
        _showTransferRequestDialog(key, res['message'] ?? '');
        return;
      }
      await _loadAll();
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Berhasil aktivasi!')));
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString()), backgroundColor: Colors.red));
      }
    }
  }

  void _showTransferRequestDialog(String key, String message) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: Text('Pindahkan Lisensi?', style: GoogleFonts.inter(fontWeight: FontWeight.bold)),
        content: Text('$message\n\nApakah Anda ingin mengirim permintaan transfer ke email pemilik sebelumnya untuk mengambil alih Jar ini?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              setState(() => _isLoading = true);
              try {
                final res = await ApiService.requestLicenseTransfer(key);
                if (mounted) {
                  setState(() => _isLoading = false);
                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message'] ?? 'Permintaan dikirim!')));
                }
              } catch (e) {
                if (mounted) {
                  setState(() => _isLoading = false);
                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString()), backgroundColor: Colors.red));
                }
              }
            },
            child: const Text('Ya, Kirim Permintaan'),
          ),
        ],
      ),
    );
  }
}
