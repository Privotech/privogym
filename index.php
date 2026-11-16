<?php
/**
 * IronPulse Gym & Fitness Management System
 * Homepage / Landing Page
 */

$pageTitle = "Home - Ultimate Strength & Conditioning";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

// Safe queries in case database is just being installed
$classes = [];
$trainers = [];
$plans = [];
$schedules = [];

try {
    $db = getDB();
    if ($db) {
        // Fetch featured classes
        $stmt = $db->query("
            SELECT c.*, t.name AS trainer_name 
            FROM classes c 
            LEFT JOIN trainers t ON c.trainer_id = t.id 
            WHERE c.status = 'active' 
            ORDER BY c.id ASC LIMIT 6
        ");
        $classes = $stmt->fetchAll();

        // Fetch trainers
        $stmt = $db->query("SELECT * FROM trainers WHERE status = 'active' ORDER BY id ASC LIMIT 4");
        $trainers = $stmt->fetchAll();

        // Fetch membership plans
        $stmt = $db->query("SELECT * FROM membership_plans WHERE status = 'active' ORDER BY price ASC");
        $plans = $stmt->fetchAll();

        // Fetch sample schedules for today / Monday
        $stmt = $db->query("
            SELECT s.*, c.name AS class_name, c.category, c.intensity, t.name AS trainer_name
            FROM class_schedules s
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN trainers t ON c.trainer_id = t.id
            ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.start_time ASC
            LIMIT 12
        ");
        $schedules = $stmt->fetchAll();
    }
} catch (Exception $e) {
    // Graceful fallback if database is not yet initialized
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-content">
            <div class="section-tag">
                <span>⚡</span> The Premier Athletic Training Facility
            </div>
            <h1>
                UNLEASH YOUR <span class="text-gradient">PEAK PHYSICAL</span> POTENTIAL
            </h1>
            <p>
                Engineered for serious lifters, functional athletes, and everyday achievers. Experience Olympic-grade equipment, elite certified coaching, and high-energy conditioning classes.
            </p>
            <div class="hero-buttons">
                <a href="<?= url('register.php') ?>" class="btn btn-primary btn-lg">
                    Join IronPulse Today &rarr;
                </a>
                <a href="<?= url('classes.php') ?>" class="btn btn-outline btn-lg">
                    Explore Classes
                </a>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-num">1,500+</div>
                    <div class="stat-label">Active Members</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">25+</div>
                    <div class="stat-label">Elite Coaches</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">48+</div>
                    <div class="stat-label">Weekly Classes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">99%</div>
                    <div class="stat-label">Goal Success Rate</div>
                </div>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-card-main">
                <div class="hero-glow-badge">🔥 LIMITED PASS OFFER</div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <div>
                        <div style="font-size: 13px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Featured Class Today</div>
                        <h3 style="font-size: 22px; font-weight: 800; color: #fff; margin-top: 4px;">High-Octane HIIT Circuit</h3>
                    </div>
                    <span class="badge badge-primary">Starts in 35m</span>
                </div>

                <div style="background: rgba(255, 255, 255, 0.04); border-radius: 12px; padding: 18px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                        <span style="color: var(--text-muted);">Lead Trainer:</span>
                        <strong style="color: #fff;">David Kim (CPT, CSCS)</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                        <span style="color: var(--text-muted);">Intensity:</span>
                        <span class="badge badge-warning">High Intensity</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px;">
                        <span style="color: var(--text-muted);">Studio Spot Capacity:</span>
                        <strong style="color: var(--accent);">16 / 20 Reserved</strong>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <a href="<?= url('register.php') ?>" class="btn btn-primary btn-block">Claim Free Day Pass</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features & Amenities -->
<section class="features-section">
    <div class="container">
        <div class="text-center">
            <div class="section-tag">Facility Standards</div>
            <h2 class="section-title">WHY CHOOSE <span class="text-gradient">IRONPULSE FITNESS</span></h2>
            <p class="section-subtitle mx-auto">
                We believe your environment dictates your results. Our 25,000 sq ft facility is precision-tuned for peak human performance.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6.5 6.5 11 11"/><path d="m21 21-1-1"/><path d="m3 3 1 1"/><path d="m18 22 4-4"/><path d="m2 6 4-4"/></svg>
                </div>
                <h3>Heavy Iron & Power Racks</h3>
                <p>Olympic barbells, calibrated steel competition plates, dumbbells up to 150 lbs, and 12 dedicated Eleiko power racks.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                </div>
                <h3>Dynamic HIIT & Combat</h3>
                <p>Curated high-intensity intervals, curved treadmills, air bikes, heavy punching bags, and functional turf tracks.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>Hydrotherapy & Sauna</h3>
                <p>Complete recovery lounge featuring Finnish dry saunas, cold plunge baths, and compression therapy boots.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h3>Flexible Access Hours</h3>
                <p>Open 5:00 AM to 11:00 PM on weekdays and 7:00 AM to 9:00 PM on weekends with smart digital barcode check-in.</p>
            </div>
        </div>
    </div>
</section>

<!-- Classes & Programs -->
<section class="classes-section" id="classes">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; flex-wrap: wrap; gap: 20px;">
            <div>
                <div class="section-tag">Engineered Classes</div>
                <h2 class="section-title">FEATURED <span class="text-gradient">WORKOUT PROGRAMS</span></h2>
                <p style="color: var(--text-muted); font-size: 15px; margin: 0;">Designed and led by certified trainers for guaranteed progression.</p>
            </div>
            <a href="<?= url('classes.php') ?>" class="btn btn-outline">View All Classes &rarr;</a>
        </div>

        <div class="class-grid">
            <?php if (!empty($classes)): ?>
                <?php foreach ($classes as $class): ?>
                    <div class="class-card">
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
                                <span>⏱️ <?= (int)$class['duration_minutes'] ?> Mins</span>
                                <span>👥 Max <?= (int)$class['capacity'] ?> Spots</span>
                                <span>Coach: <strong><?= e($class['trainer_name'] ?? 'Staff') ?></strong></span>
                            </div>
                            <div style="margin-top: 18px;">
                                <a href="<?= url('timetable.php') ?>" class="btn btn-outline-primary btn-block btn-sm">Check Timetable</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: var(--bg-card); border-radius: 12px;">
                    <p style="color: var(--text-muted);">Classes will appear here once the database is installed.</p>
                    <a href="<?= url('install.php') ?>" class="btn btn-primary" style="margin-top: 15px;">Run Installer</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Weekly Timetable Preview -->
<section class="timetable-section">
    <div class="container">
        <div class="text-center">
            <div class="section-tag">Live Schedule</div>
            <h2 class="section-title">WEEKLY <span class="text-gradient">CLASS SCHEDULE</span></h2>
            <p class="section-subtitle mx-auto">Select a day of the week to view available studio sessions and reserve your spot.</p>

            <div class="day-tabs">
                <button class="day-tab active" data-day="all">All Days</button>
                <button class="day-tab" data-day="Monday">Monday</button>
                <button class="day-tab" data-day="Tuesday">Tuesday</button>
                <button class="day-tab" data-day="Wednesday">Wednesday</button>
                <button class="day-tab" data-day="Thursday">Thursday</button>
                <button class="day-tab" data-day="Friday">Friday</button>
                <button class="day-tab" data-day="Saturday">Saturday</button>
            </div>
        </div>

        <div class="timetable-grid">
            <?php if (!empty($schedules)): ?>
                <?php foreach ($schedules as $sched): ?>
                    <div class="timetable-card" data-day="<?= e($sched['day_of_week']) ?>">
                        <div class="timetable-time">
                            <span>🕒 <?= format_time($sched['start_time']) ?> - <?= format_time($sched['end_time']) ?></span>
                        </div>
                        <div class="timetable-class-name"><?= e($sched['class_name']) ?></div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                            <span class="badge badge-info"><?= e($sched['day_of_week']) ?></span>
                            <span style="font-size: 12px; color: var(--text-muted);"><?= e($sched['room']) ?></span>
                        </div>
                        <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 13px; color: #fff;">Trainer: <?= e($sched['trainer_name'] ?? 'Coach') ?></span>
                            <a href="<?= url('member/book-class.php') ?>" class="btn btn-outline-primary btn-sm" style="padding: 4px 10px; font-size: 11px;">Book</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 30px;">
                    Timetable entries will show after running <a href="<?= url('install.php') ?>" style="color: var(--primary);">install.php</a>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Membership Pricing Plans -->
<section class="pricing-section" id="pricing">
    <div class="container">
        <div class="text-center">
            <div class="section-tag">Flexible Membership</div>
            <h2 class="section-title">TRANSPARENT <span class="text-gradient">PRICING TIERS</span></h2>
            <p class="section-subtitle mx-auto">No hidden fees, no lock-in contracts. Cancel or upgrade anytime directly from your member dashboard.</p>
        </div>

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
                            Select Plan &rarr;
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Meet the Trainers -->
<section class="trainers-section" id="trainers">
    <div class="container">
        <div class="text-center">
            <div class="section-tag">Certified Professionals</div>
            <h2 class="section-title">MEET OUR <span class="text-gradient">HEAD COACHES</span></h2>
            <p class="section-subtitle mx-auto">Every trainer holds national certifications with proven track records in body transformation and athletic conditioning.</p>
        </div>

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
                            <div style="font-size: 12px; color: #fff; margin-bottom: 12px;">
                                🏆 <strong><?= (int)$trainer['experience_years'] ?>+ Years</strong> Coaching Experience
                            </div>
                            <div class="trainer-social">
                                <span class="social-pill"><?= e($trainer['social_instagram'] ?? '@ironpulse') ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Interactive BMI & Calorie Calculator Section -->
<section class="calc-section" id="calculator">
    <div class="container">
        <div class="calc-box">
            <div class="text-center" style="margin-bottom: 35px;">
                <div class="section-tag">Free Health Assessment</div>
                <h2 class="section-title">CALCULATE YOUR <span class="text-gradient">BODY METRICS</span></h2>
                <p style="color: var(--text-muted); font-size: 15px;">Discover your Body Mass Index (BMI) and recommended workout guidance instantly.</p>
            </div>

            <div class="calc-grid">
                <form id="bmi-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="bmi-weight">Weight (kg)</label>
                            <input type="number" step="0.5" id="bmi-weight" class="form-control" placeholder="e.g. 78" required>
                        </div>
                        <div class="form-group">
                            <label for="bmi-height">Height (cm)</label>
                            <input type="number" step="0.5" id="bmi-height" class="form-control" placeholder="e.g. 178" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="bmi-age">Age (years)</label>
                            <input type="number" id="bmi-age" class="form-control" placeholder="e.g. 28">
                        </div>
                        <div class="form-group">
                            <label for="bmi-gender">Gender</label>
                            <select id="bmi-gender" class="form-control">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Calculate My BMI Now</button>
                    <div style="text-align: center; margin-top: 14px;">
                        <a href="<?= url('bmi-calculator.php') ?>" style="font-size: 13px; color: var(--primary);">Open Advanced Calorie & 1RM Calculators &rarr;</a>
                    </div>
                </form>

                <div class="calc-result-box">
                    <div style="font-size: 14px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Your BMI Score</div>
                    <div class="calc-score" id="bmi-result-score">23.4</div>
                    <div class="calc-status" id="bmi-result-status" style="color: var(--accent);">Healthy Weight</div>
                    <p id="bmi-result-advice" style="font-size: 14px; color: var(--text-secondary); line-height: 1.6; max-width: 400px; margin: 0 auto;">
                        Your score is in the optimal range. Keep up regular progressive resistance training and cardiovascular health routines!
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section style="padding: 90px 0; background: var(--bg-surface);">
    <div class="container">
        <div class="text-center">
            <div class="section-tag">Success Stories</div>
            <h2 class="section-title">REAL MEMBERS. <span class="text-gradient">REAL RESULTS.</span></h2>
            <p class="section-subtitle mx-auto">Hear from everyday athletes who elevated their strength and lifestyle with IronPulse.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
            <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 30px;">
                <div style="color: #f59e0b; margin-bottom: 12px; font-size: 18px;">★★★★★</div>
                <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7; margin-bottom: 20px;">
                    "The atmosphere at IronPulse is unrivaled. The Olympic platforms and coaching transformed my powerlifting total from 380kg to 495kg in 8 months. Best gym in town by far."
                </p>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff;">JA</div>
                    <div>
                        <div style="font-weight: 700; color: #fff; font-size: 14px;">John Anderson</div>
                        <div style="font-size: 12px; color: var(--text-muted);">Pro Fitness Member (1 Year)</div>
                    </div>
                </div>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 30px;">
                <div style="color: #f59e0b; margin-bottom: 12px; font-size: 18px;">★★★★★</div>
                <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7; margin-bottom: 20px;">
                    "Elena's Power Vinyasa Yoga and David's HIIT sessions are the highlight of my week. The facility is spotless, locker rooms are five-star, and the community is super supportive."
                </p>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: #e11d48; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff;">SM</div>
                    <div>
                        <div style="font-weight: 700; color: #fff; font-size: 14px;">Sarah Mitchell</div>
                        <div style="font-size: 12px; color: var(--text-muted);">Elite VIP Member (8 Months)</div>
                    </div>
                </div>
            </div>

            <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 30px;">
                <div style="color: #f59e0b; margin-bottom: 12px; font-size: 18px;">★★★★★</div>
                <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7; margin-bottom: 20px;">
                    "I dropped 24 lbs while packing on muscle thanks to Coach Marcus's custom strength program. The member portal makes logging workouts and booking classes effortless."
                </p>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: #3b82f6; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff;">AR</div>
                    <div>
                        <div style="font-weight: 700; color: #fff; font-size: 14px;">Alex Rivera</div>
                        <div style="font-size: 12px; color: var(--text-muted);">Annual Champion Member</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section style="padding: 80px 0; background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(15, 23, 42, 1)); border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
    <div class="container text-center">
        <h2 style="font-family: var(--font-heading); font-size: 48px; color: #fff; text-transform: uppercase; margin-bottom: 12px;">
            READY TO REWRITE YOUR FITNESS STORY?
        </h2>
        <p style="color: var(--text-secondary); font-size: 17px; max-width: 600px; margin: 0 auto 30px;">
            Join over 1,500 athletes who train with purpose. Sign up today and receive an immediate free body composition assessment.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= url('register.php') ?>" class="btn btn-primary btn-lg">Get Started Now &rarr;</a>
            <a href="<?= url('contact.php') ?>" class="btn btn-outline btn-lg">Book a Free Facility Tour</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
