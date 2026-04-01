class AppConstants {
  static const String appName = 'WashMate';
  
  // API Endpoints
  // Replace with your local IP address for physical device (e.g., 192.168.x.x)
  // For Android Emulator use 10.0.2.2
  // For iOS Simulator use 127.0.0.1
  static const String baseUrl = 'http://10.0.2.2/washmate/backend';
  
  static const String loginEndpoint = '$baseUrl/api/auth/login.php';
  static const String registerEndpoint = '$baseUrl/api/auth/register.php';
  static const String updateProfileEndpoint = '$baseUrl/api/users/update_profile.php';
  static const String getServicesEndpoint = '$baseUrl/api/services/get_all.php';
  static const String createOrderEndpoint = '$baseUrl/api/orders/create.php';
  static const String getUserOrdersEndpoint = '$baseUrl/api/orders/get_user_orders.php';
  
  // Storage Keys
  static const String tokenKey = 'washmate_auth_token';
  static const String userKey = 'washmate_user_data';
}
