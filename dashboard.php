<?php
/**
 * SkillSwap — Student Dashboard
 * File: dashboard.php
 * Owner: Member 1 (Project Lead)
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user     = currentUser();
$userId   = $user['id'];

// ── Gather data ───────────────────────────────────────────────────
$teachSkills     = getUserSkillsById($userId, 'teach');
$learnSkills     = getUserSkillsById($userId, 'learn');
$totalSkills     = count($teachSkills) + count($learnSkills);

$allMatches      = getMatchesForUser($userId);
$topMatches      = array_slice($allMatches, 0, 3);   // show top 3 on dashboard

$pendingReceived = array_filter(
    getRequestsForUser($userId, 'receiver'),
    fn($r) => $r['status'] === 'pending'
);
$pendingSent     = array_filter(
    getRequestsForUser($userId, 'sender'),
    fn($r) => $r['status'] === 'pending'
);
$pendingCount    = count($pendingReceived);

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<!-- Welcome bar -->
<div class="d-flex justify-between align-center mb-2">
    <div>
        <h1 class="page-title" style="margin-bottom:0.25rem;">
            Welcome, <?= htmlspecialchars($user['name']) ?> 👋
        </h1>
        <p class="text-muted">
            <?= htmlspecialchars($user['department']) ?> &middot; Year <?= $user['year'] ?>
        </p>
    </div>
    <a href="<?= BASE_URL ?>/skills.php" class="btn btn-primary">+ Add Skills</a>
</div>

<!-- Stat cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $totalSkills ?></div>
        <div class="stat-label">Total Skills</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= count($teachSkills) ?></div>
        <div class="stat-label">Skills I Teach</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= count($learnSkills) ?></div>
        <div class="stat-label">Skills I Want</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= count($allMatches) ?></div>
        <div class="stat-label">Skill Matches</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">
            <?= $pendingCount ?>
            <?php if ($pendingCount > 0): ?>
                <span style="font-size:1rem;">🔔</span>
            <?php endif; ?>
        </div>
        <div class="stat-label">Pending Requests</div>
    </div>
</div>

<!-- Two-column layout -->
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">

    <!-- Left: Top Matches -->
    <div>
        <div class="d-flex justify-between align-center mb-1">
            <h2 style="font-size:1.1rem; font-weight:600;">🎯 Your Best Matches</h2>
            <a href="<?= BASE_URL ?>/matches.php" class="text-muted" style="font-size:0.85rem;">View all →</a>
        </div>

        <?php if (empty($topMatches)): ?>
            <div class="card text-center" style="padding:2rem;">
                <p style="font-size:2rem;">🤝</p>
                <p class="text-muted mt-1">No matches yet.</p>
                <p class="text-muted" style="font-size:0.82rem;">Add skills you can teach and want to learn to find matches.</p>
                <a href="<?= BASE_URL ?>/skills.php" class="btn btn-primary btn-sm mt-2">Add Skills</a>
            </div>
        <?php else: ?>
            <?php foreach ($topMatches as $match):
                $score      = $match['match']['score'];
                $badgeClass = $score === 100 ? 'score-100' : 'score-50';

                // Build skill name lists for preview
                $theirTeachIds = array_column(getUserSkillsById($match['id'], 'teach'), 'skill_id');
                $theirLearnIds = array_column(getUserSkillsById($match['id'], 'learn'), 'skill_id');
                $previewTeach  = array_slice(array_map(fn($id) => findSkillById($id)['name'], $theirTeachIds), 0, 3);
                $previewLearn  = array_slice(array_map(fn($id) => findSkillById($id)['name'], $theirLearnIds), 0, 3);
            ?>
            <div class="card" style="margin-bottom:0.85rem;">
                <div class="d-flex justify-between align-center">
                    <strong><?= htmlspecialchars($match['name']) ?></strong>
                    <span class="match-score-badge <?= $badgeClass ?>"><?= $score ?>% Match</span>
                </div>
                <p class="text-muted" style="font-size:0.8rem; margin-top:0.2rem;">
                    <?= htmlspecialchars($match['department']) ?> &middot; Year <?= $match['year'] ?>
                </p>

                <?php if (!empty($previewTeach)): ?>
                <div class="skill-pills mt-1">
                    <?php foreach ($previewTeach as $sName): ?>
                        <span class="skill-pill pill-teach"><?= htmlspecialchars($sName) ?></span>
                    <?php endforeach; ?>
                    <span class="text-muted" style="font-size:0.75rem; align-self:center;">teaches</span>
                </div>
                <?php endif; ?>

                <div class="mt-1">
                    <a href="<?= BASE_URL ?>/matches.php" class="btn btn-outline btn-sm">View Match →</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Right: Incoming Requests -->
    <div>
        <div class="d-flex justify-between align-center mb-1">
            <h2 style="font-size:1.1rem; font-weight:600;">📬 Incoming Requests</h2>
            <a href="<?= BASE_URL ?>/requests.php" class="text-muted" style="font-size:0.85rem;">View all →</a>
        </div>

        <?php if (empty($pendingReceived)): ?>
            <div class="card text-center" style="padding:2rem;">
                <p style="font-size:2rem;">📭</p>
                <p class="text-muted mt-1">No pending requests.</p>
            </div>
        <?php else: ?>
            <?php foreach (array_slice($pendingReceived, 0, 3) as $req):
                $sender       = findUserById($req['sender_id']);
                $offeredSkill = findSkillById($req['offered_skill_id']);
                $wantedSkill  = findSkillById($req['requested_skill_id']);
            ?>
            <div class="card" style="margin-bottom:0.85rem;">
                <div class="d-flex justify-between align-center">
                    <strong><?= htmlspecialchars($sender['name']) ?></strong>
                    <span class="badge badge-pending">Pending</span>
                </div>
                <p class="text-muted" style="font-size:0.82rem; margin-top:0.3rem;">
                    Offers: <span class="skill-pill pill-teach"><?= htmlspecialchars($offeredSkill['name']) ?></span>
                    &nbsp; Wants: <span class="skill-pill pill-learn"><?= htmlspecialchars($wantedSkill['name']) ?></span>
                </p>
                <a href="<?= BASE_URL ?>/requests.php" class="btn btn-success btn-sm mt-1">Respond →</a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- My Skills quick view -->
        <h2 style="font-size:1.1rem; font-weight:600; margin-top:1.5rem; margin-bottom:0.75rem;">🧠 My Skills</h2>
        <div class="card">
            <?php if (empty($teachSkills) && empty($learnSkills)): ?>
                <p class="text-muted text-center">No skills added yet. <a href="<?= BASE_URL ?>/skills.php">Add now →</a></p>
            <?php else: ?>
                <?php if (!empty($teachSkills)): ?>
                    <p style="font-size:0.82rem; font-weight:600; color:var(--gray-600); margin-bottom:0.4rem;">CAN TEACH</p>
                    <div class="skill-pills mb-1">
                        <?php foreach ($teachSkills as $us):
                            $sk = findSkillById($us['skill_id']);
                        ?>
                            <span class="skill-pill pill-teach"><?= htmlspecialchars($sk['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($learnSkills)): ?>
                    <p style="font-size:0.82rem; font-weight:600; color:var(--gray-600); margin-bottom:0.4rem; margin-top:0.75rem;">WANT TO LEARN</p>
                    <div class="skill-pills">
                        <?php foreach ($learnSkills as $us):
                            $sk = findSkillById($us['skill_id']);
                        ?>
                            <span class="skill-pill pill-learn"><?= htmlspecialchars($sk['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/skills.php" class="btn btn-outline btn-sm mt-2">Manage Skills →</a>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Responsive fix for small screens -->
<style>
@media (max-width: 700px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
