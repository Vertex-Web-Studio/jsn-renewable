<?php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$message = '';
$error = '';

$id = (int)($_GET['id'] ?? 0);
$isEditing = ($id > 0);

$project = [
    'title' => '',
    'slug' => '',
    'category' => 'Commercial Solar',
    'location' => '',
    'client_name' => '',
    'system_type' => 'On-Grid Solar',
    'capacity_kw' => 0.00,
    'completion_date' => date('Y-m-d'),
    'installation_year' => (int)date('Y'),
    'description' => '',
    'hero_image' => '',
    'is_published' => 1,
    'is_featured' => 0,
    'seo_title' => '',
    'seo_description' => ''
];

$galleryImages = [];

if ($isEditing && $db) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if ($existing) {
        $project = array_merge($project, $existing);
        $imgStmt = $db->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
        $imgStmt->execute([$id]);
        $galleryImages = $imgStmt->fetchAll();
    } else {
        $error = "Project not found.";
        $isEditing = false;
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Invalid CSRF token.';
    } else {
        $subAction = $_POST['sub_action'] ?? 'save_project';

        // Delete individual gallery image
        if ($subAction === 'delete_gallery_image' && $isEditing) {
            $imgId = (int)($_POST['image_id'] ?? 0);
            $gStmt = $db->prepare("SELECT * FROM project_images WHERE id = ? AND project_id = ?");
            $gStmt->execute([$imgId, $id]);
            $imgRow = $gStmt->fetch();
            if ($imgRow) {
                deleteUploadedFile($imgRow['image_url']);
                $delStmt = $db->prepare("DELETE FROM project_images WHERE id = ? AND project_id = ?");
                $delStmt->execute([$imgId, $id]);
                $message = 'Gallery image deleted.';
            }
        }
        // Replace individual gallery image
        elseif ($subAction === 'replace_gallery_image' && $isEditing) {
            $imgId = (int)($_POST['image_id'] ?? 0);
            $gStmt = $db->prepare("SELECT * FROM project_images WHERE id = ? AND project_id = ?");
            $gStmt->execute([$imgId, $id]);
            $imgRow = $gStmt->fetch();
            if ($imgRow && !empty($_FILES['replace_gallery_file']['name'])) {
                $rRes = uploadImage($_FILES['replace_gallery_file'], 'gallery');
                if ($rRes['success']) {
                    deleteUploadedFile($imgRow['image_url']);
                    $updStmt = $db->prepare("UPDATE project_images SET image_url = ? WHERE id = ? AND project_id = ?");
                    $updStmt->execute([$rRes['url'], $imgId, $id]);
                    $message = 'Gallery image replaced successfully.';
                } else {
                    $error = 'Replace failed: ' . $rRes['error'];
                }
            }
        }
        // Delete cover image
        elseif ($subAction === 'delete_cover_image' && $isEditing) {
            if (!empty($project['hero_image'])) {
                deleteUploadedFile($project['hero_image']);
            }
            $updStmt = $db->prepare("UPDATE projects SET hero_image = '' WHERE id = ?");
            $updStmt->execute([$id]);
            $project['hero_image'] = '';
            $message = 'Cover image removed.';
        }
        // Save Project Details + Cover + Gallery
        else {
            $title = sanitize($_POST['title'] ?? '');
            $slugInput = sanitize($_POST['slug'] ?? '');
            $slug = empty($slugInput) ? slugify($title) : slugify($slugInput);

            $category = sanitize($_POST['category'] ?? 'Commercial Solar');
            $location = sanitize($_POST['location'] ?? '');
            $client_name = sanitize($_POST['client_name'] ?? '');
            $system_type = sanitize($_POST['system_type'] ?? 'On-Grid Solar');
            $capacity_kw = max(0, (float)($_POST['capacity_kw'] ?? 0));
            $completion_date = sanitize($_POST['completion_date'] ?? date('Y-m-d')) ?: null;
            $installation_year = (int)($_POST['installation_year'] ?? ($completion_date ? date('Y', strtotime($completion_date)) : date('Y')));
            $description = sanitize($_POST['description'] ?? '');
            $is_published = isset($_POST['is_published']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $seo_title = sanitize($_POST['seo_title'] ?? '');
            $seo_description = sanitize($_POST['seo_description'] ?? '');

            $hero_image = $project['hero_image'];

            if (!empty($_FILES['hero_file']['name'])) {
                $uploadRes = uploadImage($_FILES['hero_file'], 'projects');
                if ($uploadRes['success']) {
                    if (!empty($hero_image)) {
                        deleteUploadedFile($hero_image);
                    }
                    $hero_image = $uploadRes['url'];
                } else {
                    $error = 'Cover Image Upload Failed: ' . $uploadRes['error'];
                }
            } elseif (isset($_POST['hero_url']) && trim($_POST['hero_url']) !== '') {
                $hero_image = sanitize($_POST['hero_url']);
            }

            if (empty($title) || empty($location)) {
                $error = 'Please fill in the project title and location.';
            }

            if (empty($error)) {
                // Ensure unique slug
                $slugCheckSql = $isEditing ? "SELECT COUNT(*) FROM projects WHERE slug = ? AND id != ?" : "SELECT COUNT(*) FROM projects WHERE slug = ?";
                $slugStmt = $db->prepare($slugCheckSql);
                $isEditing ? $slugStmt->execute([$slug, $id]) : $slugStmt->execute([$slug]);
                if ((int)$slugStmt->fetchColumn() > 0) {
                    $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
                }

                if ($isEditing) {
                    $stmt = $db->prepare("
                        UPDATE projects SET
                        title = :title, slug = :slug, category = :category, location = :location,
                        client_name = :client_name, system_type = :system_type, capacity_kw = :capacity_kw,
                        completion_date = :completion_date, installation_year = :installation_year,
                        description = :description, hero_image = :hero_image,
                        is_published = :is_published, is_featured = :is_featured, seo_title = :seo_title,
                        seo_description = :seo_description, updated_at = NOW()
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':title' => $title, ':slug' => $slug, ':category' => $category, ':location' => $location,
                        ':client_name' => $client_name, ':system_type' => $system_type, ':capacity_kw' => $capacity_kw,
                        ':completion_date' => $completion_date, ':installation_year' => $installation_year,
                        ':description' => $description, ':hero_image' => $hero_image,
                        ':is_published' => $is_published, ':is_featured' => $is_featured, ':seo_title' => $seo_title,
                        ':seo_description' => $seo_description, ':id' => $id
                    ]);
                    $message = 'Project updated successfully.';
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO projects
                        (title, slug, category, location, client_name, system_type, capacity_kw, completion_date, installation_year, description, hero_image, is_published, is_featured, seo_title, seo_description, created_at)
                        VALUES
                        (:title, :slug, :category, :location, :client_name, :system_type, :capacity_kw, :completion_date, :installation_year, :description, :hero_image, :is_published, :is_featured, :seo_title, :seo_description, NOW())
                    ");
                    $stmt->execute([
                        ':title' => $title, ':slug' => $slug, ':category' => $category, ':location' => $location,
                        ':client_name' => $client_name, ':system_type' => $system_type, ':capacity_kw' => $capacity_kw,
                        ':completion_date' => $completion_date, ':installation_year' => $installation_year,
                        ':description' => $description, ':hero_image' => $hero_image,
                        ':is_published' => $is_published, ':is_featured' => $is_featured, ':seo_title' => $seo_title,
                        ':seo_description' => $seo_description
                    ]);
                    $id = (int)$db->lastInsertId();
                    $isEditing = true;
                    $message = 'Project created and saved successfully.';
                }

                // Upload multiple gallery images
                if (!empty($_FILES['gallery_files']['name'][0])) {
                    foreach ($_FILES['gallery_files']['name'] as $idx => $name) {
                        if ($_FILES['gallery_files']['error'][$idx] === UPLOAD_ERR_OK) {
                            $singleFile = [
                                'name' => $_FILES['gallery_files']['name'][$idx],
                                'type' => $_FILES['gallery_files']['type'][$idx],
                                'tmp_name' => $_FILES['gallery_files']['tmp_name'][$idx],
                                'error' => $_FILES['gallery_files']['error'][$idx],
                                'size' => $_FILES['gallery_files']['size'][$idx]
                            ];
                            $gRes = uploadImage($singleFile, 'gallery');
                            if ($gRes['success']) {
                                $insG = $db->prepare("INSERT INTO project_images (project_id, image_url, alt_text, sort_order) VALUES (?, ?, ?, ?)");
                                $insG->execute([$id, $gRes['url'], $title, $idx]);
                            }
                        }
                    }
                }
            }
        }

        // Reload latest values
        if ($isEditing) {
            $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            $project = $stmt->fetch() ?: $project;

            $imgStmt = $db->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
            $imgStmt->execute([$id]);
            $galleryImages = $imgStmt->fetchAll();
        }
    }
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-size: 24px; margin-bottom: 4px;"><?php echo $isEditing ? 'Edit Project' : 'Create New Solar Project'; ?></h1>
        <p style="margin: 0; font-size: 13px;">Manage project specifications, cover image, gallery photos, and public publishing status.</p>
    </div>
    <div style="display:flex; gap:10px;">
        <?php if ($isEditing && $project['is_published']): ?>
            <a href="../projects/detail.php?slug=<?php echo urlencode($project['slug']); ?>" target="_blank" class="admin-btn admin-btn-outline"><i data-lucide="eye"></i> View Public Page</a>
        <?php endif; ?>
        <a href="projects.php" class="admin-btn admin-btn-outline"><i data-lucide="arrow-left"></i> Back to Projects</a>
    </div>
