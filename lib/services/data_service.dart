import 'dart:convert';
import 'package:http/http.dart' as http;
import '../utils/constants.dart';
import '../models/service_model.dart';
import '../models/order_model.dart';

class DataService {
  // Fetch Services
  Future<List<LaundryService>> getServices() async {
    try {
      final response = await http.get(Uri.parse(AppConstants.getServicesEndpoint));
      
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['status'] == 'success') {
          return List<LaundryService>.from(
            data['data'].map((item) => LaundryService.fromJson(item))
          );
        }
      }
      return [];
    } catch (e) {
      print('Error fetching services: $e');
      return [];
    }
  }

  // Fetch User Orders
  Future<List<Order>> getUserOrders(int userId) async {
    try {
      final url = '${AppConstants.getUserOrdersEndpoint}?user_id=$userId';
      final response = await http.get(Uri.parse(url));
      
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['status'] == 'success') {
          return List<Order>.from(
            data['data'].map((item) => Order.fromJson(item))
          );
        }
      }
      return [];
    } catch (e) {
      print('Error fetching orders: $e');
      return [];
    }
  }

  // Create Order
  Future<Map<String, dynamic>> createOrder(int userId, List<OrderItem> items, String? instructions, int deliveryDays) async {
    try {
      // Basic validation
      if (items.isEmpty) {
        return {'success': false, 'message': 'Cart is empty'};
      }

      final response = await http.post(
        Uri.parse(AppConstants.createOrderEndpoint),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'user_id': userId,
          'items': items.map((e) => e.toJson()).toList(),
          'special_instructions': instructions,
          'delivery_days': deliveryDays,
        }),
      );

      final data = json.decode(response.body);
      
      if (response.statusCode == 201 && data['status'] == 'success') {
        return {'success': true, 'order_id': data['order_id']};
      } else {
        return {'success': false, 'message': data['message'] ?? 'Failed to create order'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Network error: $e'};
    }
  }
}
