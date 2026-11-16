<?php
/**
 * IronPulse Gym & Fitness Management System
 * Authentication & Authorization Guards
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Check if a user is currently logged in
 */
function is_logged_in() {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
}

/**
 * Check if the currently logged in user is an administrator
 */
function is_admin() {
    return is_logged_in() && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if the currently logged in user is a member
 */
function is_member() {
    return is_logged_in() && $_SESSION['user_role'] === 'member';
}

/**
 * Get current user array from database or session
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    // Cache on request
    static $user = null;
    if ($user === null) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if (!$user) {
            logout_user();
            return null;
        }
    }
    return $user;
}

/**
 * Guard: Require logged in user
 */
function require_login() {
    if (!is_logged_in()) {
        flash('warning', 'Please sign in to access that page.');
        redirect('login.php');
    }
}

/**
 * Guard: Require admin user
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        flash('danger', 'Access denied. Administrator privileges are required.');
        redirect('index.php');
    }
}

/**
 * Guard: Require member user
 */
function require_member() {
    require_login();
    if (is_admin()) {
        // Admins can view member pages or redirect to admin panel
        return;
    }
    if (!is_member()) {
        flash('danger', 'Access denied. Member account required.');
        redirect('login.php');
    }
}

/**
 * Log in a user and set session data
 */
function login_user($user) {
    // Regenerate session id to protect against session fixation
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_avatar'] = $user['avatar'] ?? 'default-avatar.png';
}

/**
 * Log out user
 */
function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Helper to fetch a member's active membership
 */
function get_active_membership($user_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT m.*, p.name AS plan_name, p.badge, p.duration_days, p.features_json
        FROM memberships m
        JOIN membership_plans p ON m.plan_id = p.id
        WHERE m.user_id = ? AND m.status = 'active' AND m.end_date >= CURDATE()
        ORDER BY m.id DESC LIMIT 1
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}
