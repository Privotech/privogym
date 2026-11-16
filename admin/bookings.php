<?php
/**
 * Admin Portal - Class Bookings & Attendance Management
 */

$pageTitle = "Class Bookings & Attendance";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$db = getDB();

// Handle Status Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id']) && isset($_POST['new_status'])) {
    verify_csrf();
    $bId = (int)$_POST['booking_id'];
    $newStatus = $_POST['new_status'];

    if (in_array($newStatus, ['confirmed', 'attended', 'cancelled'])) {
        $db->prepare("UPDATE class_bookings SET status = ? WHERE id = ?")->execute([$newStatus, $bId]);
        flash('success', "Booking status updated to {$newStatus}.");
        redirect('admin/bookings.php');
    }
}

// Filters
$dateFilter = trim($_GET['date'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$query = "
    SELECT b.*, u.full_name AS member_name, u.email AS member_email, u.phone AS member_phone,
           c.name AS class_name, c.category, s.day_of_week, s.start_time, s.end_time, s.room,
           t.name AS trainer_name
    FROM class_bookings b
    JOIN users u ON b.user_id = u.id
    JOIN class_schedules s ON b.schedule_id = s.id
    JOIN classes c ON s.class_id = c.id
    LEFT JOIN trainers t ON c.trainer_id = t.id
    WHERE 1=1
";
$params = [];

if (!empty($dateFilter)) {
    $query .= " AND b.booking_date = ?";
    $params[] = $dateFilter;
}

if (!empty($statusFilter)) {
    $query .= " AND b.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY b.booking_date DESC, s.start_time ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #fff;">Class Bookings & Attendance</h1>
        <p style="color: var(--admin-muted); font-size: 13px;">Review reservations, filter by date, and verify athlete check-ins.</p>
    </div>
</div>

<!-- Filter Bar -->
<div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 12px; padding: 18px 24px; margin-bottom: 24px;">
    <form method="GET" action="" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
        <div style="width: 180px;">
            <label style="font-size: 12px; color: var(--admin-muted); margin-bottom: 4px; display: block;">Session Date</label>
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($dateFilter) ?>">
        </div>

        <div style="width: 180px;">
            <label style="font-size: 12px; color: var(--admin-muted); margin-bottom: 4px; display: block;">Status</label>
            <select name="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="attended" <?= $statusFilter === 'attended' ? 'selected' : '' ?>>Attended</option>
                <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>

        <div style="align-self: flex-end;">
            <button type="submit" class="btn btn-outline btn-sm">Apply Filter</button>
            <?php if (!empty($dateFilter) || !empty($statusFilter)): ?>
                <a href="<?= url('admin/bookings.php') ?>" class="btn btn-outline btn-sm" style="color: #ef4444;">Clear</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Bookings Table -->
<div class="data-card">
    <div class="data-card-header">
        <div class="data-card-title">Bookings Log (<?= count($bookings) ?>)</div>
    </div>
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Class & Discipline</th>
                    <th>Date & Time</th>
                    <th>Studio</th>
                    <th>Status</th>
                    <th style="text-align: right;">Update Attendance</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bookings)): ?>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <strong style="color: #fff; font-size: 14px;"><?= htmlspecialchars($b['member_name']) ?></strong>
                                <div style="font-size: 12px; color: var(--admin-muted);"><?= htmlspecialchars($b['member_email']) ?></div>
                                <div style="font-size: 11px; color: var(--admin-muted);"><?= htmlspecialchars($b['member_phone'] ?? '') ?></div>
                            </td>
                            <td>
                                <strong style="color: #fff;"><?= htmlspecialchars($b['class_name']) ?></strong>
                                <div><span class="badge badge-primary" style="font-size: 10px;"><?= htmlspecialchars($b['category']) ?></span></div>
                            </td>
                            <td>
                                <strong><?= format_date($b['booking_date']) ?></strong>
                                <div style="color: var(--admin-primary); font-size: 12px; font-weight: 700;">
                                    <?= format_time($b['start_time']) ?> &mdash; <?= format_time($b['end_time']) ?>
                                </div>
                            </td>
                            <td style="font-size: 13px; color: var(--text-secondary);">
                                <?= htmlspecialchars($b['room']) ?>
                            </td>
                            <td>
                                <?php if ($b['status'] === 'confirmed'): ?>
                                    <span class="badge badge-warning">Confirmed</span>
                                <?php elseif ($b['status'] === 'attended'): ?>
                                    <span class="badge badge-success">Attended</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <form method="POST" action="" style="display:inline-flex; gap: 6px;">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    
                                    <?php if ($b['status'] !== 'attended'): ?>
                                        <button type="submit" name="new_status" value="attended" class="action-btn" style="background: rgba(16,185,129,0.2); color: #6ee7b7;">
                                            ✓ Attended
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($b['status'] !== 'cancelled'): ?>
                                        <button type="submit" name="new_status" value="cancelled" class="action-btn action-btn-delete">
                                            ✕ Cancel
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($b['status'] === 'cancelled'): ?>
                                        <button type="submit" name="new_status" value="confirmed" class="action-btn action-btn-edit">
                                            Reconfirm
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--admin-muted); padding: 40px;">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
