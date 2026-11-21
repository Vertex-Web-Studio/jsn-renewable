<?php
require_once __DIR__ . '/../../functions.php';
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSN Admin CMS - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/css/admin.css">
</head>
<body>

<div class="admin-wrapper">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <div class="admin-brand-title">JSN <span>CMS</span></div>
            <div class="admin-brand-tag"><?php echo SITE_TAGLINE; ?></div>
        </div>

        <ul class="admin-nav">
            <li class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a>
            </li>
            <li class="<?php echo in_array($currentPage, ['projects.php', 'project-edit.php']) ? 'active' : ''; ?>">
                <a href="projects.php"><i data-lucide="folder-kanban"></i> Projects CMS</a>
            </li>
            <li class="<?php echo $currentPage === 'enquiries.php' ? 'active' : ''; ?>">
                <a href="enquiries.php"><i data-lucide="inbox"></i> Leads & Enquiries</a>
            </li>
            <li class="<?php echo $currentPage === 'visitors.php' ? 'active' : ''; ?>">
                <a href="visitors.php"><i data-lucide="activity"></i> Visitor IP Tracker</a>
            </li>
            <li class="<?php echo $currentPage === 'media.php' ? 'active' : ''; ?>">
                <a href="media.php"><i data-lucide="image"></i> Media Library</a>
            </li>
            <li class="<?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php"><i data-lucide="settings"></i> Site Settings</a>
            </li>
            <li class="<?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
                <a href="users.php"><i data-lucide="users"></i> Admin Users</a>
            </li>
            <li>
                <a href="<?php echo SITE_URL; ?>/index.php" target="_blank"><i data-lucide="external-link"></i> View Live Site</a>
            </li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <strong>JSN Renewable Energy Management System</strong>
            </div>
            <div class="admin-user-info">
                <span>Logged in as: <strong><?php echo sanitize($_SESSION['admin_user'] ?? 'Admin'); ?></strong> (<?php echo sanitize($_SESSION['admin_role'] ?? 'SuperAdmin'); ?>)</span>
                <a href="logout.php" class="admin-btn admin-btn-outline" style="padding: 4px 10px; font-size: 12px;"><i data-lucide="log-out" style="width:14px;"></i> Logout</a>
            </div>
        </header>
        <main class="admin-content">
