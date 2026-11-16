<?php
/**
 * Member Portal - Payments & Billing Receipts
 */

$pageTitle = "Billing & Invoices";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_member();
$user = current_user();
$db = getDB();

$payments = [];
try {
    $stmt = $db->prepare("
        SELECT p.*, m.start_date, m.end_date, mp.name AS plan_name
        FROM payments p
        LEFT JOIN memberships m ON p.membership_id = m.id
        LEFT JOIN membership_plans mp ON m.plan_id = mp.id
        WHERE p.user_id = ?
        ORDER BY p.payment_date DESC
    ");
    $stmt->execute([$user['id']]);
    $payments = $stmt->fetchAll();
} catch (Exception $e) {}

require_once __DIR__ . '/includes/member_header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom: 30px;">
        <h1 style="font-family: var(--font-heading); font-size: 34px; color: #fff;">BILLING & PAYMENT INVOICES</h1>
        <p style="color: var(--text-muted); font-size: 14px;">Review all past membership dues, subscription transactions, and generate official receipts.</p>
    </div>

    <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 16px; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #0d1424; text-align: left;">
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase;">Transaction ID</th>
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase;">Plan Description</th>
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase;">Payment Date</th>
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase;">Payment Method</th>
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase;">Amount</th>
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase;">Status</th>
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase; text-align: right;">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $p): ?>
                            <tr style="border-bottom: 1px solid var(--mem-border);">
                                <td style="padding: 16px 20px; font-family: monospace; font-weight: 700; color: #fff;">
                                    <?= htmlspecialchars($p['transaction_ref']) ?>
                                </td>
                                <td style="padding: 16px 20px; font-size: 14px;">
                                    <strong><?= htmlspecialchars($p['plan_name'] ?? 'Membership Renewal') ?></strong>
                                    <?php if (!empty($p['notes'])): ?>
                                        <div style="font-size: 12px; color: var(--mem-muted);"><?= htmlspecialchars($p['notes']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 16px 20px; font-size: 14px; color: var(--text-secondary);">
                                    <?= format_date($p['payment_date'], 'M d, Y - g:i A') ?>
                                </td>
                                <td style="padding: 16px 20px; font-size: 14px; color: var(--text-secondary);">
                                    💳 <?= htmlspecialchars($p['payment_method']) ?>
                                </td>
                                <td style="padding: 16px 20px; font-weight: 800; color: var(--mem-primary); font-size: 16px;">
                                    <?= money($p['amount']) ?>
                                </td>
                                <td style="padding: 16px 20px;">
                                    <span class="badge badge-success"><?= ucfirst($p['status']) ?></span>
                                </td>
                                <td style="padding: 16px 20px; text-align: right;">
                                    <a href="<?= url('member/invoice.php?id=' . $p['id']) ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 6px 12px; font-size: 12px;">
                                        📄 View Invoice
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 40px; text-align: center; color: var(--mem-muted);">
                                No payment records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/member_footer.php'; ?>
