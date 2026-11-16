<?php
/**
 * Admin Portal - System & Gym Configuration Settings
 */

$pageTitle = "System Settings";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $settings = [
        'gym_name' => trim($_POST['gym_name'] ?? 'IronPulse Fitness Club'),
        'gym_tagline' => trim($_POST['gym_tagline'] ?? ''),
        'gym_phone' => trim($_POST['gym_phone'] ?? ''),
        'gym_email' => trim($_POST['gym_email'] ?? ''),
        'gym_address' => trim($_POST['gym_address'] ?? ''),
        'currency' => trim($_POST['currency'] ?? '$'),
        'hours_weekday' => trim($_POST['hours_weekday'] ?? '05:00 AM - 11:00 PM'),
        'hours_weekend' => trim($_POST['hours_weekend'] ?? '07:00 AM - 09:00 PM')
    ];

    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($settings as $k => $v) {
        $stmt->execute([$k, $v]);
    }

    flash('success', 'System branding and gym operational settings saved successfully.');
    redirect('admin/settings.php');
}

// Fetch current settings
$currentSettings = [];
try {
    $rows = $db->query("SELECT * FROM settings")->fetchAll();
    foreach ($rows as $r) {
        $currentSettings[$r['setting_key']] = $r['setting_value'];
    }
} catch (Exception $e) {}

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="margin-bottom: 24px;">
    <h1 style="font-size: 24px; font-weight: 800; color: #fff;">Gym & System Settings</h1>
    <p style="color: var(--admin-muted); font-size: 13px;">Configure your facility details, operating hours, currency, and public contact information.</p>
</div>

<div class="form-card" style="max-width: 700px;">
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">
            1. Brand Identity
        </h3>

        <div class="form-group">
            <label>Gym Organization Name</label>
            <input type="text" name="gym_name" class="form-control" value="<?= htmlspecialchars($currentSettings['gym_name'] ?? 'IRONPULSE FITNESS CLUB') ?>" required>
        </div>

        <div class="form-group">
            <label>Tagline / Motto</label>
            <input type="text" name="gym_tagline" class="form-control" value="<?= htmlspecialchars($currentSettings['gym_tagline'] ?? 'Forge Strength. Shatter Limits. Elevate Your Life.') ?>">
        </div>

        <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 24px 0 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">
            2. Contact & Location
        </h3>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Official Phone Number</label>
                <input type="text" name="gym_phone" class="form-control" value="<?= htmlspecialchars($currentSettings['gym_phone'] ?? '+1 (555) 234-5678') ?>">
            </div>
            <div class="form-group">
                <label>Official Support Email</label>
                <input type="email" name="gym_email" class="form-control" value="<?= htmlspecialchars($currentSettings['gym_email'] ?? 'contact@ironpulsefitness.com') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Physical Facility Address</label>
            <input type="text" name="gym_address" class="form-control" value="<?= htmlspecialchars($currentSettings['gym_address'] ?? '742 Fitness Boulevard, Metro City, NY 10001') ?>">
        </div>

        <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 24px 0 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">
            3. Operational Hours & Currency
        </h3>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Weekday Opening Hours</label>
                <input type="text" name="hours_weekday" class="form-control" value="<?= htmlspecialchars($currentSettings['hours_weekday'] ?? '05:00 AM - 11:00 PM') ?>">
            </div>
            <div class="form-group">
                <label>Weekend Opening Hours</label>
                <input type="text" name="hours_weekend" class="form-control" value="<?= htmlspecialchars($currentSettings['hours_weekend'] ?? '07:00 AM - 09:00 PM') ?>">
            </div>
        </div>

        <div class="form-group" style="max-width: 180px;">
            <label>Currency Symbol</label>
            <input type="text" name="currency" class="form-control" value="<?= htmlspecialchars($currentSettings['currency'] ?? '$') ?>" required>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
            Save Settings &rarr;
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
