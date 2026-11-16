<?php
/**
 * Public Footer Template
 */
?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="<?= url('index.php') ?>" class="brand-logo">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6.5 6.5 11 11"/><path d="m21 21-1-1"/><path d="m3 3 1 1"/><path d="m18 22 4-4"/><path d="m2 6 4-4"/><path d="m3 10 7-7"/><path d="m14 21 7-7"/></svg>
                    IRON<span>PULSE</span>
                </a>
                <p>
                    IronPulse Fitness Club is a premier athletic training facility built for those who refuse average. We provide Olympic-grade equipment, elite personal trainers, and high-impact functional classes.
                </p>
                <div style="display: flex; gap: 12px; margin-top: 18px;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; background: #131c2e; color: #f97316;">★</span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; background: #131c2e; color: #f97316;">⚡</span>
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; background: #131c2e; color: #f97316;">🛡️</span>
                </div>
            </div>

            <div class="footer-col">
                <h4>Explore</h4>
                <ul class="footer-links">
                    <li><a href="<?= url('about.php') ?>">About Us</a></li>
                    <li><a href="<?= url('classes.php') ?>">Classes & Programs</a></li>
                    <li><a href="<?= url('timetable.php') ?>">Class Timetable</a></li>
                    <li><a href="<?= url('trainers.php') ?>">Expert Trainers</a></li>
                    <li><a href="<?= url('pricing.php') ?>">Membership Plans</a></li>
                    <li><a href="<?= url('bmi-calculator.php') ?>">Fitness Calculators</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Member Portal</h4>
                <ul class="footer-links">
                    <?php if (is_logged_in()): ?>
                        <li><a href="<?= is_admin() ? url('admin/index.php') : url('member/index.php') ?>">My Account</a></li>
                        <li><a href="<?= url('member/book-class.php') ?>">Book a Class</a></li>
                        <li><a href="<?= url('member/workout-tracker.php') ?>">Workout Tracker</a></li>
                        <li><a href="<?= url('logout.php') ?>">Sign Out</a></li>
                    <?php else: ?>
                        <li><a href="<?= url('login.php') ?>">Member Sign In</a></li>
                        <li><a href="<?= url('register.php') ?>">Create Account</a></li>
                        <li><a href="<?= url('login.php') ?>">Admin Portal</a></li>
                        <li><a href="<?= url('install.php') ?>">Database Installer</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Facility Hours</h4>
                <div style="font-size: 13px; color: #94a3b8; line-height: 1.8;">
                    <p><strong style="color: #fff;">Monday - Friday:</strong><br>05:00 AM - 11:00 PM</p>
                    <p style="margin-top: 10px;"><strong style="color: #fff;">Saturday - Sunday:</strong><br>07:00 AM - 09:00 PM</p>
                    <p style="margin-top: 10px;"><strong style="color: #fff;">Location:</strong><br>742 Fitness Boulevard, Metro City, NY</p>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div>
                &copy; <?= date('Y') ?> <strong><?= APP_NAME ?></strong>. All rights reserved.
            </div>
            <div style="display: flex; gap: 20px;">
                <a href="<?= url('contact.php') ?>" style="color: #64748b;">Support</a>
                <a href="<?= url('install.php') ?>" style="color: #64748b;">System Setup</a>
                <a href="#top" style="color: #f97316;">Back to top &uarr;</a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
