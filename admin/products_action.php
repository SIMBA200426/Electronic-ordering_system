<?php
require_once __DIR__ . '/../config/db_connect.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized Access");
}

// 1. DELETE ACTION
if (isset($_POST['delete_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([(int)$_POST['delete_id']]);
        $img = $stmt->fetchColumn();

        // Delete Image
        if ($img && $img != 'default.jpg' && file_exists(__DIR__ . '/../assets/images/' . $img)) {
            @unlink(__DIR__ . '/../assets/images/' . $img);
        }

        // Delete Record
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([(int)$_POST['delete_id']]);

        $_SESSION['success'] = "Product deleted successfully";
    } catch (Exception $e) {
        $_SESSION['error'] = "Delete failed: " . $e->getMessage();
    }
    header("Location: " . BASE_URL . "/admin/products.php");
    exit;
}

// 2. ADD / EDIT ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action'])) {
    try {
        $name = trim($_POST['name'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $description = trim($_POST['description'] ?? '');
        $action = $_POST['form_action'];
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

        // Validation
        if (empty($name) || $price < 0 || $stock < 0) {
            throw new Exception("Invalid input: Name is required, Price/Stock must be positive.");
        }

        // Image Handling
        $image_filename = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                throw new Exception("Invalid file type (Only JPG, PNG, GIF, WEBP allowed)");
            }
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                throw new Exception("File size too large (Max 5MB)");
            }

            $filename = uniqid('prod_') . '.' . $ext;
            $upload_dir = __DIR__ . '/../assets/images/';
            
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                $image_filename = $filename;
            } else {
                throw new Exception("Failed to save image file.");
            }
        }

        if ($action === 'add') {
            // INSERT
            $sql = "INSERT INTO products (name, description, price, stock, category_id, image, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $description, $price, $stock, $category_id, $image_filename]);
            $_SESSION['success'] = "Product created successfully!";
        } 
        elseif ($action === 'edit' && $product_id > 0) {
            // UPDATE
            if ($image_filename) {
                // Remove old image
                $old_stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                $old_stmt->execute([$product_id]);
                $old_img = $old_stmt->fetchColumn();
                if ($old_img && $old_img !== 'default.jpg' && file_exists(__DIR__ . '/../assets/images/' . $old_img)) {
                    @unlink(__DIR__ . '/../assets/images/' . $old_img);
                }

                $sql = "UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, image=? WHERE id=?";
                $params = [$name, $description, $price, $stock, $category_id, $image_filename, $product_id];
            } else {
                $sql = "UPDATE products SET name=?, description=?, price=?, stock=?, category_id=? WHERE id=?";
                $params = [$name, $description, $price, $stock, $category_id, $product_id];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $_SESSION['success'] = "Product updated successfully!";
        }

    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    header("Location: " . BASE_URL . "/admin/products.php");
    exit;
}

// Fallback
header("Location: " . BASE_URL . "/admin/products.php");
exit;
