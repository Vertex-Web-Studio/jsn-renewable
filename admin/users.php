<?php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$message = '';
$error = '';

// Create Admin User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user']) && $db) {
    $username = sanitize($_POST['username'] ?? '');
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $role = sanitize($_POST['role'] ?? 'Admin');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All user fields are required.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        try {
            $stmt = $db->prepare("INSERT INTO users (username, password_hash, full_name, email, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$username, $hash, $full_name, $email, $role]);
            $message = "Admin user '$username' created successfully.";
        } catch (PDOException $e) {
            $error = "Error: Username or Email already exists.";
        }
    }
}

// Delete User
if (isset($_GET['delete']) && $db) {
    $userId = (int)$_GET['delete'];
    if ($userId === (int)$_SESSION['admin_id']) {
        $error = "You cannot delete your own logged-in account.";
    } else {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $message = "User deleted successfully.";
    }
}

// Fetch Users
$users = [];
if ($db) {
    $users = $db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 24px; margin-bottom: 4px;">Admin User Management</h1>
        <p style="margin: 0; font-size: 13px;">Manage administrative accounts, roles, and security access.</p>
    </div>
</div>

<?php if ($message): ?>
    <div style="background:#DCFCE7; color:#15803D; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #BBF7D0;"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background:#FEE2E2; color:#DC2626; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #FECACA;"><?php echo $error; ?></div>
<?php endif; ?>

<div class="admin-card">
    <h3 style="font-size:16px; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:8px;">Add New Admin User</h3>
    <form method="POST">
        <input type="hidden" name="create_user" value="1">
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control" required>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="Admin">Admin</option>
                    <option value="Manager">Manager</option>
                    <option value="SuperAdmin">SuperAdmin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password (min. 8 chars) *</label>
                <input type="password" name="password" class="form-control" required>
            </div>
        </div>
        <button type="submit" class="admin-btn admin-btn-primary"><i data-lucide="user-plus"></i> Create Admin Account</button>
    </form>
</div>

<div class="admin-card">
    <h3 style="font-size:16px; margin-bottom:16px;">Existing Admin Accounts</h3>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?php echo sanitize($u['username']); ?></strong></td>
                        <td><?php echo sanitize($u['full_name']); ?></td>
                        <td><?php echo sanitize($u['email']); ?></td>
                        <td><span class="status-badge badge-new"><?php echo sanitize($u['role']); ?></span></td>
                        <td><?php echo $u['last_login'] ? date('M d, Y H:i', strtotime($u['last_login'])) : 'Never'; ?></td>
                        <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <?php if ((int)$u['id'] !== (int)$_SESSION['admin_id']): ?>
                                <a href="users.php?delete=<?php echo $u['id']; ?>" onclick="return confirm('Delete user account?');" style="color:#DC2626; text-decoration:none; font-weight:bold;">Delete</a>
                            <?php else: ?>
                                <span style="color:var(--admin-muted); font-size:11px;">Current Account</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
