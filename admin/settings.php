<?php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    foreach ($_POST['setting'] as $key => $val) {
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :val) ON DUPLICATE KEY UPDATE setting_value = :val, updated_at = NOW()");
        $stmt->execute([':key' => sanitize($key), ':val' => sanitize($val)]);
    }
    $message = "Website settings updated successfully.";
}

// Fetch Settings
$settings = [];
if ($db) {
    $rows = $db->query("SELECT * FROM settings")->fetchAll();
    foreach ($rows as $r) {
        $settings[$r['setting_key']] = $r['setting_value'];
    }
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 24px; margin-bottom: 4px;">Website Settings & Statistics</h1>
        <p style="margin: 0; font-size: 13px;">Manage global contact information, verified metrics, and company branding.</p>
    </div>
</div>

<?php if ($message): ?>
    <div style="background:#DCFCE7; color:#15803D; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #BBF7D0;"><?php echo $message; ?></div>
<?php endif; ?>

<form method="POST">
    <div class="admin-card">
        <h3 style="font-size:16px; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:8px;">1. Verified Company Metrics</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Commissioned Capacity</label>
                <input type="text" name="setting[stat_capacity]" class="form-control" value="<?php echo sanitize($settings['stat_capacity'] ?? '5.85 MW'); ?>">
            </div>
            <div class="form-group">
                <label>PM-KUSUM Ground Solar</label>
                <input type="text" name="setting[stat_kusum]" class="form-control" value="<?php echo sanitize($settings['stat_kusum'] ?? '5.10 MW'); ?>">
            </div>
            <div class="form-group">
                <label>Industrial Rooftop Solar</label>
                <input type="text" name="setting[stat_rooftop]" class="form-control" value="<?php echo sanitize($settings['stat_rooftop'] ?? '745 kW'); ?>">
            </div>
            <div class="form-group">
                <label>Net Metering Clearance</label>
                <input type="text" name="setting[stat_clearance]" class="form-control" value="<?php echo sanitize($settings['stat_clearance'] ?? '100%'); ?>">
            </div>
        </div>

        <h3 style="font-size:16px; margin-top:24px; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:8px;">2. Contact & Office Information</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Direct Contact Phone</label>
                <input type="text" name="setting[phone]" class="form-control" value="<?php echo sanitize($settings['phone'] ?? '+91 92890 44454'); ?>">
            </div>
            <div class="form-group">
                <label>Support Email</label>
                <input type="email" name="setting[email]" class="form-control" value="<?php echo sanitize($settings['email'] ?? 'info@jsnrenewable.com'); ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Head Office Address</label>
            <input type="text" name="setting[address]" class="form-control" value="<?php echo sanitize($settings['address'] ?? 'Plot No. 86, Kajipura, Near LK International School, Wave City, Ghaziabad, UP-201002'); ?>">
        </div>

        <h3 style="font-size:16px; margin-top:24px; margin-bottom:16px; border-bottom:1px solid var(--admin-border); padding-bottom:8px;">3. Brand Tagline & Title</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="setting[site_title]" class="form-control" value="<?php echo sanitize($settings['site_title'] ?? 'JSN Renewable Energy Private Limited'); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Approved Brand Tagline</label>
                <input type="text" name="setting[tagline]" class="form-control" value="A NEW WAY TO POWER" readonly>
            </div>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary" style="margin-top:12px;"><i data-lucide="save"></i> Save Settings</button>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
