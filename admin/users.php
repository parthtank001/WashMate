<?php
// admin/users.php
require_once '../backend/db_connect.php';
require_once 'includes/header.php';

// Handle Delete
$message = '';
$messageType = '';
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if it's the main admin
    $checkQuery = executeQuery($conn, "SELECT email FROM users WHERE id = ?", "i", $id);
    if ($checkQuery['status'] == 'success' && $checkQuery['data'][0]['email'] == 'admin@washmate.com') {
         $message = "Cannot delete the primary administrator account.";
         $messageType = "error";
    } else {
        $deleteResult = executeQuery($conn, "DELETE FROM users WHERE id = ?", "i", $id);
        if ($deleteResult['status'] == 'success') {
            $message = "User deleted successfully.";
            $messageType = "success";
        } else {
            $message = "Failed to delete user.";
            $messageType = "error";
        }
    }
}

// Fetch all users
$query = executeQuery($conn, "
    SELECT u.*, COUNT(o.id) as total_orders 
    FROM users u 
    LEFT JOIN orders o ON u.id = o.user_id 
    GROUP BY u.id 
    ORDER BY u.created_at DESC
");
$users = $query['status'] == 'success' ? $query['data'] : [];
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Customers & Users</h1>
    <p class="text-gray-500">View and manage registered accounts.</p>
</div>

<?php if ($message): ?>
<div class="mb-4 p-4 rounded-lg <?= $messageType == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- Users Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-sm border-b">
                    <th class="px-6 py-4 font-medium">Customer</th>
                    <th class="px-6 py-4 font-medium">Contact</th>
                    <th class="px-6 py-4 font-medium">Role</th>
                    <th class="px-6 py-4 font-medium">Total Orders</th>
                    <th class="px-6 py-4 font-medium">Joined</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-100">
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No users registered yet.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold mr-3">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></div>
                                    <div class="text-xs text-gray-500">ID: #<?= $user['id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-900"><?= htmlspecialchars($user['email']) ?></div>
                            <div class="text-gray-500 text-xs"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($user['role'] == 'admin'): ?>
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-medium">Admin</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs font-medium">Customer</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-600">
                            <?= $user['total_orders'] ?>
                        </td>
                        <td class="px-6 py-4 text-gray-500">
                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                            <a href="?delete=<?= $user['id'] ?>" onclick="return confirm('WARNING: Deleting this user will also delete all their orders. Are you sure?');" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded inline-block transition-colors">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
