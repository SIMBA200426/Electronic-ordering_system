<?php
require_once __DIR__ . '/../config/db_connect.php';

// Force JSON Header immediately
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
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Ensure image has full path URL for valid display
        $product['image_url'] = !empty($product['image']) 
            ? BASE_URL . '/assets/images/' . $product['image'] 
            : null;
            
        echo json_encode(['status' => 'success', 'data' => $product]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
exit;
