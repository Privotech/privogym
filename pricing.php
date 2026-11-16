<?php
/**
 * IronPulse Gym & Fitness Management System
 * Membership Plans & Pricing
 */

$pageTitle = "Membership Plans & Pricing";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

$plans = [];
try {
    $db = getDB();
    if ($db) {
        $stmt = $db->query("SELECT * FROM membership_plans WHERE status = 'active' ORDER BY price ASC");
        $plans = $stmt->fetchAll();
    }
} catch (Exception $e) {
    // Fallback
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(15, 23, 42, 0.9)); padding: 60px 0 40px; border-bottom: 1px solid var(--border-subtle);">
    <div class="container text-center">
        <div class="section-tag">Flexible Memberships</div>
        <h1 class="section-title">INVEST IN YOUR <span class="text-gradient">GREATEST ASSET</span></h1>
        <p class="section-subtitle mx-auto">No contracts, no activation penalties. Choose a tier that aligns with your athletic ambitions.</p>
    </div>
</div>

<section style="padding: 70px 0;">
    <div class="container">
        <div class="pricing-grid">
            <?php if (!empty($plans)): ?>
                <?php foreach ($plans as $plan): ?>
                    <?php 
                        $features = json_decode($plan['features_json'], true) ?: [];
                        $isPopular = (bool)$plan['is_popular'];
                    ?>
                    <div class="pricing-card <?= $isPopular ? 'featured' : '' ?>">
                        <?php if ($isPopular): ?>
                            <div class="pricing-featured-badge">MOST POPULAR</div>
                        <?php endif; ?>

                        <div class="pricing-name"><?= e($plan['name']) ?></div>
                        <p class="pricing-desc"><?= e($plan['description']) ?></p>

                        <div class="pricing-price">
                            <?= money($plan['price']) ?>
                            <span>/ <?= $plan['duration_days'] >= 365 ? 'year' : 'month' ?></span>
                        </div>

                        <ul class="pricing-features">
                            <?php foreach ($features as $f): ?>
                                <li class="pricing-feature-item">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span><?= e($f) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <a href="<?= url('register.php?plan=' . $plan['id']) ?>" class="btn <?= $isPopular ? 'btn-primary' : 'btn-outline-primary' ?> btn-block">
                            Get Started With <?= e($plan['name']) ?> &rarr;
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Membership FAQs -->
        <div style="margin-top: 80px; max-width: 800px; margin-left: auto; margin-right: auto;">
            <div class="text-center" style="margin-bottom: 40px;">
                <div class="section-tag">Common Questions</div>
                <h2 style="font-family: var(--font-heading); font-size: 34px; color: #fff;">MEMBERSHIP FAQS</h2>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 14px; padding: 24px; margin-bottom: 16px;">
                <h4 style="color: #fff; font-size: 16px; margin-bottom: 8px;">Can I cancel or freeze my membership?</h4>
                <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
                    Yes, all memberships operate on month-to-month flexibility. You can cancel or pause anytime directly within your Member Dashboard or by visiting the front desk.
                </p>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 14px; padding: 24px; margin-bottom: 16px;">
                <h4 style="color: #fff; font-size: 16px; margin-bottom: 8px;">Are group classes included in the basic starter plan?</h4>
                <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
                    The Basic Starter covers full gym floor access, cardio equipment, and free weights. For unlimited group fitness (HIIT, Yoga, Boxing, Crossfit), choose our Pro Fitness or Elite VIP tiers.
                </p>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 14px; padding: 24px;">
                <h4 style="color: #fff; font-size: 16px; margin-bottom: 8px;">Do you provide locker rooms and showers?</h4>
                <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
                    Every membership includes access to our private locker rooms, rainfall showers, and grooming stations. Pro & VIP members also receive towel service and sauna access.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
