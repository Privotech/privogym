<?php
/**
 * Admin Portal - Operations Dashboard
 */

$pageTitle = "Management Overview";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$db = getDB();

// Handle quick check-in from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attended_id'])) {
    verify_csrf();
    $bId = (int)$_POST['mark_attended_id'];
    $db->prepare("UPDATE class_bookings SET status = 'attended' WHERE id = ?")->execute([$bId]);
    flash('success', 'Member marked as Attended.');
    redirect('admin/index.php');
}

// KPI Metrics
$totalMembers = 0;
$activeMemberships = 0;
$totalRevenue = 0;
$todayBookings = 0;
$unreadInquiries = 0;
$recentMembers = [];
$recentBookings = [];

try {
    if ($db) {
        // Total members
        $totalMembers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn() ?: 0;
        
        // Active memberships
        $activeMemberships = $db->query("SELECT COUNT(*) FROM memberships WHERE status = 'active' AND end_date >= CURDATE()")->fetchColumn() ?: 0;

        // Total revenue
        $totalRevenue = $db->query("SELECT SUM(amount) FROM payments WHERE status = 'completed'")->fetchColumn() ?: 0;

        // Today's Bookings
        $todayBookings = $db->query("SELECT COUNT(*) FROM class_bookings WHERE booking_date = CURDATE() AND status != 'cancelled'")->fetchColumn() ?: 0;

        // Unread inquiries
        $unreadInquiries = $db->query("SELECT COUNT(*) FROM inquiries WHERE status = 'unread'")->fetchColumn() ?: 0;

        // Recent Members
        $stmt = $db->query("
            SELECT u.*, 
                   (SELECT mp.name FROM memberships m JOIN membership_plans mp ON m.plan_id = mp.id WHERE m.user_id = u.id AND m.status = 'active' LIMIT 1) AS current_plan
            FROM users u
            WHERE u.role = 'member'
            ORDER BY u.id DESC LIMIT 5
        ");
        $recentMembers = $stmt->fetchAll();

        // Recent / Today's Bookings
        $bStmt = $db->query("
            SELECT b.*, u.full_name AS member_name, u.email AS member_email, c.name AS class_name, s.start_time, s.end_time, s.room
            FROM class_bookings b
            JOIN users u ON b.user_id = u.id
            JOIN class_schedules s ON b.schedule_id = s.id
            JOIN classes c ON s.class_id = c.id
            ORDER BY b.booking_date DESC, b.id DESC LIMIT 6
        ");
        $recentBookings = $bStmt->fetchAll();
    }
} catch (Exception $e) {}

require_once __DIR__ . '/includes/admin_header.php';
?>

<!-- KPI Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-title">Total Members</span>
            <div class="stat-icon">👥</div>
        </div>
        <div class="stat-value"><?= number_format($totalMembers) ?></div>
        <div class="stat-desc">Registered athletes in system</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-title">Active Subscriptions</span>
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">🛡️</div>
        </div>
        <div class="stat-value" style="color: #10b981;"><?= number_format($activeMemberships) ?></div>
        <div class="stat-desc">Currently valid gym passes</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-title">Total Revenue</span>
            <div class="stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">💰</div>
        </div>
        <div class="stat-value" style="color: #3b82f6;"><?= money($totalRevenue) ?></div>
        <div class="stat-desc">All-time processed payments</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-title">Today's Class Bookings</span>
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">📅</div>
        </div>
        <div class="stat-value" style="color: #f59e0b;"><?= number_format($todayBookings) ?></div>
        <div class="stat-desc">Spots booked for today</div>
    </div>
</div>

<!-- Quick Action Bar -->
<div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 12px; padding: 18px 24px; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
    <div>
        <strong style="color: #fff; font-size: 15px;">Front Desk Quick Actions:</strong>
        <span style="color: var(--admin-muted); font-size: 13px; margin-left: 8px;">Fast access to daily operations</span>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= url('admin/member-add.php') ?>" class="btn btn-primary btn-sm">+ Add New Member</a>
        <a href="<?= url('admin/payments.php') ?>" class="btn btn-outline btn-sm">💵 Record Payment</a>
        <a href="<?= url('admin/classes.php') ?>" class="btn btn-outline btn-sm">🏋️ Add Class</a>
        <a href="<?= url('admin/schedule.php') ?>" class="btn btn-outline btn-sm">⏰ Add Schedule Slot</a>
    </div>
</div>

<!-- Two-Column Tables -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Recent Member Registrations -->
    <div class="data-card">
        <div class="data-card-header">
            <div class="data-card-title">Recent Member Registrations</div>
            <a href="<?= url('admin/members.php') ?>" style="font-size: 13px; color: var(--admin-primary); font-weight: 600;">View All Members &rarr;</a>
        </div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Current Plan</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentMembers)): ?>
                        <?php foreach ($recentMembers as $m): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: #fff;"><?= htmlspecialchars($m['full_name']) ?></div>
                                    <div style="font-size: 12px; color: var(--admin-muted);"><?= htmlspecialchars($m['email']) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= !empty($m['current_plan']) ? 'badge-primary' : 'badge-warning' ?>" style="font-size: 10px;">
                                        <?= htmlspecialchars($m['current_plan'] ?? 'No Plan') ?>
                                    </span>
                                </td>
                                <td style="font-size: 13px; color: var(--admin-muted);">
                                    <?= format_date($m['created_at']) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $m['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>">
                                        <?= ucfirst($m['status']) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= url('admin/member-view.php?id=' . $m['id']) ?>" class="action-btn action-btn-view">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--admin-muted); padding: 30px;">No members found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Class Bookings -->
    <div class="data-card">
        <div class="data-card-header">
            <div class="data-card-title">Recent Class Bookings</div>
            <a href="<?= url('admin/bookings.php') ?>" style="font-size: 13px; color: var(--admin-primary); font-weight: 600;">Manage All Bookings &rarr;</a>
        </div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Athlete</th>
                        <th>Class & Session</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="text-align: right;">Check-in</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recentBookings)): ?>
                        <?php foreach ($recentBookings as $b): ?>
                            <tr>
                                <td>
                                    <strong style="color: #fff; font-size: 13px;"><?= htmlspecialchars($b['member_name']) ?></strong>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #fff;"><?= htmlspecialchars($b['class_name']) ?></div>
                                    <div style="font-size: 11px; color: var(--admin-muted);"><?= format_time($b['start_time']) ?> &bull; <?= htmlspecialchars($b['room']) ?></div>
                                </td>
                                <td style="font-size: 13px; color: var(--admin-muted);">
                                    <?= format_date($b['booking_date'], 'M d') ?>
                                </td>
                                <td>
                                    <?php if ($b['status'] === 'confirmed'): ?>
                                        <span class="badge badge-warning">Booked</span>
                                    <?php elseif ($b['status'] === 'attended'): ?>
                                        <span class="badge badge-success">Attended</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($b['status'] === 'confirmed'): ?>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="mark_attended_id" value="<?= $b['id'] ?>">
                                            <button type="submit" class="action-btn" style="background: rgba(16,185,129,0.2); color: #6ee7b7;">✓ Check In</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: #64748b;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--admin-muted); padding: 30px;">No bookings found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
