import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:jar_app/core/app_colors.dart';
import 'package:jar_app/screens/auth/login_screen.dart';
import 'package:jar_app/screens/main/home_screen.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:jar_app/services/notification_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await NotificationService.init();
  
  SharedPreferences prefs = await SharedPreferences.getInstance();
  String? token = prefs.getString('token');
  
  if (token != null) {
    NotificationService.scheduleDailyReminders();
  }
  
  runApp(TLQApp(isLoggedIn: token != null));
}

class TLQApp extends StatelessWidget {
  final bool isLoggedIn;
  
  const TLQApp({super.key, required this.isLoggedIn});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'TLQ Jar',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: AppColors.emeraldIslamic,
          primary: AppColors.emeraldIslamic,
          secondary: AppColors.goldIslamic,
        ),
        textTheme: GoogleFonts.outfitTextTheme(),
        scaffoldBackgroundColor: AppColors.creamBg,
      ),
      home: isLoggedIn ? const HomeScreen() : const LoginScreen(),
    );
  }
}
