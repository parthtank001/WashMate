class OrderItem {
  final int? id;
  final int serviceId;
  final String? serviceName;
  final int quantity;
  final double subtotal;

  OrderItem({
    this.id,
    required this.serviceId,
    this.serviceName,
    required this.quantity,
    required this.subtotal,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? ''),
      serviceId: json['service_id'] is int ? json['service_id'] : int.tryParse(json['service_id'].toString()) ?? 0,
      serviceName: json['service_name'],
      quantity: json['quantity'] is int ? json['quantity'] : int.tryParse(json['quantity'].toString()) ?? 1,
      subtotal: json['subtotal'] is double ? json['subtotal'] : double.tryParse(json['subtotal'].toString()) ?? 0.0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'service_id': serviceId,
      'quantity': quantity,
      'subtotal': subtotal,
    };
  }
}

class Order {
  final int id;
  final int userId;
  final String status;
  final double totalAmount;
  final DateTime createdAt;
  final int? deliveryDays;
  final List<OrderItem> items;

  Order({
    required this.id,
    required this.userId,
    required this.status,
    required this.totalAmount,
    required this.createdAt,
    this.deliveryDays,
    this.items = const [],
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    var itemsList = <OrderItem>[];
    if (json['items'] != null) {
      itemsList = List<OrderItem>.from(
        json['items'].map((item) => OrderItem.fromJson(item)),
      );
    }

    return Order(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      userId: json['user_id'] is int ? json['user_id'] : int.tryParse(json['user_id'].toString()) ?? 0,
      status: json['status'] ?? 'pending',
      totalAmount: json['total_amount'] is double ? json['total_amount'] : double.tryParse(json['total_amount'].toString()) ?? 0.0,
      createdAt: json['created_at'] != null ? DateTime.parse(json['created_at']) : DateTime.now(),
      deliveryDays: json['delivery_days'] != null ? (json['delivery_days'] is int ? json['delivery_days'] : int.tryParse(json['delivery_days'].toString())) : null,
      items: itemsList,
    );
  }
}
