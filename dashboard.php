<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$user = currentUser();
$userId = $user['id'];
$teachSkills = getUserSkillsById($userId, 'teach');
$learnSkills = getUserSkillsById($userId, 'learn');
$totalSkills = count($teachSkills) + count($learnSkills);
$allMatches = getMatchesForUser($userId);
$topMatches = array_slice($allMatches, 0, 3);
$pendingReceived = array_values(array_filter(
    getRequestsForUser($userId, 'receiver'),
    fn($request) => $request['status'] === 'pending'
));
$pendingCount = count($pendingReceived);
$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="student-dashboard">
    <?php if (isset($_SESSION['flash'])): ?>
        <div data-flash class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?>">
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <header class="dashboard-welcome">
        <div>
            <h1>Welcome, <?= htmlspecialchars($user['name']) ?></h1>
            <p><?= htmlspecialchars($user['department']) ?> <span aria-hidden="true">&middot;</span> Year <?= $user['year'] ?></p>
        </div>
        <a href="<?= BASE_URL ?>/skills.php" class="btn btn-primary">+ Add skills</a>
    </header>

    <div class="dashboard-stats" aria-label="Your activity overview">
        <?php foreach ([
            [$totalSkills, 'Total skills'],
            [count($teachSkills), 'Skills I teach'],
            [count($learnSkills), 'Skills I want'],
            [count($allMatches), 'Skill matches'],
            [$pendingCount, 'Pending requests'],
        ] as [$value, $label]): ?>
            <div class="dashboard-stat">
                <span class="dashboard-stat-value"><?= $value ?></span>
                <span class="dashboard-stat-label"><?= $label ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="dashboard-grid">
        <section class="dashboard-panel" aria-labelledby="dashboard-matches-title">
            <div class="dashboard-panel-header">
                <div>
                    <h2 id="dashboard-matches-title">Your best matches</h2>
                    <p>Students with complementary skills</p>
                </div>
                <a class="dashboard-link" href="<?= BASE_URL ?>/matches.php">View all <span aria-hidden="true">&rarr;</span><span class="sr-only"> matches</span></a>
            </div>
            <?php if (empty($topMatches)): ?>
                <div class="dashboard-empty">
                    <span class="dashboard-empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="4"/><path d="M12 2v4m0 12v4M2 12h4m12 0h4"/></svg>
                    </span>
                    <h3>Your next learning partner is out there</h3>
                    <p>Add what you can teach and want to learn to discover your matches.</p>
                    <a href="<?= BASE_URL ?>/skills.php" class="btn btn-outline btn-sm">Add your skills</a>
                </div>
            <?php else: ?>
                <div class="dashboard-list">
                    <?php foreach ($topMatches as $match):
                        $score = $match['match']['score'];
                        $theirSkills = getUserSkillsById($match['id'], 'teach');
                        $previewTeach = array_slice(array_filter(array_map(
                            fn($skill) => findSkillById($skill['skill_id'])['name'] ?? null, $theirSkills
                        )), 0, 3);
                    ?>
                        <article class="dashboard-match">
                            <div class="dashboard-person-row">
                                <div class="dashboard-person">
                                    <span class="dashboard-avatar" aria-hidden="true"><?= htmlspecialchars(mb_strtoupper(mb_substr($match['name'], 0, 1))) ?></span>
                                    <div class="dashboard-person-details">
                                        <h3><?= htmlspecialchars($match['name']) ?></h3>
                                        <p><?= htmlspecialchars($match['department']) ?> &middot; Year <?= $match['year'] ?></p>
                                    </div>
                                </div>
                                <span class="match-score-badge <?= $score === 100 ? 'score-100' : 'score-50' ?>"><?= $score ?>% match</span>
                            </div>
                            <div class="dashboard-match-skills">
                                <p class="dashboard-skill-label">Can teach</p>
                                <div class="skill-pills">
                                    <?php foreach ($previewTeach as $name): ?>
                                        <span class="skill-pill pill-teach"><?= htmlspecialchars($name) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <a href="<?= BASE_URL ?>/matches.php" class="btn btn-outline btn-sm dashboard-match-action">View match <span aria-hidden="true">&rarr;</span></a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-panel" aria-labelledby="dashboard-requests-title">
            <div class="dashboard-panel-header">
                <div>
                    <h2 id="dashboard-requests-title">Incoming requests</h2>
                    <p>Your next exchange starts here</p>
                </div>
                <a class="dashboard-link" href="<?= BASE_URL ?>/requests.php">View all <span aria-hidden="true">&rarr;</span><span class="sr-only"> requests</span></a>
            </div>
            <?php if (empty($pendingReceived)): ?>
                <div class="dashboard-empty">
                    <span class="dashboard-empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m4 5-2 8v6a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1v-6l-2-8H4Z"/><path d="M2 13h6l2 3h4l2-3h6"/></svg>
                    </span>
                    <h3>You're all caught up</h3>
                    <p>New exchange requests will appear here.<br>Explore your matches to start an exchange.</p>
                    <a class="dashboard-link" href="<?= BASE_URL ?>/matches.php">Explore matches <span aria-hidden="true">&rarr;</span></a>
                </div>
            <?php else: ?>
                <div class="dashboard-list">
                    <?php foreach (array_slice($pendingReceived, 0, 3) as $request):
                        $sender = findUserById($request['sender_id']);
                        $offered = findSkillById($request['offered_skill_id']);
                        $wanted = findSkillById($request['requested_skill_id']);
                    ?>
                        <article class="dashboard-request">
                            <div class="dashboard-person-row">
                                <div class="dashboard-person">
                                    <span class="dashboard-avatar" aria-hidden="true"><?= htmlspecialchars(mb_strtoupper(mb_substr($sender['name'] ?? '?', 0, 1))) ?></span>
                                    <div class="dashboard-person-details">
                                        <h3><?= htmlspecialchars($sender['name'] ?? 'Student') ?></h3>
                                        <p>Wants to exchange skills with you</p>
                                    </div>
                                </div>
                                <span class="badge badge-pending">Pending</span>
                            </div>
                            <div class="dashboard-request-skills">
                                <div><span class="dashboard-skill-label">Offers</span><span class="skill-pill pill-teach"><?= htmlspecialchars($offered['name'] ?? 'Skill unavailable') ?></span></div>
                                <div><span class="dashboard-skill-label">Wants</span><span class="skill-pill pill-learn"><?= htmlspecialchars($wanted['name'] ?? 'Skill unavailable') ?></span></div>
                            </div>
                            <a href="<?= BASE_URL ?>/requests.php" class="btn btn-outline btn-sm dashboard-match-action">View request <span aria-hidden="true">&rarr;</span></a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-panel dashboard-skills" aria-labelledby="dashboard-skills-title">
            <div class="dashboard-panel-header">
                <div>
                    <h2 id="dashboard-skills-title">My skills</h2>
                    <p>What you bring and what you'd like to learn</p>
                </div>
                <a class="dashboard-link" href="<?= BASE_URL ?>/skills.php">Manage skills <span aria-hidden="true">&rarr;</span></a>
            </div>
            <div class="dashboard-skill-groups">
                <?php foreach ([['Can teach', $teachSkills, 'pill-teach'], ['Want to learn', $learnSkills, 'pill-learn']] as [$label, $skills, $pillClass]): ?>
                    <div class="dashboard-skill-group">
                        <h3><?= $label ?> <span><?= count($skills) ?> skill<?= count($skills) === 1 ? '' : 's' ?></span></h3>
                        <?php if (empty($skills)): ?>
                            <p class="dashboard-skill-placeholder">No skills added yet. <a href="<?= BASE_URL ?>/skills.php">Add a skill</a></p>
                        <?php else: ?>
                            <div class="skill-pills">
                                <?php foreach ($skills as $record): $skill = findSkillById($record['skill_id']); ?>
                                    <span class="skill-pill <?= $pillClass ?>"><?= htmlspecialchars($skill['name'] ?? 'Skill unavailable') ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
