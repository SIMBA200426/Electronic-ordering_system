<?php
require_once __DIR__ . '/../config/db_connect.php';

// Fetch Featured Categories (first 6)
$categories = $pdo->query("SELECT * FROM categories LIMIT 6")->fetchAll();

// Fetch Featured Products (first 8)
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT 8")->fetchAll();

$pageTitle = "Home";
include __DIR__ . '/../components/header.php';
?>

<!-- Simple Title -->
<div class="mb-8 text-center">
    <h1 class="text-4xl font-bold mb-2">Welcome to ElectroShop</h1>
    <p class="text-muted">Discover premium electronics</p>
</div>

<!-- Categories -->
<div class="mb-8">
    <div class="flex justify-between items-end mb-4">
        <h2 class="text-xl font-bold">Browse Categories</h2>
        <a href="products.php" class="text-sm font-medium">View All &rarr;</a>
    </div>
    
    <div class="flex gap-4 overflow-x-auto pb-2" style="scrollbar-width: none;">
        <a href="products.php" class="category-pill">All Products</a>
        <?php foreach($categories as $cat): ?>
        <a href="products.php?category=<?= $cat['id'] ?>" class="category-pill">
            <?= htmlspecialchars($cat['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Products Grid -->
<div class="mb-8">
    <h2 class="text-xl font-bold mb-6">Featured Products</h2>
    
    <div class="grid grid-cols-1 sm-grid-cols-2 lg-grid-cols-4 gap-6">
        <?php foreach($products as $product): ?>
        <div class="card product-card p-0 overflow-hidden border-0 shadow-sm hover:shadow-lg transition-all" style="height: 100%;">
            <a href="product-details.php?id=<?= $product['id'] ?>" class="block">
                <div class="product-image-container relative bg-slate-100">
                    <?php if(!empty($product['image']) && $product['image'] != 'default.jpg'): ?>
                        <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image absolute inset-0 w-full h-full object-cover">
                    <?php else: ?>
                        <div class="flex items-center justify-center h-full text-muted">No Image</div>
                    <?php endif; ?>
                    
                    <?php if($product['stock'] < 5 && $product['stock'] > 0): ?>
                        <span class="badge badge-warning absolute top-2 right-2 shadow-sm">Low Stock</span>
                    <?php elseif($product['stock'] == 0): ?>
                        <span class="badge badge-danger absolute top-2 right-2 shadow-sm">Out of Stock</span>
                    <?php endif; ?>
                </div>
            </a>
            
            <div class="p-4 flex flex-col flex-1">
                <div class="mb-2">
                    <span class="text-xs text-muted font-medium uppercase tracking-wider">
                        <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                    </span>
                </div>
                <h3 class="text-lg font-bold mb-1 leading-tight flex-1">
                    <a href="product-details.php?id=<?= $product['id'] ?>" class="text-slate-800 hover:text-primary transition-colors">
                        <?= htmlspecialchars($product['name']) ?>
                    </a>
                </h3>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-xl font-bold text-primary">$<?= number_format($product['price'], 2) ?></span>
                    <?php if($product['stock'] > 0): ?>
                    <form method="POST" action="<?= BASE_URL ?>/customer/cart.php" class="flex-shrink-0">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-secondary hover:bg-slate-50 transition-colors" title="Add to Cart">
                            + Add
                        </button>
                    </form>
                    <?php else: ?>
                    <button class="btn btn-sm" style="background: #e5e7eb; color: #6b7280; cursor: not-allowed;" disabled>
                        Sold Out
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- View All Link -->
    <div class="text-center mt-8">
        <a href="products.php" class="btn btn-secondary" style="padding: 0.75rem 2rem;">View All Products</a>
    </div>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>
