<?php
/**
 * JSN Renewable Energy - Database & Initial Admin Installer
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbName = sanitize($_POST['db_name'] ?? DB_NAME);
    $dbUser = sanitize($_POST['db_user'] ?? DB_USER);
    $dbPass = $_POST['db_pass'] ?? '';
    $adminUser = sanitize($_POST['admin_user'] ?? 'admin');
    $adminEmail = sanitize($_POST['admin_email'] ?? 'info@jsnrenewable.com');
    $adminPass = $_POST['admin_pass'] ?? '';

    if (empty($adminUser) || empty($adminEmail) || empty($adminPass)) {
        $error = 'Please fill in all initial admin account fields.';
    } elseif (strlen($adminPass) < 8) {
        $error = 'Admin password must be at least 8 characters long.';
    } else {
        try {
            // Establish PDO without database selected
            $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Execute schema
            $sql = file_get_contents(__DIR__ . '/schema.sql');
            $pdo->exec($sql);

            // Select Database
            $pdo->exec("USE `$dbName`");

            // Check if Admin exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$adminUser, $adminEmail]);

            if ($stmt->fetchColumn() == 0) {
                $hash = password_hash($adminPass, PASSWORD_BCRYPT);
                $insertStmt = $pdo->prepare("
                    INSERT INTO users (username, password_hash, full_name, email, role, created_at)
                    VALUES (?, ?, 'System Administrator', ?, 'SuperAdmin', NOW())
                ");
                $insertStmt->execute([$adminUser, $hash, $adminEmail]);
            }

            $message = 'Database and initial SuperAdmin created successfully! You can now log into the <a href="admin/index.php" style="color:#FF6900; font-weight:bold;">Admin Panel</a>.';
        } catch (Exception $e) {
            $error = 'Installation Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSN Renewable Energy - Database & Admin Installer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Manrope:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #041B40; color: #10213F; margin: 0; padding: 40px 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .installer-card { background: #FFFFFF; border-radius: 12px; padding: 40px; max-width: 500px; width: 100%; box-shadow: 0 16px 40px rgba(0,0,0,0.3); }
        h1 { font-family: 'Manrope', sans-serif; color: #06265C; font-size: 24px; margin-top: 0; margin-bottom: 6px; }
        .tagline { font-size: 11px; text-transform: uppercase; color: #FF6900; font-weight: 800; letter-spacing: 1px; margin-bottom: 24px; }
        .alert { padding: 12px 16px; border-radius: 6px; font-size: 14px; margin-bottom: 20px; }
        .alert-success { background: #DCFCE7; color: #15803D; border: 1px solid #BBF7D0; }
        .alert-error { background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: #10213F; }
        input { width: 100%; padding: 10px 12px; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 14px; margin-bottom: 16px; box-sizing: border-box; }
        button { width: 100%; background: #FF6900; color: #FFFFFF; font-family: 'Manrope', sans-serif; font-weight: 700; border: none; padding: 12px; border-radius: 6px; font-size: 15px; cursor: pointer; }
        button:hover { background: #E85B00; }
    </style>
</head>
<body>

    <div class="installer-card">
        <h1>JSN RENEWABLE ENERGY</h1>
        <div class="tagline">A NEW WAY TO POWER &bull; SYSTEM INSTALLER</div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <h3 style="font-size: 15px; color: #06265C; margin-bottom: 12px;">1. MySQL Credentials</h3>
            <label>Database Name</label>
            <input type="text" name="db_name" value="jsn_renewable_db" required>

            <label>Database Username</label>
            <input type="text" name="db_user" value="root" required>

            <label>Database Password</label>
            <input type="password" name="db_pass" placeholder="Leave empty if using default XAMPP">

            <h3 style="font-size: 15px; color: #06265C; margin-bottom: 12px; margin-top: 20px;">2. SuperAdmin Account Setup</h3>
            <label>Admin Username</label>
            <input type="text" name="admin_user" value="admin" required>

            <label>Admin Email</label>
            <input type="email" name="admin_email" value="info@jsnrenewable.com" required>

            <label>Admin Password (min. 8 chars)</label>
            <input type="password" name="admin_pass" placeholder="Enter secure password" required>

            <button type="submit">Run Automated Installation</button>
        </form>
    </div>

</body>
</html>
