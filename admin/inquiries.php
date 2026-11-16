<?php
/**
 * Admin Portal - Contact Form Inquiries Management
 */

$pageTitle = "Contact Inquiries";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$db = getDB();

// Handle Status Change or Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $id = (int)$_POST['inquiry_id'];
        $newStatus = $_POST['new_status'];
        if (in_array($newStatus, ['unread', 'read', 'replied'])) {
            $db->prepare("UPDATE inquiries SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
            flash('success', "Inquiry marked as {$newStatus}.");
        }
        redirect('admin/inquiries.php');

    } elseif ($action === 'delete_inquiry') {
        $id = (int)$_POST['inquiry_id'];
        $db->prepare("DELETE FROM inquiries WHERE id = ?")->execute([$id]);
        flash('info', 'Inquiry deleted.');
        redirect('admin/inquiries.php');
    }
}

$inquiries = $db->query("SELECT * FROM inquiries ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #fff;">Website Inquiries & Messages</h1>
        <p style="color: var(--admin-muted); font-size: 13px;">Manage questions and facility tour requests submitted via the public contact form.</p>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <div class="data-card-title">All Inquiries (<?= count($inquiries) ?>)</div>
    </div>
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sender & Contact</th>
                    <th>Subject & Message</th>
                    <th>Received At</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($inquiries)): ?>
                    <?php foreach ($inquiries as $inq): ?>
                        <tr>
                            <td>
                                <strong style="color: #fff; font-size: 14px;"><?= htmlspecialchars($inq['name']) ?></strong>
                                <div style="font-size: 12px; color: var(--admin-muted);"><?= htmlspecialchars($inq['email']) ?></div>
                                <div style="font-size: 11px; color: var(--admin-muted);"><?= htmlspecialchars($inq['phone'] ?? '-') ?></div>
                            </td>
                            <td style="max-width: 400px;">
                                <div style="font-weight: 700; color: #fff; margin-bottom: 4px;"><?= htmlspecialchars($inq['subject']) ?></div>
                                <div style="font-size: 13px; color: var(--text-secondary); line-height: 1.6; background: rgba(255,255,255,0.02); padding: 8px 12px; border-radius: 6px;">
                                    <?= nl2br(htmlspecialchars($inq['message'])) ?>
                                </div>
                            </td>
                            <td style="font-size: 13px; color: var(--admin-muted);">
                                <?= format_date($inq['created_at'], 'M d, Y - g:i A') ?>
                            </td>
                            <td>
                                <?php if ($inq['status'] === 'unread'): ?>
                                    <span class="badge badge-warning">Unread</span>
                                <?php elseif ($inq['status'] === 'read'): ?>
                                    <span class="badge badge-info">Read</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Replied</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-btns" style="justify-content: flex-end;">
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">

                                        <?php if ($inq['status'] !== 'replied'): ?>
                                            <button type="submit" name="new_status" value="replied" class="action-btn" style="background: rgba(16,185,129,0.2); color: #6ee7b7;">
                                                Mark Replied
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="new_status" value="unread" class="action-btn action-btn-view">
                                                Mark Unread
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                    <form method="POST" action="" onsubmit="return confirm('Delete this inquiry?');" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_inquiry">
                                        <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
                                        <button type="submit" class="action-btn action-btn-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; color: var(--admin-muted); padding: 40px;">No contact inquiries received.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
