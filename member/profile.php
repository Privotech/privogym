<?php
/**
 * Member Portal - Profile & Account Settings
 */

$pageTitle = "My Profile";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_member();
$user = current_user();
$db = getDB();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $emergency_contact = trim($_POST['emergency_contact'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $fitness_goals = trim($_POST['fitness_goals'] ?? '');

        if (!empty($full_name)) {
            $stmt = $db->prepare("
                UPDATE users SET full_name = ?, phone = ?, emergency_contact = ?, address = ?, fitness_goals = ?
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $phone, $emergency_contact, $address, $fitness_goals, $user['id']]);
            $_SESSION['user_name'] = $full_name;
            flash('success', 'Profile details updated successfully.');
            redirect('member/profile.php');
        } else {
            $error = 'Full name cannot be empty.';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        if (!password_verify($current_pass, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_pass) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new_pass !== $confirm_pass) {
            $error = 'New passwords do not match.';
        } else {
            $newHash = password_hash($new_pass, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$newHash, $user['id']]);
            flash('success', 'Your password has been changed securely.');
            redirect('member/profile.php');
        }
    }
}

// Reload fresh user
$user = current_user();

require_once __DIR__ . '/includes/member_header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom: 30px;">
        <h1 style="font-family: var(--font-heading); font-size: 34px; color: #fff;">PROFILE & ACCOUNT SETTINGS</h1>
        <p style="color: var(--text-muted); font-size: 14px;">Update your personal details, emergency contact, and security password.</p>
    </div>

    <?php if ($error): ?>
        <div class="flash-alert flash-alert-danger" style="margin-bottom: 25px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 30px; align-items: start;">
        <!-- Profile Info -->
        <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 16px; padding: 30px;">
            <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 20px;">Personal Information</h3>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address (Account Identifier)</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
                    <small style="color: var(--text-dim); font-size: 11px;">Email cannot be changed directly for security reasons.</small>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Emergency Contact</label>
                        <input type="text" name="emergency_contact" class="form-control" placeholder="Name & Phone" value="<?= htmlspecialchars($user['emergency_contact'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Residential Address</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Street, City, Zip">
                </div>

                <div class="form-group">
                    <label>Primary Fitness Goals</label>
                    <textarea name="fitness_goals" class="form-control" rows="3"><?= htmlspecialchars($user['fitness_goals'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Save Profile Changes
                </button>
            </form>
        </div>

        <!-- Security / Change Password -->
        <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 16px; padding: 30px;">
            <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 20px;">Change Password</h3>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>New Password (min 6 characters)</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-outline-primary btn-block">
                    Update Password
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/member_footer.php'; ?>
