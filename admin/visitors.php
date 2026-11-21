<?php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$logs = [];

if ($db) {
    try {
        $logs = $db->query("SELECT * FROM visitor_logs ORDER BY id DESC LIMIT 100")->fetchAll();
    } catch (Exception $e) {
        $logs = [];
    }
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 24px; margin-bottom: 4px;">Live Visitor IP Telemetry Tracker</h1>
        <p style="margin: 0; font-size: 13px;">Real-time visitor IP addresses, pages visited, and timestamp logs.</p>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Timestamp</th>
                    <th>Visitor IP Address</th>
                    <th>Page Visited</th>
                    <th>Browser / Device</th>
                </tr>
            </thead>
            <tbody id="ipLogsBody">
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" style="text-align:center; color: var(--admin-muted);">No IP logs recorded in MySQL database yet. Client-side IP tracking active.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td>#<?php echo $l['id']; ?></td>
                            <td><?php echo date('M d, Y H:i:s', strtotime($l['created_at'])); ?></td>
                            <td><strong style="color:var(--admin-primary); font-family:monospace; font-size:14px;"><?php echo sanitize($l['ip_address']); ?></strong></td>
                            <td><code><?php echo sanitize($l['page_visited']); ?></code></td>
                            <td style="font-size:11px; color:var(--admin-muted);"><?php echo sanitize(substr($l['user_agent'], 0, 70)); ?>...</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Load local visitor logs if MySQL table is empty
    const localLogs = JSON.parse(localStorage.getItem('jsn_visitor_logs') || '[]');
    if (localLogs.length > 0 && document.querySelectorAll('#ipLogsBody tr').length <= 1) {
        const body = document.getElementById('ipLogsBody');
        body.innerHTML = '';
        localLogs.forEach((log, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>#Local-${idx + 1}</td>
                <td>${new Date(log.timestamp).toLocaleString()}</td>
                <td><strong style="color:var(--admin-primary); font-family:monospace; font-size:14px;">${log.ip}</strong></td>
                <td><code>${log.page}</code></td>
                <td style="font-size:11px; color:var(--admin-muted);">${log.userAgent.substring(0, 70)}...</td>
            `;
            body.appendChild(tr);
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
