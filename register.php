<?php
/**
 * IronPulse Gym & Fitness Management System
 * Member Registration
 */

$pageTitle = "Join IronPulse - Member Registration";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/index.php' : 'member/index.php');
}

$error = '';
$selectedPlanId = isset($_GET['plan']) ? (int)$_GET['plan'] : 0;
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $gender = $_POST['gender'] ?? 'other';
    $fitness_goals = trim($_POST['fitness_goals'] ?? '');
    $plan_id = (int)($_POST['plan_id'] ?? 0);

    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Please fill out all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        try {
            $db = getDB();
            if ($db) {
                // Check if email already registered
                $check = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $check->execute([$email]);
                if ($check->fetch()) {
                    $error = 'That email address is already registered. Please sign in instead.';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("
                        INSERT INTO users (full_name, email, password, phone, role, gender, fitness_goals, status, created_at)
                        VALUES (?, ?, ?, ?, 'member', ?, ?, 'active', NOW())
                    ");
                    $stmt->execute([$full_name, $email, $hashedPassword, $phone, $gender, $fitness_goals]);
                    $userId = $db->lastInsertId();

                    // If a plan was selected, create the membership record
                    if ($plan_id > 0) {
                        $planStmt = $db->prepare("SELECT * FROM membership_plans WHERE id = ? AND status = 'active' LIMIT 1");
                        $planStmt->execute([$plan_id]);
                        $chosenPlan = $planStmt->fetch();

                        if ($chosenPlan) {
                            $days = (int)$chosenPlan['duration_days'];
                            $price = $chosenPlan['price'];
                            $startDate = date('Y-m-d');
                            $endDate = date('Y-m-d', strtotime("+{$days} days"));

                            $mStmt = $db->prepare("
                                INSERT INTO memberships (user_id, plan_id, start_date, end_date, amount_paid, payment_status, status, created_at)
                                VALUES (?, ?, ?, ?, ?, 'paid', 'active', NOW())
                            ");
                            $mStmt->execute([$userId, $plan_id, $startDate, $endDate, $price]);
                            $membershipId = $db->lastInsertId();

                            // Record payment transaction
                            $txnRef = 'TXN-' . strtoupper(bin2hex(random_bytes(4)));
                            $payStmt = $db->prepare("
                                INSERT INTO payments (user_id, membership_id, transaction_ref, amount, payment_method, status, notes, payment_date)
                                VALUES (?, ?, ?, ?, 'Credit Card', 'completed', ?, NOW())
                            ");
                            $payStmt->execute([$userId, $membershipId, $txnRef, $price, "Registration payment for {$chosenPlan['name']}"]);
                        }
                    }

                    // Auto-login user
                    $userStmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
                    $userStmt->execute([$userId]);
                    $newUser = $userStmt->fetch();
                    login_user($newUser);

                    flash('success', "Welcome to IronPulse, {$full_name}! Your membership account is active.");
                    redirect('member/index.php');
                }
            } else {
                $error = 'Database is currently not available.';
            }
        } catch (Exception $e) {
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 60px 20px;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 20px; max-width: 580px; width: 100%; padding: 40px; box-shadow: var(--shadow-lg);">
        <div class="text-center" style="margin-bottom: 28px;">
            <div class="section-tag" style="margin-bottom: 8px;">Begin Your Transformation</div>
            <h2 style="font-family: var(--font-heading); font-size: 36px; color: #fff; margin-bottom: 6px;">JOIN <span class="text-gradient">IRONPULSE FITNESS</span></h2>
            <p style="color: var(--text-muted); font-size: 14px;">Instant access to class booking, performance tracking, and facility passes.</p>
        </div>

        <?php if ($error): ?>
            <div class="flash-alert flash-alert-danger" style="margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" class="form-control" placeholder="e.g. John Doe" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Password (min 6 chars) *</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-control">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other / Prefer not to say</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Choose a Membership Tier (Optional or upgrade later)</label>
                <select name="plan_id" class="form-control">
                    <option value="0">-- Select Later / Free Visitor Trial --</option>
                    <?php foreach ($plans as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $selectedPlanId === (int)$p['id'] ? 'selected' : '' ?>>
                            <?= e($p['name']) ?> - <?= money($p['price']) ?> / <?= $p['duration_days'] >= 365 ? 'yr' : 'mo' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Your Primary Fitness Goals</label>
                <textarea name="fitness_goals" class="form-control" rows="2" placeholder="e.g. Muscle hypertrophy, marathon conditioning, 10kg fat loss..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 14px; margin-top: 10px;">
                Complete Registration & Join Now &rarr;
            </button>
        </form>

        <div style="text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-muted);">
            Already have an account? <a href="<?= url('login.php') ?>" style="color: var(--primary); font-weight: 700;">Sign In here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
