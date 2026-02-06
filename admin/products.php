<?php
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

// Fetch Data for Display
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$pageTitle = "Manage Products";
include __DIR__ . '/../components/header.php';
?>

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/fe5wyref1cxrkp3npzkzee3jlkusis2o32cpoeaobz39dlcn/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<style>
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9998;
    align-items: flex-start;
    justify-content: center;
    padding-top: 50px;
    overflow-y: auto;
}
.modal-overlay.active {
    display: flex !important;
}
.modal-box {
    background: white;
    border-radius: 8px;
    max-width: 750px;
    width: 90%;
    margin: 20px;
    position: relative;
    z-index: 9999;
}
</style>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Products</h1>
    <button onclick="openAddModal()" class="btn btn-primary">+ Add Product</button>
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
                    <th style="width: 50px;">ID</th>
                    <th style="width: 60px;">Img</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th style="text-align: right; min-width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $p): ?>
                <tr>
                    <td class="text-muted">#<?= $p['id'] ?></td>
                    <td>
                        <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 4px; overflow: hidden; border: 1px solid var(--border);">
                           <?php 
                                $imgSrc = (!empty($p['image']) && $p['image'] !== 'default.jpg') 
                                    ? BASE_URL . '/assets/images/' . htmlspecialchars($p['image'])
                                    : 'https://placehold.co/40x40?text=IMG';
                            ?>
                            <img src="<?= $imgSrc ?>" style="width:100%; height:100%; object-fit:cover;"
                                 onerror="this.src='https://placehold.co/40x40?text=ERR'">
                        </div>
                    </td>
                    <td class="font-medium"><?= htmlspecialchars($p['name']) ?></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($p['category_name'] ?? '-') ?></span></td>
                    <td>$<?= number_format($p['price'], 2) ?></td>
                    <td><?= $p['stock'] ?></td>
                    <td style="text-align: right;">
                        <button onclick="openEditModal(<?= $p['id'] ?>)" class="btn btn-sm btn-secondary mr-2">Edit</button>
                        
                        <form action="products_action.php" method="POST" onsubmit="return confirm('Delete?')" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-sm badge-danger" style="border:none; cursor:pointer;">Del</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL -->
<div id="productModal" class="modal-overlay" onclick="if(event.target===this) closeModal()">
    <div class="modal-box card" onclick="event.stopPropagation()">
        
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold" id="modalTitle">Product</h2>
            <button onclick="closeModal()" type="button" class="text-2xl text-muted hover:text-red-500" style="background:none; border:none; cursor:pointer;">&times;</button>
        </div>

        <div id="modalLoading" style="display:none; text-align:center; padding: 2rem;">Loading...</div>

        <form action="products_action.php" method="POST" enctype="multipart/form-data" id="productForm">
            <input type="hidden" name="form_action" id="formAction" value="add">
            <input type="hidden" name="product_id" id="productId" value="">

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="label text-sm text-muted">Name *</label>
                    <input type="text" name="name" id="pName" class="input" required>
                </div>
                <div>
                    <label class="label text-sm text-muted">Category</label>
                    <select name="category_id" id="pCategory" class="input">
                        <option value="">-- No Category --</option>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="label text-sm text-muted">Price ($) *</label>
                    <input type="number" step="0.01" name="price" id="pPrice" class="input" required>
                </div>
                <div>
                    <label class="label text-sm text-muted">Stock *</label>
                    <input type="number" name="stock" id="pStock" class="input" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="label text-sm text-muted">Description</label>
                <textarea name="description" id="pDesc" style="height: 250px;"></textarea>
            </div>

            <div class="mb-6">
                <label class="label text-sm text-muted">Image</label>
                <div class="flex items-center gap-4 border p-3 rounded">
                    <img src="" id="previewImg" style="width:60px; height:60px; object-fit:cover; display:none; border-radius:4px;">
                    <input type="file" name="image" id="imageInput" accept="image/*" class="input" style="border:none;">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
let isEditorInit = false;

function initEditor() {
    if (isEditorInit) return;
    
    tinymce.init({
        selector: '#pDesc',
        height: 250,
        menubar: false,
        plugins: 'advlist autolink lists link table code wordcount',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | table code',
        setup: function(editor) {
            editor.on('change', function() { tinymce.triggerSave(); });
        }
    });
    isEditorInit = true;
}

document.getElementById('productForm').addEventListener('submit', function() {
    if (tinymce.get('pDesc')) tinymce.triggerSave();
});

function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').innerText = 'Add New Product';
    document.getElementById('submitBtn').innerText = 'Create';
    document.getElementById('formAction').value = 'add';
    
    showModal();
    setTimeout(() => {
        initEditor();
        if(tinymce.get('pDesc')) tinymce.get('pDesc').setContent('');
    }, 100);
}

function openEditModal(id) {
    resetForm();
    document.getElementById('modalTitle').innerText = 'Edit Product';
    document.getElementById('submitBtn').innerText = 'Update';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('productId').value = id;
    
    showModal();
    document.getElementById('productForm').style.display = 'none';
    document.getElementById('modalLoading').style.display = 'block';

    fetch(`get_product.php?id=${id}`)
        .then(res => res.json())
        .then(json => {
            if (json.status === 'success') {
                populateForm(json.data);
            } else {
                alert("Error: " + json.message);
                closeModal();
            }
        })
        .catch(err => {
            console.error(err);
            alert("System Error: Check console.");
            closeModal();
        });
}

function populateForm(data) {
    document.getElementById('modalLoading').style.display = 'none';
    document.getElementById('productForm').style.display = 'block';

    document.getElementById('pName').value = data.name;
    document.getElementById('pPrice').value = data.price;
    document.getElementById('pStock').value = data.stock;
    document.getElementById('pCategory').value = data.category_id || '';

    const preview = document.getElementById('previewImg');
    if (data.image_url) {
        preview.src = data.image_url;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }

    setTimeout(() => {
        initEditor();
        if (tinymce.get('pDesc')) {
            tinymce.get('pDesc').setContent(data.description || '');
        } else {
            document.getElementById('pDesc').value = data.description || '';
        }
    }, 100);
}

function resetForm() {
    document.getElementById('productForm').reset();
    document.getElementById('previewImg').style.display = 'none';
    document.getElementById('modalLoading').style.display = 'none';
    document.getElementById('productForm').style.display = 'block';
}

function showModal() {
    document.getElementById('productModal').classList.add('active');
}

function closeModal() {
    document.getElementById('productModal').classList.remove('active');
}

document.getElementById('imageInput').onchange = function() {
    const [file] = this.files;
    if (file) {
        const preview = document.getElementById('previewImg');
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
}
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>
