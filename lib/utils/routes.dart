import 'package:go_router/go_router.dart';

import '../models/order_model.dart';
import '../views/splash_screen.dart';
import '../views/login_screen.dart';
import '../views/register_screen.dart';
import '../views/root_screen.dart';
import '../views/create_order_screen.dart';
import '../views/checkout_screen.dart';
import '../views/tracking_screen.dart';
import '../views/edit_profile_screen.dart';
import '../viewmodels/auth_viewmodel.dart';
import 'package:provider/provider.dart';
import 'package:flutter/material.dart';

class AppRouter {
  static GoRouter getRouter(BuildContext context) {
    return GoRouter(
      initialLocation: '/',
      redirect: (context, state) {
        final authViewModel = Provider.of<AuthViewModel>(context, listen: false);
        final isLoggingIn = state.matchedLocation == '/login' || state.matchedLocation == '/register';

        // Check if user is authenticated
        if (!authViewModel.isAuthenticated && !isLoggingIn && state.matchedLocation != '/') {
          return '/login';
        }

        // If user is authenticated and trying to access login/register, send to home
        if (authViewModel.isAuthenticated && isLoggingIn) {
          return '/home';
        }

        return null;
      },
      routes: [
        GoRoute(
          path: '/',
          builder: (context, state) => const SplashScreen(),
        ),
        GoRoute(
          path: '/login',
          builder: (context, state) => const LoginScreen(),
        ),
        GoRoute(
          path: '/register',
          builder: (context, state) => const RegisterScreen(),
        ),
        GoRoute(
          path: '/home',
          builder: (context, state) => const RootScreen(),
        ),
        GoRoute(
          path: '/create_order',
          builder: (context, state) => const CreateOrderScreen(),
        ),
        GoRoute(
          path: '/checkout',
          builder: (context, state) => const CheckoutScreen(),
        ),
        GoRoute(
          path: '/tracking',
          builder: (context, state) {
            final order = state.extra as Order;
            return TrackingScreen(order: order);
          },
        ),
        GoRoute(
          path: '/edit_profile',
          builder: (context, state) => const EditProfileScreen(),
        ),
      ],
    );
  }
}
