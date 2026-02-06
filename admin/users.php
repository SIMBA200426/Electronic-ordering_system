<?php
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

// Fetch Users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

$pageTitle = "Manage Users";
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
    max-width: 550px;
    width: 90%;
    margin: 20px;
    position: relative;
    z-index: 9999;
}
</style>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Users</h1>
    <button onclick="openAddModal()" class="btn btn-primary">+ Add User</button>
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
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th style="text-align: right; min-width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                <tr>
                    <td class="text-muted">#<?= $u['id'] ?></td>
                    <td class="font-medium"><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge badge-<?= $u['role'] === 'admin' ? 'warning' : 'info' ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td style="text-align: right;">
                        <button onclick="openEditModal(<?= $u['id'] ?>)" class="btn btn-sm btn-secondary mr-2">Edit</button>
                        
                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                        <form action="users_action.php" method="POST" onsubmit="return confirm('Delete this user?')" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-sm badge-danger" style="border:none; cursor:pointer;">Del</button>
                        </form>
                        <?php else: ?>
                        <span class="text-xs text-muted">(You)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL -->
<div id="userModal" class="modal-overlay" onclick="if(event.target===this) closeModal()">
    <div class="modal-box card" onclick="event.stopPropagation()">
        
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-bold" id="modalTitle">User</h2>
            <button onclick="closeModal()" type="button" class="text-2xl text-muted hover:text-red-500" style="background:none; border:none; cursor:pointer;">&times;</button>
        </div>

        <div id="modalLoading" style="display:none; text-align:center; padding: 2rem;">Loading...</div>

        <form action="users_action.php" method="POST" id="userForm">
            <input type="hidden" name="form_action" id="formAction" value="add">
            <input type="hidden" name="user_id" id="userId" value="">

            <div class="mb-4">
                <label class="label text-sm text-muted">Full Name *</label>
                <input type="text" name="name" id="uName" class="input" required>
            </div>

            <div class="mb-4">
                <label class="label text-sm text-muted">Email *</label>
                <input type="email" name="email" id="uEmail" class="input" required>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="label text-sm text-muted">Role *</label>
                    <select name="role" id="uRole" class="input">
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label class="label text-sm text-muted">Password</label>
                    <input type="password" name="password" id="uPass" class="input" minlength="6" placeholder="Min 6 chars">
                    <p class="text-xs text-muted mt-1" id="passHint">Required for new users</p>
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
function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').innerText = 'Add New User';
    document.getElementById('submitBtn').innerText = 'Create User';
    document.getElementById('formAction').value = 'add';
    document.getElementById('passHint').innerText = 'Required for new users';
    document.getElementById('uPass').setAttribute('required', 'required');
    
    showModal();
}

function openEditModal(id) {
    resetForm();
    document.getElementById('modalTitle').innerText = 'Edit User';
    document.getElementById('submitBtn').innerText = 'Update User';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('userId').value = id;
    document.getElementById('passHint').innerText = 'Leave empty to keep current';
    document.getElementById('uPass').removeAttribute('required');
    
    showModal();
    document.getElementById('userForm').style.display = 'none';
    document.getElementById('modalLoading').style.display = 'block';

    fetch(`get_user.php?id=${id}`)
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
    document.getElementById('userForm').style.display = 'block';

    document.getElementById('uName').value = data.name;
    document.getElementById('uEmail').value = data.email;
    document.getElementById('uRole').value = data.role;
}

function resetForm() {
    document.getElementById('userForm').reset();
    document.getElementById('modalLoading').style.display = 'none';
    document.getElementById('userForm').style.display = 'block';
}

function showModal() {
    document.getElementById('userModal').classList.add('active');
}

function closeModal() {
    document.getElementById('userModal').classList.remove('active');
}
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>
