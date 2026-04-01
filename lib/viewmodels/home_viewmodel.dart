import 'package:flutter/material.dart';
import '../models/service_model.dart';
import '../models/order_model.dart';
import '../services/data_service.dart';

class HomeViewModel extends ChangeNotifier {
  final DataService _dataService = DataService();
  
  List<LaundryService> _services = [];
  List<Order> _activeOrders = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<LaundryService> get services => _services;
  List<Order> get activeOrders => _activeOrders;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> loadDashboardData(int userId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      // Fetch concurrently for speed
      final results = await Future.wait([
        _dataService.getServices(),
        _dataService.getUserOrders(userId),
      ]);

      _services = results[0] as List<LaundryService>;
      
      // Only keep active orders (not delivered or cancelled)
      final allOrders = results[1] as List<Order>;
      _activeOrders = allOrders.where((o) => o.status != 'delivered' && o.status != 'cancelled').toList();

    } catch (e) {
      _errorMessage = "Failed to load dashboard data.";
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Helper method to get a service by ID
  LaundryService? getServiceById(int id) {
    try {
      return _services.firstWhere((s) => s.id == id);
    } catch (e) {
      return null;
    }
  }
}
