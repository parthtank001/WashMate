<!-- admin/login.php -->
<?php
session_start();
require_once '../backend/db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $sql = "SELECT id, name, password_hash, role FROM users WHERE email = ? AND role = 'admin'";
        $result = executeQuery($conn, $sql, "s", $email);
        
        if ($result['status'] == 'success' && !empty($result['data'])) {
            $user = $result['data'][0];
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "Admin account not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - WashMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #F8F9FA;}
        .primary-bg { background-color: #2B5B84; }
        .primary-text { color: #2B5B84; }
    </style>
</head>
<body class="flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold primary-text mb-2">WashMate</h1>
            <p class="text-gray-500">Admin Panel Login</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                <input type="email" name="email" class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:border-blue-500" required placeholder="admin@washmate.com">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:border-blue-500" required placeholder="Enter your password">
            </div>
            
            <button type="submit" class="w-full primary-bg text-white font-bold py-3 rounded-lg hover:bg-blue-800 transition-colors">
                Sign In
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-500">
             (Default credentials - Email: admin@washmate.com, Pass: admin123)
        </div>
    </div>
</body>
</html>
