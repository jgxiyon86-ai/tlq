import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:timezone/data/latest_all.dart' as tz;
import 'package:timezone/timezone.dart' as tz;
import 'package:shared_preferences/shared_preferences.dart';


class NotificationService {
  static final FlutterLocalNotificationsPlugin _notificationsPlugin =
      FlutterLocalNotificationsPlugin();

  static Future<void> init() async {
    tz.initializeTimeZones();
    
    const AndroidInitializationSettings initializationSettingsAndroid =
        AndroidInitializationSettings('@mipmap/ic_launcher');

    const InitializationSettings initializationSettings = InitializationSettings(
      android: initializationSettingsAndroid,
    );

    await _notificationsPlugin.initialize(
      initializationSettings,
      onDidReceiveNotificationResponse: (details) {
        // Handle notification tap if needed
      },
    );

    // Request permissions for Android 13+
    await _notificationsPlugin
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.requestNotificationsPermission();
    
    await _notificationsPlugin
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.requestExactAlarmsPermission();
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
          'challenge_reminders_morning',
          'Peringatan Pagi',
          channelDescription: 'Pengingat harian untuk memulai tantangan',
          importance: Importance.max,
          priority: Priority.high,
          visibility: NotificationVisibility.public,
          fullScreenIntent: true,
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
          'challenge_reminders_evening',
          'Peringatan Sore',
          channelDescription: 'Pengingat harian untuk mengisi catatan sore',
          importance: Importance.max,
          priority: Priority.high,
          visibility: NotificationVisibility.public,
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
