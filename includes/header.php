<?php
/**
 * Public Header Template
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';

$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?><?= APP_NAME ?> - <?= APP_TAGLINE ?></title>
    <meta name="description" content="IronPulse Gym & Fitness Club - State-of-the-art gym equipment, world-class personal trainers, dynamic group fitness classes, and customized workout programs.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Core Style -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>

<!-- Top Quick Bar -->
<div style="background: #060910; border-bottom: 1px solid #141c2e; padding: 6px 0; font-size: 12px; color: #94a3b8;">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; gap: 20px; align-items: center;">
            <span>📍 742 Fitness Boulevard, Metro City</span>
            <span style="display: none; @media(min-width:600px){display:inline;}">⏱️ Mon-Fri: 5AM - 11PM | Sat-Sun: 7AM - 9PM</span>
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <span>📞 Call: +1 (555) 234-5678</span>
            <?php if (is_admin()): ?>
                <a href="<?= url('admin/index.php') ?>" style="color: #f97316; font-weight: 700;">⚙️ Admin Area</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Navbar -->
<nav class="navbar">
    <div class="container nav-container">
        <a href="<?= url('index.php') ?>" class="brand-logo">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6.5 6.5 11 11"/><path d="m21 21-1-1"/><path d="m3 3 1 1"/><path d="m18 22 4-4"/><path d="m2 6 4-4"/><path d="m3 10 7-7"/><path d="m14 21 7-7"/></svg>
            IRON<span>PULSE</span>
        </a>

        <div class="nav-menu">
            <a href="<?= url('index.php') ?>" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
            <a href="<?= url('about.php') ?>" class="nav-link <?= $currentPage === 'about.php' ? 'active' : '' ?>">About</a>
            <a href="<?= url('classes.php') ?>" class="nav-link <?= $currentPage === 'classes.php' ? 'active' : '' ?>">Classes</a>
            <a href="<?= url('timetable.php') ?>" class="nav-link <?= $currentPage === 'timetable.php' ? 'active' : '' ?>">Timetable</a>
            <a href="<?= url('trainers.php') ?>" class="nav-link <?= $currentPage === 'trainers.php' ? 'active' : '' ?>">Trainers</a>
            <a href="<?= url('pricing.php') ?>" class="nav-link <?= $currentPage === 'pricing.php' ? 'active' : '' ?>">Pricing</a>
            <a href="<?= url('bmi-calculator.php') ?>" class="nav-link <?= $currentPage === 'bmi-calculator.php' ? 'active' : '' ?>">Calculators</a>
            <a href="<?= url('contact.php') ?>" class="nav-link <?= $currentPage === 'contact.php' ? 'active' : '' ?>">Contact</a>
        </div>

        <div class="nav-actions">
            <?php if (is_logged_in()): ?>
                <?php if (is_admin()): ?>
                    <a href="<?= url('admin/index.php') ?>" class="btn btn-primary btn-sm">
                        <span>Admin Panel</span>
                    </a>
                <?php else: ?>
                    <a href="<?= url('member/index.php') ?>" class="btn btn-primary btn-sm">
                        <span>My Dashboard</span>
                    </a>
                <?php endif; ?>
                <a href="<?= url('logout.php') ?>" class="btn btn-outline btn-sm" title="Log Out">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            <?php else: ?>
                <a href="<?= url('login.php') ?>" class="btn btn-outline btn-sm">Sign In</a>
                <a href="<?= url('register.php') ?>" class="btn btn-primary btn-sm">Join Now</a>
            <?php endif; ?>

            <button class="menu-toggle" aria-label="Toggle navigation">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
    </div>
</nav>

<?php require_once __DIR__ . '/alerts.php'; ?>
