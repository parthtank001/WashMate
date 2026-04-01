<?php
// backend/db_connect.php

$host = 'localhost';
$user = 'root'; // default XAMPP user
$password = ''; // default XAMPP password
$database = 'washmate';

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]));
}

// Set charset to utf8mb4 for proper character encoding
$conn->set_charset("utf8mb4");

// Function to safely execute queries and return JSON responses
function executeQuery($conn, $sql, $types = null, ...$params) {
    if ($types && count($params) > 0) {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return ["status" => "error", "message" => "Prepare statement failed: " . $conn->error];
        }
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
             $error = $stmt->error;
             $stmt->close();
             return ["status" => "error", "message" => "Execute failed: " . $error];
        }
        $result = $stmt->get_result();
        
        // Handle queries that don't return a result set (e.g., INSERT, UPDATE, DELETE)
        if ($result === false) {
             if ($conn->error) {
                 $error = $conn->error;
                 $stmt->close();
                 return ["status" => "error", "message" => "Query result error: " . $error];
             }
             $affectedRows = $stmt->affected_rows;
             $insertId = $stmt->insert_id;
             $stmt->close();
             return ["status" => "success", "affected_rows" => $affectedRows, "insert_id" => $insertId];
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return ["status" => "success", "data" => $data];
    } else {
        // Direct query without params (use cautiously)
        $result = $conn->query($sql);
        if (!$result) {
            return ["status" => "error", "message" => "Query failed: " . $conn->error];
        }
        if ($result === true) {
             return ["status" => "success", "affected_rows" => $conn->affected_rows, "insert_id" => $conn->insert_id];
        }
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return ["status" => "success", "data" => $data];
    }
}
?>
