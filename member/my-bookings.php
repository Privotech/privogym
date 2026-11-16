<?php
/**
 * Member Portal - My Bookings List
 */

$pageTitle = "My Bookings";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_member();
$user = current_user();
$db = getDB();

// Handle Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    verify_csrf();
    $bookingId = (int)$_POST['booking_id'];

    try {
        $stmt = $db->prepare("UPDATE class_bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
        $stmt->execute([$bookingId, $user['id']]);
        flash('success', 'Your class reservation has been cancelled.');
        redirect('member/my-bookings.php');
    } catch (Exception $e) {
        flash('danger', 'Error cancelling reservation: ' . $e->getMessage());
    }
}

// Fetch all bookings for member
$bookings = [];
try {
    $stmt = $db->prepare("
        SELECT b.*, s.day_of_week, s.start_time, s.end_time, s.room, c.name AS class_name, c.category, c.intensity, t.name AS trainer_name
        FROM class_bookings b
        JOIN class_schedules s ON b.schedule_id = s.id
        JOIN classes c ON s.class_id = c.id
        LEFT JOIN trainers t ON c.trainer_id = t.id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC, s.start_time DESC
    ");
    $stmt->execute([$user['id']]);
    $bookings = $stmt->fetchAll();
} catch (Exception $e) {}

require_once __DIR__ . '/includes/member_header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-family: var(--font-heading); font-size: 34px; color: #fff;">MY CLASS RESERVATIONS</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Track your confirmed upcoming classes and past attendance history.</p>
        </div>
        <a href="<?= url('member/book-class.php') ?>" class="btn btn-primary btn-sm">+ Book Another Class</a>
    </div>

    <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 16px; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #0d1424; text-align: left;">
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase;">Class & Category</th>
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase;">Date & Time</th>
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase;">Studio / Room</th>
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase;">Coach</th>
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase;">Status</th>
                        <th style="padding: 14px 20px; font-size: 12px; color: var(--mem-muted); text-transform: uppercase; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $b): ?>
                            <tr style="border-bottom: 1px solid var(--mem-border);">
                                <td style="padding: 16px 20px;">
                                    <div style="font-weight: 700; color: #fff; font-size: 15px;"><?= htmlspecialchars($b['class_name']) ?></div>
                                    <span class="badge badge-primary" style="font-size: 10px; margin-top: 4px;"><?= htmlspecialchars($b['category']) ?></span>
                                </td>
                                <td style="padding: 16px 20px; font-size: 14px;">
                                    <strong><?= format_date($b['booking_date'], 'D, M d, Y') ?></strong><br>
                                    <span style="color: var(--mem-primary); font-size: 13px;">
                                        <?= format_time($b['start_time']) ?> - <?= format_time($b['end_time']) ?>
                                    </span>
                                </td>
                                <td style="padding: 16px 20px; font-size: 14px; color: var(--text-secondary);">
                                    <?= htmlspecialchars($b['room']) ?>
                                </td>
                                <td style="padding: 16px 20px; font-size: 14px; color: var(--text-secondary);">
                                    <?= htmlspecialchars($b['trainer_name'] ?? 'Staff Coach') ?>
                                </td>
                                <td style="padding: 16px 20px;">
                                    <?php if ($b['status'] === 'confirmed'): ?>
                                        <span class="badge badge-success">Confirmed</span>
                                    <?php elseif ($b['status'] === 'attended'): ?>
                                        <span class="badge badge-info">Attended</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 16px 20px; text-align: right;">
                                    <?php if ($b['status'] === 'confirmed' && $b['booking_date'] >= date('Y-m-d')): ?>
                                        <form method="POST" action="" onsubmit="return confirm('Cancel this confirmed spot?');" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: rgba(239,68,68,0.3); padding: 6px 12px; font-size: 12px;">
                                                Cancel Spot
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: var(--text-dim); font-size: 12px;">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: var(--mem-muted);">
                                You haven't booked any classes yet. <a href="<?= url('member/book-class.php') ?>" style="color: var(--mem-primary);">Book your first session!</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/member_footer.php'; ?>
