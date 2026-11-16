<?php
/**
 * Member Portal - Membership Plan & Subscription Management
 */

$pageTitle = "My Membership Plan";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_member();
$user = current_user();
$db = getDB();

// Handle Plan Upgrade / Renewal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['renew_plan_id'])) {
    verify_csrf();
    $plan_id = (int)$_POST['renew_plan_id'];

    try {
        $pStmt = $db->prepare("SELECT * FROM membership_plans WHERE id = ? AND status = 'active' LIMIT 1");
        $pStmt->execute([$plan_id]);
        $targetPlan = $pStmt->fetch();

        if ($targetPlan) {
            $days = (int)$targetPlan['duration_days'];
            $price = $targetPlan['price'];
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime("+{$days} days"));

            // Deactivate previous active memberships
            $db->prepare("UPDATE memberships SET status = 'expired' WHERE user_id = ? AND status = 'active'")->execute([$user['id']]);

            // Insert new membership
            $mStmt = $db->prepare("
                INSERT INTO memberships (user_id, plan_id, start_date, end_date, amount_paid, payment_status, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'paid', 'active', NOW())
            ");
            $mStmt->execute([$user['id'], $plan_id, $startDate, $endDate, $price]);
            $newMemId = $db->lastInsertId();

            // Insert payment transaction
            $txnRef = 'TXN-' . strtoupper(bin2hex(random_bytes(4)));
            $payStmt = $db->prepare("
                INSERT INTO payments (user_id, membership_id, transaction_ref, amount, payment_method, status, notes, payment_date)
                VALUES (?, ?, ?, ?, 'Credit Card', 'completed', ?, NOW())
            ");
            $payStmt->execute([$user['id'], $newMemId, $txnRef, $price, "Subscription payment for {$targetPlan['name']}"]);

            flash('success', "Your membership has been activated for {$targetPlan['name']}! Valid through " . format_date($endDate));
            redirect('member/membership.php');
        }
    } catch (Exception $e) {
        flash('danger', 'Renewal failed: ' . $e->getMessage());
    }
}

$activeMembership = get_active_membership($user['id']);

// Fetch all available plans
$availablePlans = [];
try {
    $stmt = $db->query("SELECT * FROM membership_plans WHERE status = 'active' ORDER BY price ASC");
    $availablePlans = $stmt->fetchAll();
} catch (Exception $e) {}

require_once __DIR__ . '/includes/member_header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom: 30px;">
        <h1 style="font-family: var(--font-heading); font-size: 34px; color: #fff;">MY MEMBERSHIP & SUBSCRIPTION</h1>
        <p style="color: var(--text-muted); font-size: 14px;">Review your current gym access level, renewal status, or upgrade to a higher tier.</p>
    </div>

    <!-- Current Membership Status Box -->
    <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 16px; padding: 30px; margin-bottom: 40px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
            <div>
                <span class="badge <?= $activeMembership ? 'badge-success' : 'badge-danger' ?>" style="margin-bottom: 8px;">
                    <?= $activeMembership ? 'ACTIVE PLAN' : 'INACTIVE / NO PLAN' ?>
                </span>
                <h2 style="font-size: 24px; font-weight: 800; color: #fff;">
                    <?= htmlspecialchars($activeMembership['plan_name'] ?? 'No Active Subscription') ?>
                </h2>
            </div>
            
            <?php if ($activeMembership): ?>
                <div style="text-align: right;">
                    <div style="font-size: 12px; color: var(--mem-muted); text-transform: uppercase;">Expiration Date</div>
                    <div style="font-size: 18px; font-weight: 800; color: var(--mem-primary);">
                        <?= format_date($activeMembership['end_date']) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($activeMembership): ?>
            <?php $features = json_decode($activeMembership['features_json'], true) ?: []; ?>
            <div style="border-top: 1px solid var(--mem-border); padding-top: 20px;">
                <h4 style="font-size: 14px; font-weight: 700; color: var(--text-secondary); margin-bottom: 12px;">Included In Your Membership:</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px;">
                    <?php foreach ($features as $f): ?>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-secondary);">
                            <span style="color: var(--accent);">✓</span> <?= htmlspecialchars($f) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p style="color: var(--mem-muted); font-size: 14px;">Select one of our membership packages below to unlock full gym access and class reservations.</p>
        <?php endif; ?>
    </div>

    <!-- Upgrade / Change Plan Grid -->
    <h3 style="font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 20px;">Available Membership Packages</h3>
    
    <div class="pricing-grid">
        <?php foreach ($availablePlans as $p): ?>
            <?php 
                $isCurrent = $activeMembership && (int)$activeMembership['plan_id'] === (int)$p['id'];
                $features = json_decode($p['features_json'], true) ?: [];
            ?>
            <div class="pricing-card <?= $isCurrent ? 'featured' : '' ?>">
                <?php if ($isCurrent): ?>
                    <div class="pricing-featured-badge" style="background: var(--accent);">CURRENT ACTIVE PLAN</div>
                <?php endif; ?>

                <div class="pricing-name"><?= htmlspecialchars($p['name']) ?></div>
                <p class="pricing-desc"><?= htmlspecialchars($p['description']) ?></p>

                <div class="pricing-price">
                    <?= money($p['price']) ?>
                    <span>/ <?= $p['duration_days'] >= 365 ? 'yr' : 'mo' ?></span>
                </div>

                <ul class="pricing-features">
                    <?php foreach ($features as $f): ?>
                        <li class="pricing-feature-item">
                            <span style="color: var(--accent); margin-right: 6px;">✓</span>
                            <span><?= htmlspecialchars($f) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <form method="POST" action="" onsubmit="return confirm('Confirm subscription to <?= htmlspecialchars($p['name']) ?> for <?= money($p['price']) ?>?');">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="renew_plan_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn <?= $isCurrent ? 'btn-outline-primary' : 'btn-primary' ?> btn-block">
                        <?= $isCurrent ? 'Renew Current Plan' : 'Switch / Upgrade Plan' ?> &rarr;
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/member_footer.php'; ?>
