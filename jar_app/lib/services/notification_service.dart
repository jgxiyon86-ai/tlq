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
    
    try {
      final String currentTimeZone = (await FlutterTimezone.getLocalTimezone()).identifier;
      tz.setLocalLocation(tz.getLocation(currentTimeZone));
      await _logDebug('Timezone set: $currentTimeZone');
    } catch (e) {
      // Robust Fallback: Try Jakarta first before UTC
      try {
        tz.setLocalLocation(tz.getLocation('Asia/Jakarta'));
        await _logDebug('Timezone fallback: Asia/Jakarta (WIB)');
      } catch (_) {
        tz.setLocalLocation(tz.UTC);
        await _logDebug('Timezone fallback: UTC');
      }
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
      
      // 2. Exact Alarm Permission (Android 13+)
      if (await Permission.scheduleExactAlarm.isDenied) {
        await Permission.scheduleExactAlarm.request();
      }
    }
  }

  static Future<bool> checkExactAlarmPermission() async {
    if (Platform.isAndroid) {
      return await Permission.scheduleExactAlarm.isGranted;
    }
    return true;
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

    try {
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
    } catch (e) {
      // Fallback
      await _notificationsPlugin.zonedSchedule(
        101,
        '🌅 Bismillah, Waktunya TLQ!',
        'Mari awali pagimu dengan keberkahan Al-Quran.',
        _nextInstanceOfTime(hour, minute),
        const NotificationDetails(
          android: AndroidNotificationDetails(
            'hard_alert_morning_v3',
            'Pengingat Pagi (PENTING)',
            importance: Importance.max,
            priority: Priority.high,
          ),
        ),
        androidScheduleMode: AndroidScheduleMode.inexactAllowWhileIdle,
        uiLocalNotificationDateInterpretation:
            UILocalNotificationDateInterpretation.absoluteTime,
        matchDateTimeComponents: DateTimeComponents.time,
      );
    }
  }



  static Future<void> scheduleDailyEveningReminder() async {
    final prefs = await SharedPreferences.getInstance();
    final hour = prefs.getInt('notif_evening_hour') ?? 17;
    final minute = prefs.getInt('notif_evening_minute') ?? 0;

    try {
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
    } catch (e) {
      // If exact alarm fails, fallback to inexact
      await _notificationsPlugin.zonedSchedule(
        102,
        '🌇 Alhamdulillah, Waktunya Catatan Sore!',
        'Hari hampir usai, yuk abadikan mutiara hikmah dari ayatmu hari ini di Catatan Sore.',
        _nextInstanceOfTime(hour, minute),
        const NotificationDetails(
          android: AndroidNotificationDetails(
            'hard_alert_evening_v3',
            'Pengingat Sore (PENTING)',
            importance: Importance.max,
            priority: Priority.high,
          ),
        ),
        androidScheduleMode: AndroidScheduleMode.inexactAllowWhileIdle,
        uiLocalNotificationDateInterpretation:
            UILocalNotificationDateInterpretation.absoluteTime,
        matchDateTimeComponents: DateTimeComponents.time,
      );
    }
  }

  static Future<void> testScheduledNotification(int delayMinutes) async {
    final tz.TZDateTime now = tz.TZDateTime.now(tz.local);
    final tz.TZDateTime scheduledTime = now.add(Duration(minutes: delayMinutes));
    
    await _logDebug('Scheduling test for: $scheduledTime (Now: $now)');

    try {
      await _notificationsPlugin.zonedSchedule(
        888,
        '⚙️ Tes Jadwal Berhasil!',
        'Alhamdulillah, ini adalah notifikasi terjadwal ($delayMinutes menit yang lalu). Berarti sistem jadwal di HP Anda AKTIF.',
        scheduledTime,
        const NotificationDetails(
          android: AndroidNotificationDetails(
            'scheduled_test_channel_v4', // New channel ID
            'Tes Jadwal TLQ',
            channelDescription: 'Mengetes apakah sistem penjadwalan berjalan',
            importance: Importance.max,
            priority: Priority.high,
            fullScreenIntent: true,
            category: AndroidNotificationCategory.alarm,
            audioAttributesUsage: AudioAttributesUsage.alarm,
          ),
        ),
        androidScheduleMode: AndroidScheduleMode.exactAllowWhileIdle,
        uiLocalNotificationDateInterpretation:
            UILocalNotificationDateInterpretation.absoluteTime,
      );
      await _logDebug('Success: zonedSchedule exact called.');
    } catch (e) {
      await _logDebug('Error exact: $e');
      // Fallback
      await _notificationsPlugin.zonedSchedule(
        888,
        '⚠️ Tes Jadwal (Inexact)',
        'Notifikasi muncul via mode Inexact. Mungkin sedikit terlambat.',
        scheduledTime,
        const NotificationDetails(
          android: AndroidNotificationDetails(
            'scheduled_test_channel_v4',
            'Tes Jadwal TLQ',
            importance: Importance.max,
            priority: Priority.high,
          ),
        ),
        androidScheduleMode: AndroidScheduleMode.inexactAllowWhileIdle,
        uiLocalNotificationDateInterpretation:
            UILocalNotificationDateInterpretation.absoluteTime,
      );
      await _logDebug('Success: zonedSchedule inexact called.');
    }
  }

  static Future<void> openSettings() async {
    await openAppSettings();
  }

  static Future<void> openExactAlarmSettings() async {
    // This intent opens the specific "Alarms & Reminders" screen
    const String intentName = 'android.settings.REQUEST_SCHEDULE_EXACT_ALARM';
    // We'll use a trick or just open settings if not easy, but usually 
    // permission_handler's openAppSettings is close. 
    // Let's try to use the specific one if possible.
    await openAppSettings(); 
  }

  static Future<void> _logDebug(String msg) async {
    final prefs = await SharedPreferences.getInstance();
    final String currentLogs = prefs.getString('notif_debug_logs') ?? '';
    final String timestamp = DateTime.now().toString().split('.')[0];
    await prefs.setString('notif_debug_logs', '[$timestamp] $msg\n$currentLogs');
  }

  static Future<String> getDebugLogs() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('notif_debug_logs') ?? 'No logs found.';
  }

  static Future<void> clearLogs() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('notif_debug_logs');
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
