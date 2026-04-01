<?php
// backend/api/services/get_all.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../db_connect.php';

$query = executeQuery($conn, "SELECT id, name, description, price, icon_url FROM services ORDER BY name ASC");

if ($query['status'] == 'success') {
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => $query['data']
    ]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to fetch services."]);
}
?>
