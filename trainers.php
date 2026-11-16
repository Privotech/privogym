<?php
/**
 * IronPulse Gym & Fitness Management System
 * Meet The Trainers & Coaches
 */

$pageTitle = "Expert Trainers & Coaches";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

$trainers = [];
try {
    $db = getDB();
    if ($db) {
        $stmt = $db->query("SELECT * FROM trainers WHERE status = 'active' ORDER BY id ASC");
        $trainers = $stmt->fetchAll();
    }
} catch (Exception $e) {
    // Fallback
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(15, 23, 42, 0.9)); padding: 60px 0 40px; border-bottom: 1px solid var(--border-subtle);">
    <div class="container text-center">
        <div class="section-tag">World-Class Coaching</div>
        <h1 class="section-title">MEET OUR <span class="text-gradient">MASTER TRAINERS</span></h1>
        <p class="section-subtitle mx-auto">Certified experts dedicated to coaching proper form, breaking mental plateaus, and driving safe, sustainable physical progress.</p>
    </div>
</div>

<section style="padding: 70px 0;">
    <div class="container">
        <div class="trainers-grid">
            <?php if (!empty($trainers)): ?>
                <?php foreach ($trainers as $trainer): ?>
                    <div class="trainer-card">
                        <div class="trainer-photo-placeholder">
                            <div class="trainer-avatar-initials">
                                <?php
                                    $parts = explode(' ', $trainer['name']);
                                    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                                    echo e($initials);
                                ?>
                            </div>
                        </div>

                        <div class="trainer-info">
                            <h3 class="trainer-name"><?= e($trainer['name']) ?></h3>
                            <div class="trainer-role"><?= e($trainer['specialty']) ?></div>
                            <p class="trainer-bio"><?= e($trainer['bio']) ?></p>

                            <div style="background: rgba(255,255,255,0.03); border-radius: 8px; padding: 12px; margin-bottom: 16px; font-size: 13px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                    <span style="color: var(--text-muted);">Experience:</span>
                                    <strong style="color: #fff;"><?= (int)$trainer['experience_years'] ?>+ Years</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: var(--text-muted);">Email:</span>
                                    <span style="color: var(--primary);"><?= e($trainer['email']) ?></span>
                                </div>
                            </div>

                            <div class="trainer-social">
                                <span class="social-pill"><?= e($trainer['social_instagram'] ?? '@ironpulse') ?></span>
                                <span class="social-pill"><?= e($trainer['phone']) ?></span>
                            </div>

                            <div style="margin-top: 18px;">
                                <a href="<?= url('contact.php?trainer=' . urlencode($trainer['name'])) ?>" class="btn btn-outline-primary btn-block btn-sm">
                                    Request Personal Coaching
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 50px;">
                    Trainers will be shown once database is seeded.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
