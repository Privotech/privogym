<?php
/**
 * IronPulse Gym & Fitness Management System
 * Workout Classes Catalog
 */

$pageTitle = "Classes & Programs";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

$classes = [];
try {
    $db = getDB();
    if ($db) {
        $stmt = $db->query("
            SELECT c.*, t.name AS trainer_name, t.specialty AS trainer_specialty
            FROM classes c
            LEFT JOIN trainers t ON c.trainer_id = t.id
            WHERE c.status = 'active'
            ORDER BY c.category ASC, c.name ASC
        ");
        $classes = $stmt->fetchAll();
    }
} catch (Exception $e) {
    // Graceful fallback
}

$categories = ['All', 'Strength', 'HIIT', 'Yoga & Flexibility', 'Combat / Boxing', 'Crossfit'];

require_once __DIR__ . '/includes/header.php';
?>

<div style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(15, 23, 42, 0.9)); padding: 60px 0 40px; border-bottom: 1px solid var(--border-subtle);">
    <div class="container text-center">
        <div class="section-tag">Programs & Workouts</div>
        <h1 class="section-title">ENGINEERED <span class="text-gradient">TRAINING CLASSES</span></h1>
        <p class="section-subtitle mx-auto">From heavy compound barbell lifting to high-octane metabolic conditioning and restorative flow.</p>

        <!-- Category Filters -->
        <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-top: 25px;">
            <?php foreach ($categories as $idx => $cat): ?>
                <button class="day-tab cat-filter-btn <?= $idx === 0 ? 'active' : '' ?>" data-category="<?= $cat === 'All' ? 'all' : e($cat) ?>">
                    <?= e($cat) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<section style="padding: 70px 0;">
    <div class="container">
        <div class="class-grid">
            <?php if (!empty($classes)): ?>
                <?php foreach ($classes as $class): ?>
                    <div class="class-card class-filter-item" data-category="<?= e($class['category']) ?>">
                        <div class="class-card-header">
                            <div class="class-header-art"></div>
                            <span class="badge badge-primary class-badge"><?= e($class['category']) ?></span>
                            <div>
                                <span class="badge badge-warning" style="margin-bottom: 6px;"><?= e($class['intensity']) ?></span>
                                <h3 style="color: #fff; font-size: 20px; font-weight: 700;"><?= e($class['name']) ?></h3>
                            </div>
                        </div>

                        <div class="class-card-body">
                            <p class="class-desc"><?= e($class['description']) ?></p>
                            
                            <div class="class-meta">
                                <span>⏱️ <strong><?= (int)$class['duration_minutes'] ?></strong> Mins</span>
                                <span>👥 <strong><?= (int)$class['capacity'] ?></strong> Max Spots</span>
                                <span>Coach: <strong><?= e($class['trainer_name'] ?? 'Staff') ?></strong></span>
                            </div>

                            <div style="margin-top: 20px; display: flex; gap: 10px;">
                                <a href="<?= url('timetable.php') ?>" class="btn btn-outline-primary btn-block btn-sm">
                                    View Timetable
                                </a>
                                <?php if (is_logged_in()): ?>
                                    <a href="<?= url('member/book-class.php') ?>" class="btn btn-primary btn-block btn-sm">
                                        Book Spot
                                    </a>
                                <?php else: ?>
                                    <a href="<?= url('login.php') ?>" class="btn btn-primary btn-block btn-sm">
                                        Sign In to Book
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
                    No classes found. Please run the <a href="<?= url('install.php') ?>" style="color: var(--primary);">database installer</a>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
