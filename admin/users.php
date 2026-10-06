<?php
/**
 * SkillSwap — Admin: Manage Students
 * File: admin/users.php
 * Owner: Member 5 (UI + Admin)
 */

require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// ── Handle delete ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $targetId = (int)$_POST['user_id'];
    if ($_POST['action'] === 'delete' && $targetId !== currentUserId()) {
        deleteUser($targetId);
        $_SESSION['flash'] = ['type' => 'info', 'msg' => 'Student removed successfully.'];
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$users    = getUsers();
$students = array_values(array_filter($users, fn($u) => $u['role'] === 'student'));

$pageTitle = 'Manage Students';
include __DIR__ . '/../includes/header.php';
?>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="d-flex justify-between align-center mb-2">
    <h1 class="page-title" style="margin-bottom:0;">Students (<?= count($students) ?>)</h1>
    <input type="text" id="skill-search" placeholder="🔍 Search students…" style="max-width:260px;">
</div>

<div class="card" style="padding:0;">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Year</th>
                    <th>Teaches</th>
                    <th>Learns</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr><td colspan="8" class="text-center text-muted" style="padding:2rem;">No students registered yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $s):
                        $teach = getUserSkillsById($s['id'], 'teach');
                        $learn = getUserSkillsById($s['id'], 'learn');
                    ?>
                    <tr class="skill-row">
                        <td class="text-muted"><?= $s['id'] ?></td>
                        <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                        <td class="text-muted"><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= htmlspecialchars($s['department']) ?></td>
                        <td><?= $s['year'] ?></td>
                        <td>
                            <div class="skill-pills">
                                <?php foreach ($teach as $us):
                                    $sk = findSkillById($us['skill_id']);
                                ?>
                                    <span class="skill-pill pill-teach" style="font-size:0.72rem;"><?= htmlspecialchars($sk['name']) ?></span>
                                <?php endforeach; ?>
                                <?php if (empty($teach)): ?><span class="text-muted">—</span><?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="skill-pills">
                                <?php foreach ($learn as $us):
                                    $sk = findSkillById($us['skill_id']);
                                ?>
                                    <span class="skill-pill pill-learn" style="font-size:0.72rem;"><?= htmlspecialchars($sk['name']) ?></span>
                                <?php endforeach; ?>
                                <?php if (empty($learn)): ?><span class="text-muted">—</span><?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <form method="POST" action="" style="margin:0;">
                                <input type="hidden" name="action"  value="delete">
                                <input type="hidden" name="user_id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm"
                                        data-confirm="Remove <?= htmlspecialchars($s['name']) ?> and all their data?">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
