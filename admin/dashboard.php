<?php
/**
 * SkillSwap — Admin Dashboard
 * File: admin/dashboard.php
 * Owner: Member 5 (UI + Admin)
 */

require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$stats    = getStats();
$users    = getUsers();
$students = array_filter($users, fn($u) => $u['role'] === 'student');
$recent   = array_slice(array_reverse(array_values($students)), 0, 5);
$requests = getRequests();
$recentReq = array_slice(array_reverse($requests), 0, 5);

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">Admin Dashboard</h1>

<!-- Stat cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $stats['total_students'] ?></div>
        <div class="stat-label">Total Students</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['total_skills'] ?></div>
        <div class="stat-label">Master Skills</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['total_requests'] ?></div>
        <div class="stat-label">Total Requests</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['total_accepted'] ?></div>
        <div class="stat-label">Active Exchanges</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">
            <?= $stats['total_requests'] > 0
                ? round(($stats['total_accepted'] / $stats['total_requests']) * 100) . '%'
                : '—' ?>
        </div>
        <div class="stat-label">Accept Rate</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">

    <!-- Recent Students -->
    <div class="card">
        <div class="d-flex justify-between align-center mb-2">
            <h2 class="card-title" style="margin-bottom:0;">Recent Students</h2>
            <a href="/admin/users.php" class="text-muted" style="font-size:0.85rem;">View all →</a>
        </div>
        <?php if (empty($recent)): ?>
            <p class="text-muted">No students yet.</p>
        <?php else: ?>
            <?php foreach ($recent as $s):
                $sTeach = count(getUserSkillsById($s['id'], 'teach'));
                $sLearn = count(getUserSkillsById($s['id'], 'learn'));
            ?>
            <div class="d-flex justify-between align-center"
                 style="padding:0.55rem 0; border-bottom:1px solid var(--gray-100);">
                <div>
                    <strong style="font-size:0.9rem;"><?= htmlspecialchars($s['name']) ?></strong>
                    <span class="text-muted" style="font-size:0.78rem; margin-left:0.4rem;">
                        <?= htmlspecialchars($s['department']) ?> · Yr <?= $s['year'] ?>
                    </span>
                </div>
                <span class="text-muted" style="font-size:0.78rem;">
                    T:<?= $sTeach ?> / L:<?= $sLearn ?>
                </span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Recent Requests -->
    <div class="card">
        <div class="d-flex justify-between align-center mb-2">
            <h2 class="card-title" style="margin-bottom:0;">Recent Requests</h2>
            <a href="/admin/requests.php" class="text-muted" style="font-size:0.85rem;">View all →</a>
        </div>
        <?php if (empty($recentReq)): ?>
            <p class="text-muted">No requests yet.</p>
        <?php else: ?>
            <?php foreach ($recentReq as $r):
                $sender   = findUserById($r['sender_id']);
                $receiver = findUserById($r['receiver_id']);
            ?>
            <div class="d-flex justify-between align-center"
                 style="padding:0.55rem 0; border-bottom:1px solid var(--gray-100);">
                <div style="font-size:0.85rem;">
                    <strong><?= htmlspecialchars($sender['name']) ?></strong>
                    <span class="text-muted"> → </span>
                    <strong><?= htmlspecialchars($receiver['name']) ?></strong>
                </div>
                <span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- All Skills list -->
<div class="card mt-2">
    <h2 class="card-title">Master Skill List (<?= $stats['total_skills'] ?>)</h2>
    <div class="skill-pills">
        <?php foreach (getSkills() as $sk): ?>
            <span class="skill-pill" style="background:var(--gray-100); color:var(--gray-700);">
                <?= htmlspecialchars($sk['name']) ?>
                <span style="font-size:0.7rem; color:var(--gray-600);">(<?= htmlspecialchars($sk['category']) ?>)</span>
            </span>
        <?php endforeach; ?>
    </div>
</div>

<style>
@media (max-width:700px) {
    div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns:1fr !important; }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
