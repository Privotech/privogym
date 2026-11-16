<?php
/**
 * Member Portal - Daily Workout Tracker & Training Log
 */

$pageTitle = "Workout Tracker";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_member();
$user = current_user();
$db = getDB();

// Handle Workout Entry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (isset($_POST['action']) && $_POST['action'] === 'add_workout') {
        $workout_date = trim($_POST['workout_date'] ?? date('Y-m-d'));
        $workout_type = trim($_POST['workout_type'] ?? '');
        $duration = (int)($_POST['duration_minutes'] ?? 45);
        $calories = (int)($_POST['calories_burned'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if (!empty($workout_type)) {
            $stmt = $db->prepare("
                INSERT INTO workout_logs (user_id, workout_date, workout_type, duration_minutes, calories_burned, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$user['id'], $workout_date, $workout_type, $duration, $calories, $notes]);
            flash('success', 'Workout session logged successfully! Keep up the momentum.');
            redirect('member/workout-tracker.php');
        } else {
            flash('danger', 'Please enter a workout title or focus area.');
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_workout') {
        $logId = (int)$_POST['log_id'];
        $stmt = $db->prepare("DELETE FROM workout_logs WHERE id = ? AND user_id = ?");
        $stmt->execute([$logId, $user['id']]);
        flash('info', 'Workout entry deleted.');
        redirect('member/workout-tracker.php');
    }
}

// Fetch logs
$logs = [];
$totalMins = 0;
$totalCals = 0;

try {
    $stmt = $db->prepare("SELECT * FROM workout_logs WHERE user_id = ? ORDER BY workout_date DESC, id DESC");
    $stmt->execute([$user['id']]);
    $logs = $stmt->fetchAll();

    foreach ($logs as $l) {
        $totalMins += (int)$l['duration_minutes'];
        $totalCals += (int)$l['calories_burned'];
    }
} catch (Exception $e) {}

require_once __DIR__ . '/includes/member_header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div style="margin-bottom: 30px;">
        <h1 style="font-family: var(--font-heading); font-size: 34px; color: #fff;">WORKOUT TRACKER & TRAINING LOG</h1>
        <p style="color: var(--text-muted); font-size: 14px;">Log your gym sessions, track sets, reps, duration, and monitor total caloric expenditure.</p>
    </div>

    <!-- Summary Metrics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 35px;">
        <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 12px; padding: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--mem-muted); text-transform: uppercase;">Total Sessions</div>
            <div style="font-size: 30px; font-weight: 800; color: #fff; margin-top: 4px;"><?= count($logs) ?></div>
        </div>
        <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 12px; padding: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--mem-muted); text-transform: uppercase;">Time Under Tension</div>
            <div style="font-size: 30px; font-weight: 800; color: var(--mem-primary); margin-top: 4px;"><?= number_format($totalMins) ?> mins</div>
        </div>
        <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 12px; padding: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--mem-muted); text-transform: uppercase;">Est. Calories Burned</div>
            <div style="font-size: 30px; font-weight: 800; color: var(--accent); margin-top: 4px;"><?= number_format($totalCals) ?> kcal</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 360px 1fr; gap: 30px; align-items: start;">
        <!-- New Entry Form -->
        <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 16px; padding: 26px;">
            <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 18px;">+ Log Today's Session</h3>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="add_workout">

                <div class="form-group">
                    <label>Workout Date</label>
                    <input type="date" name="workout_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label>Session Focus / Routine</label>
                    <input type="text" name="workout_type" class="form-control" placeholder="e.g. Heavy Squat & Leg Hypertrophy" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>Duration (mins)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="60" min="5" max="300" required>
                    </div>
                    <div class="form-group">
                        <label>Calories (kcal)</label>
                        <input type="number" name="calories_burned" class="form-control" value="400" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label>Exercises, Sets, Reps & Notes</label>
                    <textarea name="notes" class="form-control" rows="4" placeholder="e.g. Back Squat 140kg 5x5, Romanian Deadlift 120kg 4x8, Walking Lunges 24kg dumbbells 3x12..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Save Workout Entry
                </button>
            </form>
        </div>

        <!-- Workout History Timeline -->
        <div>
            <h3 style="font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 18px;">Past Workout Log History</h3>

            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $l): ?>
                    <div class="workout-log-entry">
                        <div style="flex: 1;">
                            <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 6px;">
                                <span class="badge badge-primary"><?= format_date($l['workout_date']) ?></span>
                                <strong style="font-size: 17px; color: #fff;"><?= htmlspecialchars($l['workout_type']) ?></strong>
                            </div>

                            <div style="display: flex; gap: 16px; font-size: 13px; color: var(--mem-muted); margin-bottom: 8px;">
                                <span>⏱️ <strong><?= (int)$l['duration_minutes'] ?></strong> Minutes</span>
                                <span>🔥 <strong><?= (int)$l['calories_burned'] ?></strong> Calories</span>
                            </div>

                            <?php if (!empty($l['notes'])): ?>
                                <div style="background: rgba(255,255,255,0.03); border-left: 3px solid var(--mem-primary); padding: 10px 14px; border-radius: 4px; font-size: 13px; color: var(--text-secondary); line-height: 1.6;">
                                    <?= nl2br(htmlspecialchars($l['notes'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="margin-left: 20px;">
                            <form method="POST" action="" onsubmit="return confirm('Delete this workout log?');">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="action" value="delete_workout">
                                <input type="hidden" name="log_id" value="<?= $l['id'] ?>">
                                <button type="submit" class="action-btn action-btn-delete" title="Delete Log">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="background: var(--mem-card); border: 1px solid var(--mem-border); border-radius: 16px; padding: 40px; text-align: center; color: var(--mem-muted);">
                    No workout logs yet. Use the form on the left to record your first workout session!
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/member_footer.php'; ?>
