import 'package:flutter/material.dart';
import '../models/order_model.dart';
import '../services/data_service.dart';

class OrderViewModel extends ChangeNotifier {
  final DataService _dataService = DataService();
  
  List<Order> _orders = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<Order> get orders => _orders;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> fetchUserOrders(int userId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _orders = await _dataService.getUserOrders(userId);
    } catch (e) {
      _errorMessage = "Failed to load orders.";
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
