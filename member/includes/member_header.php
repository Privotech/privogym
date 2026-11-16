<?php
/**
 * Member Portal Header
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/auth.php';

require_login();
$user = current_user();
$activeMembership = get_active_membership($user['id']);
$curPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Member Portal - <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/member.css') ?>">
</head>
<body class="member-body">

<header class="member-navbar">
    <div class="container member-nav-inner">
        <a href="<?= url('member/index.php') ?>" class="brand-logo" style="font-size: 24px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6.5 6.5 11 11"/><path d="m21 21-1-1"/><path d="m3 3 1 1"/><path d="m18 22 4-4"/><path d="m2 6 4-4"/><path d="m3 10 7-7"/><path d="m14 21 7-7"/></svg>
            IRON<span>PULSE</span> <span style="font-size: 13px; font-family: var(--font-body); color: var(--text-muted); font-weight: 600; letter-spacing: 0;">MEMBER</span>
        </a>

        <nav class="member-links">
            <a href="<?= url('member/index.php') ?>" class="member-nav-item <?= $curPage === 'index.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="<?= url('member/membership.php') ?>" class="member-nav-item <?= $curPage === 'membership.php' ? 'active' : '' ?>">My Plan</a>
            <a href="<?= url('member/book-class.php') ?>" class="member-nav-item <?= $curPage === 'book-class.php' ? 'active' : '' ?>">Book Classes</a>
            <a href="<?= url('member/my-bookings.php') ?>" class="member-nav-item <?= $curPage === 'my-bookings.php' ? 'active' : '' ?>">My Bookings</a>
            <a href="<?= url('member/workout-tracker.php') ?>" class="member-nav-item <?= $curPage === 'workout-tracker.php' ? 'active' : '' ?>">Workout Log</a>
            <a href="<?= url('member/payments.php') ?>" class="member-nav-item <?= $curPage === 'payments.php' ? 'active' : '' ?>">Invoices</a>
        </nav>

        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="<?= url('member/profile.php') ?>" style="display: flex; align-items: center; gap: 10px; color: #fff; font-size: 13px; font-weight: 700; text-decoration: none;">
                <div style="width: 34px; height: 34px; border-radius: 50%; background: var(--mem-primary); display: flex; align-items: center; justify-content: center; font-weight: 800; color: #fff;">
                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                </div>
                <span><?= htmlspecialchars($user['full_name']) ?></span>
            </a>
            <a href="<?= url('index.php') ?>" class="btn btn-outline btn-sm" title="Go to Public Website">Website</a>
            <a href="<?= url('logout.php') ?>" class="btn btn-outline btn-sm" title="Log Out">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </a>
        </div>
    </div>
</header>

<?php require_once __DIR__ . '/../../includes/alerts.php'; ?>
