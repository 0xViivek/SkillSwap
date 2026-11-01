<?php
/**
 * SkillSwap — Manage Skills
 * File: skills.php
 * Owner: Member 3 (Profile + Skills)
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$userId  = currentUserId();
$errors  = [];
$allSkills = getSkills();

// ── Handle Add Skill ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $skillId = (int)($_POST['skill_id'] ?? 0);
    $type    = $_POST['type'] ?? '';

    if ($skillId === 0)                          $errors[] = 'Please select a skill.';
    elseif (!in_array($type, ['teach','learn'])) $errors[] = 'Please select a valid type.';
    elseif (findSkillById($skillId) === null)    $errors[] = 'Skill not found.';
    else {
        $result = addUserSkill($userId, $skillId, $type);
        if ($result === false) {
            $errors[] = 'Could not add skill. It may already exist; please try again.';
        } else {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Skill added successfully!'];
            header('Location: ' . BASE_URL . '/skills.php');
            exit;
        }
    }
}

// ── Handle Remove Skill ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $userSkillId = (int)($_POST['user_skill_id'] ?? 0);
    if ($userSkillId > 0) {
        if (!removeUserSkill($userSkillId, $userId)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Could not remove this skill.'];
            header('Location: ' . BASE_URL . '/skills.php');
            exit;
        }
        $_SESSION['flash'] = ['type' => 'info', 'msg' => 'Skill removed.'];
        header('Location: ' . BASE_URL . '/skills.php');
        exit;
    }
}

$teachSkills = getUserSkillsById($userId, 'teach');
$learnSkills = getUserSkillsById($userId, 'learn');

// Group master skills by category for the dropdown
$skillsByCategory = [];
foreach ($allSkills as $sk) {
    $skillsByCategory[$sk['category']][] = $sk;
}

$pageTitle = 'My Skills';
include __DIR__ . '/includes/header.php';
?>

<?php if (isset($_SESSION['flash'])): ?>
    <div data-flash class="alert alert-<?= $_SESSION['flash']['type'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="d-flex justify-between align-center mb-2">
    <h1 class="page-title" style="margin-bottom:0;">My Skills</h1>
</div>

<div style="display:grid; grid-template-columns:1fr 1.6fr; gap:1.5rem;">

    <!-- Add skill form -->
    <div class="card" style="align-self:start;">
        <h2 class="card-title">Add a Skill</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                    <div><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label for="skill_id">Skill</label>
                <select id="skill_id" name="skill_id" required>
                    <option value="">— Select a skill —</option>
                    <?php foreach ($skillsByCategory as $category => $skills): ?>
                        <optgroup label="<?= htmlspecialchars($category) ?>">
                            <?php foreach ($skills as $sk): ?>
                                <option value="<?= $sk['id'] ?>"><?= htmlspecialchars($sk['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>I want to…</label>
                <div style="display:flex; gap:1rem; margin-top:0.25rem;">
                    <label style="display:flex; align-items:center; gap:0.4rem; font-weight:400; cursor:pointer;">
                        <input type="radio" name="type" value="teach" required>
                        <span class="skill-pill pill-teach">Teach this</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:0.4rem; font-weight:400; cursor:pointer;">
                        <input type="radio" name="type" value="learn">
                        <span class="skill-pill pill-learn">Learn this</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Add Skill</button>
        </form>
    </div>

    <!-- Current skills -->
    <div>

        <!-- Teach skills -->
        <div class="card mb-2">
            <h2 class="card-title">Skills I Can Teach
                <span class="text-muted" style="font-weight:400; font-size:0.85rem;">(<?= count($teachSkills) ?>)</span>
            </h2>

            <?php if (empty($teachSkills)): ?>
                <p class="text-muted">No teaching skills added yet.</p>
            <?php else: ?>
                <div>
                    <?php foreach ($teachSkills as $us):
                        $sk = findSkillById($us['skill_id']);
                    ?>
                        <div class="d-flex justify-between align-center skill-row"
                             style="padding:0.5rem 0; border-bottom:1px solid var(--gray-100);">
                            <div>
                                <span class="skill-pill pill-teach"><?= htmlspecialchars($sk['name']) ?></span>
                                <span class="text-muted" style="font-size:0.78rem; margin-left:0.5rem;"><?= htmlspecialchars($sk['category']) ?></span>
                            </div>
                            <form method="POST" action="" style="margin:0;">
            <?= csrfField() ?>
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="user_skill_id" value="<?= $us['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm"
                                        data-confirm="Remove '<?= htmlspecialchars($sk['name']) ?>' from your teach list?">
                                    Remove
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Learn skills -->
        <div class="card">
            <h2 class="card-title">Skills I Want to Learn
                <span class="text-muted" style="font-weight:400; font-size:0.85rem;">(<?= count($learnSkills) ?>)</span>
            </h2>

            <?php if (empty($learnSkills)): ?>
                <p class="text-muted">No learning skills added yet.</p>
            <?php else: ?>
                <div>
                    <?php foreach ($learnSkills as $us):
                        $sk = findSkillById($us['skill_id']);
                    ?>
                        <div class="d-flex justify-between align-center skill-row"
                             style="padding:0.5rem 0; border-bottom:1px solid var(--gray-100);">
                            <div>
                                <span class="skill-pill pill-learn"><?= htmlspecialchars($sk['name']) ?></span>
                                <span class="text-muted" style="font-size:0.78rem; margin-left:0.5rem;"><?= htmlspecialchars($sk['category']) ?></span>
                            </div>
                            <form method="POST" action="" style="margin:0;">
            <?= csrfField() ?>
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="user_skill_id" value="<?= $us['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm"
                                        data-confirm="Remove '<?= htmlspecialchars($sk['name']) ?>' from your learn list?">
                                    Remove
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<style>
@media (max-width: 700px) {
    div[style*="grid-template-columns:1fr 1.6fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
