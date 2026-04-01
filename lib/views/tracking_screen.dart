import 'dart:async';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../models/order_model.dart';
import '../theme/app_theme.dart';
import '../viewmodels/auth_viewmodel.dart';
import '../viewmodels/order_viewmodel.dart';

class TrackingScreen extends StatefulWidget {
  final Order order;

  const TrackingScreen({super.key, required this.order});

  @override
  State<TrackingScreen> createState() => _TrackingScreenState();
}

class _TrackingScreenState extends State<TrackingScreen> {
  Timer? _pollingTimer;

  @override
  void initState() {
    super.initState();
    _startPolling();
  }

  void _startPolling() {
    // Poll the backend every 5 seconds for real-time updates
    _pollingTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      if (mounted) {
        final user = context.read<AuthViewModel>().currentUser;
        if (user != null) {
          context.read<OrderViewModel>().fetchUserOrders(user.id);
        }
      }
    });
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // Reactively watch the OrderViewModel and find the latest state of this specific order
    final orderState = context.watch<OrderViewModel>();
    final currentOrder = orderState.orders.firstWhere(
      (o) => o.id == widget.order.id,
      orElse: () => widget.order,
    );

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new),
          onPressed: () => context.pop(),
        ),
        title: Text('Order #${currentOrder.id.toString().padLeft(5, '0')}'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status Header
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: AppTheme.primaryBlue,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Row(
                children: [
                  const Icon(Icons.local_shipping, color: Colors.white, size: 48),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Estimated Delivery',
                          style: TextStyle(color: Colors.white70, fontSize: 14),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          _getEstimatedDelivery(currentOrder.createdAt),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 32),

            Text('Tracking Timeline', style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 24),

            // Timeline
            _buildTimelineIndicator('Order Placed', 'pending', true, Icons.receipt_long),
            _buildTimelineIndicator('Picked Up', 'picked_up', _isPastOrCurrent(currentOrder.status, 'picked_up'), Icons.directions_car),
            _buildTimelineIndicator('Washing / Ironing', 'washing', _isPastOrCurrent(currentOrder.status, 'washing'), Icons.local_laundry_service),
            _buildTimelineIndicator('Ready for Delivery', 'ready', _isPastOrCurrent(currentOrder.status, 'ready'), Icons.check_circle_outline),
            _buildTimelineIndicator('Delivered', 'delivered', _isPastOrCurrent(currentOrder.status, 'delivered'), Icons.home, isLast: true),
            
            const SizedBox(height: 40),
            
            // Order Summary inside Tracking
            Text('Order Summary', style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  children: currentOrder.items.map<Widget>((item) {
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 8.0),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              '${item.quantity}x ${item.serviceName ?? 'Service'}',
                              style: const TextStyle(fontSize: 14),
                            ),
                          ),
                          Text(
                            '\$${item.subtotal.toStringAsFixed(2)}',
                            style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    );
                  }).toList()
                    ..add(
                      const Divider(height: 24),
                    )
                    ..add(
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Total Paid', style: TextStyle(fontWeight: FontWeight.bold)),
                          Text(
                            '\$${currentOrder.totalAmount.toStringAsFixed(2)}',
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 18,
                              color: AppTheme.primaryBlue,
                            ),
                          ),
                        ],
                      )
                    ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  bool _isPastOrCurrent(String currentStatus, String targetPhase) {
    const phases = ['pending', 'picked_up', 'washing', 'ready', 'delivered'];
    final currentIndex = phases.indexOf(currentStatus);
    final targetIndex = phases.indexOf(targetPhase);
    return currentIndex >= targetIndex;
  }

  String _getEstimatedDelivery(DateTime createdAt) {
    // Arbitrary logical estimate of +2 days
    final deliveryDate = createdAt.add(const Duration(days: 2));
    return DateFormat('MMM dd, yyyy - hh:mm a').format(deliveryDate);
  }

  Widget _buildTimelineIndicator(String title, String state, bool isCompleted, IconData iconData, {bool isLast = false}) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: isCompleted ? AppTheme.primaryBlue : Colors.grey.shade200,
                shape: BoxShape.circle,
              ),
              child: Icon(iconData, color: isCompleted ? Colors.white : Colors.grey, size: 20),
            ),
            if (!isLast)
              Container(
                width: 2,
                height: 40,
                color: isCompleted ? AppTheme.primaryBlue : Colors.grey.shade300,
              ),
          ],
        ),
        const SizedBox(width: 16),
        Padding(
          padding: const EdgeInsets.only(top: 8.0),
          child: Text(
            title,
            style: TextStyle(
              fontSize: 16,
              fontWeight: isCompleted ? FontWeight.bold : FontWeight.normal,
              color: isCompleted ? AppTheme.textDark : Colors.grey,
            ),
          ),
        ),
      ],
    );
  }
}
