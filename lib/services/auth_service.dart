import 'dart:convert';
import 'package:http/http.dart' as http;
import '../utils/constants.dart';
import '../models/user_model.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AuthService {
  // Login
  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse(AppConstants.loginEndpoint),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({'email': email, 'password': password}),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['status'] == 'success') {
        final user = User.fromJson(data['user']);
        await _saveUserSession(user);
        return {'success': true, 'user': user};
      } else {
        return {'success': false, 'message': data['message'] ?? 'Login failed'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }

  // Register
  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    String? phone,
    String? address,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(AppConstants.registerEndpoint),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'name': name,
          'email': email,
          'password': password,
          'phone': phone,
          'address': address,
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 201 && data['status'] == 'success') {
        // Automatically login or return success to prompt login
        return {'success': true, 'message': data['message']};
      } else {
        return {'success': false, 'message': data['message'] ?? 'Registration failed'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }

  // Update Profile
  Future<Map<String, dynamic>> updateProfile({
    required int id,
    required String name,
    required String phone,
    required String address,
  }) async {
    try {
      final response = await http.post(
        Uri.parse(AppConstants.updateProfileEndpoint),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'id': id,
          'name': name,
          'phone': phone,
          'address': address,
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['status'] == 'success') {
        final user = User.fromJson(data['user']);
        await _saveUserSession(user);
        return {'success': true, 'user': user};
      } else {
        return {'success': false, 'message': data['message'] ?? 'Profile update failed'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }

  // Update FCM Token
  Future<bool> updateFcmToken(int userId, String token) async {
    try {
      final response = await http.post(
        Uri.parse('${AppConstants.baseUrl}/api/users/update_fcm.php'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'user_id': userId,
          'token': token,
        }),
      );
      final data = json.decode(response.body);
      return response.statusCode == 200 && data['status'] == 'success';
    } catch (e) {
      return false;
    }
  }

  // Session Management
  Future<void> _saveUserSession(User user) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(AppConstants.userKey, json.encode(user.toJson()));
  }

  Future<User?> getCurrentUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userData = prefs.getString(AppConstants.userKey);
    if (userData != null) {
      return User.fromJson(json.decode(userData));
    }
    return null;
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(AppConstants.userKey);
  }
}
