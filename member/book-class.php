<?php
/**
 * Member Portal - Class Reservation System
 */

$pageTitle = "Book a Class";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_member();
$user = current_user();
$db = getDB();
$activeMembership = get_active_membership($user['id']);

// Handle Booking Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_id'])) {
    verify_csrf();

    if (!$activeMembership) {
        flash('danger', 'You need an active membership subscription to book group fitness classes.');
        redirect('member/membership.php');
    }

    $scheduleId = (int)$_POST['schedule_id'];
    $bookingDate = trim($_POST['booking_date'] ?? date('Y-m-d'));

    try {
        // Fetch schedule & class capacity
        $sStmt = $db->prepare("
            SELECT s.*, c.name AS class_name, c.capacity
            FROM class_schedules s
            JOIN classes c ON s.class_id = c.id
            WHERE s.id = ? LIMIT 1
        ");
        $sStmt->execute([$scheduleId]);
        $schedule = $sStmt->fetch();

        if (!$schedule) {
            flash('danger', 'Invalid class session selected.');
            redirect('member/book-class.php');
        }

        // Check if user already booked this session on this date
        $chk = $db->prepare("
            SELECT id FROM class_bookings 
            WHERE schedule_id = ? AND user_id = ? AND booking_date = ? AND status = 'confirmed'
            LIMIT 1
        ");
        $chk->execute([$scheduleId, $user['id'], $bookingDate]);
        if ($chk->fetch()) {
            flash('warning', 'You already have a confirmed spot reserved for this class on ' . format_date($bookingDate) . '.');
            redirect('member/my-bookings.php');
        }

        // Check current capacity
        $capStmt = $db->prepare("
            SELECT COUNT(*) AS booked_count FROM class_bookings 
            WHERE schedule_id = ? AND booking_date = ? AND status = 'confirmed'
        ");
        $capStmt->execute([$scheduleId, $bookingDate]);
        $booked = $capStmt->fetch()['booked_count'] ?? 0;

        if ($booked >= $schedule['capacity']) {
            flash('danger', 'Sorry! This class session is completely full (' . $booked . '/' . $schedule['capacity'] . ' spots taken).');
            redirect('member/book-class.php');
        }

        // Insert confirmed booking
        $ins = $db->prepare("
            INSERT INTO class_bookings (schedule_id, user_id, booking_date, status, booked_at)
            VALUES (?, ?, ?, 'confirmed', NOW())
        ");
        $ins->execute([$scheduleId, $user['id'], $bookingDate]);

        flash('success', "Spot successfully reserved for {$schedule['class_name']} on " . format_date($bookingDate) . " at " . format_time($schedule['start_time']) . "!");
        redirect('member/my-bookings.php');

    } catch (Exception $e) {
        flash('danger', 'Booking failed: ' . $e->getMessage());
        redirect('member/book-class.php');
    }
}

// Fetch all schedules
$schedules = [];
try {
    $stmt = $db->query("
        SELECT s.*, c.name AS class_name, c.category, c.intensity, c.capacity, t.name AS trainer_name,
        (SELECT COUNT(*) FROM class_bookings cb WHERE cb.schedule_id = s.id AND cb.booking_date = CURDATE() AND cb.status = 'confirmed') AS current_booked
        FROM class_schedules s
        JOIN classes c ON s.class_id = c.id
        LEFT JOIN trainers t ON c.trainer_id = t.id
        ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.start_time ASC
    ");
    $schedules = $stmt->fetchAll();
} catch (Exception $e) {}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

require_once __DIR__ . '/includes/member_header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-family: var(--font-heading); font-size: 34px; color: #fff;">BOOK A WORKOUT CLASS</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Select any weekly session to reserve your studio spot. Free cancellations up to 2 hours prior.</p>
        </div>
        <a href="<?= url('member/my-bookings.php') ?>" class="btn btn-outline btn-sm">View My Active Bookings &rarr;</a>
    </div>

    <?php if (!$activeMembership): ?>
        <div class="flash-alert flash-alert-warning" style="margin-bottom: 30px;">
            <div>
                <strong>Notice:</strong> You do not have an active membership plan. Please <a href="<?= url('member/membership.php') ?>" style="color: #fff; text-decoration: underline; font-weight: 700;">activate a plan</a> before reserving class spots.
            </div>
        </div>
    <?php endif; ?>

    <!-- Day Switcher Tabs -->
    <div class="day-tabs" style="justify-content: flex-start; margin-bottom: 25px;">
        <button class="day-tab active" data-day="all">All Days</button>
        <?php foreach ($days as $day): ?>
            <button class="day-tab" data-day="<?= $day ?>"><?= $day ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Schedules Grid -->
    <div class="timetable-grid">
        <?php foreach ($schedules as $sched): ?>
            <?php 
                $spotsLeft = max(0, $sched['capacity'] - $sched['current_booked']);
                $isFull = $spotsLeft <= 0;
            ?>
            <div class="timetable-card" data-day="<?= htmlspecialchars($sched['day_of_week']) ?>">
                <div class="timetable-time">
                    🕒 <?= format_time($sched['start_time']) ?> &mdash; <?= format_time($sched['end_time']) ?>
                </div>
                <div class="timetable-class-name"><?= htmlspecialchars($sched['class_name']) ?></div>

                <div style="display: flex; gap: 6px; flex-wrap: wrap; margin: 8px 0;">
                    <span class="badge badge-info"><?= htmlspecialchars($sched['day_of_week']) ?></span>
                    <span class="badge badge-primary"><?= htmlspecialchars($sched['category']) ?></span>
                    <span class="badge badge-warning"><?= htmlspecialchars($sched['intensity']) ?></span>
                </div>

                <div style="font-size: 13px; color: var(--mem-muted); margin: 12px 0;">
                    📍 Studio: <strong style="color: #fff;"><?= htmlspecialchars($sched['room']) ?></strong><br>
                    👤 Coach: <strong style="color: #fff;"><?= htmlspecialchars($sched['trainer_name'] ?? 'Staff') ?></strong>
                </div>

                <div style="background: rgba(255,255,255,0.03); border-radius: 8px; padding: 10px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                    <span style="color: var(--mem-muted);">Studio Capacity:</span>
                    <span class="badge <?= $isFull ? 'badge-danger' : 'badge-success' ?>">
                        <?= $isFull ? 'SOLD OUT' : $spotsLeft . ' spots available' ?>
                    </span>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="schedule_id" value="<?= $sched['id'] ?>">
                    <input type="hidden" name="booking_date" value="<?= date('Y-m-d') ?>">
                    <button type="submit" class="btn btn-primary btn-block btn-sm" <?= $isFull ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>>
                        <?= $isFull ? 'Class Full' : 'Reserve Spot For Today' ?>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/member_footer.php'; ?>
