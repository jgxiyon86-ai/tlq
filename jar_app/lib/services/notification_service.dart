import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:timezone/data/latest_all.dart' as tz;
import 'package:timezone/timezone.dart' as tz;

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
  }

  static Future<void> scheduleDailyReminders() async {
    await scheduleDaily5AMReminder();
    await scheduleDaily5PMReminder();
  }

  static Future<void> scheduleDaily5AMReminder() async {
    await _notificationsPlugin.zonedSchedule(
      101,
      '🌅 Waktunya Tantangan TLQ!',
      'Bismillah, mari mulai harimu dengan menghidupkan Al-Quran. Klik untuk ambil ayat hari ini.',
      _nextInstanceOfTime(5),
      const NotificationDetails(
        android: AndroidNotificationDetails(
          'challenge_reminders_morning',
          'Morning Reminders',
          channelDescription: 'Daily reminder at 5 AM to start the challenge',
          importance: Importance.max,
          priority: Priority.high,
        ),
      ),
      androidScheduleMode: AndroidScheduleMode.exactAllowWhileIdle,
      uiLocalNotificationDateInterpretation:
          UILocalNotificationDateInterpretation.absoluteTime,
      matchDateTimeComponents: DateTimeComponents.time,
    );
  }

  static Future<void> scheduleDaily5PMReminder() async {
    await _notificationsPlugin.zonedSchedule(
      102,
      '🌇 Waktunya Catatan Sore!',
      'Alhamdulillah harimu hampir usai. Yuk isi Catatan Soremu untuk melihat perubahan hari ini!',
      _nextInstanceOfTime(17),
      const NotificationDetails(
        android: AndroidNotificationDetails(
          'challenge_reminders_evening',
          'Evening Reminders',
          channelDescription: 'Daily reminder at 5 PM to fill the after journal',
          importance: Importance.max,
          priority: Priority.high,
        ),
      ),
      androidScheduleMode: AndroidScheduleMode.exactAllowWhileIdle,
      uiLocalNotificationDateInterpretation:
          UILocalNotificationDateInterpretation.absoluteTime,
      matchDateTimeComponents: DateTimeComponents.time,
    );
  }

  static tz.TZDateTime _nextInstanceOfTime(int hour) {
    final tz.TZDateTime now = tz.TZDateTime.now(tz.local);
    tz.TZDateTime scheduledDate =
        tz.TZDateTime(tz.local, now.year, now.month, now.day, hour);
    if (scheduledDate.isBefore(now)) {
      scheduledDate = scheduledDate.add(const Duration(days: 1));
    }
    return scheduledDate;
  }
}
