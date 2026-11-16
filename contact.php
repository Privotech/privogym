<?php
/**
 * IronPulse Gym & Fitness Management System
 * Contact Us & Inquiries Form
 */

$pageTitle = "Contact & Location";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill out all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $db = getDB();
            if ($db) {
                $stmt = $db->prepare("
                    INSERT INTO inquiries (name, email, phone, subject, message, status, created_at)
                    VALUES (?, ?, ?, ?, ?, 'unread', NOW())
                ");
                $stmt->execute([$name, $email, $phone, $subject, $message]);
                $success = true;
                flash('success', 'Thank you! Your message has been received. Our team will contact you shortly.');
                redirect('contact.php');
            } else {
                $error = 'Database connection unavailable.';
            }
        } catch (Exception $e) {
            $error = 'Failed to submit inquiry: ' . $e->getMessage();
        }
    }
}

$prefillSubject = isset($_GET['trainer']) ? 'Personal Coaching with ' . $_GET['trainer'] : '';

require_once __DIR__ . '/includes/header.php';
?>

<div style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(15, 23, 42, 0.9)); padding: 60px 0 40px; border-bottom: 1px solid var(--border-subtle);">
    <div class="container text-center">
        <div class="section-tag">Get in Touch</div>
        <h1 class="section-title">CONNECT WITH <span class="text-gradient">OUR TEAM</span></h1>
        <p class="section-subtitle mx-auto">Have questions about memberships, private coaching, or want to schedule a facility walkthrough?</p>
    </div>
</div>

<section style="padding: 70px 0;">
    <div class="container">
        <?php if ($error): ?>
            <div class="flash-alert flash-alert-danger" style="margin-bottom: 30px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="contact-grid">
            <!-- Contact Form -->
            <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 40px;">
                <h3 style="font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 20px;">Send Us an Inquiry</h3>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                        </div>

                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
                        </div>

                        <div class="form-group">
                            <label>Subject *</label>
                            <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($prefillSubject) ?>" placeholder="Membership Inquiry / Tour" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Your Message *</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Tell us how we can help you achieve your goals..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Submit Message &rarr;
                    </button>
                </form>
            </div>

            <!-- Facility Info & Hours -->
            <div>
                <div class="contact-info-card" style="margin-bottom: 24px;">
                    <h3 style="font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 24px;">Facility Headquarters</h3>

                    <div class="contact-item">
                        <div class="contact-item-icon">📍</div>
                        <div>
                            <h4>Address</h4>
                            <p>742 Fitness Boulevard, Downtown Athletic District, Metro City, NY 10001</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item-icon">📞</div>
                        <div>
                            <h4>Phone & WhatsApp</h4>
                            <p>+1 (555) 234-5678 (Toll Free Front Desk)</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item-icon">✉️</div>
                        <div>
                            <h4>General & Press Inquiries</h4>
                            <p>contact@ironpulsefitness.com</p>
                        </div>
                    </div>

                    <div class="contact-item" style="margin-bottom: 0;">
                        <div class="contact-item-icon">⏱️</div>
                        <div>
                            <h4>Hours of Operation</h4>
                            <p>
                                <strong>Mon - Fri:</strong> 5:00 AM - 11:00 PM<br>
                                <strong>Sat - Sun:</strong> 7:00 AM - 9:00 PM
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Map visual preview box -->
                <div style="background: #0d1424; border: 1px solid var(--border-subtle); border-radius: 16px; padding: 24px; text-align: center;">
                    <div style="font-size: 32px; margin-bottom: 8px;">🗺️</div>
                    <h4 style="color: #fff; font-size: 16px; margin-bottom: 4px;">Convenient Downtown Metro Access</h4>
                    <p style="color: var(--text-muted); font-size: 13px;">Located 2 blocks from Grand Central Transit with free on-site member parking for 2 hours.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
