import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:washmate/viewmodels/home_viewmodel.dart';
import 'package:washmate/viewmodels/cart_viewmodel.dart';
import 'package:washmate/viewmodels/order_viewmodel.dart';
import 'theme/app_theme.dart';
import 'utils/routes.dart';
import 'viewmodels/auth_viewmodel.dart';
import 'package:go_router/go_router.dart';
import 'services/notification_service.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await NotificationService().initialize();
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthViewModel()),
        ChangeNotifierProvider(create: (_) => HomeViewModel()),
        ChangeNotifierProvider(create: (_) => CartViewModel()),
        ChangeNotifierProvider(create: (_) => OrderViewModel()),
      ],
      child: const WashMateApp(),
    ),
  );
}

class WashMateApp extends StatefulWidget {
  const WashMateApp({super.key});

  @override
  State<WashMateApp> createState() => _WashMateAppState();
}

class _WashMateAppState extends State<WashMateApp> {
  late GoRouter _router;
  bool _initialized = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_initialized) {
      _router = AppRouter.getRouter(context);
      _initialized = true;
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'WashMate',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      routerConfig: _router,
    );
  }
}
