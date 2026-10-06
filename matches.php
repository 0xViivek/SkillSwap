<?php
/**
 * SkillSwap — Skill Matches
 * File: matches.php
 * Owner: Member 1 (Project Lead + Matching)
 *
 * This is the MAIN showcase page of the project.
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$userId  = currentUserId();
$matches = getMatchesForUser($userId);

// ── Handle Send Request ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_request') {
    $receiverId       = (int)($_POST['receiver_id']        ?? 0);
    $offeredSkillId   = (int)($_POST['offered_skill_id']   ?? 0);
    $requestedSkillId = (int)($_POST['requested_skill_id'] ?? 0);

    if ($receiverId && $offeredSkillId && $requestedSkillId) {
        $result = createRequest($userId, $receiverId, $offeredSkillId, $requestedSkillId);
        if ($result === false) {
            $_SESSION['flash'] = ['type' => 'info', 'msg' => 'You already have a pending request with this student.'];
        } else {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Exchange request sent successfully!'];
        }
    }
    header('Location: ' . BASE_URL . '/matches.php');
    exit;
}

$myTeachIds = array_column(getUserSkillsById($userId, 'teach'), 'skill_id');
$myLearnIds = array_column(getUserSkillsById($userId, 'learn'), 'skill_id');

$pageTitle = 'Skill Matches';
include __DIR__ . '/includes/header.php';
?>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="d-flex justify-between align-center mb-2">
    <h1 class="page-title" style="margin-bottom:0;">Your Skill Matches</h1>
    <span class="text-muted"><?= count($matches) ?> match<?= count($matches) !== 1 ? 'es' : '' ?> found</span>
</div>

<!-- Search -->
<?php if (!empty($matches)): ?>
<div class="form-group mb-2" style="max-width:360px;">
    <input type="text" id="match-search" placeholder="🔍  Search by name or skill…">
</div>
<?php endif; ?>

<?php if (empty($matches)): ?>
    <div class="card text-center" style="padding:3rem;">
        <p style="font-size:3rem;">🔍</p>
        <h2 style="margin:0.5rem 0;">No matches yet</h2>
        <p class="text-muted">Add skills you can teach and skills you want to learn — the system will find compatible students automatically.</p>
        <a href="<?= BASE_URL ?>/skills.php" class="btn btn-primary mt-2">Add Skills →</a>
    </div>

<?php else: ?>

    <?php foreach ($matches as $match):
        $other     = $match;
        $scoreData = $match['match'];
        $score     = $scoreData['score'];
        $badgeClass = $score === 100 ? 'score-100' : 'score-50';

        $otherTeach = getUserSkillsById($other['id'], 'teach');
        $otherLearn = getUserSkillsById($other['id'], 'learn');

        $otherTeachIds = array_column($otherTeach, 'skill_id');
        $otherLearnIds = array_column($otherLearn, 'skill_id');

        // What I can learn from them (dir1 matches)
        $iCanLearnFrom = $scoreData['dir1_matches'];   // skill IDs
        // What they can learn from me (dir2 matches)
        $theyLearnFrom = $scoreData['dir2_matches'];   // skill IDs

        // Pick skills for the request form:
        // offered = first skill I teach that they want
        // requested = first skill I want that they teach
        $defaultOffered   = !empty($theyLearnFrom) ? $theyLearnFrom[0]   : null;
        $defaultRequested = !empty($iCanLearnFrom) ? $iCanLearnFrom[0]   : null;

        // Check if a pending request already exists
        $existingPending = false;
        foreach (getRequestsForUser($userId, 'sender') as $r) {
            if ($r['receiver_id'] === $other['id'] && $r['status'] === 'pending') {
                $existingPending = true;
                break;
            }
        }
    ?>

    <div class="match-card">

        <!-- Left: student info + why it's a match -->
        <div>
            <div class="d-flex align-center gap-2 mb-1">
                <div style="width:42px; height:42px; border-radius:50%; background:var(--primary);
                            color:#fff; display:flex; align-items:center; justify-content:center;
                            font-weight:700; font-size:1.1rem; flex-shrink:0;">
                    <?= strtoupper(mb_substr($other['name'], 0, 1)) ?>
                </div>
                <div>
                    <strong style="font-size:1rem;"><?= htmlspecialchars($other['name']) ?></strong>
                    <p class="text-muted" style="font-size:0.8rem; margin:0;">
                        <?= htmlspecialchars($other['department']) ?> &middot; Year <?= $other['year'] ?>
                    </p>
                </div>
            </div>

            <!-- Their skills -->
            <?php if (!empty($otherTeach)): ?>
            <p style="font-size:0.78rem; font-weight:600; color:var(--gray-600); margin-bottom:0.3rem;">CAN TEACH</p>
            <div class="skill-pills mb-1">
                <?php foreach ($otherTeach as $us):
                    $sk = findSkillById($us['skill_id']);
                    $highlight = in_array($us['skill_id'], $iCanLearnFrom) ? 'outline:2px solid #16a34a;' : '';
                ?>
                    <span class="skill-pill pill-teach" style="<?= $highlight ?>"><?= htmlspecialchars($sk['name']) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($otherLearn)): ?>
            <p style="font-size:0.78rem; font-weight:600; color:var(--gray-600); margin-bottom:0.3rem;">WANTS TO LEARN</p>
            <div class="skill-pills mb-1">
                <?php foreach ($otherLearn as $us):
                    $sk = findSkillById($us['skill_id']);
                    $highlight = in_array($us['skill_id'], $theyLearnFrom) ? 'outline:2px solid #4f46e5;' : '';
                ?>
                    <span class="skill-pill pill-learn" style="<?= $highlight ?>"><?= htmlspecialchars($sk['name']) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Why this is a match -->
            <div style="background:var(--gray-50); border-radius:var(--radius); padding:0.75rem; margin-top:0.75rem;">
                <p style="font-size:0.8rem; font-weight:600; color:var(--gray-600); margin-bottom:0.4rem;">WHY THIS IS A MATCH</p>
                <ul class="match-reasons">
                    <?php foreach ($iCanLearnFrom as $sid):
                        $sk = findSkillById($sid);
                    ?>
                        <li><?= htmlspecialchars($other['name']) ?> teaches <strong><?= htmlspecialchars($sk['name']) ?></strong></li>
                        <li>You want to learn <strong><?= htmlspecialchars($sk['name']) ?></strong></li>
                    <?php endforeach; ?>
                    <?php foreach ($theyLearnFrom as $sid):
                        $sk = findSkillById($sid);
                    ?>
                        <li>You teach <strong><?= htmlspecialchars($sk['name']) ?></strong></li>
                        <li><?= htmlspecialchars($other['name']) ?> wants to learn <strong><?= htmlspecialchars($sk['name']) ?></strong></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Right: score + send request -->
        <div style="text-align:center; min-width:160px;">
            <div class="match-score-badge <?= $badgeClass ?>" style="font-size:1rem; padding:0.4rem 1rem; display:block; margin-bottom:0.5rem;">
                <?= $score ?>% Match
            </div>
            <div style="font-size:0.75rem; color:var(--gray-600); margin-bottom:1rem;">
                <?= $score === 100 ? '🔁 Mutual Exchange' : '➡️ One-way Match' ?>
            </div>

            <?php if ($existingPending): ?>
                <span class="badge badge-pending" style="display:block; margin-bottom:0.5rem;">Request Pending</span>
                <a href="<?= BASE_URL ?>/requests.php" class="btn btn-outline btn-sm">View Request</a>
            <?php elseif ($defaultOffered && $defaultRequested): ?>
                <form method="POST" action="">
                    <input type="hidden" name="action"             value="send_request">
                    <input type="hidden" name="receiver_id"        value="<?= $other['id'] ?>">
                    <input type="hidden" name="offered_skill_id"   value="<?= $defaultOffered ?>">
                    <input type="hidden" name="requested_skill_id" value="<?= $defaultRequested ?>">

                    <div class="form-group" style="text-align:left; font-size:0.8rem;">
                        <label>I'll teach:</label>
                        <select name="offered_skill_id" style="font-size:0.8rem; padding:0.3rem 0.5rem;">
                            <?php foreach ($theyLearnFrom as $sid):
                                $sk = findSkillById($sid);
                            ?>
                                <option value="<?= $sid ?>" <?= $sid === $defaultOffered ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sk['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="text-align:left; font-size:0.8rem;">
                        <label>I want to learn:</label>
                        <select name="requested_skill_id" style="font-size:0.8rem; padding:0.3rem 0.5rem;">
                            <?php foreach ($iCanLearnFrom as $sid):
                                $sk = findSkillById($sid);
                            ?>
                                <option value="<?= $sid ?>" <?= $sid === $defaultRequested ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sk['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Send Exchange Request
                    </button>
                </form>
            <?php else: ?>
                <p class="text-muted" style="font-size:0.8rem;">Add matching skills to send a request.</p>
            <?php endif; ?>
        </div>

    </div>
    <?php endforeach; ?>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