</div>

<?php if ($message): ?>
    <div style="background:#DCFCE7; color:#15803D; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #BBF7D0;"><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div style="background:#FEE2E2; color:#DC2626; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #FECACA;"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
<input type="hidden" name="sub_action" value="save_project">

<div style="display:grid; grid-template-columns: 8fr 4fr; gap: 24px;">
    <!-- LEFT COLUMN: MAIN FIELDS -->
    <div class="admin-card">
        <h3 style="font-size:16px; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:8px;">1. Project Details</h3>

        <div class="form-group">
            <label>Project Title *</label>
            <input type="text" name="title" class="form-control" value="<?php echo sanitize($project['title']); ?>" required>
        </div>

        <div class="form-group">
            <label>URL Slug (Leave blank to auto-generate)</label>
            <input type="text" name="slug" class="form-control" value="<?php echo sanitize($project['slug']); ?>">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Category *</label>
                <select name="category" class="form-control">
                    <?php
                    $cats = ['Commercial Solar', 'Industrial Solar Sheds', 'PM-KUSUM Ground Solar', 'Residential Solar', 'Housing Societies', 'Government & PSU', 'Hospitals', 'Schools & Institutions'];
                    foreach ($cats as $c) {
                        $sel = ($project['category'] === $c) ? 'selected' : '';
                        echo "<option value=\"" . sanitize($c) . "\" $sel>" . sanitize($c) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>System Type</label>
                <select name="system_type" class="form-control">
                    <?php
                    $sysList = ['On-Grid Solar', 'Off-Grid Solar', 'Hybrid Solar', 'Ground-Mounted Solar'];
                    foreach ($sysList as $s) {
                        $sel = ($project['system_type'] === $s) ? 'selected' : '';
                        echo "<option value=\"" . sanitize($s) . "\" $sel>" . sanitize($s) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Location (City, State) *</label>
                <input type="text" name="location" class="form-control" value="<?php echo sanitize($project['location']); ?>" required>
            </div>
            <div class="form-group">
                <label>Client / Entity Name</label>
                <input type="text" name="client_name" class="form-control" value="<?php echo sanitize($project['client_name']); ?>">
            </div>
            <div class="form-group">
                <label>System Capacity (kW) *</label>
                <input type="number" step="0.01" min="0" name="capacity_kw" class="form-control" value="<?php echo (float)$project['capacity_kw']; ?>" required>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Installation Year</label>
                <input type="number" min="2010" max="2035" name="installation_year" class="form-control" value="<?php echo (int)($project['installation_year'] ?: date('Y')); ?>">
            </div>
            <div class="form-group">
                <label>Completion Date</label>
                <input type="date" name="completion_date" class="form-control" value="<?php echo sanitize($project['completion_date']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Project Description / Case Study Details</label>
            <textarea name="description" rows="5" class="form-control"><?php echo sanitize($project['description']); ?></textarea>
        </div>

        <h3 style="font-size:16px; margin-top:24px; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:8px;">2. Search Engine Optimization (SEO)</h3>
        <div class="form-group">
            <label>SEO Title</label>
            <input type="text" name="seo_title" class="form-control" value="<?php echo sanitize($project['seo_title']); ?>">
        </div>
        <div class="form-group">
            <label>SEO Meta Description</label>
            <textarea name="seo_description" rows="2" class="form-control"><?php echo sanitize($project['seo_description']); ?></textarea>
        </div>
    </div>

    <!-- RIGHT COLUMN: MEDIA & PUBLISHING -->
    <div>
        <div class="admin-card">
            <h3 style="font-size:16px; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:8px;">Publishing Options</h3>

            <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                <input type="checkbox" name="is_published" value="1" id="pubCheck" <?php echo $project['is_published'] ? 'checked' : ''; ?> style="width:auto; margin:0;">
                <label for="pubCheck" style="margin:0; cursor:pointer;">Publish Publicly on Website</label>
            </div>

            <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                <input type="checkbox" name="is_featured" value="1" id="featCheck" <?php echo $project['is_featured'] ? 'checked' : ''; ?> style="width:auto; margin:0;">
                <label for="featCheck" style="margin:0; cursor:pointer;">Feature on Homepage</label>
            </div>

            <button type="submit" class="admin-btn admin-btn-primary" style="width:100%; margin-top:12px;">
                <i data-lucide="save"></i> <?php echo $isEditing ? 'Save Project Changes' : 'Create & Publish Project'; ?>
            </button>
        </div>

        <div class="admin-card">
            <h3 style="font-size:16px; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:8px;">Main Cover Image</h3>

            <?php if (!empty($project['hero_image'])): ?>
                <div style="margin-bottom:12px; position:relative;">
                    <img src="<?php echo sanitize(resolveAssetPath($project['hero_image'], '../')); ?>" alt="Cover Preview" style="width:100%; height:150px; object-fit:cover; border-radius:6px; border:1px solid var(--admin-border);">
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Upload / Replace Cover Image (JPG, PNG, WEBP)</label>
                <input type="file" name="hero_file" accept="image/jpeg,image/png,image/webp" class="form-control">
            </div>

            <div class="form-group">
                <label>Or Existing Image Path / URL</label>
                <input type="text" name="hero_url" class="form-control" value="<?php echo sanitize($project['hero_image']); ?>" placeholder="assets/projects/...">
            </div>
        </div>

        <div class="admin-card">
            <h3 style="font-size:16px; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:8px;">Project Gallery Images</h3>
            <div class="form-group">
                <label>Upload Multiple Gallery Photos</label>
                <input type="file" name="gallery_files[]" accept="image/jpeg,image/png,image/webp" multiple class="form-control">
            </div>
        </div>
    </div>
</div>

</form>

<?php if ($isEditing && !empty($project['hero_image'])): ?>
<form method="POST" style="margin-top:-8px; margin-bottom:20px;">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="sub_action" value="delete_cover_image">
    <button type="submit" class="admin-btn admin-btn-danger" style="font-size:12px;" onclick="return confirm('Remove current cover image?');">
        <i data-lucide="trash-2" style="width:14px;"></i> Remove Cover Image
    </button>
</form>
<?php endif; ?>

<?php if ($isEditing && !empty($galleryImages)): ?>
<div class="admin-card" style="margin-top:20px;">
    <h3 style="font-size:16px; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:8px;">Manage Existing Gallery Images (<?php echo count($galleryImages); ?>)</h3>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:16px;">
        <?php foreach ($galleryImages as $gImg): ?>
            <div style="border:1px solid var(--admin-border); border-radius:8px; padding:10px; background:#F8FAFC;">
                <img src="<?php echo sanitize(resolveAssetPath($gImg['image_url'], '../')); ?>" alt="Gallery Image" style="width:100%; height:130px; object-fit:cover; border-radius:6px; margin-bottom:8px;">
                <form method="POST" enctype="multipart/form-data" style="margin-bottom:6px;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="sub_action" value="replace_gallery_image">
                    <input type="hidden" name="image_id" value="<?php echo (int)$gImg['id']; ?>">
                    <input type="file" name="replace_gallery_file" accept="image/jpeg,image/png,image/webp" required style="font-size:11px; margin-bottom:6px; width:100%;">
                    <button type="submit" class="admin-btn admin-btn-outline" style="width:100%; padding:4px 8px; font-size:11px;">Replace Photo</button>
                </form>
                <form method="POST" onsubmit="return confirm('Delete this gallery photo?');">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="sub_action" value="delete_gallery_image">
                    <input type="hidden" name="image_id" value="<?php echo (int)$gImg['id']; ?>">
                    <button type="submit" class="admin-btn admin-btn-danger" style="width:100%; padding:4px 8px; font-size:11px; border:none; cursor:pointer;">Delete Photo</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
