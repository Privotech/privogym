<?php
/**
 * IronPulse Gym & Fitness Management System
 * Global Application Configuration & Helpers
 */

if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------------
// Database Constants (Supports Local XAMPP & Vercel Environment Variables)
// -------------------------------------------------------------
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'gym_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

// -------------------------------------------------------------
// Application Metadata
// -------------------------------------------------------------
define('APP_NAME', 'IronPulse Gym');
define('APP_TAGLINE', 'Forge Strength. Shatter Limits. Elevate Your Life.');
define('CURRENCY', '$');

// Detect Base URL dynamically (Works seamlessly for both XAMPP subfolder and PHP built-in server / root)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

if (strpos($scriptName, '/student') !== false) {
    // Served under /student/ (e.g. XAMPP htdocs)
    $basePath = substr($scriptName, 0, strpos($scriptName, '/student') + 8);
} else {
    // Served directly from root (e.g. php -S localhost:8000 or production domain)
    $basePath = '';
}
define('BASE_URL', rtrim($protocol . $host . $basePath, '/'));

// -------------------------------------------------------------
// Helper Functions
// -------------------------------------------------------------

/**
 * Return absolute URL to a relative project path
 */
function url($path = '') {
    $cleanPath = ltrim($path, '/');
    return BASE_URL . ($cleanPath ? '/' . $cleanPath : '');
}

/**
 * Return absolute URL to an asset
 */
function asset($path = '') {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Escape string for safe HTML output
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Safe redirect to a path or URL
 */
function redirect($path) {
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        header("Location: " . $path);
    } else {
        header("Location: " . url($path));
    }
    exit;
}

/**
 * Set a session flash message
 */
function flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Retrieve and clear session flash message
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format currency amount
 */
function money($amount) {
    return CURRENCY . number_format((float)$amount, 2);
}

/**
 * Format date for friendly display
 */
function format_date($dateStr, $format = 'M d, Y') {
    if (empty($dateStr)) return '-';
    $time = strtotime($dateStr);
    return $time ? date($format, $time) : '-';
}

/**
 * Format time for display (e.g. 07:00:00 -> 7:00 AM)
 */
function format_time($timeStr) {
    if (empty($timeStr)) return '-';
    $time = strtotime($timeStr);
    return $time ? date('g:i A', $time) : '-';
}

/**
 * Generate CSRF token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from POST
 */
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            die('Invalid CSRF token security check.');
        }
    }
}
