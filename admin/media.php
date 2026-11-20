<?php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$message = '';
$error = '';

// Handle Image Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['media_file']['name'])) {
    $res = uploadImage($_FILES['media_file'], 'media_library');
    if ($res['success']) {
        if ($db) {
            $stmt = $db->prepare("INSERT INTO media (filename, file_url, mime_type, file_size, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$res['filename'], $res['url'], 'image', $_FILES['media_file']['size']]);
        }
        $message = "Image uploaded successfully.";
    } else {
        $error = $res['error'];
    }
}

// Handle Delete
if (isset($_GET['delete']) && $db) {
    $mediaId = (int)$_GET['delete'];
    $stmt = $db->prepare("SELECT * FROM media WHERE id = ?");
    $stmt->execute([$mediaId]);
    $media = $stmt->fetch();
    if ($media) {
        if (file_exists($media['filepath'])) {
            @unlink($media['filepath']);
        }
        $db->prepare("DELETE FROM media WHERE id = ?")->execute([$mediaId]);
        $message = "Media deleted.";
    }
}

// Fetch Media
$mediaList = [];
if ($db) {
    $mediaList = $db->query("SELECT * FROM media ORDER BY id DESC")->fetchAll();
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 24px; margin-bottom: 4px;">Media Library</h1>
        <p style="margin: 0; font-size: 13px;">Centralized asset management for uploaded project photos and website graphics.</p>
    </div>
</div>

<?php if ($message): ?>
    <div style="background:#DCFCE7; color:#15803D; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #BBF7D0;"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background:#FEE2E2; color:#DC2626; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #FECACA;"><?php echo $error; ?></div>
<?php endif; ?>

<div class="admin-card">
    <h3 style="font-size:16px; margin-bottom:14px;">Upload New Image</h3>
    <form method="POST" enctype="multipart/form-data" style="display:flex; gap:12px; align-items:center;">
        <input type="file" name="media_file" accept="image/*" class="form-control" style="max-width:350px;" required>
        <button type="submit" class="admin-btn admin-btn-primary"><i data-lucide="upload"></i> Upload to Media Library</button>
    </form>
</div>

<div class="admin-card">
    <h3 style="font-size:16px; margin-bottom:16px;">Uploaded Assets</h3>

    <?php if (empty($mediaList)): ?>
        <p style="color:var(--admin-muted); font-size:13px;">No media files uploaded yet in the database library.</p>
    <?php else: ?>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap:16px;">
            <?php foreach ($mediaList as $m): ?>
                <div style="border:1px solid var(--admin-border); border-radius:6px; overflow:hidden; background:#F8FAFC;">
                    <img src="<?php echo sanitize($m['file_url']); ?>" style="width:100%; height:120px; object-fit:cover;">
                    <div style="padding:10px; font-size:11px;">
                        <div style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-weight:bold;"><?php echo sanitize($m['filename']); ?></div>
                        <div style="color:var(--admin-muted); margin-top:2px;"><?php echo date('M d, Y', strtotime($m['created_at'])); ?></div>
                        <div style="margin-top:8px; display:flex; justify-content:space-between; align-items:center;">
                            <a href="<?php echo sanitize($m['file_url']); ?>" target="_blank" style="color:var(--admin-primary); text-decoration:none;">View</a>
                            <a href="media.php?delete=<?php echo $m['id']; ?>" onclick="return confirm('Delete this image?')" style="color:#DC2626; text-decoration:none;">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
