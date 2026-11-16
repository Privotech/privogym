<?php
/**
 * Admin Portal - Membership Plans Management
 */

$pageTitle = "Membership Plans";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$db = getDB();

// Handle Add / Edit / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'create_plan') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $duration = (int)($_POST['duration_days'] ?? 30);
        $price = (float)($_POST['price'] ?? 0);
        $badge = trim($_POST['badge'] ?? '');
        $features_raw = trim($_POST['features'] ?? '');
        $is_popular = isset($_POST['is_popular']) ? 1 : 0;

        $features = array_filter(array_map('trim', explode("\n", $features_raw)));
        $features_json = json_encode(array_values($features));

        $stmt = $db->prepare("
            INSERT INTO membership_plans (name, description, duration_days, price, badge, features_json, is_popular, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([$name, $description, $duration, $price, $badge, $features_json, $is_popular]);
        flash('success', "Plan '{$name}' created successfully.");
        redirect('admin/plans.php');

    } elseif ($action === 'delete_plan') {
        $planId = (int)$_POST['plan_id'];
        try {
            $stmt = $db->prepare("DELETE FROM membership_plans WHERE id = ?");
            $stmt->execute([$planId]);
            flash('info', 'Membership plan deleted.');
        } catch (Exception $e) {
            flash('danger', 'Cannot delete this plan as it is linked to existing member subscriptions. You can deactivate it instead.');
        }
        redirect('admin/plans.php');
    }
}

$plans = $db->query("SELECT * FROM membership_plans ORDER BY price ASC")->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #fff;">Membership Plans</h1>
        <p style="color: var(--admin-muted); font-size: 13px;">Create and manage subscription packages, pricing, durations, and included perks.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 360px; gap: 30px; align-items: start;">
    <!-- Plans Table -->
    <div class="data-card">
        <div class="data-card-header">
            <div class="data-card-title">Existing Plans (<?= count($plans) ?>)</div>
        </div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Plan Name</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Popular</th>
                        <th>Features</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($plans)): ?>
                        <?php foreach ($plans as $p): ?>
                            <?php $features = json_decode($p['features_json'], true) ?: []; ?>
                            <tr>
                                <td>
                                    <strong style="color: #fff; font-size: 15px;"><?= htmlspecialchars($p['name']) ?></strong>
                                    <?php if (!empty($p['badge'])): ?>
                                        <span class="badge badge-primary" style="font-size: 10px; margin-left: 6px;"><?= htmlspecialchars($p['badge']) ?></span>
                                    <?php endif; ?>
                                    <div style="font-size: 12px; color: var(--admin-muted); margin-top: 2px;"><?= htmlspecialchars($p['description']) ?></div>
                                </td>
                                <td style="font-size: 13px; color: #fff;">
                                    <?= (int)$p['duration_days'] ?> Days
                                </td>
                                <td style="font-weight: 800; color: var(--admin-primary); font-size: 16px;">
                                    <?= money($p['price']) ?>
                                </td>
                                <td>
                                    <?= $p['is_popular'] ? '<span class="badge badge-warning">Featured</span>' : '<span style="color:var(--admin-muted); font-size:12px;">No</span>' ?>
                                </td>
                                <td style="font-size: 12px; color: var(--text-secondary);">
                                    <?= count($features) ?> Perks configured
                                </td>
                                <td style="text-align: right;">
                                    <form method="POST" action="" onsubmit="return confirm('Delete this plan?');" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_plan">
                                        <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="action-btn action-btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--admin-muted); padding: 30px;">No plans found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Plan Form -->
    <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 14px; padding: 24px;">
        <h3 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 16px;">+ Create New Plan</h3>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="create_plan">

            <div class="form-group">
                <label>Plan Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Student Saver" required>
            </div>

            <div class="form-group">
                <label>Short Description</label>
                <input type="text" name="description" class="form-control" placeholder="Targeted for university students">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label>Duration (Days)</label>
                    <input type="number" name="duration_days" class="form-control" value="30" required>
                </div>
                <div class="form-group">
                    <label>Price (<?= CURRENCY ?>)</label>
                    <input type="number" step="0.01" name="price" class="form-control" placeholder="39.99" required>
                </div>
            </div>

            <div class="form-group">
                <label>Badge Label (Optional)</label>
                <input type="text" name="badge" class="form-control" placeholder="e.g. Best Value, Student Offer">
            </div>

            <div class="form-group">
                <label>Features / Included Perks (One per line)</label>
                <textarea name="features" class="form-control" rows="4" placeholder="Gym floor access&#10;Locker room access&#10;Free fitness assessment" required></textarea>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="is_popular" id="is_popular" value="1" style="width: auto;">
                <label for="is_popular" style="margin-bottom: 0; cursor: pointer;">Highlight as "Most Popular"</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Save Membership Plan
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
