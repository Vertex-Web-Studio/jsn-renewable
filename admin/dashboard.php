<?php
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Fetch Real Metrics
$totalEnquiries = 0;
$newEnquiries = 0;
$convertedEnquiries = 0;
$totalProjects = 0;
$publishedProjects = 0;
$recentEnquiries = [];
$recentProjects = [];

if ($db) {
    $totalEnquiries = $db->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
    $newEnquiries = $db->query("SELECT COUNT(*) FROM enquiries WHERE status = 'New'")->fetchColumn();
    $convertedEnquiries = $db->query("SELECT COUNT(*) FROM enquiries WHERE status = 'Converted'")->fetchColumn();
    $totalProjects = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    $publishedProjects = $db->query("SELECT COUNT(*) FROM projects WHERE is_published = 1")->fetchColumn();

    $recentEnquiries = $db->query("SELECT * FROM enquiries ORDER BY id DESC LIMIT 5")->fetchAll();
    $recentProjects = $db->query("SELECT * FROM projects ORDER BY id DESC LIMIT 5")->fetchAll();
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 24px; margin-bottom: 4px;">Dashboard Overview</h1>
        <p style="margin: 0; font-size: 13px;">Real-time database statistics and lead management summary.</p>
    </div>
    <div>
        <a href="project-edit.php" class="admin-btn admin-btn-primary"><i data-lucide="plus"></i> Add New Project</a>
    </div>
</div>

<!-- METRICS CARDS -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-label">Total Enquiries</div>
        <div class="metric-number"><?php echo number_format($totalEnquiries); ?></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">New Leads</div>
        <div class="metric-number" style="color: var(--admin-orange);"><?php echo number_format($newEnquiries); ?></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Converted Clients</div>
        <div class="metric-number" style="color: #15803D;"><?php echo number_format($convertedEnquiries); ?></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Published Projects</div>
        <div class="metric-number" style="color: var(--admin-blue);"><?php echo number_format($publishedProjects); ?> / <?php echo number_format($totalProjects); ?></div>
    </div>
</div>

<!-- RECENT ENQUIRIES -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
        <h3 style="margin:0; font-size:16px;">Recent Site Survey Enquiries</h3>
        <a href="enquiries.php" class="admin-btn admin-btn-outline" style="padding: 4px 10px; font-size: 12px;">View All Enquiries</a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentEnquiries)): ?>
                    <tr><td colspan="7" style="text-align:center; color: var(--admin-muted);">No enquiries found in database.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentEnquiries as $enq): ?>
                        <tr>
                            <td><?php echo date('M d, Y H:i', strtotime($enq['created_at'])); ?></td>
                            <td><strong><?php echo sanitize($enq['name']); ?></strong></td>
                            <td><a href="tel:<?php echo sanitize($enq['phone']); ?>" style="color:var(--admin-primary);"><?php echo sanitize($enq['phone']); ?></a></td>
                            <td><?php echo sanitize($enq['project_category']); ?></td>
                            <td><?php echo sanitize($enq['location']); ?></td>
                            <td><span class="status-badge badge-<?php echo strtolower($enq['status']); ?>"><?php echo sanitize($enq['status']); ?></span></td>
                            <td><a href="enquiries.php?id=<?php echo $enq['id']; ?>" class="admin-btn admin-btn-outline" style="padding:3px 8px; font-size:11px;">Manage Lead</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- RECENT PROJECTS -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
        <h3 style="margin:0; font-size:16px;">Recent Commissioned Projects</h3>
        <a href="projects.php" class="admin-btn admin-btn-outline" style="padding: 4px 10px; font-size: 12px;">View Portfolio CMS</a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentProjects)): ?>
                    <tr><td colspan="6" style="text-align:center; color: var(--admin-muted);">No projects found in database.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentProjects as $proj): ?>
                        <tr>
                            <td><strong><?php echo sanitize($proj['title']); ?></strong></td>
                            <td><?php echo sanitize($proj['category']); ?></td>
                            <td><?php echo sanitize($proj['location']); ?></td>
                            <td><strong style="color:var(--admin-orange);"><?php echo number_format($proj['capacity_kw'], 2); ?> kW</strong></td>
                            <td><?php echo $proj['is_published'] ? '<span style="color:#15803D; font-weight:bold;">Published</span>' : '<span style="color:#991B1B;">Draft</span>'; ?></td>
                            <td><a href="project-edit.php?id=<?php echo $proj['id']; ?>" class="admin-btn admin-btn-outline" style="padding:3px 8px; font-size:11px;">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
