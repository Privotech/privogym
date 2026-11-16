<?php
/**
 * Admin Portal - Personal Trainers & Coaches Management
 */

$pageTitle = "Trainers & Coaches";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$db = getDB();

// Handle Add / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'create_trainer') {
        $name = trim($_POST['name'] ?? '');
        $specialty = trim($_POST['specialty'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $experience = (int)($_POST['experience_years'] ?? 3);
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $social = trim($_POST['social_instagram'] ?? '');

        if (!empty($name) && !empty($specialty)) {
            $stmt = $db->prepare("
                INSERT INTO trainers (name, specialty, bio, experience_years, email, phone, social_instagram, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([$name, $specialty, $bio, $experience, $email, $phone, $social]);
            flash('success', "Coach {$name} added successfully.");
            redirect('admin/trainers.php');
        } else {
            flash('danger', 'Please enter trainer name and specialty.');
        }

    } elseif ($action === 'delete_trainer') {
        $trainerId = (int)$_POST['trainer_id'];
        $db->prepare("DELETE FROM trainers WHERE id = ?")->execute([$trainerId]);
        flash('info', 'Trainer removed.');
        redirect('admin/trainers.php');
    }
}

$trainers = $db->query("SELECT * FROM trainers ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #fff;">Personal Trainers & Coaches</h1>
        <p style="color: var(--admin-muted); font-size: 13px;">Manage certified staff coaches, specializations, credentials, and bios.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 380px; gap: 30px; align-items: start;">
    <!-- Trainers Table -->
    <div class="data-card">
        <div class="data-card-header">
            <div class="data-card-title">Staff Coaches (<?= count($trainers) ?>)</div>
        </div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Coach Name</th>
                        <th>Specialty Focus</th>
                        <th>Experience</th>
                        <th>Contact</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($trainers)): ?>
                        <?php foreach ($trainers as $t): ?>
                            <tr>
                                <td>
                                    <strong style="color: #fff; font-size: 15px;"><?= htmlspecialchars($t['name']) ?></strong>
                                    <div style="font-size: 12px; color: var(--admin-muted); margin-top: 2px; max-width: 280px;"><?= htmlspecialchars($t['bio']) ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-primary"><?= htmlspecialchars($t['specialty']) ?></span>
                                </td>
                                <td style="font-size: 13px; color: #fff; font-weight: 700;">
                                    <?= (int)$t['experience_years'] ?>+ Years
                                </td>
                                <td style="font-size: 13px; color: var(--text-secondary);">
                                    <div><?= htmlspecialchars($t['email'] ?? '-') ?></div>
                                    <div style="color: var(--admin-muted); font-size: 12px;"><?= htmlspecialchars($t['phone'] ?? '-') ?></div>
                                </td>
                                <td style="text-align: right;">
                                    <form method="POST" action="" onsubmit="return confirm('Remove coach <?= htmlspecialchars($t['name']) ?>?');" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_trainer">
                                        <input type="hidden" name="trainer_id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="action-btn action-btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--admin-muted); padding: 30px;">No trainers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Trainer Form -->
    <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 14px; padding: 24px;">
        <h3 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 16px;">+ Add New Trainer</h3>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="create_trainer">

            <div class="form-group">
                <label>Coach Full Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Rachel Sterling" required>
            </div>

            <div class="form-group">
                <label>Specialty Focus *</label>
                <input type="text" name="specialty" class="form-control" placeholder="e.g. Olympic Weightlifting & Mobility" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label>Experience (Years)</label>
                    <input type="number" name="experience_years" class="form-control" value="5" min="1" required>
                </div>
                <div class="form-group">
                    <label>Instagram Handle</label>
                    <input type="text" name="social_instagram" class="form-control" placeholder="@coach_rachel">
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="coach@ironpulsefitness.com">
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 888-0000">
            </div>

            <div class="form-group">
                <label>Coach Bio & Philosophy</label>
                <textarea name="bio" class="form-control" rows="3" placeholder="Former collegiate athlete focused on biomechanics..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Save Trainer Profile
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
