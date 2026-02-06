<?php
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

// Fetch Categories with product count
$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.id ASC
")->fetchAll();

$pageTitle = "Manage Categories";
include __DIR__ . '/../components/header.php';
?>

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
    max-width: 450px;
    width: 90%;
    margin: 20px;
    position: relative;
    z-index: 9999;
}
</style>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Categories</h1>
    <button onclick="openAddModal()" class="btn btn-primary">+ Add Category</button>
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
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="card overflow-hidden p-0" style="border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
        <div class="table-container" style="border: none; box-shadow: none;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Name</th>
                        <th style="width: 100px;">Products</th>
                        <th style="text-align: right; min-width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $c): ?>
                    <tr>
                        <td class="text-muted">#<?= $c['id'] ?></td>
                        <td class="font-medium"><?= htmlspecialchars($c['name']) ?></td>
                        <td><span class="badge badge-info"><?= $c['product_count'] ?></span></td>
                        <td style="text-align: right;">
                            <button onclick="openEditModal(<?= $c['id'] ?>)" class="btn btn-sm btn-secondary mr-2">Edit</button>
                            
                            <form action="categories_action.php" method="POST" onsubmit="return confirm('Delete this category? Products will be uncategorized.')" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn btn-sm badge-danger" style="border:none; cursor:pointer;">Del</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL -->
<div id="categoryModal" class="modal-overlay" onclick="if(event.target===this) closeModal()">
    <div class="modal-box card" onclick="event.stopPropagation()">
        
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold" id="modalTitle">Category</h2>
            <button onclick="closeModal()" type="button" class="text-2xl text-muted hover:text-red-500" style="background:none; border:none; cursor:pointer;">&times;</button>
        </div>

        <div id="modalLoading" style="display:none; text-align:center; padding: 2rem;">Loading...</div>

        <form action="categories_action.php" method="POST" id="categoryForm">
            <input type="hidden" name="form_action" id="formAction" value="add">
            <input type="hidden" name="category_id" id="categoryId" value="">

            <div class="mb-6">
                <label class="label text-sm text-muted">Category Name *</label>
                <input type="text" name="name" id="cName" class="input" required placeholder="e.g. Laptops">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').innerText = 'Add New Category';
    document.getElementById('submitBtn').innerText = 'Create';
    document.getElementById('formAction').value = 'add';
    
    showModal();
}

function openEditModal(id) {
    resetForm();
    document.getElementById('modalTitle').innerText = 'Edit Category';
    document.getElementById('submitBtn').innerText = 'Update';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('categoryId').value = id;
    
    showModal();
    document.getElementById('categoryForm').style.display = 'none';
    document.getElementById('modalLoading').style.display = 'block';

    fetch(`get_category.php?id=${id}`)
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
    document.getElementById('categoryForm').style.display = 'block';
    document.getElementById('cName').value = data.name;
}

function resetForm() {
    document.getElementById('categoryForm').reset();
    document.getElementById('modalLoading').style.display = 'none';
    document.getElementById('categoryForm').style.display = 'block';
}

function showModal() {
    document.getElementById('categoryModal').classList.add('active');
}

function closeModal() {
    document.getElementById('categoryModal').classList.remove('active');
}
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>
