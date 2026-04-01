import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../theme/app_theme.dart';
import '../viewmodels/home_viewmodel.dart';
import '../viewmodels/cart_viewmodel.dart';

class CreateOrderScreen extends StatelessWidget {
  const CreateOrderScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final homeState = context.watch<HomeViewModel>();
    final cartState = context.watch<CartViewModel>();

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new),
          onPressed: () => context.pop(),
        ),
        title: const Text('Schedule Pickup'),
      ),
      body: homeState.isLoading
          ? const Center(child: CircularProgressIndicator())
          : homeState.services.isEmpty
              ? const Center(child: Text('No services available currently.'))
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: homeState.services.length,
                  itemBuilder: (context, index) {
                    final service = homeState.services[index];
                    final cartItem = cartState.items[service.id];
                    final quantity = cartItem?.quantity ?? 0;

                    return Card(
                      margin: const EdgeInsets.only(bottom: 16),
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: AppTheme.primaryBlue.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: const Icon(Icons.local_laundry_service, color: AppTheme.primaryBlue),
                            ),
                            const SizedBox(width: 16),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    service.name,
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    '\$${service.price.toStringAsFixed(2)}',
                                    style: const TextStyle(
                                      color: AppTheme.primaryBlue,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            if (quantity == 0)
                              ElevatedButton(
                                onPressed: () => cartState.addItem(service),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppTheme.primaryBlue.withOpacity(0.1),
                                  foregroundColor: AppTheme.primaryBlue,
                                  elevation: 0,
                                ),
                                child: const Text('Add'),
                              )
                            else
                              Row(
                                children: [
                                  IconButton(
                                    icon: const Icon(Icons.remove_circle_outline),
                                    color: AppTheme.primaryBlue,
                                    onPressed: () => cartState.removeItem(service.id),
                                  ),
                                  Text(
                                    '$quantity',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.add_circle_outline),
                                    color: AppTheme.primaryBlue,
                                    onPressed: () => cartState.addItem(service),
                                  ),
                                ],
                              )
                          ],
                        ),
                      ),
                    );
                  },
                ),
      bottomNavigationBar: cartState.itemCount > 0
          ? Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.05),
                    offset: const Offset(0, -5),
                    blurRadius: 10,
                  )
                ],
              ),
              child: SafeArea(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Total Estimate', style: TextStyle(color: AppTheme.textLight)),
                        Text(
                          '\$${cartState.totalAmount.toStringAsFixed(2)}',
                          style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                fontWeight: FontWeight.bold,
                                color: AppTheme.primaryBlue,
                              ),
                        ),
                      ],
                    ),
                    ElevatedButton(
                      onPressed: () {
                        context.push('/checkout');
                      },
                      child: const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 16.0),
                        child: Text('Review Order'),
                      ),
                    ),
                  ],
                ),
              ),
            )
          : null,
    );
  }
}
