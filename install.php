<?php
/**
 * IronPulse Gym & Fitness Management System
 * 1-Click Interactive Database & System Installer
 */

// Basic error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

$message = '';
$status = '';
$step = isset($_GET['step']) ? $_GET['step'] : 'welcome';

// Handle POST Installation
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_install'])) {
    $db_host = trim($_POST['db_host'] ?? DB_HOST);
    $db_port = trim($_POST['db_port'] ?? DB_PORT);
    $db_user = trim($_POST['db_user'] ?? DB_USER);
    $db_pass = trim($_POST['db_pass'] ?? DB_PASS);
    $db_name = trim($_POST['db_name'] ?? DB_NAME);

    try {
        // 1. Connect to MySQL Server (without database selected)
        $dsn = "mysql:host={$db_host};port={$db_port};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // 2. Create Database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `{$db_name}`;");

        // 3. Read database.sql file
        $sqlFile = __DIR__ . '/database.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("database.sql file not found in " . __DIR__);
        }

        $sqlContent = file_get_contents($sqlFile);

        // Split SQL queries safely
        // Temporarily disable foreign keys
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

        // Split statements by semicolon that are at end of line
        $queries = preg_split('/;\s*[\r\n]+/', $sqlContent);

        foreach ($queries as $query) {
            $query = trim($query);
            // Skip empty or comment lines
            if (!empty($query) && !preg_match('/^--/', $query)) {
                $pdo->exec($query);
            }
        }

        // 4. Update the passwords with verified fresh PHP hashes
        $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
        $memberHash = password_hash('member123', PASSWORD_BCRYPT);

        $updateAdmin = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@gym.com'");
        $updateAdmin->execute([$adminHash]);

        $updateMember = $pdo->prepare("UPDATE users SET password = ? WHERE role = 'member'");
        $updateMember->execute([$memberHash]);

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $status = 'success';
        $message = "Database '{$db_name}' and all tables installed successfully with demo seed data!";
        $step = 'success';

    } catch (Exception $e) {
        $status = 'error';
        $message = "Installation failed: " . $e->getMessage();
    }
}

// Support CLI execution: php install.php
if (php_sapi_name() === 'cli') {
    echo "=========================================\n";
    echo "  IRONPULSE GYM - CLI INSTALLER\n";
    echo "=========================================\n";
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `" . DB_NAME . "`;");

        $sqlContent = file_get_contents(__DIR__ . '/database.sql');
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $queries = preg_split('/;\s*[\r\n]+/', $sqlContent);
        foreach ($queries as $q) {
            $q = trim($q);
            if (!empty($q) && !preg_match('/^--/', $q)) {
                $pdo->exec($q);
            }
        }
        $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
        $memberHash = password_hash('member123', PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@gym.com'")->execute([$adminHash]);
        $pdo->prepare("UPDATE users SET password = ? WHERE role = 'member'")->execute([$memberHash]);
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        echo "[SUCCESS] Gym database installed successfully with demo accounts!\n";
        echo "Admin: admin@gym.com / admin123\n";
        echo "Member: john@example.com / member123\n";
        exit(0);
    } catch (Exception $e) {
        echo "[ERROR] " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IronPulse Gym - System Installation Wizard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #f97316;
            --primary-hover: #ea580c;
            --bg-dark: #090d16;
            --card-bg: #111827;
            --border-color: #1f2937;
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --success: #10b981;
            --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .installer-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            max-width: 680px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }
        .header {
            padding: 30px;
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(17, 24, 39, 0.8));
            border-bottom: 1px solid var(--border-color);
            text-align: center;
        }
        .header .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .header .logo span {
            color: var(--primary);
        }
        .header p {
            color: var(--text-muted);
            margin-top: 8px;
            font-size: 14px;
        }
        .body {
            padding: 30px;
        }
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }
        .check-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .check-item {
            background: #1e293b;
            padding: 12px 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
        }
        .check-item .badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
        }
        .badge-ok { background: #065f46; color: #6ee7b7; }
        .badge-warn { background: #92400e; color: #fde68a; }
        .form-group {
            margin-bottom: 16px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 12px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
        }
        input {
            width: 100%;
            padding: 12px 14px;
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.35);
            transition: all 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.45);
        }
        .btn-secondary {
            background: #374151;
            box-shadow: none;
            margin-top: 10px;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .credentials-box {
            background: #1e293b;
            border-left: 4px solid var(--primary);
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 24px;
        }
        .cred-title {
            font-weight: 700;
            margin-bottom: 8px;
            color: #fff;
            font-size: 14px;
        }
        .cred-line {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }
        .cred-line strong {
            color: #f3f4f6;
        }
    </style>
</head>
<body>

<div class="installer-card">
    <div class="header">
        <div class="logo">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary)"><path d="m6.5 6.5 11 11"/><path d="m21 21-1-1"/><path d="m3 3 1 1"/><path d="m18 22 4-4"/><path d="m2 6 4-4"/><path d="m3 10 7-7"/><path d="m14 21 7-7"/></svg>
            IRON<span>PULSE</span> FITNESS
        </div>
        <p>1-Click Setup & Database Installer Wizard</p>
    </div>

    <div class="body">
        <?php if ($status === 'error'): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 'success'): ?>
            <div class="alert alert-success">
                <strong>Success!</strong> <?= htmlspecialchars($message) ?>
            </div>

            <div class="credentials-box">
                <div class="cred-title">Default Demo Login Accounts Created:</div>
                <div class="cred-line">
                    <strong>Administrator Account:</strong><br>
                    Email: <code>admin@gym.com</code> | Password: <code>admin123</code>
                </div>
                <hr style="border: 0; border-top: 1px solid #334155; margin: 10px 0;">
                <div class="cred-line">
                    <strong>Member Account:</strong><br>
                    Email: <code>john@example.com</code> | Password: <code>member123</code>
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 20px;">
                <a href="<?= url('index.php') ?>" class="btn">View Website</a>
                <a href="<?= url('login.php') ?>" class="btn btn-secondary" style="margin-top:0;">Sign In</a>
            </div>

        <?php else: ?>

            <div class="check-grid">
                <div class="check-item">
                    <span>PHP Version (<?= PHP_VERSION ?>)</span>
                    <span class="badge badge-ok">Compatible</span>
                </div>
                <div class="check-item">
                    <span>PDO MySQL Extension</span>
                    <span class="badge <?= extension_loaded('pdo_mysql') ? 'badge-ok' : 'badge-warn' ?>">
                        <?= extension_loaded('pdo_mysql') ? 'Available' : 'Missing' ?>
                    </span>
                </div>
            </div>

            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Database Host</label>
                        <input type="text" name="db_host" value="<?= htmlspecialchars(DB_HOST) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Port</label>
                        <input type="text" name="db_port" value="<?= htmlspecialchars(DB_PORT) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Database Name</label>
                    <input type="text" name="db_name" value="<?= htmlspecialchars(DB_NAME) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Database User</label>
                        <input type="text" name="db_user" value="<?= htmlspecialchars(DB_USER) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Database Password</label>
                        <input type="password" name="db_pass" value="<?= htmlspecialchars(DB_PASS) ?>" placeholder="(Empty in default XAMPP)">
                    </div>
                </div>

                <button type="submit" name="run_install" class="btn">
                    Install Database & Demo Data Now
                </button>
            </form>
            <p style="text-align: center; color: var(--text-muted); font-size: 12px; margin-top: 15px;">
                Note: Make sure your MySQL service is running in XAMPP before clicking install.
            </p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
