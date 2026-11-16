<?php
/**
 * Admin Portal - Member Profile & Detailed View
 */

$pageTitle = "Member Profile";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$db = getDB();

$userId = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'member' LIMIT 1");
$stmt->execute([$userId]);
$member = $stmt->fetch();

if (!$member) {
    flash('danger', 'Member not found.');
    redirect('admin/members.php');
}

// Handle Manual Plan Assignment / Renewal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign_plan') {
    verify_csrf();
    $plan_id = (int)$_POST['plan_id'];
    $pay_method = $_POST['payment_method'] ?? 'Cash';

    $pStmt = $db->prepare("SELECT * FROM membership_plans WHERE id = ? LIMIT 1");
    $pStmt->execute([$plan_id]);
    $plan = $pStmt->fetch();

    if ($plan) {
        $days = (int)$plan['duration_days'];
        $price = $plan['price'];
        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime("+{$days} days"));

        $db->prepare("UPDATE memberships SET status = 'expired' WHERE user_id = ? AND status = 'active'")->execute([$userId]);

        $mStmt = $db->prepare("
            INSERT INTO memberships (user_id, plan_id, start_date, end_date, amount_paid, payment_status, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'paid', 'active', NOW())
        ");
        $mStmt->execute([$userId, $plan_id, $start, $end, $price]);
        $memId = $db->lastInsertId();

        $txn = 'TXN-FD-' . strtoupper(bin2hex(random_bytes(4)));
        $db->prepare("
            INSERT INTO payments (user_id, membership_id, transaction_ref, amount, payment_method, status, notes, payment_date)
            VALUES (?, ?, ?, ?, ?, 'completed', 'Front desk renewal', NOW())
        ")->execute([$userId, $memId, $txn, $price, $pay_method]);

        flash('success', "Assigned plan {$plan['name']} to {$member['full_name']} successfully!");
        redirect('admin/member-view.php?id=' . $userId);
    }
}

// Fetch active membership
$activePlan = get_active_membership($userId);

