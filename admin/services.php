<?php
// admin/services.php
require_once '../backend/db_connect.php';
require_once 'includes/header.php';

$message = '';
$messageType = '';

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $deleteResult = executeQuery($conn, "DELETE FROM services WHERE id = ?", "i", $id);
    if ($deleteResult['status'] == 'success') {
        $message = "Service deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Failed to delete service. It might be linked to existing orders.";
        $messageType = "error";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    
    if ($_POST['action'] == 'add') {
        $result = executeQuery($conn, "INSERT INTO services (name, description, price, icon_url) VALUES (?, ?, ?, '')", "ssd", $name, $description, $price);
        if ($result['status'] == 'success') {
            $message = "Service added successfully.";
            $messageType = "success";
        }
    } elseif ($_POST['action'] == 'edit') {
        $id = $_POST['id'];
        $result = executeQuery($conn, "UPDATE services SET name = ?, description = ?, price = ? WHERE id = ?", "ssdi", $name, $description, $price, $id);
        if ($result['status'] == 'success') {
            $message = "Service updated successfully.";
            $messageType = "success";
        }
    }
}

// Fetch all services
$query = executeQuery($conn, "SELECT * FROM services ORDER BY created_at DESC");
$services = $query['status'] == 'success' ? $query['data'] : [];
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Manage Services</h1>
        <p class="text-gray-500">Add, edit, or remove laundry services.</p>
    </div>
    <button onclick="openModal('addModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
        <i class="fas fa-plus mr-2"></i> Add Service
    </button>
</div>

<?php if ($message): ?>
<div class="mb-4 p-4 rounded-lg <?= $messageType == 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- Services Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-sm border-b">
                    <th class="px-6 py-4 font-medium">ID</th>
                    <th class="px-6 py-4 font-medium w-1/4">Service Name</th>
                    <th class="px-6 py-4 font-medium w-1/3">Description</th>
                    <th class="px-6 py-4 font-medium">Price</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-gray-100">
                <?php if (empty($services)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No services available.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-gray-500">#<?= $service['id'] ?></td>
                        <td class="px-6 py-4 font-medium text-gray-900">
                            <i class="fas fa-tshirt text-blue-400 mr-2"></i> <?= htmlspecialchars($service['name']) ?>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($service['description']) ?></td>
                        <td class="px-6 py-4 font-bold text-gray-800">$<?= number_format($service['price'], 2) ?></td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button onclick='openEditModal(<?= json_encode($service) ?>)' class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 p-2 rounded transition-colors">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete=<?= $service['id'] ?>" onclick="return confirm('Are you sure you want to delete this service?');" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded inline-block transition-colors">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Background -->
<div id="modalOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
    
    <!-- Add Modal -->
    <div id="addModal" class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 hidden transform transition-all">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Add New Service</h3>
            <button onclick="closeModals()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="" class="p-6">
            <input type="hidden" name="action" value="add">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                    <input type="number" step="0.01" name="price" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Save Service</button>
            </div>
        </form>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 hidden transform transition-all">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Edit Service</h3>
            <button onclick="closeModals()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="" class="p-6">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Name</label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                    <input type="number" step="0.01" name="price" id="edit_price" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="edit_desc" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeModals()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Update Service</button>
            </div>
        </form>
    </div>

</div>

<script>
function openModal(modalId) {
    document.getElementById('modalOverlay').classList.remove('hidden');
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModals() {
    document.getElementById('modalOverlay').classList.add('hidden');
    document.getElementById('addModal').classList.add('hidden');
    document.getElementById('editModal').classList.add('hidden');
}

function openEditModal(service) {
    document.getElementById('edit_id').value = service.id;
    document.getElementById('edit_name').value = service.name;
    document.getElementById('edit_price').value = service.price;
    document.getElementById('edit_desc').value = service.description;
    openModal('editModal');
}
</script>

<?php require_once 'includes/footer.php'; ?>
