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
    $title = sanitize($_POST['title'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = slugify($title);
    } else {
        $slug = slugify($slug);
    }

    $category = sanitize($_POST['category'] ?? 'Commercial Solar');
    $location = sanitize($_POST['location'] ?? '');
    $client_name = sanitize($_POST['client_name'] ?? '');
    $system_type = sanitize($_POST['system_type'] ?? 'On-Grid Solar');
    $capacity_kw = (float)($_POST['capacity_kw'] ?? 0);
    $completion_date = sanitize($_POST['completion_date'] ?? date('Y-m-d'));
    $description = sanitize($_POST['description'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $seo_title = sanitize($_POST['seo_title'] ?? '');
    $seo_description = sanitize($_POST['seo_description'] ?? '');

    $hero_image = $project['hero_image'];

    // Handle Hero Image Upload
    if (!empty($_FILES['hero_file']['name'])) {
        $uploadRes = uploadImage($_FILES['hero_file'], 'projects');
        if ($uploadRes['success']) {
            $hero_image = $uploadRes['url'];
        } else {
            $error = 'Hero Image Upload Failed: ' . $uploadRes['error'];
        }
    } elseif (!empty($_POST['hero_url'])) {
        $hero_image = sanitize($_POST['hero_url']);
    }

    if (empty($title) || empty($location)) {
        $error = "Please fill in the project title and location.";
    }

    if (empty($error)) {
        if ($isEditing) {
            $stmt = $db->prepare("
                UPDATE projects SET
                title = :title, slug = :slug, category = :category, location = :location,
                client_name = :client_name, system_type = :system_type, capacity_kw = :capacity_kw,
                completion_date = :completion_date, description = :description, hero_image = :hero_image,
                is_published = :is_published, is_featured = :is_featured, seo_title = :seo_title,
                seo_description = :seo_description, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ':title' => $title, ':slug' => $slug, ':category' => $category, ':location' => $location,
                ':client_name' => $client_name, ':system_type' => $system_type, ':capacity_kw' => $capacity_kw,
                ':completion_date' => $completion_date, ':description' => $description, ':hero_image' => $hero_image,
                ':is_published' => $is_published, ':is_featured' => $is_featured, ':seo_title' => $seo_title,
                ':seo_description' => $seo_description, ':id' => $id
            ]);
            $message = "Project updated successfully.";
        } else {
            $stmt = $db->prepare("
                INSERT INTO projects
                (title, slug, category, location, client_name, system_type, capacity_kw, completion_date, description, hero_image, is_published, is_featured, seo_title, seo_description, created_at)
                VALUES
                (:title, :slug, :category, :location, :client_name, :system_type, :capacity_kw, :completion_date, :description, :hero_image, :is_published, :is_featured, :seo_title, :seo_description, NOW())
            ");
            $stmt->execute([
                ':title' => $title, ':slug' => $slug, ':category' => $category, ':location' => $location,
                ':client_name' => $client_name, ':system_type' => $system_type, ':capacity_kw' => $capacity_kw,
                ':completion_date' => $completion_date, ':description' => $description, ':hero_image' => $hero_image,
                ':is_published' => $is_published, ':is_featured' => $is_featured, ':seo_title' => $seo_title,
                ':seo_description' => $seo_description
            ]);
            $id = $db->lastInsertId();
            $isEditing = true;
            $message = "Project created successfully.";
        }

        // Handle Gallery Image Uploads
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
                        $insG = $db->prepare("INSERT INTO project_images (project_id, image_url, sort_order) VALUES (?, ?, ?)");
                        $insG->execute([$id, $gRes['url'], $idx]);
                    }
                }
            }
        }

        // Reload latest values
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch();

        $imgStmt = $db->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
        $imgStmt->execute([$id]);
        $galleryImages = $imgStmt->fetchAll();
    }
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 24px; margin-bottom: 4px;"><?php echo $isEditing ? 'Edit Project' : 'Create New Solar Project'; ?></h1>
        <p style="margin: 0; font-size: 13px;">Manage project specs, hero image, gallery, and search engine optimization.</p>
    </div>
    <div>
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
                    $cats = ['Commercial Solar', 'Industrial Solar Sheds', 'PM-KUSUM Ground Solar', 'Housing Societies', 'Government & PSU', 'Hospitals', 'Schools & Institutions'];
                    foreach ($cats as $c) {
                        $sel = ($project['category'] === $c) ? 'selected' : '';
                        echo "<option value='$c' $sel>$c</option>";
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
                        echo "<option value='$s' $sel>$s</option>";
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
                <label>Installed Capacity (kW) *</label>
                <input type="number" step="0.01" name="capacity_kw" class="form-control" value="<?php echo $project['capacity_kw']; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Project Completion Date</label>
            <input type="date" name="completion_date" class="form-control" value="<?php echo $project['completion_date']; ?>">
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

            <div class="form-group" style="display:flex; items-center; gap:10px;">
                <input type="checkbox" name="is_published" value="1" id="pubCheck" <?php echo $project['is_published'] ? 'checked' : ''; ?> style="width:auto; margin:0;">
                <label for="pubCheck" style="margin:0; cursor:pointer;">Publish Publicly on Website</label>
            </div>

            <div class="form-group" style="display:flex; items-center; gap:10px;">
                <input type="checkbox" name="is_featured" value="1" id="featCheck" <?php echo $project['is_featured'] ? 'checked' : ''; ?> style="width:auto; margin:0;">
                <label for="featCheck" style="margin:0; cursor:pointer;">Feature on Homepage</label>
            </div>

            <button type="submit" class="admin-btn admin-btn-primary" style="width:100%; margin-top:12px;">
                <i data-lucide="save"></i> <?php echo $isEditing ? 'Save Changes' : 'Publish Project'; ?>
            </button>
        </div>

        <div class="admin-card">
            <h3 style="font-size:16px; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:8px;">Hero Image</h3>

            <?php if (!empty($project['hero_image'])): ?>
                <div style="margin-bottom:12px;">
                    <img src="<?php echo sanitize($project['hero_image']); ?>" style="width:100%; height:140px; object-fit:cover; border-radius:6px;">
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Upload New Hero File</label>
                <input type="file" name="hero_file" accept="image/*" class="form-control">
            </div>

            <div class="form-group">
                <label>Or Image URL</label>
                <input type="text" name="hero_url" class="form-control" value="<?php echo sanitize($project['hero_image']); ?>" placeholder="https://...">
            </div>
        </div>

        <?php if ($isEditing): ?>
            <div class="admin-card">
                <h3 style="font-size:16px; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:8px;">Gallery Images</h3>

                <div class="form-group">
                    <label>Upload Gallery Images</label>
                    <input type="file" name="gallery_files[]" accept="image/*" multiple class="form-control">
                </div>

                <?php if (!empty($galleryImages)): ?>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:12px;">
                        <?php foreach ($galleryImages as $gImg): ?>
                            <div style="position:relative;">
                                <img src="<?php echo sanitize($gImg['image_url']); ?>" style="width:100%; height:70px; object-fit:cover; border-radius:4px;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
