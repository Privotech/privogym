<?php
/**
 * Admin Portal - Workout Classes Management
 */

$pageTitle = "Workout Classes";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$db = getDB();

// Handle Add / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'create_class') {
        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? 'Strength';
        $trainer_id = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;
        $description = trim($_POST['description'] ?? '');
        $duration = (int)($_POST['duration_minutes'] ?? 60);
        $capacity = (int)($_POST['capacity'] ?? 20);
        $intensity = $_POST['intensity'] ?? 'All Levels';

        $stmt = $db->prepare("
            INSERT INTO classes (name, category, trainer_id, description, duration_minutes, capacity, intensity, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        $stmt->execute([$name, $category, $trainer_id, $description, $duration, $capacity, $intensity]);
        flash('success', "Class '{$name}' created successfully.");
        redirect('admin/classes.php');

    } elseif ($action === 'delete_class') {
        $classId = (int)$_POST['class_id'];
        $db->prepare("DELETE FROM classes WHERE id = ?")->execute([$classId]);
        flash('info', 'Class deleted successfully.');
        redirect('admin/classes.php');
    }
}

// Fetch classes
$classes = $db->query("
    SELECT c.*, t.name AS trainer_name 
    FROM classes c 
    LEFT JOIN trainers t ON c.trainer_id = t.id 
    ORDER BY c.id DESC
")->fetchAll();

// Fetch trainers for dropdown
$trainers = $db->query("SELECT id, name, specialty FROM trainers WHERE status = 'active' ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #fff;">Workout Classes Catalog</h1>
        <p style="color: var(--admin-muted); font-size: 13px;">Manage group fitness disciplines, capacity limits, and lead coach assignments.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 380px; gap: 30px; align-items: start;">
    <!-- Classes Table -->
    <div class="data-card">
        <div class="data-card-header">
            <div class="data-card-title">All Classes (<?= count($classes) ?>)</div>
        </div>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Class Name & Category</th>
                        <th>Coach</th>
                        <th>Duration</th>
                        <th>Max Spots</th>
                        <th>Intensity</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($classes)): ?>
                        <?php foreach ($classes as $c): ?>
                            <tr>
                                <td>
                                    <strong style="color: #fff; font-size: 14px;"><?= htmlspecialchars($c['name']) ?></strong>
                                    <div style="margin-top: 3px;">
                                        <span class="badge badge-primary" style="font-size: 10px;"><?= htmlspecialchars($c['category']) ?></span>
                                    </div>
                                    <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;"><?= htmlspecialchars($c['description']) ?></div>
                                </td>
                                <td style="font-size: 13px; color: var(--text-secondary);">
                                    <?= htmlspecialchars($c['trainer_name'] ?? 'Unassigned') ?>
                                </td>
                                <td style="font-size: 13px; color: #fff;">
                                    <?= (int)$c['duration_minutes'] ?> Mins
                                </td>
                                <td style="font-size: 13px; font-weight: 700; color: var(--admin-primary);">
                                    <?= (int)$c['capacity'] ?> Spots
                                </td>
                                <td>
                                    <span class="badge badge-warning" style="font-size: 10px;"><?= htmlspecialchars($c['intensity']) ?></span>
                                </td>
                                <td style="text-align: right;">
                                    <form method="POST" action="" onsubmit="return confirm('Delete class <?= htmlspecialchars($c['name']) ?>?');" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete_class">
                                        <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="action-btn action-btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--admin-muted); padding: 30px;">No workout classes found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Class Form -->
    <div style="background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 14px; padding: 24px;">
        <h3 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 16px;">+ Add New Workout Class</h3>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="create_class">

            <div class="form-group">
                <label>Class Title *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Spartan Core Conditioning" required>
            </div>

            <div class="form-group">
                <label>Category *</label>
                <select name="category" class="form-control" required>
                    <option value="Strength">Strength</option>
                    <option value="HIIT">HIIT</option>
                    <option value="Yoga & Flexibility">Yoga & Flexibility</option>
                    <option value="Combat / Boxing">Combat / Boxing</option>
                    <option value="Crossfit">Crossfit</option>
                    <option value="Cardio">Cardio</option>
                </select>
            </div>

            <div class="form-group">
                <label>Lead Coach / Trainer</label>
                <select name="trainer_id" class="form-control">
                    <option value="">-- Select Trainer --</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['specialty']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label>Duration (mins)</label>
                    <input type="number" name="duration_minutes" class="form-control" value="60" required>
                </div>
                <div class="form-group">
                    <label>Capacity (Spots)</label>
                    <input type="number" name="capacity" class="form-control" value="20" required>
                </div>
            </div>

            <div class="form-group">
                <label>Intensity Level</label>
                <select name="intensity" class="form-control">
                    <option value="All Levels">All Levels</option>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Brief overview of exercises and goals..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Save Class
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
