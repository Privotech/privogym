<?php
/**
 * IronPulse Gym & Fitness Management System
 * Unified Login (Admin & Member)
 */

$pageTitle = "Sign In - Member & Admin Access";
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

// If already logged in, redirect to respective dashboard
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/index.php');
    } else {
        redirect('member/index.php');
    }
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        try {
            $db = getDB();
            if ($db) {
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    if ($user['status'] !== 'active') {
                        $error = 'Your account has been deactivated or suspended. Please contact gym administration.';
                    } else {
                        login_user($user);
                        flash('success', 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!');
                        
                        if ($user['role'] === 'admin') {
                            redirect('admin/index.php');
                        } else {
                            redirect('member/index.php');
                        }
                    }
                } else {
                    $error = 'Invalid email address or password combination.';
                }
            } else {
                $error = 'Database connection error. Please run the installer.';
            }
        } catch (Exception $e) {
            $error = 'Authentication failed: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height: 75vh; display: flex; align-items: center; justify-content: center; padding: 60px 20px;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 20px; max-width: 480px; width: 100%; padding: 40px; box-shadow: var(--shadow-lg);">
        <div class="text-center" style="margin-bottom: 28px;">
            <div class="section-tag" style="margin-bottom: 8px;">Portal Access</div>
            <h2 style="font-family: var(--font-heading); font-size: 36px; color: #fff; margin-bottom: 6px;">SIGN IN TO <span class="text-gradient">IRONPULSE</span></h2>
            <p style="color: var(--text-muted); font-size: 14px;">Access your member workouts or gym management portal.</p>
        </div>

        <?php if ($error): ?>
            <div class="flash-alert flash-alert-danger" style="margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" id="login-email" class="form-control" value="<?= htmlspecialchars($email) ?>" placeholder="e.g. member@example.com" required autofocus>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label style="margin-bottom: 0;">Password</label>
                </div>
                <input type="password" name="password" id="login-password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 14px; margin-top: 10px;">
                Sign In to Dashboard &rarr;
            </button>
        </form>

        <!-- Quick Demo Switcher for fast evaluation -->
        <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--border-subtle);">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px; text-align: center;">
                Quick Demo Credentials:
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="setDemo('admin@gym.com', 'admin123')">
                    👑 Demo Admin
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="setDemo('john@example.com', 'member123')">
                    🏋️ Demo Member
                </button>
            </div>
        </div>

        <div style="text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-muted);">
            Don't have an account yet? <a href="<?= url('register.php') ?>" style="color: var(--primary); font-weight: 700;">Join IronPulse</a>
        </div>
    </div>
</div>

<script>
function setDemo(email, pass) {
    document.getElementById('login-email').value = email;
    document.getElementById('login-password').value = pass;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
