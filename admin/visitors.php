<?php
require_once __DIR__ . '/../functions.php';
requireLogin();

$db = getDB();

// Lightweight AJAX endpoint for live active visitor polling
if (isset($_GET['ajax']) && $_GET['ajax'] === 'live') {
    header('Content-Type: application/json');
    $res = ['active' => 0, 'todayVisitors' => 0, 'todayViews' => 0];
    if ($db) {
        try {
            $res['active'] = (int)$db->query("SELECT COUNT(DISTINCT session_id) FROM visitor_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)")->fetchColumn();
            $res['todayVisitors'] = (int)$db->query("SELECT COUNT(DISTINCT session_id) FROM visitor_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
            $res['todayViews'] = (int)$db->query("SELECT COUNT(*) FROM visitor_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        } catch (Exception $e) {}
    }
    echo json_encode($res);
    exit;
}

require_once __DIR__ . '/includes/header.php';

$activeVisitors = 0;
$todayVisitors = 0;
$todayPageViews = 0;
$yesterdayVisitors = 0;
$last7Days = 0;
$last30Days = 0;
$monthlyVisitors = 0;
$topPages = [];
$dailyTrend = [];
$logs = [];

$userRole = $_SESSION['admin_role'] ?? 'Admin';
$isSuperAdmin = ($userRole === 'SuperAdmin');

if ($db) {
    try {
        $activeVisitors = (int)$db->query("SELECT COUNT(DISTINCT session_id) FROM visitor_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)")->fetchColumn();
        $todayVisitors = (int)$db->query("SELECT COUNT(DISTINCT session_id) FROM visitor_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $todayPageViews = (int)$db->query("SELECT COUNT(*) FROM visitor_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $yesterdayVisitors = (int)$db->query("SELECT COUNT(DISTINCT session_id) FROM visitor_logs WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)")->fetchColumn();
        $last7Days = (int)$db->query("SELECT COUNT(DISTINCT session_id) FROM visitor_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
        $last30Days = (int)$db->query("SELECT COUNT(DISTINCT session_id) FROM visitor_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
        $monthlyVisitors = (int)$db->query("SELECT COUNT(DISTINCT session_id) FROM visitor_logs WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn();

        $topPages = $db->query("SELECT page_visited, COUNT(*) as page_hits, COUNT(DISTINCT session_id) as unique_hits FROM visitor_logs GROUP BY page_visited ORDER BY page_hits DESC LIMIT 6")->fetchAll();

        $dailyTrend = $db->query("
            SELECT DATE(created_at) as visit_day, COUNT(DISTINCT session_id) as unique_visitors, COUNT(*) as page_views
            FROM visitor_logs
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(created_at)
            ORDER BY visit_day ASC
        ")->fetchAll();

        $logs = $db->query("SELECT * FROM visitor_logs ORDER BY id DESC LIMIT 100")->fetchAll();
    } catch (Exception $e) {
        $logs = [];
    }
}

$maxTrend = 1;
foreach ($dailyTrend as $d) {
    if ((int)$d['page_views'] > $maxTrend) $maxTrend = (int)$d['page_views'];
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-size: 24px; margin-bottom: 4px;">Live Website Visitor Analytics</h1>
        <p style="margin: 0; font-size: 13px;">Server-side deduplicated traffic tracking, page popularity, and device telemetry.</p>
    </div>
    <div style="font-size:12px; color:#15803D; background:#DCFCE7; padding:6px 12px; border-radius:20px; border:1px solid #BBF7D0; font-weight:600;">
        ● Live Polling Active (30s)
    </div>
</div>

<!-- METRICS GRID -->
<div class="metrics-grid" style="grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));">
    <div class="metric-card">
        <div class="metric-label">Current Active (15m)</div>
        <div id="liveActiveCount" class="metric-number" style="color:var(--admin-orange);"><?php echo number_format($activeVisitors); ?></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Today's Visitors</div>
        <div id="liveTodayVisitors" class="metric-number"><?php echo number_format($todayVisitors); ?></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Today's Page Views</div>
        <div id="liveTodayViews" class="metric-number" style="color:var(--admin-blue);"><?php echo number_format($todayPageViews); ?></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Yesterday's Visitors</div>
        <div class="metric-number"><?php echo number_format($yesterdayVisitors); ?></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Last 7 Days</div>
        <div class="metric-number"><?php echo number_format($last7Days); ?></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Last 30 Days</div>
        <div class="metric-number" style="color:#15803D;"><?php echo number_format($last30Days); ?></div>
    </div>
    <div class="metric-card">
        <div class="metric-label">This Month</div>
        <div class="metric-number"><?php echo number_format($monthlyVisitors); ?></div>
    </div>
</div>

<!-- 7-DAY VISITOR CHART -->
<div class="admin-card">
    <h3 style="margin-top:0; font-size:16px; margin-bottom:14px; color:var(--admin-primary);">7-Day Traffic Trend (Unique Visitors vs Page Views)</h3>
    <?php if (empty($dailyTrend)): ?>
        <p style="font-size:13px; color:var(--admin-muted); margin:0;">Traffic chart will populate automatically as visitors browse the live website.</p>
    <?php else: ?>
        <div style="display:flex; align-items:flex-end; gap:14px; height:160px; padding:10px 0; border-bottom:1px solid var(--admin-border);">
            <?php foreach ($dailyTrend as $day):
                $pct = max(8, round(((int)$day['page_views'] / $maxTrend) * 100));
            ?>
                <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%;">
                    <div style="font-size:11px; color:var(--admin-primary); font-weight:700; margin-bottom:4px;"><?php echo (int)$day['unique_visitors']; ?> / <?php echo (int)$day['page_views']; ?></div>
                    <div style="width:100%; max-width:48px; height:<?php echo $pct; ?>%; background:linear-gradient(180deg, #009FE3, #06265C); border-radius:6px 6px 0 0;"></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="display:flex; gap:14px; padding-top:8px;">
            <?php foreach ($dailyTrend as $day): ?>
                <div style="flex:1; text-align:center; font-size:11px; color:var(--admin-muted);"><?php echo date('M d', strtotime($day['visit_day'])); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- TOP VISITED PAGES -->
<?php if (!empty($topPages)): ?>
<div class="admin-card">
    <h3 style="margin-top:0; font-size:16px; margin-bottom:14px; color:var(--admin-primary);">Most Visited Pages</h3>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:12px;">
        <?php foreach ($topPages as $tp): ?>
            <div style="background:#F8FAFC; padding:12px; border-radius:6px; border:1px solid var(--admin-border);">
                <code style="font-size:12px; color:#06265C;"><?php echo sanitize($tp['page_visited']); ?></code>
                <div style="font-weight:bold; font-size:16px; color:var(--admin-orange); margin-top:4px;"><?php echo number_format($tp['page_hits']); ?> views <span style="font-size:12px; color:var(--admin-muted); font-weight:normal;">(<?php echo number_format($tp['unique_hits']); ?> unique)</span></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- RECENT TRAFFIC LOGS TABLE -->
<div class="admin-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
        <h3 style="margin:0; font-size:16px; color:var(--admin-primary);">Recent Visitor Activity</h3>
        <span style="font-size:11px; color:var(--admin-muted);"><?php echo $isSuperAdmin ? 'Full IP Access (SuperAdmin)' : 'IP Masked for Privacy'; ?> &bull; 90-Day Auto-Retention</span>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date &amp; Time</th>
                    <th>IP Address</th>
                    <th>Visited Page</th>
                    <th>Browser / OS</th>
                    <th>Device</th>
                    <th>Referrer</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" style="text-align:center; color: var(--admin-muted);">No visitor logs recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td style="white-space:nowrap;"><?php echo date('M d, Y H:i:s', strtotime($l['created_at'])); ?></td>
                            <td><strong style="color:var(--admin-primary); font-family:monospace; font-size:13px;"><?php echo sanitize(maskIP($l['ip_address'], $isSuperAdmin)); ?></strong></td>
                            <td><code><?php echo sanitize($l['page_visited']); ?></code></td>
                            <td><?php echo sanitize(($l['browser'] ?? 'Other') . ' / ' . ($l['os'] ?? 'Other')); ?></td>
                            <td><span class="status-badge badge-new"><?php echo sanitize($l['device_type'] ?? 'Desktop'); ?></span></td>
                            <td style="font-size:11px; color:var(--admin-muted);"><?php echo $l['referrer'] ? sanitize(substr($l['referrer'], 0, 45)) : 'Direct'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px; padding:12px; background:#F8FAFC; border-left:3px solid #009FE3; font-size:12px; color:#64748B;">
        <strong>Privacy &amp; Data Retention Notice:</strong> Visitor IP logs are collected strictly on the server for security auditing and aggregate traffic measurement, masked for non-SuperAdmin roles, and automatically purged after 90 days.
    </div>
</div>

<script>
    setInterval(() => {
        fetch('visitors.php?ajax=live')
            .then(r => r.json())
            .then(data => {
                if (typeof data.active !== 'undefined') {
                    document.getElementById('liveActiveCount').textContent = Number(data.active).toLocaleString();
                    document.getElementById('liveTodayVisitors').textContent = Number(data.todayVisitors).toLocaleString();
                    document.getElementById('liveTodayViews').textContent = Number(data.todayViews).toLocaleString();
                }
            })
            .catch(() => {});
    }, 30000);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
