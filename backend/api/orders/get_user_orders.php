<?php
// backend/api/orders/get_user_orders.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../db_connect.php';

// Expecting user_id as a GET parameter
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch orders for the user
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $result = executeQuery($conn, $sql, "i", $user_id);

    if ($result['status'] == 'success') {
        $orders = $result['data'];
        
        // For each order, fetch its items
        foreach ($orders as &$order) {
            $order_id = $order['id'];
            $itemsSql = "SELECT oi.quantity, oi.subtotal, s.name as service_name, s.icon_url 
                         FROM order_items oi 
                         JOIN services s ON oi.service_id = s.id 
                         WHERE oi.order_id = ?";
            $itemsResult = executeQuery($conn, $itemsSql, "i", $order_id);
            
            if ($itemsResult['status'] == 'success') {
                $order['items'] = $itemsResult['data'];
            } else {
                $order['items'] = [];
            }
        }

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "data" => $orders
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to fetch orders."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing user_id parameter."]);
}
?>
