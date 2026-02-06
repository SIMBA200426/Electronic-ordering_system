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
        
        // Prevent self-deletion
        if ($delete_id == $_SESSION['user_id']) {
            throw new Exception("You cannot delete yourself.");
        }
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_id]);
        
        $_SESSION['success'] = "User deleted successfully";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header("Location: " . BASE_URL . "/admin/users.php");
    exit;
}

// 2. ADD / EDIT ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action'])) {
    try {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'customer';
        $password = $_POST['password'] ?? '';
        $action = $_POST['form_action'];
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

        // Validation
        if (empty($name) || empty($email)) {
            throw new Exception("Name and Email are required.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        
        if (!in_array($role, ['admin', 'customer'])) {
            throw new Exception("Invalid role.");
        }

        if ($action === 'add') {
            // Validate password for new users
            if (empty($password) || strlen($password) < 6) {
                throw new Exception("Password must be at least 6 characters.");
            }
            
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception("Email already exists.");
            }
            
            // Insert new user
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $hashed, $role]);
            
            $_SESSION['success'] = "User created successfully!";
            
        } elseif ($action === 'edit' && $user_id > 0) {
            // Check if email is taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                throw new Exception("Email already exists.");
            }
            
            // Update user
            if (!empty($password)) {
                // Update with new password
                if (strlen($password) < 6) {
                    throw new Exception("Password must be at least 6 characters.");
                }
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?";
                $params = [$name, $email, $role, $hashed, $user_id];
            } else {
                // Update without changing password
                $sql = "UPDATE users SET name=?, email=?, role=? WHERE id=?";
                $params = [$name, $email, $role, $user_id];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $_SESSION['success'] = "User updated successfully!";
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: " . BASE_URL . "/admin/users.php");
    exit;
}

// Fallback
header("Location: " . BASE_URL . "/admin/users.php");
exit;
