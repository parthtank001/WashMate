import 'package:flutter/material.dart';
import '../models/order_model.dart';
import '../models/service_model.dart';

class CartItem {
  final LaundryService service;
  int quantity;

  CartItem({required this.service, this.quantity = 1});
  
  double get subtotal => service.price * quantity;
}

class CartViewModel extends ChangeNotifier {
  final Map<int, CartItem> _items = {};

  Map<int, CartItem> get items => _items;

  int get itemCount => _items.length;

  double get totalAmount {
    double total = 0.0;
    _items.forEach((key, item) {
      total += item.subtotal;
    });
    return total;
  }

  void addItem(LaundryService service) {
    if (_items.containsKey(service.id)) {
      _items[service.id]!.quantity += 1;
    } else {
      _items[service.id] = CartItem(service: service);
    }
    notifyListeners();
  }

  void removeItem(int serviceId) {
    if (_items.containsKey(serviceId)) {
      if (_items[serviceId]!.quantity > 1) {
        _items[serviceId]!.quantity -= 1;
      } else {
        _items.remove(serviceId);
      }
      notifyListeners();
    }
  }

  void clearItem(int serviceId) {
    _items.remove(serviceId);
    notifyListeners();
  }

  void clearCart() {
    _items.clear();
    notifyListeners();
  }

  // Convert CartItems to API OrderItems
  List<OrderItem> getOrderItemsForApi() {
    return _items.values.map((cartItem) {
      return OrderItem(
        serviceId: cartItem.service.id,
        quantity: cartItem.quantity,
        subtotal: cartItem.subtotal,
      );
    }).toList();
  }
}
