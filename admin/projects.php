<?php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$message = '';
$error = '';

// Handle POST Actions with CSRF verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id']) && $db) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Invalid CSRF token.';
    } else {
        $id = (int)$_POST['id'];
        $action = $_POST['action'];

        if ($action === 'toggle_publish') {
            $stmt = $db->prepare("UPDATE projects SET is_published = 1 - is_published WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Project publication status updated.';
        } elseif ($action === 'toggle_featured') {
            $stmt = $db->prepare("UPDATE projects SET is_featured = 1 - is_featured WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Project featured status updated.';
        } elseif ($action === 'delete') {
            // Delete associated uploaded files cleanly
            $pStmt = $db->prepare("SELECT hero_image FROM projects WHERE id = ?");
            $pStmt->execute([$id]);
            $proj = $pStmt->fetch();
            if ($proj && !empty($proj['hero_image'])) {
                deleteUploadedFile($proj['hero_image']);
            }
            $gStmt = $db->prepare("SELECT image_url FROM project_images WHERE project_id = ?");
            $gStmt->execute([$id]);
            foreach ($gStmt->fetchAll() as $g) {
                deleteUploadedFile($g['image_url']);
            }

            $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Project deleted successfully.';
        }
    }
}

$projects = [];
if ($db) {
    $projects = $db->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll();
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-size: 24px; margin-bottom: 4px;">Projects CMS Management</h1>
        <p style="margin: 0; font-size: 13px;">Create, edit, publish, and manage commissioned solar projects. Published projects appear automatically on the public website.</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="../projects/index.php" target="_blank" class="admin-btn admin-btn-outline"><i data-lucide="external-link"></i> Public Projects</a>
        <a href="project-edit.php" class="admin-btn admin-btn-primary"><i data-lucide="plus"></i> Add New Project</a>
    </div>
</div>

<?php if ($message): ?>
    <div style="background:#DCFCE7; color:#15803D; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #BBF7D0;"><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div style="background:#FEE2E2; color:#DC2626; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #FECACA;"><?php echo $error; ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Capacity</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                    <tr><td colspan="9" style="text-align:center; color: var(--admin-muted);">No projects found. Click "Add New Project" to create one.</td></tr>
                <?php else: ?>
                    <?php foreach ($projects as $p): ?>
                        <tr>
                            <td>
                                <img src="<?php echo sanitize(resolveAssetPath($p['hero_image'], '../')); ?>" alt="<?php echo sanitize($p['title']); ?>" style="width: 56px; height: 38px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td>
                                <strong><?php echo sanitize($p['title']); ?></strong><br>
                                <span style="font-size: 11px; color: var(--admin-muted);">/projects/detail.php?slug=<?php echo sanitize($p['slug']); ?></span>
                            </td>
                            <td><?php echo sanitize($p['category']); ?></td>
                            <td><?php echo sanitize($p['location']); ?></td>
                            <td><strong style="color:var(--admin-orange);"><?php echo formatCapacity($p['capacity_kw']); ?></strong></td>
                            <td><?php echo sanitize($p['installation_year'] ?: ($p['completion_date'] ? date('Y', strtotime($p['completion_date'])) : '—')); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                    <input type="hidden" name="action" value="toggle_publish">
                                    <button type="submit" class="admin-btn" style="padding:3px 9px; font-size:11px; border:none; cursor:pointer; background:<?php echo $p['is_published'] ? '#DCFCE7; color:#15803D;' : '#FEE2E2; color:#DC2626;'; ?>">
                                        <?php echo $p['is_published'] ? 'Published' : 'Draft'; ?>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                    <input type="hidden" name="action" value="toggle_featured">
                                    <button type="submit" class="admin-btn" style="padding:3px 9px; font-size:11px; border:none; cursor:pointer; background:<?php echo $p['is_featured'] ? '#FEF3C7; color:#92400E;' : '#F1F5F9; color:#64748B;'; ?>">
                                        <?php echo $p['is_featured'] ? '★ Featured' : 'Normal'; ?>
                                    </button>
                                </form>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="../projects/detail.php?slug=<?php echo urlencode($p['slug']); ?>" target="_blank" class="admin-btn admin-btn-outline" style="padding:3px 8px; font-size:11px;" title="View Public Page"><i data-lucide="eye" style="width:12px;"></i></a>
                                <a href="project-edit.php?id=<?php echo (int)$p['id']; ?>" class="admin-btn admin-btn-outline" style="padding:3px 8px; font-size:11px;"><i data-lucide="edit-3" style="width:12px;"></i> Edit</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this project and its gallery images?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="admin-btn admin-btn-danger" style="padding:3px 8px; font-size:11px; border:none; cursor:pointer;"><i data-lucide="trash-2" style="width:12px;"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
