<?php
/**
 * Admin Portal - Payments & Revenue Management
 */

$pageTitle = "Payments & Revenue";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$db = getDB();

// Handle Recording Manual Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'record_payment') {
    verify_csrf();

    $user_id = (int)$_POST['user_id'];
    $amount = (float)$_POST['amount'];
    $method = $_POST['payment_method'] ?? 'Cash';
    $notes = trim($_POST['notes'] ?? 'Front desk offline payment');

    if ($user_id > 0 && $amount > 0) {
        $txn = 'TXN-MAN-' . strtoupper(bin2hex(random_bytes(4)));
        $stmt = $db->prepare("
            INSERT INTO payments (user_id, transaction_ref, amount, payment_method, status, notes, payment_date)
            VALUES (?, ?, ?, ?, 'completed', ?, NOW())
        ");
        $stmt->execute([$user_id, $txn, $amount, $method, $notes]);
        flash('success', "Payment of " . money($amount) . " recorded successfully with ref {$txn}.");
        redirect('admin/payments.php');
    } else {
        flash('danger', 'Please choose a member and enter a valid positive amount.');
    }
}

// Summary Metrics
$totalRev = $db->query("SELECT SUM(amount) FROM payments WHERE status = 'completed'")->fetchColumn() ?: 0;
$cashRev = $db->query("SELECT SUM(amount) FROM payments WHERE status = 'completed' AND payment_method = 'Cash'")->fetchColumn() ?: 0;
$cardRev = $db->query("SELECT SUM(amount) FROM payments WHERE status = 'completed' AND payment_method != 'Cash'")->fetchColumn() ?: 0;

// Transactions
$payments = $db->query("
    SELECT p.*, u.full_name, u.email, mp.name AS plan_name
    FROM payments p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN memberships m ON p.membership_id = m.id
    LEFT JOIN membership_plans mp ON m.plan_id = mp.id
    ORDER BY p.payment_date DESC
")->fetchAll();

// All members for dropdown
$members = $db->query("SELECT id, full_name, email FROM users WHERE role = 'member' ORDER BY full_name ASC")->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #fff;">Financial Transactions & Payments</h1>
        <p style="color: var(--admin-muted); font-size: 13px;">Monitor gym revenues, processed membership dues, and record offline cash transactions.</p>
    </div>
</div>

<!-- Revenue Stat Cards -->
<div class="stats-grid" style="margin-bottom: 25px;">
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-title">Total Processed Revenue</span>
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">💵</div>
        </div>
        <div class="stat-value" style="color: #10b981;"><?= money($totalRev) ?></div>
        <div class="stat-desc">All confirmed payments</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-title">Cash Collections</span>
            <div class="stat-icon">💵</div>
        </div>
        <div class="stat-value"><?= money($cashRev) ?></div>
        <div class="stat-desc">Front desk cash payments</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-title">Electronic / Card</span>
            <div class="stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">💳</div>
        </div>
        <div class="stat-value" style="color: #3b82f6;"><?= money($cardRev) ?></div>
        <div class="stat-desc">Card, PayPal, and online</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 360px; gap: 30px; align-items: start;">
    <!-- Transactions Table -->
    <div class="data-card">
        <div class="data-card-header">
            <div class="data-card-title">All Payment Transactions (<?= count($payments) ?>)</div>
        </div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Transaction Ref</th>
                        <th>Member</th>
                        <th>Description / Plan</th>
                        <th>Date & Time</th>
                        <th>Method</th>
                        <th>Amount</th>
                        <th style="text-align: right;">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td style="font-family: monospace; font-weight: 700; color: #fff;">
                                    <?= htmlspecialchars($p['transaction_ref']) ?>
                                </td>
                                <td>
                                    <strong style="color: #fff; font-size: 14px;"><?= htmlspecialchars($p['full_name']) ?></strong>
                                    <div style="font-size: 11px; color: var(--admin-muted);"><?= htmlspecialchars($p['email']) ?></div>
                                </td>
                                <td style="font-size: 13px;">
                                    <?= htmlspecialchars($p['plan_name'] ?? $p['notes'] ?? 'General Gym Payment') ?>
                                </td>
                                <td style="font-size: 13px; color: var(--admin-muted);">
                                    <?= format_date($p['payment_date'], 'M d, Y - g:i A') ?>
                                </td>
                                <td style="font-size: 13px; color: var(--text-secondary);">
                                    <?= htmlspecialchars($p['payment_method']) ?>
                                </td>
                                <td style="font-weight: 800; color: var(--admin-primary); font-size: 15px;">
                                    <?= money($p['amount']) ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= url('member/invoice.php?id=' . $p['id']) ?>" target="_blank" class="action-btn action-btn-view">
                                        📄 Receipt
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; color: var(--admin-muted); padding: 40px;">No transactions recorded.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Record Manual Payment Form -->
    <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 14px; padding: 24px;">
        <h3 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 16px;">+ Record Front Desk Payment</h3>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="record_payment">

            <div class="form-group">
                <label>Select Member *</label>
                <select name="user_id" class="form-control" required>
                    <option value="">-- Choose Member --</option>
                    <?php foreach ($members as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['full_name']) ?> (<?= htmlspecialchars($m['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Amount (<?= CURRENCY ?>) *</label>
                <input type="number" step="0.01" name="amount" class="form-control" placeholder="59.99" required>
            </div>

            <div class="form-group">
                <label>Payment Method *</label>
                <select name="payment_method" class="form-control" required>
                    <option value="Cash">Cash (Front Desk)</option>
                    <option value="Credit Card">Credit Card Terminal</option>
                    <option value="Bank Transfer">Bank Wire Transfer</option>
                    <option value="PayPal">PayPal</option>
                </select>
            </div>

            <div class="form-group">
                <label>Notes / Description</label>
                <input type="text" name="notes" class="form-control" placeholder="e.g. Monthly renewal cash payment" value="Front desk cash payment">
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Record Payment
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
