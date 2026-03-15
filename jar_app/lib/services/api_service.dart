import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String baseUrl = 'http://192.168.1.9/api/v1';

  // --- AUTH ---
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {'Accept': 'application/json'},
      body: {
        'email': email,
        'password': password,
        'device_name': 'android_phone',
      },
    );
    final data = json.decode(response.body);
    if (response.statusCode == 200) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', data['token']);
      await prefs.setString('user', json.encode(data['user']));
      await prefs.setString('user_name', data['user']['name'] ?? '');
      await prefs.setString('user_nickname', data['user']['nickname'] ?? data['user']['name'] ?? '');
      return data;
    }
    throw Exception(data['message'] ?? 'Login gagal');
  }

  static Future<Map<String, dynamic>> googleLogin(String email, String name, String idToken, {String? photoUrl}) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login/google'),
      headers: {'Accept': 'application/json'},
      body: {
        'google_id': idToken, // Using idToken as google_id for simulation
        'email': email,
        'name': name,
        'photo_url': photoUrl ?? '',
        'device_name': 'android_phone',
      },
    );
    final data = json.decode(response.body);
    if (response.statusCode == 200) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', data['token']);
      await prefs.setString('user', json.encode(data['user']));
      await prefs.setString('user_name', data['user']['name'] ?? '');
      await prefs.setString('user_nickname', data['user']['nickname'] ?? data['user']['name'] ?? '');
      return data;
    }
    throw Exception(data['message'] ?? 'Google login gagal');
  }

  static Future<Map<String, dynamic>> facebookLogin(String email, String name, String fbId, {String? photoUrl}) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login/facebook'),
      headers: {'Accept': 'application/json'},
      body: {
        'facebook_id': fbId,
        'email': email,
        'name': name,
        'photo_url': photoUrl ?? '',
        'device_name': 'android_phone',
      },
    );
    final data = json.decode(response.body);
    if (response.statusCode == 200) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', data['token']);
      await prefs.setString('user', json.encode(data['user']));
      await prefs.setString('user_name', data['user']['name'] ?? '');
      await prefs.setString('user_nickname', data['user']['nickname'] ?? data['user']['name'] ?? '');
      return data;
    }
    throw Exception(data['message'] ?? 'Facebook login gagal');
  }

  static Future<Map<String, dynamic>> phoneLogin(String phone) async {
    // Basic simulation for phone login
    final response = await http.post(
      Uri.parse('$baseUrl/login/phone'),
      headers: {'Accept': 'application/json'},
      body: {
        'phone': phone,
        'device_name': 'android_phone',
      },
    );
    final data = json.decode(response.body);
    if (response.statusCode == 200) {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', data['token']);
      return data;
    }
    throw Exception(data['message'] ?? 'Phone login gagal');
  }

  static Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    try {
      await http.post(
        Uri.parse('$baseUrl/logout'),
        headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
      );
    } catch (_) {}
    await prefs.clear();
  }

  static Future<Map<String, dynamic>> changePassword(String current, String password) async {
    return _authenticatedPost('$baseUrl/profile/password', {
      'current_password': current,
      'password': password,
      'password_confirmation': password,
    });
  }

  static Future<Map<String, dynamic>> getDashboard() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    
    final response = await http.get(
      Uri.parse('$baseUrl/challenges'),
      headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
    );
    final data = json.decode(response.body);

    final responseJars = await http.get(
      Uri.parse('$baseUrl/licenses'),
      headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
    );
    final dataJars = json.decode(responseJars.body);

    return {
      'active_challenges': data['challenges'] ?? [],
      'jars': dataJars is List ? dataJars : (dataJars['licenses'] ?? []),
    };
  }

  static Future<Map<String, dynamic>> updateProfile(String name, String nickname) async {
    final prefs = await SharedPreferences.getInstance();
    final response = await _authenticatedPost('$baseUrl/profile/update', {
      'name': name,
      'nickname': nickname,
    });
    
    final userJson = prefs.getString('user');
    if (userJson != null) {
      final user = json.decode(userJson);
      user['name'] = name;
      user['nickname'] = nickname;
      await prefs.setString('user', json.encode(user));
      await prefs.setString('user_name', name);
      await prefs.setString('user_nickname', nickname);
    }
    
    return response;
  }

  static Future<void> releaseJar(String key) async {
    await _authenticatedPost('$baseUrl/licenses/release', {'license_key': key});
  }

  static Future<Map<String, dynamic>> activateJar(String key) async {
    return _authenticatedPost('$baseUrl/licenses/activate', {'license_key': key});
  }

  static Future<List<dynamic>> getBySeries(String seriesId) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    final response = await http.get(
      Uri.parse('$baseUrl/contents?series_id=$seriesId'),
      headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
    );
    final data = json.decode(response.body);
    return data['contents'] ?? [];
  }

  static Future<Map<String, dynamic>> shakeJar(String licenseKey) async {
    return _authenticatedPost('$baseUrl/licenses/shake', {'license_key': licenseKey});
  }

  static Future<Map<String, dynamic>> activateChallenge(int seriesId, {bool confirmed = false, bool isSevenDays = false}) async {
    return _authenticatedPost('$baseUrl/challenges/activate', {
      'series_id': seriesId.toString(),
      if (confirmed) 'confirmed': '1',
      if (isSevenDays) 'is_seven_days': '1',
    });
  }

  static Future<Map<String, dynamic>> rollContent(int challengeId) async {
    return _authenticatedPost('$baseUrl/challenges/$challengeId/roll', {});
  }

  static Future<Map<String, dynamic>> saveBefore(int entryId, String pesan, String perasaan, String action) async {
    return _authenticatedPost('$baseUrl/journal/$entryId/before', {
      'before_pesan': pesan,
      'before_perasaan': perasaan,
      'before_action': action,
    });
  }

  static Future<Map<String, dynamic>> saveAfter(int entryId, String berhasil, String perubahan, String perasaan) async {
    return _authenticatedPost('$baseUrl/journal/$entryId/after', {
      'after_berhasil': berhasil,
      'after_perubahan': perubahan,
      'after_perasaan': perasaan,
    });
  }

  static Future<List<dynamic>> getChallengeHistory(int challengeId) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    final response = await http.get(
      Uri.parse('$baseUrl/challenges/$challengeId/history'),
      headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
    );
    final data = json.decode(response.body);
    if (response.statusCode == 200) {
      return data['entries'];
    }
    throw Exception('Gagal memuat riwayat jurnal');
  }

  static Future<Map<String, dynamic>> saveFinalReflections(int challengeId, Map<String, String> reflections) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/challenges/$challengeId/reflections'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: json.encode({'reflections': reflections}),
      ).timeout(const Duration(seconds: 15));

      final data = json.decode(response.body);
      if (response.statusCode == 200) return data;
      throw Exception(data['message'] ?? 'Gagal menyimpan refleksi akhir');
    } catch (e) {
       if (e.toString().contains('SocketException') || e.toString().contains('Timeout')) {
          await _addToQueue('$baseUrl/challenges/$challengeId/reflections', reflections, isJson: true);
          return {'queued': true, 'message': 'Tersimpan offline!'};
       }
       rethrow;
    }
  }

  static Future<Map<String, dynamic>> _authenticatedPost(String url, Map<String, dynamic> body) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    
    try {
      final response = await http.post(
        Uri.parse(url),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
        body: body,
      ).timeout(const Duration(seconds: 15));

      final data = json.decode(response.body);
      if (response.statusCode >= 200 && response.statusCode < 300) {
        return data;
      }
      
      throw Exception(data['message'] ?? 'Request failed');
    } catch (e) {
      if (e.toString().contains('SocketException') || e.toString().contains('Timeout')) {
        await _addToQueue(url, body);
        return {'queued': true, 'message': 'Koneksi bermasalah. Data disimpan offline.'};
      }
      rethrow;
    }
  }

  static Future<void> _addToQueue(String url, dynamic body, {bool isJson = false}) async {
    final prefs = await SharedPreferences.getInstance();
    final queueJson = prefs.getString('offline_queue') ?? '[]';
    final List<dynamic> queue = json.decode(queueJson);
    queue.add({
      'url': url,
      'body': body,
      'isJson': isJson,
      'timestamp': DateTime.now().toIso8601String(),
    });
    await prefs.setString('offline_queue', json.encode(queue));
  }

  static Future<void> syncOfflineData() async {
    final prefs = await SharedPreferences.getInstance();
    final queueJson = prefs.getString('offline_queue') ?? '[]';
    final List<dynamic> queue = json.decode(queueJson);
    if (queue.isEmpty) return;

    final token = prefs.getString('token');
    if (token == null) return;

    List<dynamic> remaining = [];
    for (var item in queue) {
      try {
        final headers = {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        };
        if (item['isJson'] == true) {
          headers['Content-Type'] = 'application/json';
        }

        final response = await http.post(
          Uri.parse(item['url']),
          headers: headers,
          body: item['isJson'] == true ? json.encode({'reflections': item['body']}) : item['body'],
        ).timeout(const Duration(seconds: 20));

        if (response.statusCode < 300) {
        } else {
          remaining.add(item);
        }
      } catch (e) {
        remaining.add(item);
      }
    }
    await prefs.setString('offline_queue', json.encode(remaining));
  }
}
