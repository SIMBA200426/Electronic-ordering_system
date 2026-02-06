<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/db_connect.php';
}

// Detect Mode
$isAdminPath = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$isCustomerPath = (strpos($_SERVER['PHP_SELF'], '/customer/') !== false);
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
$isCustomer = (isset($_SESSION['role']) && $_SESSION['role'] === 'customer');

// Enable sidebar if strictly in these areas
$showSidebar = (($isCustomerPath && $isCustomer) || ($isAdminPath && $isAdmin));

$sidebarType = '';
if ($showSidebar) {
   $sidebarType = $isAdminPath ? 'admin' : 'customer';
}

// CSS Version
$cssVersion = time(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - Electronics Shop' : 'Electronics Shop' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= $cssVersion ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Specific overrides for color themes if needed beyond style.css defaults */
        <?php if($sidebarType === 'admin'): ?>
        .app-sidebar { background-color: #0f172a !important; color: white; }
        <?php elseif($sidebarType === 'customer'): ?>
        .app-sidebar { background-color: #1e293b !important; color: white; }
        <?php endif; ?>
    </style>
</head>
<body class="<?= $showSidebar ? 'sidebar-mode' : '' ?>">

<?php if ($showSidebar): ?>
    <!-- Persistent Sidebar -->
    <?php include __DIR__ . '/drawer.php'; ?>
    
    <!-- Main Content Wrapper -->
    <div class="app-wrapper">
        <header class="header">
            <div class="container flex items-center justify-between">
                <div class="flex items-center gap-4">
                     <!-- Mobile Toggle (visible on mobile via CSS) -->
                    <button class="hamburger-mobile-only" onclick="toggleDrawer()" aria-label="Toggle menu" style="color: black;">
                        ☰
                    </button>
                    <span class="text-xl font-bold text-primary">
                        <?= $sidebarType === 'admin' ? 'Admin Panel' : 'My Account' ?>
                    </span>
                </div>
                <div class="flex items-center gap-4">
                     <span class="text-sm font-medium">Hello, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                     
                     <?php if($sidebarType === 'customer'): ?>
                        <a href="<?= BASE_URL ?>/customer/cart.php" class="relative" style="text-decoration: none; font-size: 1.2rem;">🛒</a>
                     <?php endif; ?>
                     
                     <a href="<?= BASE_URL ?>/auth/logout.php" class="text-sm text-red-500">Logout</a>
                </div>
            </div>
        </header>

        <main class="container mt-4 mb-8" style="min-height: 80vh;">
<?php else: ?>
    <!-- Standard Public Header -->
    <header class="header">
        <div class="container flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button class="hamburger" onclick="toggleDrawer()" aria-label="Toggle menu">
                    ☰
                </button>
                <a href="<?= BASE_URL ?>/public/index.php" class="text-xl font-bold text-primary" style="font-size: 1.25rem; font-weight: 700; color: var(--primary);">
                    ElectroShop
                </a>
                
                <nav class="desktop-nav">
                    <a href="<?= BASE_URL ?>/public/index.php">Home</a>
                    <a href="<?= BASE_URL ?>/public/products.php">Products</a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/customer/dashboard.php">Dashboard</a>
                            <a href="<?= BASE_URL ?>/customer/orders.php">My Orders</a>
                        <?php endif; ?>
                        <!-- Logout is usually a secondary action in nav, better to keep it right side or here. 
                             Let's keep it here for clarity but style it delicately. -->
                    <?php endif; ?>
                </nav>
            </div>
            
            <div class="flex items-center gap-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-sm hidden sm-block">Hi, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                     <a href="<?= BASE_URL ?>/auth/logout.php" class="text-sm text-red-500">Logout</a>
                <?php else: ?>
                    <!-- Removed duplicate Login link from desktop-nav above, keeping only this CTA -->
                    <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-primary">Login</a>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>/customer/cart.php" class="relative" style="text-decoration: none; font-size: 1.2rem;">
                    🛒 
                </a>
            </div>
        </div>
    </header>

    <?php include __DIR__ . '/drawer.php'; ?>

    <main class="container mt-4 mb-8" style="min-height: 80vh;">
<?php endif; ?>
