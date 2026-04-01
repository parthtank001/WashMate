import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../theme/app_theme.dart';
import '../viewmodels/cart_viewmodel.dart';
import '../viewmodels/auth_viewmodel.dart';
import '../services/data_service.dart';
import '../viewmodels/home_viewmodel.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _addressController = TextEditingController();
  final _instructionsController = TextEditingController();
  int _selectedDeliveryDays = 2; // Default to 2 days
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthViewModel>().currentUser;
    if (user?.address != null) {
      _addressController.text = user!.address!;
    }
  }

  @override
  void dispose() {
    _addressController.dispose();
    _instructionsController.dispose();
    super.dispose();
  }

  Future<void> _submitOrder() async {
    final cartState = context.read<CartViewModel>();
    final authState = context.read<AuthViewModel>();
    final user = authState.currentUser;

    if (user == null || cartState.items.isEmpty) return;

    if (_addressController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please provide a pickup/delivery address.')),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    final dataService = DataService();
    final result = await dataService.createOrder(
      user.id,
      cartState.getOrderItemsForApi(),
      _instructionsController.text.trim(),
      _selectedDeliveryDays,
    );

    setState(() => _isSubmitting = false);

    if (result['success']) {
      // Clear Cart
      cartState.clearCart();
      
      // Refresh home dashboard data
      if (mounted) {
        context.read<HomeViewModel>().loadDashboardData(user.id);
        
        // Show Success and go home
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Order placed successfully!')),
        );
        context.go('/home');
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'Failed to place order')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final cartState = context.watch<CartViewModel>();

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new),
          onPressed: () => context.pop(),
        ),
        title: const Text('Checkout'),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Order Summary',
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 16),
              
              // Cart Items List
              Container(
                decoration: BoxDecoration(
                  border: Border.all(color: Colors.grey.shade200),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: cartState.items.length,
                  separatorBuilder: (context, index) => const Divider(height: 1),
                  itemBuilder: (context, index) {
                    final item = cartState.items.values.elementAt(index);
                    return ListTile(
                      title: Text(item.service.name, style: const TextStyle(fontWeight: FontWeight.w500)),
                      subtitle: Text('Qty: ${item.quantity}'),
                      trailing: Text(
                        '\$${item.subtotal.toStringAsFixed(2)}',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                    );
                  },
                ),
              ),
              const SizedBox(height: 24),

              // Total
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Total Amount', style: Theme.of(context).textTheme.titleMedium),
                  Text(
                    '\$${cartState.totalAmount.toStringAsFixed(2)}',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: AppTheme.primaryBlue,
                        ),
                  ),
                ],
              ),
              const SizedBox(height: 32),

              // Delivery Details
              Text(
                'Delivery Details',
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 16),

              Text('Preferred Return Time', style: const TextStyle(fontWeight: FontWeight.bold)),
              //Text('The cloth will be delivered after the day collected',style: TextStyle(fontSize: 9.0),),
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                child: SegmentedButton<int>(
                  segments: const [
                    ButtonSegment(value: 1, label: Text('Rush (1d)'), icon: Icon(Icons.flash_on, size: 16)),
                    ButtonSegment(value: 2, label: Text('Standard (2d)')),
                    ButtonSegment(value: 5, label: Text('Economy (5d)')),
                  ],
                  selected: {_selectedDeliveryDays},
                  onSelectionChanged: (Set<int> newSelection) {
                    setState(() {
                      _selectedDeliveryDays = newSelection.first;
                    });
                  },
                ),
              ),
              const SizedBox(height: 24),

              TextFormField(
                controller: _addressController,
                maxLines: 2,
                decoration: const InputDecoration(
                  labelText: 'Pickup & Delivery Address',
                  hintText: 'Enter your full address',
                  prefixIcon: Icon(Icons.location_on_outlined),
                ),
              ),
              const SizedBox(height: 20),
              
              // Special Instructions
              TextFormField(
                controller: _instructionsController,
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText: 'Special Instructions (Optional)',
                  hintText: 'E.g., Please fold shirts, handle silk with care...',
                  alignLabelWithHint: true,
                  prefixIcon: Padding(
                    padding: EdgeInsets.only(bottom: 40.0),
                    child: Icon(Icons.notes),
                  ),
                ),
              ),
              const SizedBox(height: 40),

              // Submit Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _isSubmitting || cartState.items.isEmpty ? null : _submitOrder,
                  child: _isSubmitting
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                          ),
                        )
                      : const Text('Confirm Order (Cash on Delivery)'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