// Fetch membership history
$historyStmt = $db->prepare("
    SELECT m.*, mp.name AS plan_name 
    FROM memberships m 
    JOIN membership_plans mp ON m.plan_id = mp.id 
    WHERE m.user_id = ? 
    ORDER BY m.id DESC
");
$historyStmt->execute([$userId]);
$membershipHistory = $historyStmt->fetchAll();

// Fetch payments
$payStmt = $db->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY payment_date DESC");
$payStmt->execute([$userId]);
$payments = $payStmt->fetchAll();

// Fetch bookings
$bStmt = $db->prepare("
    SELECT b.*, c.name AS class_name, s.day_of_week, s.start_time, s.end_time, s.room
    FROM class_bookings b
    JOIN class_schedules s ON b.schedule_id = s.id
    JOIN classes c ON s.class_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$bStmt->execute([$userId]);
$bookings = $bStmt->fetchAll();

// All plans for assignment dropdown
$allPlans = $db->query("SELECT * FROM membership_plans WHERE status = 'active' ORDER BY price ASC")->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="margin-bottom: 24px;">
    <a href="<?= url('admin/members.php') ?>" style="color: var(--admin-primary); font-size: 13px; font-weight: 600;">&larr; Back to Members Directory</a>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; flex-wrap: wrap; gap: 14px;">
        <h1 style="font-size: 24px; font-weight: 800; color: #fff;">
            <?= htmlspecialchars($member['full_name']) ?>
            <span style="font-size: 14px; font-weight: 600; color: var(--admin-muted); margin-left: 8px;">(Member ID #<?= str_pad($member['id'], 5, '0', STR_PAD_LEFT) ?>)</span>
        </h1>
        <span class="badge <?= $member['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
            Account <?= ucfirst($member['status']) ?>
        </span>
    </div>
</div>

<!-- 2-Column Overview -->
<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; margin-bottom: 30px;">
    <!-- Profile Card -->
    <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 14px; padding: 26px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: var(--admin-primary); display: flex; align-items: center; justify-content: center; font-family: var(--font-heading); font-size: 32px; color: #fff; margin: 0 auto 12px;">
                <?= strtoupper(substr($member['full_name'], 0, 1)) ?>
            </div>
            <h3 style="color: #fff; font-size: 18px; font-weight: 700;"><?= htmlspecialchars($member['full_name']) ?></h3>
            <div style="font-size: 13px; color: var(--admin-muted);"><?= htmlspecialchars($member['email']) ?></div>
        </div>

        <div style="font-size: 13px; color: var(--text-secondary); line-height: 1.8; border-top: 1px solid var(--admin-border); padding-top: 16px;">
            <div><strong>Phone:</strong> <?= htmlspecialchars($member['phone'] ?? '-') ?></div>
            <div><strong>Emergency Contact:</strong> <?= htmlspecialchars($member['emergency_contact'] ?? '-') ?></div>
            <div><strong>Gender:</strong> <?= ucfirst($member['gender'] ?? 'Other') ?></div>
            <div><strong>Address:</strong> <?= htmlspecialchars($member['address'] ?? '-') ?></div>
            <div><strong>Member Since:</strong> <?= format_date($member['created_at']) ?></div>
            <div style="margin-top: 8px;"><strong>Goals:</strong><br><span style="color: var(--admin-muted);"><?= htmlspecialchars($member['fitness_goals'] ?? 'None specified') ?></span></div>
        </div>
    </div>

    <!-- Active Membership & Assign Plan -->
    <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 14px; padding: 26px;">
        <h3 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 16px;">Current Membership Status</h3>

        <?php if ($activePlan): ?>
            <div style="background: rgba(249, 115, 22, 0.1); border: 1px solid rgba(249, 115, 22, 0.3); border-radius: 10px; padding: 20px; margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span class="badge badge-success" style="margin-bottom: 6px;">Active Gym Pass</span>
                        <div style="font-size: 20px; font-weight: 800; color: #fff;"><?= htmlspecialchars($activePlan['plan_name']) ?></div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 12px; color: var(--admin-muted);">Expiration Date</div>
                        <div style="font-size: 16px; font-weight: 800; color: var(--admin-primary);"><?= format_date($activePlan['end_date']) ?></div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 10px; padding: 18px; margin-bottom: 24px; color: #fca5a5; font-size: 14px;">
                ⚠️ This member currently has no active subscription.
            </div>
        <?php endif; ?>

        <h4 style="font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 12px;">Assign or Renew Membership Plan:</h4>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="assign_plan">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="font-size: 12px; color: var(--admin-muted);">Select Plan</label>
                    <select name="plan_id" class="form-control" required>
                        <?php foreach ($allPlans as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['name']) ?> - <?= money($p['price']) ?> (<?= $p['duration_days'] ?>d)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; color: var(--admin-muted);">Payment Method</label>
                    <select name="payment_method" class="form-control">
                        <option value="Cash">Cash (Front Desk)</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-sm">
                Confirm Plan Assignment &rarr;
            </button>
        </form>
    </div>
</div>

<!-- Tabs for Bookings & Payments -->
<div class="data-card">
    <div class="data-card-header">
        <div class="data-card-title">Class Reservations (<?= count($bookings) ?>)</div>
    </div>
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Class Name</th>
                    <th>Date & Time</th>
                    <th>Studio</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td style="font-weight: 700; color: #fff;"><?= htmlspecialchars($b['class_name']) ?></td>
                            <td><?= format_date($b['booking_date']) ?> (<?= format_time($b['start_time']) ?>)</td>
                            <td><?= htmlspecialchars($b['room']) ?></td>
                            <td>
                                <span class="badge <?= $b['status'] === 'confirmed' ? 'badge-warning' : ($b['status'] === 'attended' ? 'badge-success' : 'badge-danger') ?>">
                                    <?= ucfirst($b['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; color: var(--admin-muted); padding: 25px;">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <div class="data-card-title">Payment & Invoicing History (<?= count($payments) ?>)</div>
    </div>
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Transaction Ref</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th style="text-align: right;">Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: #fff;"><?= htmlspecialchars($p['transaction_ref']) ?></td>
                            <td><?= format_date($p['payment_date'], 'M d, Y - g:i A') ?></td>
                            <td><?= htmlspecialchars($p['payment_method']) ?></td>
                            <td style="font-weight: 800; color: var(--admin-primary);"><?= money($p['amount']) ?></td>
                            <td><span class="badge badge-success"><?= ucfirst($p['status']) ?></span></td>
                            <td style="text-align: right;">
                                <a href="<?= url('member/invoice.php?id=' . $p['id']) ?>" target="_blank" class="action-btn action-btn-view">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--admin-muted); padding: 25px;">No payments recorded.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
