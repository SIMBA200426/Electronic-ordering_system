<?php
require_once __DIR__ . '/../config/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            if ($stmt->execute([$name, $email, $hashed])) {
                // Auto login
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                $_SESSION['role'] = 'customer';
                header("Location: " . BASE_URL . "/customer/dashboard.php");
                exit;
            } else {
                $error = "Registration failed.";
            }
        }
    }
}

$pageTitle = "Register";
include __DIR__ . '/../components/header.php';
?>

<div class="flex items-center justify-center" style="min-height: 60vh;">
    <div class="card" style="width: 100%; max-width: 400px;">
        <h2 style="text-align: center; margin-bottom: 2rem;">Create Account</h2>
        
        <?php if($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; text-align: center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="label" for="name">Full Name</label>
                <input type="text" id="name" name="name" class="input" required placeholder="mustaf cbe">
            </div>

            <div class="mb-4">
                <label class="label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="input" required placeholder="john@example.com">
            </div>
            
            <div class="mb-8">
                <label class="label" for="password">Password</label>
                <input type="password" id="password" name="password" class="input" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-size: 1rem;">Register</button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; color: var(--muted); font-size: 0.875rem;">
            Already have an account? <a href="<?= BASE_URL ?>/auth/login.php">Login</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
