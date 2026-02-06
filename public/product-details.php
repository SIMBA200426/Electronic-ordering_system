<?php
require_once __DIR__ . '/../config/db_connect.php';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header("Location: " . BASE_URL . "/public/products.php");
    exit;
}

// Fetch product details
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: " . BASE_URL . "/public/products.php");
    exit;
}

// Fetch related products (same category, exclude current)
$stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();

$pageTitle = htmlspecialchars($product['name']);
include __DIR__ . '/../components/header.php';
?>

<div class="mb-4">
    <a href="<?= BASE_URL ?>/public/products.php" class="text-sm text-muted hover:text-primary">&larr; Back to Products</a>
</div>

<!-- Product Details -->
<div class="grid grid-cols-1 lg-grid-cols-2 gap-8 mb-12">
    <!-- Product Image -->
    <div class="card p-4 overflow-hidden" style="max-width: 500px; margin: 0 auto;">
        <div class="relative bg-slate-100 rounded-lg overflow-hidden" style="width: 100%; max-height: 450px;">
            <?php if(!empty($product['image']) && $product['image'] != 'default.jpg'): ?>
                <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($product['image']) ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>" 
                     style="width: 100%; height: auto; max-height: 450px; object-fit: contain; display: block;"
                     onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-full text-muted text-2xl\' style=\'min-height:300px;\'>Image Not Found</div>';">
            <?php else: ?>
                <div class="flex items-center justify-center text-muted text-2xl" style="min-height: 300px;">No Image Available</div>
            <?php endif; ?>
            
            <?php if($product['stock'] < 5 && $product['stock'] > 0): ?>
                <span class="badge badge-warning absolute top-4 right-4 shadow-sm">Only <?= $product['stock'] ?> Left</span>
            <?php elseif($product['stock'] == 0): ?>
                <span class="badge badge-danger absolute top-4 right-4 shadow-sm">Out of Stock</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Product Info -->
    <div class="flex flex-col">
        <div class="mb-4">
            <span class="text-sm text-muted font-medium uppercase tracking-wider">
                <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
            </span>
        </div>
        
        <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($product['name']) ?></h1>
        
        <div class="mb-6">
            <span class="text-4xl font-bold text-primary">$<?= number_format($product['price'], 2) ?></span>
        </div>
        
        <div class="mb-6 pb-6 border-b">
            <h3 class="font-bold mb-2">Description</h3>
            <div class="text-muted leading-relaxed product-description">
                <?php 
                // Display rich text HTML from TinyMCE, but strip dangerous tags
                $description = $product['description'] ?? 'No description available.';
                // Allow safe HTML tags from TinyMCE
                echo strip_tags($description, '<p><br><strong><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><a><table><tr><td><th><tbody><thead>');
                ?>
            </div>
        </div>
        
        <div class="mb-6">
            <h3 class="font-bold mb-3">Product Details</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-sm text-muted">SKU</span>
                    <p class="font-medium">#<?= str_pad($product['id'], 6, '0', STR_PAD_LEFT) ?></p>
                </div>
                <div>
                    <span class="text-sm text-muted">Availability</span>
                    <p class="font-medium">
                        <?php if($product['stock'] > 0): ?>
                            <span class="text-green-600">In Stock (<?= $product['stock'] ?> units)</span>
                        <?php else: ?>
                            <span class="text-red-600">Out of Stock</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Add to Cart -->
        <div class="mt-auto">
            <?php if($product['stock'] > 0): ?>
            <form method="POST" action="<?= BASE_URL ?>/customer/cart.php" class="flex gap-4 items-center">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium">Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" 
                           class="input" style="width: 80px; margin-bottom: 0; padding: 0.5rem;">
                </div>
                
                <button type="submit" class="btn btn-primary flex-1" style="padding: 0.75rem 2rem; font-size: 1rem;">
                    Add to Cart
                </button>
            </form>
            <?php else: ?>
            <button class="btn w-full" style="background: #e5e7eb; color: #6b7280; cursor: not-allowed; padding: 0.75rem;" disabled>
                Out of Stock
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Related Products -->
<?php if(!empty($related_products)): ?>
<div class="mb-8">
    <h2 class="text-xl font-bold mb-6">Related Products</h2>
    
    <div class="grid grid-cols-1 sm-grid-cols-2 lg-grid-cols-4 gap-6">
        <?php foreach($related_products as $rel): ?>
        <div class="card product-card p-0 overflow-hidden border-0 shadow-sm hover:shadow-lg transition-all">
            <a href="product-details.php?id=<?= $rel['id'] ?>" class="block">
                <div class="product-image-container relative bg-slate-100">
                    <?php if(!empty($rel['image']) && $rel['image'] != 'default.jpg'): ?>
                        <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($rel['image']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>" class="product-image absolute inset-0 w-full h-full object-cover">
                    <?php else: ?>
                        <div class="flex items-center justify-center h-full text-muted">No Image</div>
                    <?php endif; ?>
                </div>
            </a>
            
            <div class="p-4">
                <h3 class="text-lg font-bold mb-2 leading-tight">
                    <a href="product-details.php?id=<?= $rel['id'] ?>" class="text-slate-800 hover:text-primary transition-colors">
                        <?= htmlspecialchars($rel['name']) ?>
                    </a>
                </h3>
                <span class="text-xl font-bold text-primary">$<?= number_format($rel['price'], 2) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../components/footer.php'; ?>
