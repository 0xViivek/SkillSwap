<?php
/**
 * SkillSwap — Student Profile
 * File: profile.php
 * Owner: Member 3 (Profile + Skills)
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user   = currentUser();
$userId = $user['id'];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']       ?? '');
    $department = trim($_POST['department'] ?? '');
    $year       = (int)($_POST['year']      ?? 0);

    if ($name === '')        $errors[] = 'Full name is required.';
    if (strlen($name) < 2)  $errors[] = 'Name must be at least 2 characters.';
    if ($department === '')  $errors[] = 'Department is required.';
    if ($year < 1 || $year > 4) $errors[] = 'Year must be between 1 and 4.';

    if (empty($errors)) {
        updateUser($userId, $name, $department, $year);
        // Refresh session name
        $_SESSION['user_name'] = $name;
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Profile updated successfully!'];
        header('Location: /SkillSwap/profile.php');
        exit;
    }

    // Keep edits on error
    $user['name']       = $name;
    $user['department'] = $department;
    $user['year']       = $year;
}

$teachSkills = getUserSkillsById($userId, 'teach');
$learnSkills = getUserSkillsById($userId, 'learn');

$pageTitle = 'My Profile';
include __DIR__ . '/includes/header.php';
?>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<h1 class="page-title">My Profile</h1>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">

    <!-- Edit form -->
    <div class="card">
        <h2 class="card-title">Edit Details</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                    <div><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name"
                       value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled
                       style="background:var(--gray-100); cursor:not-allowed;">
                <small class="text-muted">Email cannot be changed.</small>
            </div>

            <div class="form-group">
                <label>Department</label>
                <select name="department" required>
                    <?php
                    $departments = ['CSE','ECE','EEE','ME','CE','IT','MBA','MCA','Other'];
                    foreach ($departments as $d):
                        $sel = ($user['department'] === $d) ? 'selected' : '';
                    ?>
                        <option value="<?= $d ?>" <?= $sel ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Year of Study</label>
                <select name="year" required>
                    <?php for ($y = 1; $y <= 4; $y++): ?>
                        <option value="<?= $y ?>" <?= ($user['year'] == $y) ? 'selected' : '' ?>>
                            Year <?= $y ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
    </div>

    <!-- Skills summary -->
    <div>
        <div class="card">
            <div class="d-flex justify-between align-center mb-1">
                <h2 class="card-title" style="margin-bottom:0;">Skills Overview</h2>
                <a href="/SkillSwap/skills.php" class="btn btn-outline btn-sm">Manage →</a>
            </div>

            <p style="font-size:0.82rem; font-weight:600; color:var(--gray-600); margin-bottom:0.4rem; margin-top:0.75rem;">
                CAN TEACH (<?= count($teachSkills) ?>)
            </p>
            <?php if (empty($teachSkills)): ?>
                <p class="text-muted" style="font-size:0.85rem;">None added yet.</p>
            <?php else: ?>
                <div class="skill-pills">
                    <?php foreach ($teachSkills as $us):
                        $sk = findSkillById($us['skill_id']);
                    ?>
                        <span class="skill-pill pill-teach"><?= htmlspecialchars($sk['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <p style="font-size:0.82rem; font-weight:600; color:var(--gray-600); margin-bottom:0.4rem; margin-top:1rem;">
                WANT TO LEARN (<?= count($learnSkills) ?>)
            </p>
            <?php if (empty($learnSkills)): ?>
                <p class="text-muted" style="font-size:0.85rem;">None added yet.</p>
            <?php else: ?>
                <div class="skill-pills">
                    <?php foreach ($learnSkills as $us):
                        $sk = findSkillById($us['skill_id']);
                    ?>
                        <span class="skill-pill pill-learn"><?= htmlspecialchars($sk['name']) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Account info -->
        <div class="card mt-2">
            <h2 class="card-title">Account Info</h2>
            <table style="width:100%; font-size:0.88rem;">
                <tr>
                    <td class="text-muted" style="padding:0.3rem 0; width:40%;">User ID</td>
                    <td><strong>#<?= $user['id'] ?></strong></td>
                </tr>
                <tr>
                    <td class="text-muted" style="padding:0.3rem 0;">Role</td>
                    <td><span class="badge badge-accepted"><?= ucfirst($user['role']) ?></span></td>
                </tr>
                <tr>
                    <td class="text-muted" style="padding:0.3rem 0;">Total Skills</td>
                    <td><?= count($teachSkills) + count($learnSkills) ?></td>
                </tr>
            </table>
        </div>
    </div>

</div>

<style>
@media (max-width: 700px) {
    div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
