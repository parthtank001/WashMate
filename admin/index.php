<?php
// admin/index.php
require_once '../backend/db_connect.php';
require_once 'includes/header.php';

// Fetch quick stats
$totalUsers = 0;
$activeOrders = 0;
$totalRevenue = 0;

$userQuery = executeQuery($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
if ($userQuery['status'] == 'success') $totalUsers = $userQuery['data'][0]['count'];

$orderQuery = executeQuery($conn, "SELECT COUNT(*) as count FROM orders WHERE status != 'delivered' AND status != 'cancelled'");
if ($orderQuery['status'] == 'success') $activeOrders = $orderQuery['data'][0]['count'];

$revenueQuery = executeQuery($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status = 'delivered'");
if ($revenueQuery['status'] == 'success') $totalRevenue = $revenueQuery['data'][0]['total'] ?? 0;

// Fetch recent orders
$recentOrdersQuery = executeQuery($conn, "
    SELECT o.id, u.name as customer_name, o.status, o.total_amount, o.created_at 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC LIMIT 5
");
$recentOrders = $recentOrdersQuery['status'] == 'success' ? $recentOrdersQuery['data'] : [];
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1>
    <p class="text-gray-500">Here's what's happening today.</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
        <div class="h-14 w-14 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-2xl mr-4">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div class="text-sm text-gray-500 mb-1">Total Customers</div>
            <div class="text-2xl font-bold text-gray-800"><?= number_format($totalUsers) ?></div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
        <div class="h-14 w-14 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-2xl mr-4">
            <i class="fas fa-box-open"></i>
        </div>
        <div>
            <div class="text-sm text-gray-500 mb-1">Active Orders</div>
            <div class="text-2xl font-bold text-gray-800"><?= number_format($activeOrders) ?></div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
        <div class="h-14 w-14 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-2xl mr-4">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div>
            <div class="text-sm text-gray-500 mb-1">Total Revenue</div>
            <div class="text-2xl font-bold text-gray-800">$<?= number_format($totalRevenue, 2) ?></div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
        <h2 class="font-bold text-gray-800">Recent Orders</h2>
        <a href="orders.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-gray-500 text-sm border-b">
                    <th class="px-6 py-3 font-medium">Order ID</th>
                    <th class="px-6 py-3 font-medium">Customer</th>
                    <th class="px-6 py-3 font-medium">Date</th>
                    <th class="px-6 py-3 font-medium">Amount</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (empty($recentOrders)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No orders found.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr class="border-b hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td class="px-6 py-4 text-gray-500"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td class="px-6 py-4 font-medium">$<?= number_format($order['total_amount'], 2) ?></td>
                        <td class="px-6 py-4">
                            <?php 
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'picked_up' => 'bg-blue-100 text-blue-800',
                                'washing' => 'bg-purple-100 text-purple-800',
                                'ready' => 'bg-indigo-100 text-indigo-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                            $color = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800';
                            $label = ucwords(str_replace('_', ' ', $order['status']));
                            ?>
                            <span class="px-3 py-1 rounded-full text-xs font-medium <?= $color ?>"><?= $label ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
