<?php
/**
 * IronPulse Gym & Fitness Management System
 * Full Weekly Schedule & Timetable
 */

$pageTitle = "Class Timetable & Schedule";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

$schedules = [];
try {
    $db = getDB();
    if ($db) {
        $stmt = $db->query("
            SELECT s.*, c.name AS class_name, c.category, c.intensity, c.duration_minutes, t.name AS trainer_name
            FROM class_schedules s
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN trainers t ON c.trainer_id = t.id
            ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.start_time ASC
        ");
        $schedules = $stmt->fetchAll();
    }
} catch (Exception $e) {
    // Graceful fallback
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

require_once __DIR__ . '/includes/header.php';
?>

<div style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(15, 23, 42, 0.9)); padding: 60px 0 40px; border-bottom: 1px solid var(--border-subtle);">
    <div class="container text-center">
        <div class="section-tag">Weekly Schedule</div>
        <h1 class="section-title">STUDIO & ARENA <span class="text-gradient">TIMETABLE</span></h1>
        <p class="section-subtitle mx-auto">Explore all group fitness, strength, combat, and recovery sessions throughout the week.</p>

        <div class="day-tabs">
            <button class="day-tab active" data-day="all">All Sessions</button>
            <?php foreach ($days as $day): ?>
                <button class="day-tab" data-day="<?= $day ?>"><?= $day ?></button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<section style="padding: 70px 0;">
    <div class="container">
        <div class="timetable-grid">
            <?php if (!empty($schedules)): ?>
                <?php foreach ($schedules as $sched): ?>
                    <div class="timetable-card" data-day="<?= e($sched['day_of_week']) ?>">
                        <div class="timetable-time">
                            🕒 <?= format_time($sched['start_time']) ?> &mdash; <?= format_time($sched['end_time']) ?>
                        </div>
                        <div class="timetable-class-name"><?= e($sched['class_name']) ?></div>
                        
                        <div style="display: flex; gap: 8px; align-items: center; margin: 10px 0;">
                            <span class="badge badge-info"><?= e($sched['day_of_week']) ?></span>
                            <span class="badge badge-primary"><?= e($sched['category']) ?></span>
                            <span class="badge badge-warning"><?= e($sched['intensity']) ?></span>
                        </div>

                        <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 14px;">
                            📍 Room: <strong style="color: #fff;"><?= e($sched['room']) ?></strong><br>
                            👤 Coach: <strong style="color: #fff;"><?= e($sched['trainer_name'] ?? 'Staff') ?></strong>
                        </div>

                        <div style="padding-top: 12px; border-top: 1px solid var(--border-subtle);">
                            <?php if (is_logged_in()): ?>
                                <a href="<?= url('member/book-class.php?schedule_id=' . $sched['id']) ?>" class="btn btn-outline-primary btn-block btn-sm">
                                    Reserve My Spot &rarr;
                                </a>
                            <?php else: ?>
                                <a href="<?= url('login.php') ?>" class="btn btn-outline-primary btn-block btn-sm">
                                    Sign In to Book &rarr;
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 50px;">
                    Timetable entries will show after database setup.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
