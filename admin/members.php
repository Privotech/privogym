<?php
/**
 * Admin Portal - Members Management
 */

$pageTitle = "Members Directory";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$db = getDB();

// Handle Status Toggle or Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
        $userId = (int)$_POST['user_id'];
        $newStatus = $_POST['status'] === 'active' ? 'inactive' : 'active';
        $db->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'member'")->execute([$newStatus, $userId]);
        flash('success', "Member status updated to {$newStatus}.");
        redirect('admin/members.php');
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_member') {
        $userId = (int)$_POST['user_id'];
        $db->prepare("DELETE FROM users WHERE id = ? AND role = 'member'")->execute([$userId]);
        flash('info', 'Member account and associated records deleted.');
        redirect('admin/members.php');
    }
}

// Search and Filter
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$query = "
    SELECT u.*, 
           (SELECT mp.name FROM memberships m JOIN membership_plans mp ON m.plan_id = mp.id WHERE m.user_id = u.id AND m.status = 'active' AND m.end_date >= CURDATE() LIMIT 1) AS active_plan,
           (SELECT m.end_date FROM memberships m WHERE m.user_id = u.id AND m.status = 'active' AND m.end_date >= CURDATE() LIMIT 1) AS plan_expiry
    FROM users u
    WHERE u.role = 'member'
";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if (!empty($statusFilter)) {
    $query .= " AND u.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY u.id DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$members = $stmt->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #fff;">Members Directory</h1>
        <p style="color: var(--admin-muted); font-size: 13px;">Manage registered gym members, membership statuses, and profiles.</p>
    </div>
    <a href="<?= url('admin/member-add.php') ?>" class="btn btn-primary btn-sm">+ Register New Member</a>
</div>

<!-- Search & Filter Card -->
<div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 12px; padding: 18px 24px; margin-bottom: 24px;">
    <form method="GET" action="" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 240px;">
            <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone..." value="<?= htmlspecialchars($search) ?>">
        </div>

        <div style="width: 160px;">
            <select name="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active Only</option>
                <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
            </select>
        </div>

        <button type="submit" class="btn btn-outline btn-sm">Filter</button>
        <?php if (!empty($search) || !empty($statusFilter)): ?>
            <a href="<?= url('admin/members.php') ?>" class="btn btn-outline btn-sm" style="color: #ef4444;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Table Card -->
<div class="data-card">
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>Full Name & Email</th>
                    <th>Phone</th>
                    <th>Active Membership</th>
                    <th>Expires</th>
                    <th>Account Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($members)): ?>
                    <?php foreach ($members as $m): ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: var(--admin-muted);">
                                #<?= str_pad($m['id'], 5, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td>
                                <strong style="color: #fff; font-size: 14px;"><?= htmlspecialchars($m['full_name']) ?></strong>
                                <div style="font-size: 12px; color: var(--admin-muted);"><?= htmlspecialchars($m['email']) ?></div>
                            </td>
                            <td style="font-size: 13px; color: var(--text-secondary);">
                                <?= htmlspecialchars($m['phone'] ?? '-') ?>
                            </td>
                            <td>
                                <?php if (!empty($m['active_plan'])): ?>
                                    <span class="badge badge-primary"><?= htmlspecialchars($m['active_plan']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning">No Plan</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 13px; color: var(--text-secondary);">
                                <?= !empty($m['plan_expiry']) ? format_date($m['plan_expiry']) : '-' ?>
                            </td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="user_id" value="<?= $m['id'] ?>">
                                    <input type="hidden" name="status" value="<?= $m['status'] ?>">
                                    <button type="submit" class="badge <?= $m['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>" style="cursor: pointer; border: none;" title="Click to toggle status">
                                        <?= ucfirst($m['status']) ?>
                                    </button>
                                </form>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-btns" style="justify-content: flex-end;">
                                    <a href="<?= url('admin/member-view.php?id=' . $m['id']) ?>" class="action-btn action-btn-view">
                                        Details
                                    </a>
                                    <form method="POST" action="" onsubmit="return confirm('Permanently delete <?= htmlspecialchars($m['full_name']) ?>?');" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_member">
                                        <input type="hidden" name="user_id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="action-btn action-btn-delete" title="Delete Member">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--admin-muted); padding: 40px;">
                            No members found matching your search criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
