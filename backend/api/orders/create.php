<?php
// backend/api/orders/create.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../db_connect.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->user_id) && !empty($data->items) && is_array($data->items)) {
    $user_id = $data->user_id;
    $special_instructions = !empty($data->special_instructions) ? $data->special_instructions : null;
    $total_amount = 0;
    $items = $data->items; // Expected format: [{"service_id": 1, "quantity": 2, "subtotal": 10.00}, ...]

    // First validate items and calculate total amount
    foreach ($items as $item) {
        if (empty($item->service_id) || empty($item->quantity) || empty($item->subtotal)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid item data format."]);
            exit();
        }
        $total_amount += $item->subtotal;
    }

    // Begin Transaction manually because we have multiple related inserts
    $conn->begin_transaction();

    try {
        $delivery_days = isset($data->delivery_days) ? (int)$data->delivery_days : null;
        $payment_method = isset($data->payment_method) ? $data->payment_method : 'cod';
        $payment_status = isset($data->payment_status) ? $data->payment_status : 'pending';
        
        // 1. Insert into orders table
        $sqlOrder = "INSERT INTO orders (user_id, status, total_amount, special_instructions, delivery_days, payment_method, payment_status) VALUES (?, 'pending', ?, ?, ?, ?, ?)";
        $stmtOrder = $conn->prepare($sqlOrder);
        $stmtOrder->bind_param("idsiss", $user_id, $total_amount, $special_instructions, $delivery_days, $payment_method, $payment_status);
        $stmtOrder->execute();
        $order_id = $stmtOrder->insert_id;
        $stmtOrder->close();

        // 2. Insert into order_items table
        $sqlItem = "INSERT INTO order_items (order_id, service_id, quantity, subtotal) VALUES (?, ?, ?, ?)";
        $stmtItem = $conn->prepare($sqlItem);

        foreach ($items as $item) {
            $stmtItem->bind_param("iiid", $order_id, $item->service_id, $item->quantity, $item->subtotal);
            $stmtItem->execute();
        }
        $stmtItem->close();

        // 3. Commit the transaction
        $conn->commit();

        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Order created successfully.",
            "order_id" => $order_id
        ]);

    } catch (Exception $e) {
        // Rollback on any failure
        $conn->rollback();
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to create order: " . $e->getMessage()
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data. Please provide user_id and an array of items."]);
}
?>
