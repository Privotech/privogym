<?php
/**
 * Member Portal - Main Dashboard
 */

$pageTitle = "My Dashboard";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_member();
$user = current_user();
$db = getDB();

$activeMembership = get_active_membership($user['id']);

// Fetch upcoming bookings
$upcomingBookings = [];
$pastWorkouts = [];
$totalWorkouts = 0;
$totalMinutes = 0;

try {
    if ($db) {
        // Bookings
        $bStmt = $db->prepare("
            SELECT b.*, s.day_of_week, s.start_time, s.end_time, s.room, c.name AS class_name, c.intensity, t.name AS trainer_name
            FROM class_bookings b
            JOIN class_schedules s ON b.schedule_id = s.id
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN trainers t ON c.trainer_id = t.id
            WHERE b.user_id = ? AND b.status = 'confirmed' AND b.booking_date >= CURDATE()
            ORDER BY b.booking_date ASC, s.start_time ASC
            LIMIT 5
        ");
        $bStmt->execute([$user['id']]);
        $upcomingBookings = $bStmt->fetchAll();

        // Recent Workouts
        $wStmt = $db->prepare("
            SELECT * FROM workout_logs
            WHERE user_id = ?
            ORDER BY workout_date DESC, id DESC
            LIMIT 5
        ");
        $wStmt->execute([$user['id']]);
        $pastWorkouts = $wStmt->fetchAll();

        // Workout stats
        $sStmt = $db->prepare("
            SELECT COUNT(*) AS total_count, COALESCE(SUM(duration_minutes), 0) AS total_mins
            FROM workout_logs WHERE user_id = ?
        ");
        $sStmt->execute([$user['id']]);
        $wStats = $sStmt->fetch();
        $totalWorkouts = $wStats['total_count'] ?? 0;
        $totalMinutes = $wStats['total_mins'] ?? 0;
    }
} catch (Exception $e) {
    // Graceful fallback
}

// Days remaining calculation
$daysRemaining = 0;
if ($activeMembership && !empty($activeMembership['end_date'])) {
    $now = new DateTime();
    $end = new DateTime($activeMembership['end_date']);
    $diff = $now->diff($end);
    $daysRemaining = $end >= $now ? $diff->days : 0;
}

require_once __DIR__ . '/includes/member_header.php';
?>

<!-- Welcome Banner -->
<div class="member-hero-banner">
    <div class="container member-welcome-row">
        <div class="member-avatar-box">
            <div class="member-avatar-circle">
                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
            </div>
            <div>
                <div style="font-size: 13px; color: var(--mem-primary); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Welcome Athlete</div>
                <h1 style="font-family: var(--font-heading); font-size: 38px; color: #fff; line-height: 1.1; margin: 2px 0 6px;">
                    <?= htmlspecialchars($user['full_name']) ?>
                </h1>
                <p style="color: var(--mem-muted); font-size: 14px; margin: 0;">
                    Goals: <?= htmlspecialchars($user['fitness_goals'] ?: 'Consistency and Progressive Overload') ?>
                </p>
            </div>
        </div>

        <!-- Digital Membership Pass Card -->
        <div class="member-pass-card">
            <div class="member-pass-header">
                <span class="badge <?= $activeMembership ? 'badge-success' : 'badge-danger' ?>">
                    <?= $activeMembership ? 'ACTIVE PASS' : 'NO ACTIVE PLAN' ?>
                </span>
                <span style="font-size: 12px; color: var(--mem-muted);">MEMBER ID #<?= str_pad($user['id'], 5, '0', STR_PAD_LEFT) ?></span>
            </div>
            
            <div class="member-pass-name"><?= htmlspecialchars($activeMembership['plan_name'] ?? 'Visitor / Expired') ?></div>
            
            <?php if ($activeMembership): ?>
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 14px;">
                    <div>
                        <div style="font-size: 11px; color: var(--mem-muted); text-transform: uppercase;">Valid Until</div>
                        <div style="font-size: 14px; font-weight: 700; color: #fff;"><?= format_date($activeMembership['end_date']) ?></div>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge badge-primary"><?= $daysRemaining ?> Days Left</span>
                    </div>
                </div>
            <?php else: ?>
                <p style="font-size: 13px; color: var(--mem-muted); margin: 10px 0 14px;">You currently have no active gym plan.</p>
                <a href="<?= url('member/membership.php') ?>" class="btn btn-primary btn-sm btn-block">Choose a Plan Now</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container" style="padding: 40px 20px;">
    <!-- Stat Counter Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 35px;">
        <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 14px; padding: 22px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--mem-muted); text-transform: uppercase;">Total Workouts</div>
            <div style="font-size: 32px; font-weight: 800; color: #fff; margin-top: 4px;"><?= $totalWorkouts ?></div>
            <div style="font-size: 12px; color: var(--accent); margin-top: 4px;">Logged in personal tracker</div>
        </div>

        <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 14px; padding: 22px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--mem-muted); text-transform: uppercase;">Upcoming Classes</div>
            <div style="font-size: 32px; font-weight: 800; color: var(--mem-primary); margin-top: 4px;"><?= count($upcomingBookings) ?></div>
            <div style="font-size: 12px; color: var(--mem-muted); margin-top: 4px;">Reserved spots</div>
        </div>

        <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 14px; padding: 22px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--mem-muted); text-transform: uppercase;">Minutes Trained</div>
            <div style="font-size: 32px; font-weight: 800; color: #fff; margin-top: 4px;"><?= number_format($totalMinutes) ?></div>
            <div style="font-size: 12px; color: var(--accent); margin-top: 4px;">Time invested in health</div>
        </div>

        <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 14px; padding: 22px; display: flex; flex-direction: column; justify-content: center;">
            <a href="<?= url('member/book-class.php') ?>" class="btn btn-primary btn-sm btn-block" style="margin-bottom: 8px;">
                Reserve a Class
            </a>
            <a href="<?= url('member/workout-tracker.php') ?>" class="btn btn-outline btn-sm btn-block">
                + Log Today's Workout
            </a>
        </div>
    </div>

    <!-- Main Grid: Bookings & Workout History -->
    <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 30px;">
        <!-- Left: Upcoming Class Reservations -->
        <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 16px; padding: 28px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 700; color: #fff;">Upcoming Class Reservations</h3>
                <a href="<?= url('member/book-class.php') ?>" style="font-size: 13px; color: var(--mem-primary); font-weight: 600;">+ Book More</a>
            </div>

            <?php if (!empty($upcomingBookings)): ?>
                <?php foreach ($upcomingBookings as $b): ?>
                    <div class="booking-ticket">
                        <div>
                            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 4px;">
                                <span class="badge badge-primary"><?= format_date($b['booking_date'], 'D, M d') ?></span>
                                <span style="font-size: 13px; font-weight: 700; color: var(--mem-primary);">
                                    <?= format_time($b['start_time']) ?> - <?= format_time($b['end_time']) ?>
                                </span>
                            </div>
                            <h4 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 4px;"><?= htmlspecialchars($b['class_name']) ?></h4>
                            <div style="font-size: 12px; color: var(--mem-muted);">
                                Room: <?= htmlspecialchars($b['room']) ?> &bull; Coach: <?= htmlspecialchars($b['trainer_name'] ?? 'Staff') ?>
                            </div>
                        </div>

                        <div>
                            <form method="POST" action="<?= url('member/my-bookings.php') ?>" onsubmit="return confirm('Cancel this reservation?');">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="action" value="cancel">
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: rgba(239,68,68,0.3);">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px 20px; color: var(--mem-muted); background: rgba(255,255,255,0.02); border-radius: 12px;">
                    <p style="margin-bottom: 15px;">You have no upcoming class bookings.</p>
                    <a href="<?= url('member/book-class.php') ?>" class="btn btn-outline-primary btn-sm">Browse Weekly Timetable</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Recent Workouts -->
        <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 16px; padding: 28px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 18px; font-weight: 700; color: #fff;">Recent Workout Logs</h3>
                <a href="<?= url('member/workout-tracker.php') ?>" style="font-size: 13px; color: var(--mem-primary); font-weight: 600;">View All</a>
            </div>

            <?php if (!empty($pastWorkouts)): ?>
                <?php foreach ($pastWorkouts as $w): ?>
                    <div style="border-bottom: 1px solid var(--mem-border); padding: 14px 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <strong style="color: #fff; font-size: 15px;"><?= htmlspecialchars($w['workout_type']) ?></strong>
                            <span style="font-size: 12px; color: var(--mem-muted);"><?= format_date($w['workout_date']) ?></span>
                        </div>
                        <div style="display: flex; gap: 15px; font-size: 13px; color: var(--mem-muted); margin-bottom: 6px;">
                            <span>⏱️ <?= (int)$w['duration_minutes'] ?> mins</span>
                            <span>🔥 <?= (int)$w['calories_burned'] ?> kcal</span>
                        </div>
                        <?php if (!empty($w['notes'])): ?>
                            <div style="font-size: 12px; color: var(--text-secondary); background: rgba(255,255,255,0.02); padding: 8px 10px; border-radius: 6px;">
                                <?= htmlspecialchars($w['notes']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px 20px; color: var(--mem-muted); background: rgba(255,255,255,0.02); border-radius: 12px;">
                    <p style="margin-bottom: 15px;">No workout logs recorded yet.</p>
                    <a href="<?= url('member/workout-tracker.php') ?>" class="btn btn-outline-primary btn-sm">+ Log Your First Workout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/member_footer.php'; ?>
