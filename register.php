<?php
/**
 * SkillSwap — Student Registration
 * File: register.php
 * Owner: Member 2 (Auth)
 */

require_once __DIR__ . '/includes/auth.php';
requireGuest();   // redirect to dashboard if already logged in

$errors = [];
$formData = ['name' => '', 'email' => '', 'department' => '', 'year' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Collect & sanitize inputs ────────────────────────────────
    $name       = trim($_POST['name']       ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   = $_POST['password']        ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';
    $department = trim($_POST['department'] ?? '');
    $year       = (int)($_POST['year']      ?? 0);

    // Keep form filled on error
    $formData = compact('name', 'email', 'department', 'year');

    // ── Validation ───────────────────────────────────────────────
    if ($name === '')       $errors[] = 'Full name is required.';
    if (strlen($name) < 2) $errors[] = 'Name must be at least 2 characters.';

    if ($email === '')                      $errors[] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';

    if ($password === '')           $errors[] = 'Password is required.';
    elseif (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    elseif ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if ($department === '') $errors[] = 'Department is required.';
    if ($year < 1 || $year > 4) $errors[] = 'Year must be between 1 and 4.';

    // ── Try to create account ────────────────────────────────────
    if (empty($errors)) {
        $user = createUser($name, $email, $password, $department, $year);

        if ($user === false) {
            $errors[] = 'An account with this email already exists.';
        } else {
            // Log the user in immediately after registration
            loginUser($email, $password);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Welcome to SkillSwap, ' . $user['name'] . '!'];
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        }
    }
}

$pageTitle = 'Register';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-card">

        <h1>Create Account</h1>
        <p class="subtitle">Join SkillSwap and start exchanging skills</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                    <div><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name"
                       value="<?= htmlspecialchars($formData['name']) ?>"
                       placeholder="e.g. Vivek Kumar" required autofocus>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($formData['email']) ?>"
                       placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="At least 6 characters" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password"
                       placeholder="Repeat password" required>
            </div>

            <div class="form-group">
                <label for="department">Department</label>
                <select id="department" name="department" required>
                    <option value="">— Select department —</option>
                    <?php
                    $departments = ['CSE','ECE','EEE','ME','CE','IT','MBA','MCA','Other'];
                    foreach ($departments as $d):
                        $sel = ($formData['department'] === $d) ? 'selected' : '';
                    ?>
                        <option value="<?= $d ?>" <?= $sel ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="year">Year of Study</label>
                <select id="year" name="year" required>
                    <option value="">— Select year —</option>
                    <?php for ($y = 1; $y <= 4; $y++): ?>
                        <option value="<?= $y ?>" <?= ($formData['year'] == $y) ? 'selected' : '' ?>>
                            Year <?= $y ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-1">
                Create Account
            </button>

        </form>

        <p class="text-center mt-2 text-muted">
            Already have an account? <a href="<?= BASE_URL ?>/login.php">Login here</a>
        </p>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
