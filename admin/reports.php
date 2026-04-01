<?php
// admin/reports.php
require_once '../backend/db_connect.php';
require_once 'includes/header.php';

// --- 1. Top Metrics (Overall Totals) ---
$totalsQuery = executeQuery($conn, "SELECT COUNT(*) as total_orders, IFNULL(SUM(total_amount), 0) as total_revenue FROM orders");
$totals = $totalsQuery['status'] == 'success' ? $totalsQuery['data'][0] : ['total_orders' => 0, 'total_revenue' => 0];

$customersQuery = executeQuery($conn, "SELECT COUNT(*) as total_customers FROM users WHERE role = 'customer'");
$totalCustomers = $customersQuery['status'] == 'success' ? $customersQuery['data'][0]['total_customers'] : 0;

// --- 2. Chart 1: Revenue Over Time (Last 30 Days) ---
$revenueSql = "
    SELECT DATE(created_at) as date, SUM(total_amount) as daily_revenue 
    FROM orders 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
";
$revenueQuery = executeQuery($conn, $revenueSql);
$revenueData = $revenueQuery['status'] == 'success' ? $revenueQuery['data'] : [];

$dates = [];
$revenues = [];
foreach ($revenueData as $row) {
    $dates[] = date('M d', strtotime($row['date']));
    $revenues[] = (float)$row['daily_revenue'];
}

// --- 3. Chart 2: Order Status Distribution ---
$statusSql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$statusQuery = executeQuery($conn, $statusSql);
$statusData = $statusQuery['status'] == 'success' ? $statusQuery['data'] : [];

$statusLabels = [];
$statusCounts = [];
foreach ($statusData as $row) {
    $statusLabels[] = ucwords(str_replace('_', ' ', $row['status']));
    $statusCounts[] = (int)$row['count'];
}

// --- 4. Chart 3: Top Services ---
$servicesSql = "
    SELECT s.name, SUM(oi.quantity) as total_quantity
    FROM order_items oi
    JOIN services s ON oi.service_id = s.id
    GROUP BY s.id
    ORDER BY total_quantity DESC
    LIMIT 5
";
$servicesQuery = executeQuery($conn, $servicesSql);
$servicesData = $servicesQuery['status'] == 'success' ? $servicesQuery['data'] : [];

$serviceNames = [];
$serviceQuantities = [];
foreach ($servicesData as $row) {
    $serviceNames[] = $row['name'];
    $serviceQuantities[] = (int)$row['total_quantity'];
}
?>

<!-- Import Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Analytics & Reports</h1>
    <p class="text-gray-500">Business performance and metrics overview.</p>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
        <div class="h-12 w-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl mr-4">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div>
            <div class="text-sm text-gray-500 font-medium">Total Revenue</div>
            <div class="text-2xl font-bold text-gray-900">$<?= number_format($totals['total_revenue'], 2) ?></div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
        <div class="h-12 w-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xl mr-4">
            <i class="fas fa-shopping-bag"></i>
        </div>
        <div>
            <div class="text-sm text-gray-500 font-medium">Total Orders</div>
            <div class="text-2xl font-bold text-gray-900"><?= number_format($totals['total_orders']) ?></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center">
        <div class="h-12 w-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl mr-4">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div class="text-sm text-gray-500 font-medium">Total Customers</div>
            <div class="text-2xl font-bold text-gray-900"><?= number_format($totalCustomers) ?></div>
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    
    <!-- Revenue Line Chart -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Revenue Over Time (Last 30 Days)</h3>
        <div class="relative h-72 w-full">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Order Status Pie Chart -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Order Status Distribution</h3>
        <div class="relative h-64 w-full flex justify-center">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Top Services Bar Chart -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Top Services by Volume</h3>
        <div class="relative h-64 w-full">
            <canvas id="servicesChart"></canvas>
        </div>
    </div>

</div>

<!-- Chart.js Initialization -->
<script>
    // 1. Revenue Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Daily Revenue ($)',
                data: <?= json_encode($revenues) ?>,
                borderColor: '#00B4D8',
                backgroundColor: 'rgba(0, 180, 216, 0.1)',
                borderWidth: 3,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#2B5B84',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($statusLabels) ?>,
            datasets: [{
                data: <?= json_encode($statusCounts) ?>,
                backgroundColor: [
                    '#FCD34D', // Pending (yellow)
                    '#93C5FD', // Picked Up (blue)
                    '#C4B5FD', // Washing (purple)
                    '#A5B4FC', // Ready (indigo)
                    '#A7F3D0', // Delivered (green)
                    '#FECACA'  // Cancelled (red)
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            },
            cutout: '70%'
        }
    });

    // 3. Services Chart
    const servCtx = document.getElementById('servicesChart').getContext('2d');
    new Chart(servCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($serviceNames) ?>,
            datasets: [{
                label: 'Quantity Ordered',
                data: <?= json_encode($serviceQuantities) ?>,
                backgroundColor: '#2B5B84',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
