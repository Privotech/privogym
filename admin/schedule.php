<?php
/**
 * Admin Portal - Class Timetable & Schedule Management
 */

$pageTitle = "Timetable Schedule";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$db = getDB();

// Handle Add / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'create_schedule') {
        $class_id = (int)$_POST['class_id'];
        $day = $_POST['day_of_week'] ?? 'Monday';
        $start_time = trim($_POST['start_time'] ?? '');
        $end_time = trim($_POST['end_time'] ?? '');
        $room = trim($_POST['room'] ?? 'Main Studio A');

        if ($class_id > 0 && !empty($start_time) && !empty($end_time)) {
            $stmt = $db->prepare("
                INSERT INTO class_schedules (class_id, day_of_week, start_time, end_time, room, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$class_id, $day, $start_time, $end_time, $room]);
            flash('success', 'Schedule session added successfully.');
            redirect('admin/schedule.php');
        } else {
            flash('danger', 'Please provide class, start time, and end time.');
        }

    } elseif ($action === 'delete_schedule') {
        $schedId = (int)$_POST['schedule_id'];
        $db->prepare("DELETE FROM class_schedules WHERE id = ?")->execute([$schedId]);
        flash('info', 'Schedule session removed.');
        redirect('admin/schedule.php');
    }
}

// Fetch all schedules
$schedules = $db->query("
    SELECT s.*, c.name AS class_name, c.category, t.name AS trainer_name
    FROM class_schedules s
    JOIN classes c ON s.class_id = c.id
    LEFT JOIN trainers t ON c.trainer_id = t.id
    ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.start_time ASC
")->fetchAll();

// All classes for dropdown
$classes = $db->query("SELECT id, name, category FROM classes WHERE status = 'active' ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #fff;">Weekly Timetable Schedule</h1>
        <p style="color: var(--admin-muted); font-size: 13px;">Manage time slots, studios, and weekly recurring class allocations.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 360px; gap: 30px; align-items: start;">
    <!-- Timetable List -->
    <div class="data-card">
        <div class="data-card-header">
            <div class="data-card-title">Scheduled Sessions (<?= count($schedules) ?>)</div>
        </div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Time Window</th>
                        <th>Class & Category</th>
                        <th>Room / Studio</th>
                        <th>Coach</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($schedules)): ?>
                        <?php foreach ($schedules as $s): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-info"><?= htmlspecialchars($s['day_of_week']) ?></span>
                                </td>
                                <td style="font-size: 13px; font-weight: 700; color: var(--admin-primary);">
                                    <?= format_time($s['start_time']) ?> &mdash; <?= format_time($s['end_time']) ?>
                                </td>
                                <td>
                                    <strong style="color: #fff; font-size: 14px;"><?= htmlspecialchars($s['class_name']) ?></strong>
                                    <div style="font-size: 11px; color: var(--admin-muted);"><?= htmlspecialchars($s['category']) ?></div>
                                </td>
                                <td style="font-size: 13px; color: var(--text-secondary);">
                                    <?= htmlspecialchars($s['room']) ?>
                                </td>
                                <td style="font-size: 13px; color: var(--text-secondary);">
                                    <?= htmlspecialchars($s['trainer_name'] ?? 'Staff') ?>
                                </td>
                                <td style="text-align: right;">
                                    <form method="POST" action="" onsubmit="return confirm('Remove this session from timetable?');" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_schedule">
                                        <input type="hidden" name="schedule_id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="action-btn action-btn-delete">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--admin-muted); padding: 30px;">No scheduled sessions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Schedule Session Form -->
    <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 14px; padding: 24px;">
        <h3 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 16px;">+ Add Session to Schedule</h3>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="create_schedule">

            <div class="form-group">
                <label>Select Workout Class *</label>
                <select name="class_id" class="form-control" required>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['category']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Day of Week *</label>
                <select name="day_of_week" class="form-control" required>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label>Start Time *</label>
                    <input type="time" name="start_time" class="form-control" value="09:00" required>
                </div>
                <div class="form-group">
                    <label>End Time *</label>
                    <input type="time" name="end_time" class="form-control" value="10:00" required>
                </div>
            </div>

            <div class="form-group">
                <label>Studio / Location</label>
                <input type="text" name="room" class="form-control" placeholder="e.g. Iron Arena (Room A)" value="Main Studio A" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Add Session to Timetable
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
