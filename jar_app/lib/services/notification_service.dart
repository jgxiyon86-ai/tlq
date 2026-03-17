import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:timezone/data/latest_all.dart' as tz_data;
import 'package:timezone/timezone.dart' as tz;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:flutter_timezone/flutter_timezone.dart';
import 'dart:io';


class NotificationService {
  static final FlutterLocalNotificationsPlugin _notificationsPlugin =
      FlutterLocalNotificationsPlugin();

  static Future<void> init() async {
    tz_data.initializeTimeZones();
    
    // DETECT AND SET LOCATION (Crucial for fixed scheduling)
    try {
      final String currentTimeZone = (await FlutterTimezone.getLocalTimezone()).identifier;
      tz.setLocalLocation(tz.getLocation(currentTimeZone));
    } catch (e) {
      // Fallback to UTC if detection fails, though usually tz.local handles it
    }

    const AndroidInitializationSettings initializationSettingsAndroid =
        AndroidInitializationSettings('@mipmap/ic_launcher');

    const InitializationSettings initializationSettings = InitializationSettings(
      android: initializationSettingsAndroid,
    );

    await _notificationsPlugin.initialize(
      initializationSettings,
      onDidReceiveNotificationResponse: (details) {
        // Handle notification tap
      },
    );

    // AUTOMATIC PERMISSION REQUESTS (Hard Mode)
    if (Platform.isAndroid) {
      // 1. Basic Notification Permission
      await Permission.notification.request();
      
      // 2. Exact Alarm Permission
      if (await Permission.scheduleExactAlarm.isDenied) {
        await Permission.scheduleExactAlarm.request();
      }
    }
  }



  static Future<void> testInstantNotification() async {
    const AndroidNotificationDetails androidPlatformChannelSpecifics =
        AndroidNotificationDetails(
      'challenge_test_channel_v3',
      'Tes Notifikasi TLQ',
      channelDescription: 'Digunakan untuk mengetes notifikasi aplikasi',
      importance: Importance.max,
      priority: Priority.high,
      showWhen: true,
      playSound: true,
      enableVibration: true,
      fullScreenIntent: true,
      category: AndroidNotificationCategory.alarm,
      audioAttributesUsage: AudioAttributesUsage.alarm,
    );
    const NotificationDetails platformChannelSpecifics =
        NotificationDetails(android: androidPlatformChannelSpecifics);

    await _notificationsPlugin.show(
      999,
      '🧪 Tes Notifikasi Berhasil!',
      'Alhamdulillah, sistem notifikasi TLQ sudah aktif dan "Mantap".',
      platformChannelSpecifics,
    );
  }



  static Future<void> scheduleDailyReminders() async {
    await scheduleDailyMorningReminder();
    await scheduleDailyEveningReminder();
  }

  static Future<void> scheduleDailyMorningReminder() async {
    final prefs = await SharedPreferences.getInstance();
    final hour = prefs.getInt('notif_morning_hour') ?? 5;
    final minute = prefs.getInt('notif_morning_minute') ?? 0;

    await _notificationsPlugin.zonedSchedule(
      101,
      '🌅 Bismillah, Waktunya TLQ!',
      'Mari awali pagimu dengan keberkahan Al-Quran. Klik untuk mengambil ayat pilihanmu hari ini.',
      _nextInstanceOfTime(hour, minute),
      const NotificationDetails(
        android: AndroidNotificationDetails(
          'hard_alert_morning_v3',
          'Pengingat Pagi (PENTING)',
          channelDescription: 'Notifikasi utama untuk memulai tantangan harian',
          importance: Importance.max,
          priority: Priority.high,
          visibility: NotificationVisibility.public,
          fullScreenIntent: true,
          playSound: true,
          enableVibration: true,
          category: AndroidNotificationCategory.alarm,
          audioAttributesUsage: AudioAttributesUsage.alarm,
          styleInformation: BigTextStyleInformation(
            'Mari awali pagimu dengan keberkahan Al-Quran. Klik untuk mengambil ayat pilihanmu hari ini.',
            contentTitle: '🌅 Bismillah, Waktunya TLQ!',
          ),
        ),
      ),
      androidScheduleMode: AndroidScheduleMode.exactAllowWhileIdle,
      uiLocalNotificationDateInterpretation:
          UILocalNotificationDateInterpretation.absoluteTime,
      matchDateTimeComponents: DateTimeComponents.time,
    );
  }



  static Future<void> scheduleDailyEveningReminder() async {
    final prefs = await SharedPreferences.getInstance();
    final hour = prefs.getInt('notif_evening_hour') ?? 17;
    final minute = prefs.getInt('notif_evening_minute') ?? 0;

    await _notificationsPlugin.zonedSchedule(
      102,
      '🌇 Alhamdulillah, Waktunya Catatan Sore!',
      'Hari hampir usai, yuk abadikan mutiara hikmah dari ayatmu hari ini di Catatan Sore.',
      _nextInstanceOfTime(hour, minute),
      const NotificationDetails(
        android: AndroidNotificationDetails(
          'hard_alert_evening_v3',
          'Pengingat Sore (PENTING)',
          channelDescription: 'Notifikasi utama untuk mengisi catatan perubahan',
          importance: Importance.max,
          priority: Priority.high,
          visibility: NotificationVisibility.public,
          playSound: true,
          enableVibration: true,
          category: AndroidNotificationCategory.alarm,
          audioAttributesUsage: AudioAttributesUsage.alarm,
          styleInformation: BigTextStyleInformation(
            'Hari hampir usai, yuk abadikan mutiara hikmah dari ayatmu hari ini di Catatan Sore.',
            contentTitle: '🌇 Alhamdulillah, Waktunya Catatan Sore!',
          ),
        ),
      ),
      androidScheduleMode: AndroidScheduleMode.exactAllowWhileIdle,
      uiLocalNotificationDateInterpretation:
          UILocalNotificationDateInterpretation.absoluteTime,
      matchDateTimeComponents: DateTimeComponents.time,
    );
  }



  static tz.TZDateTime _nextInstanceOfTime(int hour, int minute) {
    // This uses tz.local which is the DEVICE's local timezone.
    final tz.TZDateTime now = tz.TZDateTime.now(tz.local);
    tz.TZDateTime scheduledDate =
        tz.TZDateTime(tz.local, now.year, now.month, now.day, hour, minute);
    if (scheduledDate.isBefore(now)) {
      scheduledDate = scheduledDate.add(const Duration(days: 1));
    }
    return scheduledDate;
  }


}
