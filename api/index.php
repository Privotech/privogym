<?php
/**
 * Vercel Serverless PHP Router & Entry Point
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = trim($uri, '/');

$baseDir = dirname(__DIR__);
chdir($baseDir);

// Handle root
if (empty($uri) || $uri === 'index.php') {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    require $baseDir . '/index.php';
    exit;
}

// Handle admin root
if ($uri === 'admin' || $uri === 'admin/') {
    $_SERVER['SCRIPT_NAME'] = '/admin/index.php';
    require $baseDir . '/admin/index.php';
    exit;
}

// Handle member root
if ($uri === 'member' || $uri === 'member/') {
    $_SERVER['SCRIPT_NAME'] = '/member/index.php';
    require $baseDir . '/member/index.php';
    exit;
}

$targetFile = $baseDir . '/' . $uri;

// Direct PHP file match
if (is_file($targetFile) && pathinfo($targetFile, PATHINFO_EXTENSION) === 'php') {
    $_SERVER['SCRIPT_NAME'] = '/' . $uri;
    require $targetFile;
    exit;
}

// Extension-less URL match (e.g. /about -> /about.php)
if (is_file($targetFile . '.php')) {
    $_SERVER['SCRIPT_NAME'] = '/' . $uri . '.php';
    require $targetFile . '.php';
    exit;
}

// Directory index match
if (is_dir($targetFile) && is_file($targetFile . '/index.php')) {
    $_SERVER['SCRIPT_NAME'] = '/' . $uri . '/index.php';
    require $targetFile . '/index.php';
    exit;
}

// 404 Fallback
http_response_code(404);
?>
<!DOCTYPE html>
<html>
<head><title>404 Not Found</title></head>
<body style="font-family:sans-serif; text-align:center; padding:80px; background:#090d16; color:#fff;">
    <h1 style="color:#f97316; font-size:48px;">404</h1>
    <p style="color:#94a3b8;">The requested page could not be found.</p>
    <a href="/" style="color:#f97316; text-decoration:none; font-weight:bold;">&larr; Back to Home</a>
</body>
</html>
