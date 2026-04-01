<!-- admin/includes/header.php -->
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WashMate Admin Panel</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #F8F9FA;}
        .primary-bg { background-color: #2B5B84; }
        .accent-text { color: #00B4D8; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 primary-bg text-white hidden md:flex flex-col">
        <div class="h-16 flex items-center justify-center font-bold text-2xl border-b border-blue-800">
            <i class="fas fa-water mr-2"></i> WashMate
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="index.php" class="flex items-center px-4 py-3 hover:bg-blue-800 rounded-lg transition-colors <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-blue-800' : '' ?>">
                <i class="fas fa-tachometer-alt w-6"></i> Dashboard
            </a>
            <a href="orders.php" class="flex items-center px-4 py-3 hover:bg-blue-800 rounded-lg transition-colors <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'bg-blue-800' : '' ?>">
                <i class="fas fa-box w-6"></i> Orders
            </a>
            <a href="services.php" class="flex items-center px-4 py-3 hover:bg-blue-800 rounded-lg transition-colors <?= basename($_SERVER['PHP_SELF']) == 'services.php' ? 'bg-blue-800' : '' ?>">
                <i class="fas fa-tshirt w-6"></i> Services
            </a>
            <a href="users.php" class="flex items-center px-4 py-3 hover:bg-blue-800 rounded-lg transition-colors <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'bg-blue-800' : '' ?>">
                <i class="fas fa-users w-6"></i> Customers
            </a>
            <a href="reports.php" class="flex items-center px-4 py-3 hover:bg-blue-800 rounded-lg transition-colors <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'bg-blue-800' : '' ?>">
                <i class="fas fa-chart-pie w-6"></i> Reports
            </a>
        </nav>
        <div class="p-4 border-t border-blue-800">
            <a href="logout.php" class="flex items-center px-4 py-2 text-red-300 hover:text-red-100 hover:bg-blue-800 rounded-lg transition-colors">
                <i class="fas fa-sign-out-alt w-6"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-y-auto">
        <!-- Top Navigation -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6">
            <div class="md:hidden">
                <button class="text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            <div class="flex-1"></div>
            <div class="flex items-center">
                <div class="text-sm border-r pr-4 mr-4 text-gray-500">
                    Welcome, <b><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></b>
                </div>
                <!-- Profile dummy -->
                <div class="h-8 w-8 rounded-full primary-bg flex items-center justify-center text-white font-bold">
                    A
                </div>
            </div>
        </header>

        <!-- Main Inner Content -->
        <main class="flex-1 p-6">
