import 'package:animate_do/animate_do.dart';
import 'package:flutter/material.dart';
import 'package:jar_app/core/app_colors.dart';
import 'package:jar_app/screens/jar/shake_screen.dart';
import 'package:jar_app/services/api_service.dart';

class ManualBookScreen extends StatefulWidget {
  final Map<String, dynamic> series;
  final String licenseKey;

  const ManualBookScreen({
    super.key,
    required this.series,
    required this.licenseKey,
  });

  @override
  State<ManualBookScreen> createState() => _ManualBookScreenState();
}

class _ManualBookScreenState extends State<ManualBookScreen> {
  final PageController _pageController = PageController();
  List<dynamic> _pages = [];
  bool _isLoading = true;
  int _currentPage = 0;

  @override
  void initState() {
    super.initState();
    _fetchPages();
  }

  Future<void> _fetchPages() async {
    try {
      final pages = await ApiService.getBySeries(widget.series['id'].toString());
      setState(() {
        _pages = pages;
        _isLoading = false;
      });
    } catch (e) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4EFE3), // Aged paper color
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: const BackButton(color: AppColors.textDark),
        title: Text(
          'Panduan ${widget.series['name']}',
          style: const TextStyle(color: AppColors.textDark, fontWeight: FontWeight.bold),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Stack(
              children: [
                PageView.builder(
                  controller: _pageController,
                  onPageChanged: (idx) => setState(() => _currentPage = idx),
                  itemCount: _pages.length,
                  itemBuilder: (context, index) {
                    final page = _pages[index];
                    return _buildPage(page);
                  },
                ),
                
                // Navigation Buttons
                Positioned(
                  bottom: 40,
                  left: 30,
                  right: 30,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      if (_currentPage > 0)
                        IconButton.filled(
                          onPressed: () => _pageController.previousPage(
                              duration: const Duration(milliseconds: 500), curve: Curves.easeInOut),
                          icon: const Icon(Icons.arrow_back_ios_new),
                          style: IconButton.styleFrom(backgroundColor: AppColors.emeraldIslamic),
                        )
                      else
                        const SizedBox(),
                      
                      if (_currentPage < _pages.length - 1)
                        ElevatedButton(
                          onPressed: () => _pageController.nextPage(
                              duration: const Duration(milliseconds: 500), curve: Curves.easeInOut),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.emeraldIslamic,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                            padding: const EdgeInsets.symmetric(horizontal: 25, vertical: 15),
                          ),
                          child: const Text('Selanjutnya'),
                        )
                      else
                        FadeInRight(
                          child: ElevatedButton(
                            onPressed: () {
                              Navigator.pushReplacement(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => ShakeScreen(
                                    series: widget.series,
                                    licenseKey: widget.licenseKey,
                                  ),
                                ),
                              );
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.goldIslamic,
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                              padding: const EdgeInsets.symmetric(horizontal: 30, vertical: 15),
                            ),
                            child: const Text('Mulai Kocok Jar', style: TextStyle(fontWeight: FontWeight.bold)),
                          ),
                        ),
                    ],
                  ),
                ),
                
                // Page Indicator
                Positioned(
                  bottom: 100,
                  left: 0,
                  right: 0,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(_pages.length, (index) => Container(
                      margin: const EdgeInsets.symmetric(horizontal: 4),
                      width: 8,
                      height: 8,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: _currentPage == index ? AppColors.goldIslamic : Colors.grey[300],
                      ),
                    )),
                  ),
                )
              ],
            ),
    );
  }

  Widget _buildPage(dynamic page) {
    return Padding(
      padding: const EdgeInsets.all(40.0),
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            if (page['title'] != null)
              FadeInDown(
                child: Text(
                  page['title'],
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: AppColors.goldIslamic,
                  ),
                ),
              ),
            const SizedBox(height: 30),
            FadeIn(
              delay: const Duration(milliseconds: 300),
              child: Container(
                padding: const EdgeInsets.all(30),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.5),
                  borderRadius: BorderRadius.circular(30),
                  border: Border.all(color: AppColors.goldIslamic.withOpacity(0.2)),
                ),
                child: Text(
                  page['content'].replaceAll('\\n', '\n'),
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 18,
                    height: 1.6,
                    color: AppColors.textDark,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
