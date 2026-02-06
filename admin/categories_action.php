<?php
require_once __DIR__ . '/../config/db_connect.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized Access");
}

// 1. DELETE ACTION
if (isset($_POST['delete_id'])) {
    try {
        $delete_id = (int)$_POST['delete_id'];
        
        // Set products with this category to NULL
        $stmt = $pdo->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?");
        $stmt->execute([$delete_id]);
        
        // Delete category
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$delete_id]);
        
        $_SESSION['success'] = "Category deleted successfully";
    } catch (Exception $e) {
        $_SESSION['error'] = "Delete failed: " . $e->getMessage();
    }
    header("Location: " . BASE_URL . "/admin/categories.php");
    exit;
}

// 2. ADD / EDIT ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action'])) {
    try {
        $name = trim($_POST['name'] ?? '');
        $action = $_POST['form_action'];
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

        // Validation
        if (empty($name)) {
            throw new Exception("Category name is required.");
        }

        if ($action === 'add') {
            // Check if category name exists
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                throw new Exception("Category name already exists.");
            }
            
            // Insert new category
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            
            $_SESSION['success'] = "Category created successfully!";
            
        } elseif ($action === 'edit' && $category_id > 0) {
            // Check if name is taken by another category
            $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
            $stmt->execute([$name, $category_id]);
            if ($stmt->fetch()) {
                throw new Exception("Category name already exists.");
            }
            
            // Update category
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $category_id]);
            
            $_SESSION['success'] = "Category updated successfully!";
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: " . BASE_URL . "/admin/categories.php");
    exit;
}

// Fallback
header("Location: " . BASE_URL . "/admin/categories.php");
exit;
