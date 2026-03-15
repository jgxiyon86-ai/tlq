import 'dart:async';
import 'dart:math';
import 'package:animate_do/animate_do.dart';
import 'package:flutter/material.dart';
import 'package:jar_app/core/app_colors.dart';
import 'package:jar_app/services/api_service.dart';
import 'package:sensors_plus/sensors_plus.dart';

class ShakeScreen extends StatefulWidget {
  final Map<String, dynamic> series;
  final String licenseKey;

  const ShakeScreen({
    super.key,
    required this.series,
    required this.licenseKey,
  });

  @override
  State<ShakeScreen> createState() => _ShakeScreenState();
}

class _ShakeScreenState extends State<ShakeScreen> with SingleTickerProviderStateMixin {
  late AnimationController _shakeController;
  StreamSubscription? _accelerometerSubscription;
  bool _isShaking = false;
  bool _revealing = false;
  Map<String, dynamic>? _content;

  @override
  void initState() {
    super.initState();
    _shakeController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 100),
    );

    _startListening();
  }

  void _startListening() {
    _accelerometerSubscription = accelerometerEvents.listen((AccelerometerEvent event) {
      if (_revealing) return;

      double acceleration = sqrt(event.x * event.x + event.y * event.y + event.z * event.z);
      if (acceleration > 25) { // Threshold for a shake
        if (!_isShaking) {
          _onShakeDetected();
        }
      }
    });
  }

  void _onShakeDetected() async {
    setState(() => _isShaking = true);
    _shakeController.repeat(reverse: true);
    
    // Simulate shaking for 1.5 seconds then reveal
    await Future.delayed(const Duration(milliseconds: 1500));
    
    if (mounted) {
      _shakeController.stop();
      setState(() {
        _isShaking = false;
        _revealing = true;
      });
      _fetchContent();
    }
  }

  Future<void> _fetchContent() async {
    try {
      final content = await ApiService.shakeJar(widget.licenseKey);
      setState(() => _content = content);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
        setState(() => _revealing = false);
      }
    }
  }

  @override
  void dispose() {
    _shakeController.dispose();
    _accelerometerSubscription?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    Color seriesColor = widget.series['color'] ?? AppColors.emeraldIslamic;

    return Scaffold(
      appBar: AppBar(
        title: Text('Seri ${widget.series['name']}'),
        leading: BackButton(onPressed: () => Navigator.pop(context)),
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            if (!_revealing) ...[
              const Text(
                'Goyangkan Smartphone Anda',
                style: TextStyle(fontSize: 18, color: AppColors.textLight),
              ),
              const SizedBox(height: 10),
              const Text(
                'Untuk mengambil pesan cinta hari ini',
                style: TextStyle(fontSize: 14, color: AppColors.textLight, fontStyle: FontStyle.italic),
              ),
              const SizedBox(height: 50),
              
              AnimatedBuilder(
                animation: _shakeController,
                builder: (context, child) {
                  return Transform.translate(
                    offset: Offset(_shakeController.value * 10 - 5, 0),
                    child: child,
                  );
                },
                child: Icon(Icons.door_front_door, size: 200, color: seriesColor), // Placeholder for actual Jar Illustration
              ),
            ] else if (_content == null) ...[
              const CircularProgressIndicator(),
              const SizedBox(height: 20),
              const Text('Membuka gulungan kertas...'),
            ] else ...[
              // The Content Reveal
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(30),
                  child: Column(
                    children: [
                      FadeInDown(
                        child: Text(
                          _content!['surah_ayah'],
                          style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppColors.goldIslamic),
                        ),
                      ),
                      const SizedBox(height: 30),
                      FadeIn(
                        delay: const Duration(milliseconds: 500),
                        child: Text(
                          _content!['arabic_text'],
                          textAlign: TextAlign.center,
                          textDirection: TextDirection.rtl,
                          style: const TextStyle(fontSize: 28, fontFamily: 'Serif', height: 1.5),
                        ),
                      ),
                      const SizedBox(height: 20),
                      FadeIn(
                        delay: const Duration(milliseconds: 800),
                        child: Text(
                          _content!['translation'],
                          textAlign: TextAlign.center,
                          style: const TextStyle(fontSize: 16, fontStyle: FontStyle.italic, color: AppColors.textLight),
                        ),
                      ),
                      const Divider(height: 60),
                      FadeInLeft(
                        delay: const Duration(milliseconds: 1100),
                        child: _cardSection('Insight Pencerahan', _content!['insight'], Icons.lightbulb_outline),
                      ),
                      const SizedBox(height: 20),
                      FadeInLeft(
                        delay: const Duration(milliseconds: 1400),
                        child: _cardSection('Action Plan Hari Ini', _content!['action_plan'], Icons.flash_on),
                      ),
                      const SizedBox(height: 50),
                      ElevatedButton(
                        onPressed: () {
                          setState(() {
                            _revealing = false;
                            _content = null;
                            _startListening();
                          });
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.goldIslamic,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 15),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                        ),
                        child: const Text('Kocok Lagi'),
                      ),
                    ],
                  ),
                ),
              )
            ]
          ],
        ),
      ),
    );
  }

  Widget _cardSection(String title, String body, IconData icon) {
    return Container(
      padding: const EdgeInsets.all(25),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(30),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 5))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: AppColors.goldIslamic, size: 20),
              const SizedBox(width: 10),
              Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            ],
          ),
          const SizedBox(height: 10),
          Text(body, style: const TextStyle(color: AppColors.textDark, height: 1.5)),
        ],
      ),
    );
  }
}
