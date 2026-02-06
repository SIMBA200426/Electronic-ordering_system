<?php
require_once __DIR__ . '/../config/db_connect.php';

// Force JSON Header
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No ID provided']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        echo json_encode(['status' => 'success', 'data' => $category]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Category not found']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
exit;
