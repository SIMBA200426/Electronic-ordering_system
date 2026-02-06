<?php
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

// Fetch Orders with customer info
$orders = $pdo->query("
    SELECT o.*, u.name as customer_name, u.email as customer_email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
")->fetchAll();

$pageTitle = "Manage Orders";
include __DIR__ . '/../components/header.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold">Orders</h1>
</div>

<!-- ALERTS -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-4 p-4 rounded bg-green-100 text-green-800 border border-green-200">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="mb-4 p-4 rounded bg-red-100 text-red-800 border border-red-200">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- TABLE -->
<div class="card overflow-hidden p-0" style="border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
    <div class="table-container" style="border: none; box-shadow: none;">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($orders)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: var(--muted);">
                        No orders found.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach($orders as $o): ?>
                <tr>
                    <td class="text-muted">#<?= $o['id'] ?></td>
                    <td class="font-medium"><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td class="text-sm text-muted"><?= htmlspecialchars($o['customer_email']) ?></td>
                    <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                    <td class="font-medium">$<?= number_format($o['total'], 2) ?></td>
                    <td>
                        <?php
                        $badge_class = 'badge-info';
                        if ($o['status'] === 'completed') $badge_class = 'badge-success';
                        elseif ($o['status'] === 'shipped') $badge_class = 'badge-warning';
                        elseif ($o['status'] === 'paid') $badge_class = 'badge-info';
                        ?>
                        <span class="badge <?= $badge_class ?>">
                            <?= ucfirst($o['status']) ?>
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <form action="orders_action.php" method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <input type="hidden" name="update_status" value="1">
                            <select name="status" onchange="this.form.submit()" class="input" style="padding: 0.35rem 0.5rem; font-size: 0.875rem; margin: 0; width: auto; display: inline-block;">
                                <option value="pending" <?= $o['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="paid" <?= $o['status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="shipped" <?= $o['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="completed" <?= $o['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
