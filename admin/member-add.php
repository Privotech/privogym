<?php
/**
 * Admin Portal - Register New Member
 */

$pageTitle = "Add New Member";
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

require_admin();
$db = getDB();

$error = '';
$plans = [];
try {
    $plans = $db->query("SELECT * FROM membership_plans WHERE status = 'active' ORDER BY price ASC")->fetchAll();
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? 'member123';
    $phone = trim($_POST['phone'] ?? '');
    $gender = $_POST['gender'] ?? 'other';
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $address = trim($_POST['address'] ?? '');
    $emergency = trim($_POST['emergency_contact'] ?? '');
    $fitness_goals = trim($_POST['fitness_goals'] ?? '');
    $plan_id = (int)($_POST['plan_id'] ?? 0);
    $pay_method = $_POST['payment_method'] ?? 'Cash';

    if (empty($full_name) || empty($email)) {
        $error = 'Full name and email address are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        try {
            // Check duplicate email
            $chk = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $error = 'A user with that email already exists in the system.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("
                    INSERT INTO users (full_name, email, password, phone, role, gender, dob, address, emergency_contact, fitness_goals, status, created_at)
                    VALUES (?, ?, ?, ?, 'member', ?, ?, ?, ?, ?, 'active', NOW())
                ");
                $stmt->execute([$full_name, $email, $hash, $phone, $gender, $dob, $address, $emergency, $fitness_goals]);
                $newUserId = $db->lastInsertId();

                // If plan selected
                if ($plan_id > 0) {
                    $pStmt = $db->prepare("SELECT * FROM membership_plans WHERE id = ? LIMIT 1");
                    $pStmt->execute([$plan_id]);
                    $targetPlan = $pStmt->fetch();

                    if ($targetPlan) {
                        $days = (int)$targetPlan['duration_days'];
                        $price = $targetPlan['price'];
                        $startDate = date('Y-m-d');
                        $endDate = date('Y-m-d', strtotime("+{$days} days"));

                        $mStmt = $db->prepare("
                            INSERT INTO memberships (user_id, plan_id, start_date, end_date, amount_paid, payment_status, status, created_at)
                            VALUES (?, ?, ?, ?, ?, 'paid', 'active', NOW())
                        ");
                        $mStmt->execute([$newUserId, $plan_id, $startDate, $endDate, $price]);
                        $membershipId = $db->lastInsertId();

                        // Payment transaction
                        $txn = 'TXN-FD-' . strtoupper(bin2hex(random_bytes(4)));
                        $payStmt = $db->prepare("
                            INSERT INTO payments (user_id, membership_id, transaction_ref, amount, payment_method, status, notes, payment_date)
                            VALUES (?, ?, ?, ?, ?, 'completed', 'Front desk registration', NOW())
                        ");
                        $payStmt->execute([$newUserId, $membershipId, $txn, $price, $pay_method]);
                    }
                }

                flash('success', "Member {$full_name} registered successfully!");
                redirect('admin/member-view.php?id=' . $newUserId);
            }
        } catch (Exception $e) {
            $error = 'Failed to register member: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<div style="margin-bottom: 24px;">
    <a href="<?= url('admin/members.php') ?>" style="color: var(--admin-primary); font-size: 13px; font-weight: 600;">&larr; Back to Members Directory</a>
    <h1 style="font-size: 24px; font-weight: 800; color: #fff; margin-top: 8px;">Register New Member</h1>
</div>

<?php if ($error): ?>
    <div class="flash-alert flash-alert-danger" style="margin-bottom: 20px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">
            1. Account & Identity
        </h3>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" class="form-control" placeholder="Jane Doe" required>
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="jane@example.com" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
            </div>
            <div class="form-group">
                <label>Default Password (Default: member123)</label>
                <input type="text" name="password" class="form-control" value="member123" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Gender</label>
                <select name="gender" class="form-control">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label>Emergency Contact</label>
            <input type="text" name="emergency_contact" class="form-control" placeholder="e.g. spouse or parent phone & name">
        </div>

        <div class="form-group">
            <label>Residential Address</label>
            <input type="text" name="address" class="form-control" placeholder="123 Workout Ave, City">
        </div>

        <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin: 24px 0 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">
            2. Initial Membership & Payment
        </h3>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Assign Membership Plan</label>
                <select name="plan_id" class="form-control">
                    <option value="0">-- No Initial Plan / Trial Only --</option>
                    <?php foreach ($plans as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            <?= htmlspecialchars($p['name']) ?> - <?= money($p['price']) ?> (<?= $p['duration_days'] ?> Days)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Payment Method</label>
                <select name="payment_method" class="form-control">
                    <option value="Cash">Cash (Front Desk)</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="PayPal">PayPal / Online</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Fitness Goals & Medical Notes</label>
            <textarea name="fitness_goals" class="form-control" rows="2" placeholder="e.g. Hypertrophy, lower back sensitivity, marathon prep..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
            Create Member Account &rarr;
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
