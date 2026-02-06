<?php
require_once __DIR__ . '/../config/db_connect.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized Access");
}

// UPDATE ORDER STATUS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $order_id = (int)$_POST['order_id'];
        $status = $_POST['status'];
        
        // Validate status
        $valid_statuses = ['pending', 'paid', 'shipped', 'completed'];
        if (!in_array($status, $valid_statuses)) {
            throw new Exception("Invalid status value.");
        }
        
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        
        $_SESSION['success'] = "Order status updated successfully!";
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: " . BASE_URL . "/admin/orders.php");
    exit;
}

// Fallback
header("Location: " . BASE_URL . "/admin/orders.php");
exit;
