<?php
// admin/orders.php
require_once '../backend/db_connect.php';
require_once 'includes/header.php';

$message = '';
$messageType = '';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];
    
    // Ensure valid enum status
    $validStatuses = ['pending', 'picked_up', 'washing', 'ready', 'delivered', 'cancelled'];
    if (in_array($newStatus, $validStatuses)) {
        $result = executeQuery($conn, "UPDATE orders SET status = ? WHERE id = ?", "si", $newStatus, $orderId);
        if ($result['status'] == 'success') {
            $message = "Order #$orderId status updated to " . strtoupper(str_replace('_', ' ', $newStatus)) . ".";
            $messageType = "success";
        } else {
            $message = "Failed to update order status.";
            $messageType = "error";
        }
    }
}

// Handle Status Filter
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build Query
$sql = "
    SELECT o.*, u.name as customer_name, u.phone as customer_phone, u.address as customer_address 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
";
if ($filter != 'all') {
    $sql .= " WHERE o.status = '" . $conn->real_escape_string($filter) . "' ";
}
$sql .= " ORDER BY o.created_at DESC";

$query = executeQuery($conn, $sql);
$orders = $query['status'] == 'success' ? $query['data'] : [];

$statusColors = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'picked_up' => 'bg-blue-100 text-blue-800',
    'washing' => 'bg-purple-100 text-purple-800',
    'ready' => 'bg-indigo-100 text-indigo-800',
    'delivered' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800'
];
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
    <div class="mb-4 md:mb-0">
        <h1 class="text-2xl font-bold text-gray-800">Order Management</h1>
        <p class="text-gray-500">Track and update customer laundry orders.</p>
    </div>
    
    <!-- Filter -->
    <div class="flex space-x-2 overflow-x-auto pb-2 w-full md:w-auto">
        <a href="?status=all" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap <?= $filter == 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' ?>">
            All Orders
        </a>
        <a href="?status=pending" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap <?= $filter == 'pending' ? 'bg-yellow-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' ?>">
            Pending
        </a>
        <a href="?status=washing" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap <?= $filter == 'washing' ? 'bg-purple-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' ?>">
            Processing
        </a>
        <a href="?status=ready" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap <?= $filter == 'ready' ? 'bg-indigo-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' ?>">
            Ready
        </a>
    </div>
</div>

<?php if ($message): ?>
<div class="mb-4 p-4 rounded-lg <?= $messageType == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- Orders List -->
<div class="space-y-4">
    <?php if (empty($orders)): ?>
        <div class="bg-white p-8 rounded-xl shadow-sm text-center border border-gray-100 text-gray-500">
            No orders found matching this filter.
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row justify-between">
                        <!-- Left Info -->
                        <div class="mb-4 md:mb-0">
                            <div class="flex items-center mb-2">
                                <span class="text-xl font-bold text-gray-900 mr-3">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                <?php 
                                    $color = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                    $label = ucwords(str_replace('_', ' ', $order['status']));
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?= $color ?>"><?= $label ?></span>
                            </div>
                            
                            <div class="text-sm text-gray-500 mb-4">
                                Placed: <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?>
                                <?php if (!empty($order['delivery_days'])): ?>
                                <br><span class="text-blue-600 font-medium"><i class="fas fa-clock mr-1"></i> Requested Return: In <?= htmlspecialchars($order['delivery_days']) ?> Days</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Customer Details Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mt-4 p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <div class="font-medium text-gray-700 mb-1"><i class="fas fa-user text-gray-400 w-5"></i> Customer</div>
                                    <div class="text-gray-900"><?= htmlspecialchars($order['customer_name']) ?></div>
                                    <div class="text-gray-500"><?= htmlspecialchars($order['customer_phone'] ?? 'No phone') ?></div>
                                </div>
                                <?php if (!empty($order['customer_address'])): ?>
                                <div>
                                    <div class="font-medium text-gray-700 mb-1"><i class="fas fa-map-marker-alt text-gray-400 w-5"></i> Address</div>
                                    <div class="text-gray-900"><?= htmlspecialchars($order['customer_address']) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Right Side (Cost & Actions) -->
                        <div class="flex flex-col items-start md:items-end justify-between border-t md:border-t-0 md:border-l border-gray-100 pt-4 md:pt-0 md:pl-6">
                            <div class="text-left md:text-right mb-4 w-full">
                                <div class="text-sm text-gray-500 mb-1">Total Amount</div>
                                <div class="text-2xl font-bold text-primary-text">$<?= number_format($order['total_amount'], 2) ?></div>
                            </div>
                            
                            <!-- Status Update Form -->
                            <div class="w-full">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Update Status</label>
                                <form method="POST" action="" class="flex w-full space-x-2">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" class="flex-1 border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2 bg-white font-medium shadow-sm" onchange="this.form.submit()">
                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="picked_up" <?= $order['status'] == 'picked_up' ? 'selected' : '' ?>>Picked Up</option>
                                        <option value="washing" <?= $order['status'] == 'washing' ? 'selected' : '' ?>>Washing</option>
                                        <option value="ready" <?= $order['status'] == 'ready' ? 'selected' : '' ?>>Ready for Delivery</option>
                                        <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Items Display -->
                <?php
                $itemsQuery = executeQuery($conn, "SELECT oi.quantity, oi.subtotal, s.name FROM order_items oi JOIN services s ON oi.service_id = s.id WHERE oi.order_id = ?", "i", $order['id']);
                if ($itemsQuery['status'] == 'success' && !empty($itemsQuery['data'])):
                ?>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 text-sm">
                    <div class="font-medium text-gray-700 mb-2">Order Items:</div>
                    <ul class="space-y-1">
                        <?php foreach ($itemsQuery['data'] as $item): ?>
                            <li class="flex justify-between text-gray-600">
                                <span><?= $item['quantity'] ?>x <?= htmlspecialchars($item['name']) ?></span>
                                <span>$<?= number_format($item['subtotal'], 2) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
