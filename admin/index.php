<?php
require_once __DIR__ . '/../functions.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!verifyCSRFToken($submittedToken)) {
        $error = 'Security check failed. Invalid CSRF token.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $db = getDB();
        if (!$db) {
            $error = 'Database connection error. Please run <a href="../install.php">install.php</a> first.';
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :user OR email = :email LIMIT 1");
            $stmt->execute([':user' => $username, ':email' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_user'] = $user['username'];
                $_SESSION['admin_name'] = $user['full_name'];
                $_SESSION['admin_role'] = $user['role'];

                // Update last login
                $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Manrope:wght@700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #041B40; color: #10213F; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-card { background: #FFFFFF; border-radius: 12px; padding: 40px; max-width: 400px; width: 100%; box-shadow: 0 16px 40px rgba(0,0,0,0.3); }
        .logo-box { text-align: center; margin-bottom: 24px; }
        .logo-box img { height: 48px; width: auto; background: #ffffff; padding: 4px; }
        h1 { font-family: 'Manrope', sans-serif; color: #06265C; font-size: 20px; text-align: center; margin-top: 10px; margin-bottom: 4px; }
        .tagline { font-size: 10px; text-transform: uppercase; color: #FF6900; font-weight: 800; letter-spacing: 1px; text-align: center; margin-bottom: 24px; }
        .error-msg { background: #FEE2E2; color: #DC2626; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; border: 1px solid #FECACA; }
        label { display: block; font-size: 12.5px; font-weight: 600; margin-bottom: 5px; }
        input { width: 100%; padding: 11px; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 14px; margin-bottom: 16px; box-sizing: border-box; }
        button { width: 100%; background: #FF6900; color: #FFFFFF; font-family: 'Manrope', sans-serif; font-weight: 700; border: none; padding: 12px; border-radius: 6px; font-size: 15px; cursor: pointer; }
        button:hover { background: #E85B00; }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-box">
            <img src="../assets/logo.svg" alt="JSN Logo">
        </div>
        <h1>ADMIN CMS LOGIN</h1>
        <div class="tagline"><?php echo SITE_TAGLINE; ?></div>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

            <label>Username or Email</label>
            <input type="text" name="username" placeholder="admin" required autofocus>

            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>

            <button type="submit">Log In to Admin Panel</button>
        </form>
    </div>

</body>
</html>
