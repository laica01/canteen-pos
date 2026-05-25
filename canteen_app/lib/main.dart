// ================================================================
//  CANTEEN POS — Flutter App
//  File: lib/main.dart
// ================================================================

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

// ⬇️ YOUR RAILWAY URL — already set!
const String API = 'https://canteen-pos-production.up.railway.app/api';

void main() {
  runApp(const CanteenApp());
}

class CanteenApp extends StatelessWidget {
  const CanteenApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Canteen POS',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFFFF6B35),
        ),
        useMaterial3: true,
      ),
      home: const SplashScreen(),
    );
  }
}

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});
  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _checkIfLoggedIn();
  }

  Future<void> _checkIfLoggedIn() async {
    await Future.delayed(const Duration(seconds: 1));
    final prefs = await SharedPreferences.getInstance();
    final userId = prefs.getInt('user_id');
    if (!mounted) return;
    if (userId != null) {
      Navigator.pushReplacement(
          context, MaterialPageRoute(builder: (_) => const DashboardScreen()));
    } else {
      Navigator.pushReplacement(
          context, MaterialPageRoute(builder: (_) => const LoginScreen()));
    }
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Color(0xFFFF6B35),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text('🍱', style: TextStyle(fontSize: 80)),
            SizedBox(height: 16),
            Text('Mura-Mura Canteen',
                style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: Colors.white)),
            SizedBox(height: 8),
            Text("Your school's favorite food spot 🧡",
                style: TextStyle(color: Colors.white70, fontSize: 14)),
            SizedBox(height: 40),
            CircularProgressIndicator(color: Colors.white),
          ],
        ),
      ),
    );
  }
}

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _usernameCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  bool _isLoading = false;
  String _errorMessage = '';
  bool _showPassword = false;

  Future<void> _login() async {
    final username = _usernameCtrl.text.trim();
    final password = _passwordCtrl.text;
    if (username.isEmpty || password.isEmpty) {
      setState(() => _errorMessage = 'Please enter username and password.');
      return;
    }
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });
    try {
      final response = await http
          .post(
            Uri.parse('$API/login.php'),
            headers: {'Content-Type': 'application/json'},
            body: jsonEncode({'username': username, 'password': password}),
          )
          .timeout(const Duration(seconds: 10));
      final result = jsonDecode(response.body);
      if (result['success'] == true) {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setInt('user_id', result['user']['id']);
        await prefs.setString('user_fullname', result['user']['fullname']);
        await prefs.setString('user_username', result['user']['username']);
        await prefs.setString('user_role', result['user']['role']);
        await prefs.setDouble(
            'user_balance', (result['user']['balance'] as num).toDouble());
        if (!mounted) return;
        Navigator.pushReplacement(context,
            MaterialPageRoute(builder: (_) => const DashboardScreen()));
      } else {
        setState(() => _errorMessage = result['message']);
      }
    } catch (e) {
      setState(() => _errorMessage =
          'Cannot connect to server. Check your internet connection.');
    }
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFFF8F0),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 20),
            child: Column(
              children: [
                const Text('🍱', style: TextStyle(fontSize: 72)),
                const SizedBox(height: 8),
                const Text('Mura-Mura Canteen',
                    style: TextStyle(
                        fontSize: 26,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFFFF6B35))),
                const Text("Sign in to order food",
                    style: TextStyle(color: Colors.grey)),
                const SizedBox(height: 36),
                if (_errorMessage.isNotEmpty)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(14),
                    margin: const EdgeInsets.only(bottom: 18),
                    decoration: BoxDecoration(
                        color: Colors.red.shade50,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.red.shade200)),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(Icons.error_outline,
                            color: Colors.red, size: 20),
                        const SizedBox(width: 8),
                        Expanded(
                            child: Text(_errorMessage,
                                style: TextStyle(color: Colors.red.shade700))),
                      ],
                    ),
                  ),
                Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                          color: Colors.orange.withOpacity(0.1),
                          blurRadius: 20,
                          offset: const Offset(0, 6))
                    ],
                  ),
                  child: Column(
                    children: [
                      TextField(
                        controller: _usernameCtrl,
                        decoration: InputDecoration(
                          labelText: 'Username',
                          prefixIcon: const Icon(Icons.person_outline,
                              color: Color(0xFFFF6B35)),
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12)),
                          focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(
                                  color: Color(0xFFFF6B35), width: 2)),
                        ),
                        textInputAction: TextInputAction.next,
                      ),
                      const SizedBox(height: 16),
                      TextField(
                        controller: _passwordCtrl,
                        obscureText: !_showPassword,
                        decoration: InputDecoration(
                          labelText: 'Password',
                          prefixIcon: const Icon(Icons.lock_outline,
                              color: Color(0xFFFF6B35)),
                          suffixIcon: IconButton(
                            icon: Icon(
                                _showPassword
                                    ? Icons.visibility_off
                                    : Icons.visibility,
                                color: Colors.grey),
                            onPressed: () =>
                                setState(() => _showPassword = !_showPassword),
                          ),
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12)),
                          focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: const BorderSide(
                                  color: Color(0xFFFF6B35), width: 2)),
                        ),
                        textInputAction: TextInputAction.done,
                        onSubmitted: (_) => _login(),
                      ),
                      const SizedBox(height: 24),
                      SizedBox(
                        width: double.infinity,
                        height: 52,
                        child: ElevatedButton(
                          onPressed: _isLoading ? null : _login,
                          style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFFFF6B35),
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12))),
                          child: _isLoading
                              ? const SizedBox(
                                  width: 22,
                                  height: 22,
                                  child: CircularProgressIndicator(
                                      color: Colors.white, strokeWidth: 2.5))
                              : const Text('Sign In',
                                  style: TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold)),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});
  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  int _userId = 0;
  String _fullname = '';
  double _balance = 0;
  List<dynamic> _products = [];
  List<dynamic> _orders = [];
  bool _loadingProducts = true;
  bool _loadingOrders = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadEverything();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadEverything() async {
    await _loadUserFromStorage();
    await Future.wait([_fetchProducts(), _fetchOrders()]);
  }

  Future<void> _loadUserFromStorage() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _userId = prefs.getInt('user_id') ?? 0;
      _fullname = prefs.getString('user_fullname') ?? '';
      _balance = prefs.getDouble('user_balance') ?? 0;
    });
  }

  Future<void> _saveBalance(double newBalance) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setDouble('user_balance', newBalance);
    setState(() => _balance = newBalance);
  }

  Future<void> _fetchProducts() async {
    setState(() => _loadingProducts = true);
    try {
      final res = await http
          .get(Uri.parse('$API/products.php'))
          .timeout(const Duration(seconds: 10));
      final data = jsonDecode(res.body);
      if (data['success'] == true) setState(() => _products = data['products']);
    } catch (_) {}
    setState(() => _loadingProducts = false);
  }

  Future<void> _fetchOrders() async {
    setState(() => _loadingOrders = true);
    try {
      final res = await http
          .get(Uri.parse('$API/history.php?student_id=$_userId'))
          .timeout(const Duration(seconds: 10));
      final data = jsonDecode(res.body);
      if (data['success'] == true) setState(() => _orders = data['orders']);
    } catch (_) {}
    setState(() => _loadingOrders = false);
  }

  void _openOrderSheet(Map product) {
    int qty = 1;
    final int maxStock = product['stock'] as int;
    final double price = (product['price'] as num).toDouble();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setSheetState) {
          final double total = price * qty;
          final bool canAfford = _balance >= total;
          return Container(
            padding: EdgeInsets.only(
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 28,
                top: 24,
                left: 24,
                right: 24),
            decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                    width: 40,
                    height: 4,
                    margin: const EdgeInsets.only(bottom: 20),
                    decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(2))),
                Text(product['product_name'],
                    style: const TextStyle(
                        fontSize: 22, fontWeight: FontWeight.bold)),
                const SizedBox(height: 4),
                Text('₱${price.toStringAsFixed(2)} each',
                    style: const TextStyle(color: Colors.grey)),
                const SizedBox(height: 24),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text('Quantity:',
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.w600)),
                    const SizedBox(width: 20),
                    IconButton(
                        onPressed:
                            qty > 1 ? () => setSheetState(() => qty--) : null,
                        icon: const Icon(Icons.remove_circle_outline),
                        color: const Color(0xFFFF6B35),
                        iconSize: 32),
                    SizedBox(
                        width: 40,
                        child: Text('$qty',
                            textAlign: TextAlign.center,
                            style: const TextStyle(
                                fontSize: 24, fontWeight: FontWeight.bold))),
                    IconButton(
                        onPressed: qty < maxStock
                            ? () => setSheetState(() => qty++)
                            : null,
                        icon: const Icon(Icons.add_circle_outline),
                        color: const Color(0xFFFF6B35),
                        iconSize: 32),
                  ],
                ),
                const SizedBox(height: 16),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
                  decoration: BoxDecoration(
                      color: const Color(0xFFFFF3EC),
                      borderRadius: BorderRadius.circular(12)),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Total',
                          style: TextStyle(
                              fontWeight: FontWeight.w600, fontSize: 16)),
                      Text('₱${total.toStringAsFixed(2)}',
                          style: const TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFFFF6B35))),
                    ],
                  ),
                ),
                if (!canAfford)
                  Container(
                    padding: const EdgeInsets.all(10),
                    margin: const EdgeInsets.only(top: 8),
                    decoration: BoxDecoration(
                        color: Colors.red.shade50,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.red.shade200)),
                    child: Row(children: [
                      const Icon(Icons.warning_amber,
                          color: Colors.red, size: 18),
                      const SizedBox(width: 8),
                      Text(
                          'Not enough balance! You have ₱${_balance.toStringAsFixed(2)}',
                          style: const TextStyle(color: Colors.red))
                    ]),
                  ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  height: 52,
                  child: ElevatedButton(
                    onPressed: canAfford
                        ? () async {
                            Navigator.pop(ctx);
                            await _placeOrder(product['id'] as int, qty, total);
                          }
                        : null,
                    style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFFF6B35),
                        foregroundColor: Colors.white,
                        disabledBackgroundColor: Colors.grey.shade300,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12))),
                    child: Text(
                        canAfford
                            ? 'Pay ₱${total.toStringAsFixed(2)}'
                            : 'Insufficient Balance',
                        style: const TextStyle(
                            fontSize: 16, fontWeight: FontWeight.bold)),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Future<void> _placeOrder(int productId, int qty, double total) async {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Row(children: [
          SizedBox(
              width: 18,
              height: 18,
              child: CircularProgressIndicator(
                  strokeWidth: 2, color: Colors.white)),
          SizedBox(width: 12),
          Text('Placing order...')
        ]),
        duration: Duration(seconds: 30),
        backgroundColor: Colors.orange));
    try {
      final res = await http
          .post(Uri.parse('$API/order.php'),
              headers: {'Content-Type': 'application/json'},
              body: jsonEncode(
                  {'student_id': _userId, 'product_id': productId, 'qty': qty}))
          .timeout(const Duration(seconds: 10));
      final data = jsonDecode(res.body);
      if (!mounted) return;
      ScaffoldMessenger.of(context).hideCurrentSnackBar();
      if (data['success'] == true) {
        await _saveBalance((data['new_balance'] as num).toDouble());
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text('✅ ${data['message']}'),
            backgroundColor: Colors.green));
        await Future.wait([_fetchProducts(), _fetchOrders()]);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text('❌ ${data['message']}'),
            backgroundColor: Colors.red));
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).hideCurrentSnackBar();
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Connection error. Try again.'),
          backgroundColor: Colors.red));
    }
  }

  Future<void> _cancelOrder(int orderId) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel Order?'),
        content: const Text('You will get a full refund to your wallet.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('No, Keep It')),
          TextButton(
              onPressed: () => Navigator.pop(ctx, true),
              style: TextButton.styleFrom(foregroundColor: Colors.red),
              child: const Text('Yes, Cancel')),
        ],
      ),
    );
    if (confirmed != true) return;
    try {
      final res = await http
          .post(Uri.parse('$API/cancel.php'),
              headers: {'Content-Type': 'application/json'},
              body: jsonEncode({'order_id': orderId, 'student_id': _userId}))
          .timeout(const Duration(seconds: 10));
      final data = jsonDecode(res.body);
      if (!mounted) return;
      if (data['success'] == true) {
        await _saveBalance((data['new_balance'] as num).toDouble());
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text('✅ ${data['message']}'),
            backgroundColor: Colors.green));
        await _fetchOrders();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text('❌ ${data['message']}'),
            backgroundColor: Colors.red));
      }
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Connection error.'), backgroundColor: Colors.red));
    }
  }

  Future<void> _logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
    if (!mounted) return;
    Navigator.pushReplacement(
        context, MaterialPageRoute(builder: (_) => const LoginScreen()));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFFFF8F0),
      appBar: AppBar(
        backgroundColor: const Color(0xFFFF6B35),
        foregroundColor: Colors.white,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Hi, ${_fullname.split(' ').first}! 👋',
                style:
                    const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
            Text('Balance: ₱${_balance.toStringAsFixed(2)}',
                style: const TextStyle(fontSize: 12, color: Colors.white70)),
          ],
        ),
        actions: [
          IconButton(
              icon: const Icon(Icons.refresh), onPressed: _loadEverything),
          IconButton(icon: const Icon(Icons.logout), onPressed: _logout),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white60,
          tabs: const [
            Tab(icon: Icon(Icons.restaurant_menu), text: 'Menu'),
            Tab(icon: Icon(Icons.receipt_long), text: 'My Orders'),
          ],
        ),
      ),
      body: TabBarView(
          controller: _tabController,
          children: [_buildMenuTab(), _buildOrdersTab()]),
    );
  }

  Widget _buildMenuTab() {
    if (_loadingProducts)
      return const Center(
          child: CircularProgressIndicator(color: Color(0xFFFF6B35)));
    if (_products.isEmpty)
      return Center(
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        const Text('🍽️', style: TextStyle(fontSize: 60)),
        const SizedBox(height: 12),
        const Text('No food items yet.', style: TextStyle(color: Colors.grey)),
        const SizedBox(height: 16),
        ElevatedButton(
            onPressed: _fetchProducts,
            style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFFF6B35),
                foregroundColor: Colors.white),
            child: const Text('Refresh'))
      ]));
    return RefreshIndicator(
      onRefresh: _fetchProducts,
      color: const Color(0xFFFF6B35),
      child: GridView.builder(
        padding: const EdgeInsets.all(14),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            childAspectRatio: 0.72,
            crossAxisSpacing: 12,
            mainAxisSpacing: 12),
        itemCount: _products.length,
        itemBuilder: (ctx, i) {
          final p = _products[i];
          final bool soldOut = (p['stock'] as int) <= 0;
          final double price = (p['price'] as num).toDouble();
          return GestureDetector(
            onTap: soldOut ? null : () => _openOrderSheet(p),
            child: Container(
              decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                        color: Colors.orange.withOpacity(0.08),
                        blurRadius: 10,
                        offset: const Offset(0, 4))
                  ]),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Stack(
                    children: [
                      ClipRRect(
                        borderRadius: const BorderRadius.vertical(
                            top: Radius.circular(16)),
                        child: p['image_url'] != null
                            ? Image.network(p['image_url'],
                                height: 120,
                                width: double.infinity,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) =>
                                    _imagePlaceholder())
                            : _imagePlaceholder(),
                      ),
                      if (soldOut)
                        Positioned.fill(
                            child: ClipRRect(
                                borderRadius: const BorderRadius.vertical(
                                    top: Radius.circular(16)),
                                child: Container(
                                    color: Colors.black.withOpacity(0.5),
                                    child: const Center(
                                        child: Text('SOLD OUT',
                                            style: TextStyle(
                                                color: Colors.white,
                                                fontWeight: FontWeight.bold,
                                                letterSpacing: 1.5)))))),
                    ],
                  ),
                  Padding(
                    padding: const EdgeInsets.all(10),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(p['product_name'],
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                                fontWeight: FontWeight.bold, fontSize: 13)),
                        Text('₱${price.toStringAsFixed(2)}',
                            style: const TextStyle(
                                fontSize: 17,
                                fontWeight: FontWeight.w900,
                                color: Color(0xFFFF6B35))),
                        if (!soldOut)
                          Text('${p['stock']} left',
                              style: TextStyle(
                                  fontSize: 11, color: Colors.grey.shade500)),
                        const SizedBox(height: 6),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            onPressed:
                                soldOut ? null : () => _openOrderSheet(p),
                            style: ElevatedButton.styleFrom(
                                backgroundColor: soldOut
                                    ? Colors.grey.shade200
                                    : const Color(0xFFFF6B35),
                                foregroundColor:
                                    soldOut ? Colors.grey : Colors.white,
                                padding:
                                    const EdgeInsets.symmetric(vertical: 6),
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(8))),
                            child: Text(soldOut ? 'Unavailable' : 'Order',
                                style: const TextStyle(fontSize: 12)),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _imagePlaceholder() => Container(
      height: 120,
      width: double.infinity,
      color: const Color(0xFFFFE0CC),
      child: const Center(child: Text('🍽️', style: TextStyle(fontSize: 40))));

  Widget _buildOrdersTab() {
    if (_loadingOrders)
      return const Center(
          child: CircularProgressIndicator(color: Color(0xFFFF6B35)));
    if (_orders.isEmpty)
      return Center(
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
        const Text('📭', style: TextStyle(fontSize: 60)),
        const SizedBox(height: 12),
        const Text('No orders yet!\nGo order something delicious 😋',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.grey, fontSize: 15)),
        const SizedBox(height: 16),
        ElevatedButton(
            onPressed: () => _tabController.animateTo(0),
            style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFFF6B35),
                foregroundColor: Colors.white),
            child: const Text('Browse Menu'))
      ]));
    return RefreshIndicator(
      onRefresh: _fetchOrders,
      color: const Color(0xFFFF6B35),
      child: ListView.builder(
        padding: const EdgeInsets.all(14),
        itemCount: _orders.length,
        itemBuilder: (ctx, i) {
          final o = _orders[i];
          final bool canCancel = o['can_cancel'] == true;
          final double total = (o['total'] as num).toDouble();
          final Color statusColor = o['status'] == 'Preparing'
              ? Colors.orange
              : o['status'] == 'Completed'
                  ? Colors.green
                  : Colors.red;
          return Container(
            margin: const EdgeInsets.only(bottom: 12),
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                      color: Colors.orange.withOpacity(0.07),
                      blurRadius: 10,
                      offset: const Offset(0, 3))
                ]),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                        child: Text(o['product_name'],
                            style: const TextStyle(
                                fontSize: 16, fontWeight: FontWeight.bold))),
                    Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 4),
                        decoration: BoxDecoration(
                            color: statusColor.withOpacity(0.12),
                            borderRadius: BorderRadius.circular(20)),
                        child: Text(o['status'],
                            style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: statusColor))),
                  ],
                ),
                const SizedBox(height: 6),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                        '×${o['quantity']}  •  ${o['created_at']?.substring(0, 16) ?? ''}',
                        style: TextStyle(
                            color: Colors.grey.shade500, fontSize: 12)),
                    Text('₱${total.toStringAsFixed(2)}',
                        style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w900,
                            color: Color(0xFFFF6B35))),
                  ],
                ),
                if (canCancel) ...[
                  const SizedBox(height: 12),
                  SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                          onPressed: () => _cancelOrder(o['id'] as int),
                          icon: const Icon(Icons.cancel_outlined,
                              size: 18, color: Colors.red),
                          label: const Text('Cancel & Refund',
                              style: TextStyle(color: Colors.red)),
                          style: OutlinedButton.styleFrom(
                              side: const BorderSide(color: Colors.red),
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8))))),
                ],
              ],
            ),
          );
        },
      ),
    );
  }
}
