<?php
/**
 * SkillSwap — Login
 * File: login.php
 * Owner: Member 2 (Auth)
 */

require_once __DIR__ . '/includes/auth.php';
requireGuest();   // already logged-in users go to dashboard

$error    = '';
$formEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $formEmail = $email;

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $user = loginUser($email, $password);

        if ($user === false) {
            $error = 'Invalid email or password. Please try again.';
        } else {
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: ' . BASE_URL . '/admin/dashboard.php');
            } else {
                header('Location: ' . BASE_URL . '/dashboard.php');
            }
            exit;
        }
    }
}

$pageTitle = 'Login';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">

        <h1>Welcome Back</h1>
        <p class="subtitle">Login to your SkillSwap account</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
                <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($formEmail) ?>"
                       placeholder="you@example.com"
                       required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Your password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-1">
                Login
            </button>

        </form>

        <p class="text-center mt-2 text-muted">
            Don't have an account? <a href="<?= BASE_URL ?>/register.php">Register here</a>
        </p>

        <!-- Demo hint — remove before final presentation if needed -->
        <div class="alert alert-info mt-2" style="font-size:0.8rem;">
            <strong>Demo accounts:</strong><br>
            Student: vivek@gmail.com / password<br>
            Admin &nbsp;: admin@skillswap.com / password
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
