<?php
// backend/api/auth/register.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../db_connect.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->name) && !empty($data->email) && !empty($data->password)) {
    $name = $data->name;
    $email = $data->email;
    $password = $data->password;
    $phone = !empty($data->phone) ? $data->phone : null;
    $address = !empty($data->address) ? $data->address : null;

    // Check if email already exists
    $checkSql = "SELECT id FROM users WHERE email = ?";
    $checkResult = executeQuery($conn, $checkSql, "s", $email);

    if ($checkResult['status'] == 'success' && !empty($checkResult['data'])) {
        http_response_code(409); // Conflict
        echo json_encode(["status" => "error", "message" => "Email already registered."]);
        exit();
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (name, email, password_hash, phone, address, role) VALUES (?, ?, ?, ?, ?, 'customer')";
    $result = executeQuery($conn, $sql, "sssss", $name, $email, $password_hash, $phone, $address);

    if ($result['status'] == 'success') {
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Registration successful.",
            "user_id" => $result['insert_id']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Registration failed. " . $result['message']]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data. Please provide name, email, and password."]);
}
?>
