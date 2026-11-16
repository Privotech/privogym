<?php
/**
 * Printable Invoice & Digital Receipt
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_login();
$currentUser = current_user();

$paymentId = (int)($_GET['id'] ?? 0);
$db = getDB();

$payment = null;
try {
    $stmt = $db->prepare("
        SELECT p.*, u.full_name, u.email, u.phone, u.address,
               m.start_date, m.end_date, mp.name AS plan_name, mp.duration_days
        FROM payments p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN memberships m ON p.membership_id = m.id
        LEFT JOIN membership_plans mp ON m.plan_id = mp.id
        WHERE p.id = ? LIMIT 1
    ");
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch();
} catch (Exception $e) {}

if (!$payment) {
    die("Invoice not found.");
}

// Security: Only the invoice owner or an admin can view this receipt
if (!is_admin() && $payment['user_id'] !== $currentUser['id']) {
    die("Access denied.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= htmlspecialchars($payment['transaction_ref']) ?> - <?= APP_NAME ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            padding: 40px 20px;
            margin: 0;
        }
        .invoice-card {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 45px;
            border: 1px solid #e2e8f0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 25px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .logo span { color: #f97316; }
        .invoice-meta { text-align: right; }
        .meta-title { font-size: 24px; font-weight: 800; color: #f97316; margin-bottom: 4px; }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
        }
        .table th {
            background: #f8fafc;
            padding: 12px 16px;
            text-align: left;
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
        }
        .table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        .total-box {
            text-align: right;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #0f172a;
        }
        .total-amount {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
        }
        .btn-print {
            background: #f97316;
            color: #fff;
            padding: 10px 22px;
            border-radius: 6px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-card { box-shadow: none; border: none; padding: 0; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

<div style="max-width: 800px; margin: 0 auto; text-align: right;">
    <button class="btn-print" onclick="window.print()">🖨️ Print / Download PDF</button>
</div>

<div class="invoice-card">
    <div class="header">
        <div>
            <div class="logo">IRON<span>PULSE</span> FITNESS</div>
            <p style="color: #64748b; font-size: 13px; margin: 6px 0 0; line-height: 1.5;">
                742 Fitness Boulevard, Metro City, NY 10001<br>
                contact@ironpulsefitness.com &bull; +1 (555) 234-5678
            </p>
        </div>
        <div class="invoice-meta">
            <div class="meta-title">OFFICIAL RECEIPT</div>
            <div style="font-size: 13px; color: #64748b;">Ref: <strong><?= htmlspecialchars($payment['transaction_ref']) ?></strong></div>
            <div style="font-size: 13px; color: #64748b;">Date: <?= format_date($payment['payment_date'], 'F d, Y') ?></div>
            <div style="font-size: 13px; color: #16a34a; font-weight: 700; margin-top: 4px;">STATUS: PAID IN FULL</div>
        </div>
    </div>

    <div class="grid-2">
        <div>
            <div style="font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Billed To:</div>
            <div style="font-size: 16px; font-weight: 700; color: #0f172a;"><?= htmlspecialchars($payment['full_name']) ?></div>
            <div style="font-size: 13px; color: #64748b; margin-top: 4px;"><?= htmlspecialchars($payment['email']) ?></div>
            <div style="font-size: 13px; color: #64748b;"><?= htmlspecialchars($payment['phone'] ?? '-') ?></div>
            <div style="font-size: 13px; color: #64748b;"><?= htmlspecialchars($payment['address'] ?? 'Primary Member Address') ?></div>
        </div>

        <div>
            <div style="font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">Payment Information:</div>
            <div style="font-size: 13px; color: #475569; line-height: 1.6;">
                Payment Method: <strong><?= htmlspecialchars($payment['payment_method']) ?></strong><br>
                Membership Start: <strong><?= format_date($payment['start_date']) ?></strong><br>
                Membership Expiry: <strong><?= format_date($payment['end_date']) ?></strong><br>
                Duration: <strong><?= (int)$payment['duration_days'] ?> Days</strong>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Period</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($payment['plan_name'] ?? 'Gym Membership Package') ?></strong><br>
                    <span style="font-size: 12px; color: #64748b;"><?= htmlspecialchars($payment['notes'] ?? 'Access to training facilities and group fitness classes') ?></span>
                </td>
                <td style="color: #64748b; font-size: 13px;">
                    <?= format_date($payment['start_date']) ?> &mdash; <?= format_date($payment['end_date']) ?>
                </td>
                <td style="text-align: right; font-weight: 700;">
                    <?= money($payment['amount']) ?>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        <div style="font-size: 13px; color: #64748b;">Total Paid:</div>
        <div class="total-amount"><?= money($payment['amount']) ?></div>
    </div>

    <div style="margin-top: 45px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center;">
        Thank you for being part of the IronPulse athletic community. Keep breaking limits!
    </div>
</div>

</body>
</html>
