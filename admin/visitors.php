<?php
require_once __DIR__ . '/includes/header.php';

$db = getDB();

$todayVisitors = 0;
$todayPageViews = 0;
$activeVisitors = 0;
$last7Days = 0;
$last30Days = 0;
$topPages = [];
$logs = [];

$userRole = $_SESSION['admin_role'] ?? 'Admin';
$isSuperAdmin = ($userRole === 'SuperAdmin');

if ($db) {
    try {
        // Today's Unique Visitors (deduplicated by session_id or ip_address)
        $todayVisitors = $db->query("SELECT COUNT(DISTINCT session_id) FROM visitor_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();

        // Today's Page Views
        $todayPageViews = $db->query("SELECT COUNT(*) FROM visitor_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();

        // Active Visitors (last 15 minutes)
        $activeVisitors = $db->query("SELECT COUNT(DISTINCT session_id) FROM visitor_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)")->fetchColumn();

        // Last 7 Days & 30 Days
        $last7Days = $db->query("SELECT COUNT(DISTINCT session_id) FROM visitor_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        $last30Days = $db->query("SELECT COUNT(DISTINCT session_id) FROM visitor_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

        // Top Visited Pages
        $topPages = $db->query("SELECT page_visited, COUNT(*) as page_hits FROM visitor_logs GROUP BY page_visited ORDER BY page_hits DESC LIMIT 5")->fetchAll();

        // Recent Logs
        $logs = $db->query("SELECT * FROM visitor_logs ORDER BY id DESC LIMIT 100")->fetchAll();
    } catch (Exception $e) {
        $logs = [];
    }
}

/**
 * Mask IP address for privacy unless user is SuperAdmin
 */
function maskIP($ip, $reveal = false) {
    if ($reveal) return $ip;
    $parts = explode('.', $ip);
    if (count($parts) === 4) {
        return $parts[0] . '.' . $parts[1] . '.x.x';
    }
    return 'xxx.xxx.xxx.xxx';
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 24px; margin-bottom: 4px;">Live Visitor Traffic Analytics</h1>
        <p style="margin: 0; font-size: 13px;">Server-side traffic tracking, deduplicated sessions, and live active visitors.</p>
    </div>
</div>

<!-- ANALYTICS CARDS -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-label">Live Active (Last 15m)</div>
        <div class="metric-number" style="color:var(--admin-orange);"><?php echo number_format($activeVisitors); ?></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Today's Visitors</div>
        <div class="metric-number"><?php echo number_format($todayVisitors); ?></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Today's Page Views</div>
        <div class="metric-number" style="color:var(--admin-blue);"><?php echo number_format($todayPageViews); ?></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Last 30 Days Visitors</div>
        <div class="metric-number" style="color:#15803D;"><?php echo number_format($last30Days); ?></div>
    </div>
</div>

<!-- TOP VISITED PAGES -->
<?php if (!empty($topPages)): ?>
<div class="admin-card">
    <h3 style="margin-top:0; font-size:16px; margin-bottom:14px; color:var(--admin-primary);">Most Visited Pages</h3>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
        <?php foreach ($topPages as $tp): ?>
            <div style="background:#F8FAFC; padding:12px; border-radius:6px; border:1px solid var(--admin-border);">
                <code><?php echo sanitize($tp['page_visited']); ?></code>
                <div style="font-weight:bold; font-size:18px; color:var(--admin-orange); margin-top:4px;"><?php echo number_format($tp['page_hits']); ?> views</div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- RECENT TRAFFIC LOGS TABLE -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
        <h3 style="margin:0; font-size:16px; color:var(--admin-primary);">Latest Visitor Telemetry</h3>
        <span style="font-size:11px; color:var(--admin-muted);"><?php echo $isSuperAdmin ? 'Full IP Access (SuperAdmin)' : 'IP Masked for Privacy'; ?></span>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>IP Address</th>
                    <th>Page Visited</th>
                    <th>Device</th>
                    <th>User-Agent</th>
                </tr>
            </thead>
            <tbody id="ipLogsBody">
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" style="text-align:center; color: var(--admin-muted);">No IP logs recorded in database yet. Client-side IP tracking active.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><?php echo date('M d, Y H:i:s', strtotime($l['created_at'])); ?></td>
                            <td><strong style="color:var(--admin-primary); font-family:monospace; font-size:14px;"><?php echo sanitize(maskIP($l['ip_address'], $isSuperAdmin)); ?></strong></td>
                            <td><code><?php echo sanitize($l['page_visited']); ?></code></td>
                            <td><span class="status-badge badge-new"><?php echo sanitize($l['device_type'] ?? 'Desktop'); ?></span></td>
                            <td style="font-size:11px; color:var(--admin-muted);"><?php echo sanitize(substr($l['user_agent'], 0, 60)); ?>...</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Load local visitor logs if database table is empty
    const localLogs = JSON.parse(localStorage.getItem('jsn_visitor_logs') || '[]');
    if (localLogs.length > 0 && document.querySelectorAll('#ipLogsBody tr').length <= 1) {
        const body = document.getElementById('ipLogsBody');
        body.innerHTML = '';
        localLogs.forEach((log, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${new Date(log.timestamp).toLocaleString()}</td>
                <td><strong style="color:var(--admin-primary); font-family:monospace; font-size:14px;">${log.ip}</strong></td>
                <td><code>${log.page}</code></td>
                <td><span class="status-badge badge-new">Client Session</span></td>
                <td style="font-size:11px; color:var(--admin-muted);">${log.userAgent.substring(0, 60)}...</td>
            `;
            body.appendChild(tr);
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
