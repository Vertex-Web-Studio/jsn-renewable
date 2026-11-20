<?php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$message = '';
$error = '';

// Handle Actions (Publish Toggle, Featured Toggle, Delete)
if (isset($_GET['action']) && isset($_GET['id']) && $db) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'toggle_publish') {
        $stmt = $db->prepare("UPDATE projects SET is_published = 1 - is_published WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Project publication status updated.";
    } elseif ($action === 'toggle_featured') {
        $stmt = $db->prepare("UPDATE projects SET is_featured = 1 - is_featured WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Project featured status updated.";
    } elseif ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Project deleted successfully.";
    }
}

// Fetch All Projects
$projects = [];
if ($db) {
    $projects = $db->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll();
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 24px; margin-bottom: 4px;">Projects CMS Management</h1>
        <p style="margin: 0; font-size: 13px;">Create, edit, publish and feature commissioned solar power projects.</p>
    </div>
    <div>
        <a href="project-edit.php" class="admin-btn admin-btn-primary"><i data-lucide="plus"></i> Add New Project</a>
    </div>
</div>

<?php if ($message): ?>
    <div style="background:#DCFCE7; color:#15803D; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #BBF7D0;"><?php echo $message; ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Hero Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Capacity</th>
                    <th>Published</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                    <tr><td colspan="8" style="text-align:center; color: var(--admin-muted);">No projects found. Click "Add New Project" to create one.</td></tr>
                <?php else: ?>
                    <?php foreach ($projects as $p): ?>
                        <tr>
                            <td>
                                <img src="<?php echo sanitize($p['hero_image']); ?>" style="width: 50px; height: 35px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td>
                                <strong><?php echo sanitize($p['title']); ?></strong><br>
                                <span style="font-size: 11px; color: var(--admin-muted);">Slug: <?php echo sanitize($p['slug']); ?></span>
                            </td>
                            <td><?php echo sanitize($p['category']); ?></td>
                            <td><?php echo sanitize($p['location']); ?></td>
                            <td><strong style="color:var(--admin-orange);"><?php echo number_format($p['capacity_kw'], 2); ?> kW</strong></td>
                            <td>
                                <a href="projects.php?action=toggle_publish&id=<?php echo $p['id']; ?>" class="admin-btn" style="padding:2px 8px; font-size:11px; background:<?php echo $p['is_published'] ? '#DCFCE7; color:#15803D;' : '#FEE2E2; color:#DC2626;'; ?>">
                                    <?php echo $p['is_published'] ? 'Published' : 'Draft'; ?>
                                </a>
                            </td>
                            <td>
                                <a href="projects.php?action=toggle_featured&id=<?php echo $p['id']; ?>" class="admin-btn" style="padding:2px 8px; font-size:11px; background:<?php echo $p['is_featured'] ? '#FEF3C7; color:#92400E;' : '#F1F5F9; color:#64748B;'; ?>">
                                    <?php echo $p['is_featured'] ? '★ Featured' : 'Normal'; ?>
                                </a>
                            </td>
                            <td>
                                <a href="project-edit.php?id=<?php echo $p['id']; ?>" class="admin-btn admin-btn-outline" style="padding:3px 8px; font-size:11px;"><i data-lucide="edit-3" style="width:12px;"></i> Edit</a>
                                <a href="projects.php?action=delete&id=<?php echo $p['id']; ?>" onclick="return confirm('Are you sure you want to delete this project?');" class="admin-btn admin-btn-danger" style="padding:3px 8px; font-size:11px;"><i data-lucide="trash-2" style="width:12px;"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
