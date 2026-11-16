<?php
/**
 * IronPulse Gym & Fitness Management System
 * Database Connection Handler (PDO)
 */

require_once __DIR__ . '/config.php';

function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Check if database does not exist
            if ($e->getCode() === 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
                // If not running installer, suggest visiting install.php
                $currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
                if ($currentScript !== 'install.php') {
                    die('
                    <div style="font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; max-width: 650px; margin: 80px auto; padding: 30px; background: #0f172a; color: #f8fafc; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); text-align: center; border: 1px solid #334155;">
                        <h2 style="color: #f97316; margin-top: 0; font-size: 26px;">Database Not Found</h2>
                        <p style="color: #94a3b8; font-size: 15px; line-height: 1.6;">The database <strong>' . htmlspecialchars(DB_NAME) . '</strong> has not been created yet.</p>
                        <p style="margin: 25px 0;">
                            <a href="' . url('install.php') . '" style="background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block;">
                                Click Here to Run 1-Click Auto-Installer
                            </a>
                        </p>
                        <p style="color: #64748b; font-size: 13px;">Make sure your MySQL service is started in XAMPP Control Panel.</p>
                    </div>
                    ');
                }
            }
            die('Database Connection Error: ' . htmlspecialchars($e->getMessage()));
        }
    }

    return $pdo;
}

// Global db variable for quick scripts
try {
    $db = getDB();
} catch (Exception $e) {
    $db = null;
}
