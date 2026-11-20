<?php
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$message = '';

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $db) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=JSN_Enquiries_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Submission Date', 'Customer Name', 'Phone', 'Email', 'Category', 'Location', 'Message', 'Status', 'Follow-up Notes']);

    $rows = $db->query("SELECT * FROM enquiries ORDER BY id DESC")->fetchAll();
    foreach ($rows as $r) {
        fputcsv($output, [
            $r['id'], $r['created_at'], $r['name'], $r['phone'], $r['email'],
            $r['project_category'], $r['location'], $r['message'], $r['status'], $r['follow_up_notes']
        ]);
    }
    fclose($output);
    exit;
}

// Handle Status Update & Notes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enquiry_id']) && $db) {
    $enquiryId = (int)$_POST['enquiry_id'];
    $status = sanitize($_POST['status'] ?? 'New');
    $notes = sanitize($_POST['follow_up_notes'] ?? '');

    $stmt = $db->prepare("UPDATE enquiries SET status = :status, follow_up_notes = :notes, updated_at = NOW() WHERE id = :id");
    $stmt->execute([':status' => $status, ':notes' => $notes, ':id' => $enquiryId]);
    $message = "Lead status and notes updated for Lead #$enquiryId.";
}

// Search and Filter
$filterStatus = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['q'] ?? '');

$sql = "SELECT * FROM enquiries WHERE 1=1";
$params = [];

if ($filterStatus) {
    $sql .= " AND status = :status";
    $params[':status'] = $filterStatus;
}

if ($search) {
    $sql .= " AND (name LIKE :q OR phone LIKE :q OR location LIKE :q OR project_category LIKE :q)";
    $params[':q'] = "%$search%";
}

$sql .= " ORDER BY id DESC";

$enquiries = [];
if ($db) {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $enquiries = $stmt->fetchAll();
}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 24px; margin-bottom: 4px;">Site Survey Lead Management</h1>
        <p style="margin: 0; font-size: 13px;">Manage customer enquiries, update lead statuses, and export to CSV.</p>
    </div>
    <div>
        <a href="enquiries.php?export=csv" class="admin-btn admin-btn-secondary"><i data-lucide="download"></i> Export CSV</a>
    </div>
</div>

<?php if ($message): ?>
    <div style="background:#DCFCE7; color:#15803D; padding:12px; border-radius:6px; margin-bottom:20px; border:1px solid #BBF7D0;"><?php echo $message; ?></div>
<?php endif; ?>

<!-- SEARCH & FILTER BAR -->
<div class="admin-card" style="padding: 16px; margin-bottom: 20px;">
    <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
        <input type="text" name="q" value="<?php echo $search; ?>" placeholder="Search name, phone, location..." style="width:250px;" class="form-control">

        <select name="status" class="form-control" style="width:160px;">
            <option value="">All Statuses</option>
            <?php foreach (['New', 'Contacted', 'Follow-up', 'Converted', 'Rejected'] as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $filterStatus === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="admin-btn admin-btn-primary">Filter Leads</button>
        <a href="enquiries.php" class="admin-btn admin-btn-outline">Reset</a>
    </form>
</div>

<!-- LEADS TABLE -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Contact Details</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Manage Lead</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($enquiries)): ?>
                    <tr><td colspan="8" style="text-align:center; color: var(--admin-muted);">No enquiries matched your search criteria.</td></tr>
                <?php else: ?>
                    <?php foreach ($enquiries as $enq): ?>
                        <tr>
                            <td>#<?php echo $enq['id']; ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($enq['created_at'])); ?></td>
                            <td><strong><?php echo sanitize($enq['name']); ?></strong></td>
                            <td>
                                📞 <a href="tel:<?php echo sanitize($enq['phone']); ?>" style="color:var(--admin-primary); font-weight:bold;"><?php echo sanitize($enq['phone']); ?></a><br>
                                <?php if ($enq['email']): ?>✉️ <?php echo sanitize($enq['email']); ?><?php endif; ?>
                            </td>
                            <td><?php echo sanitize($enq['project_category']); ?></td>
                            <td><?php echo sanitize($enq['location']); ?></td>
                            <td><span class="status-badge badge-<?php echo strtolower($enq['status']); ?>"><?php echo sanitize($enq['status']); ?></span></td>
                            <td>
                                <button onclick="document.getElementById('enq-modal-<?php echo $enq['id']; ?>').style.display='block'" class="admin-btn admin-btn-outline" style="padding:4px 8px; font-size:11px;">
                                    Manage Lead
                                </button>

                                <!-- MODAL FOR LEAD DETAILS -->
                                <div id="enq-modal-<?php echo $enq['id']; ?>" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; padding:40px 20px;">
                                    <div style="background:#fff; max-width:550px; margin:0 auto; padding:24px; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                            <h3 style="margin:0;">Manage Lead #<?php echo $enq['id']; ?> - <?php echo sanitize($enq['name']); ?></h3>
                                            <span onclick="document.getElementById('enq-modal-<?php echo $enq['id']; ?>').style.display='none'" style="cursor:pointer; font-weight:bold;">✕</span>
                                        </div>

                                        <p style="font-size:13px; color:var(--admin-muted); margin-bottom:12px;">Submitted: <?php echo date('F d, Y \a\t H:i', strtotime($enq['created_at'])); ?></p>

                                        <div style="background:#F8FAFC; padding:12px; border-radius:6px; margin-bottom:14px; font-size:13px;">
                                            <strong>Phone:</strong> <?php echo sanitize($enq['phone']); ?><br>
                                            <strong>Email:</strong> <?php echo sanitize($enq['email'] ?: 'Not provided'); ?><br>
                                            <strong>Category:</strong> <?php echo sanitize($enq['project_category']); ?><br>
                                            <strong>Location:</strong> <?php echo sanitize($enq['location']); ?><br>
                                            <strong>Message:</strong> <?php echo nl2br(sanitize($enq['message'] ?: 'No message provided.')); ?>
                                        </div>

                                        <form method="POST">
                                            <input type="hidden" name="enquiry_id" value="<?php echo $enq['id']; ?>">
                                            <div class="form-group">
                                                <label>Lead Status</label>
                                                <select name="status" class="form-control">
                                                    <?php foreach (['New', 'Contacted', 'Follow-up', 'Converted', 'Rejected'] as $stOpt): ?>
                                                        <option value="<?php echo $stOpt; ?>" <?php echo $enq['status'] === $stOpt ? 'selected' : ''; ?>><?php echo $stOpt; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Follow-up Notes</label>
                                                <textarea name="follow_up_notes" rows="3" class="form-control" placeholder="Enter follow-up details, quotation notes..."><?php echo sanitize($enq['follow_up_notes']); ?></textarea>
                                            </div>

                                            <div style="display:flex; justify-content:flex-end; gap:10px;">
                                                <button type="button" onclick="document.getElementById('enq-modal-<?php echo $enq['id']; ?>').style.display='none'" class="admin-btn admin-btn-outline">Cancel</button>
                                                <button type="submit" class="admin-btn admin-btn-primary">Update Lead</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
