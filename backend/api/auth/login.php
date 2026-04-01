<?php
// backend/api/auth/login.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../db_connect.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    $email = $data->email;
    $password = $data->password;

    $sql = "SELECT id, name, email, phone, address, password_hash, role FROM users WHERE email = ?";
    $result = executeQuery($conn, $sql, "s", $email);

    if ($result['status'] == 'success' && !empty($result['data'])) {
        $user = $result['data'][0];
        
        // Ensure only customers can login via the app API
        if ($user['role'] == 'admin') {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Admins must use the web panel to login."]);
            exit();
        }

        if (password_verify($password, $user['password_hash'])) {
            // Remove password hash from response
            unset($user['password_hash']);
            
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "user" => $user
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Invalid password."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "User not found."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Incomplete data. Please provide email and password."]);
}
?>
