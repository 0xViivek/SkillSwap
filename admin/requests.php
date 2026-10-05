<?php
/**
 * SkillSwap — Admin: View All Requests
 * File: admin/requests.php
 * Owner: Member 5 (UI + Admin)
 */

require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$requests = array_reverse(getRequests());   // newest first

// Filter by status if provided
$filterStatus = $_GET['status'] ?? 'all';
if ($filterStatus !== 'all') {
    $requests = array_values(array_filter($requests, fn($r) => $r['status'] === $filterStatus));
}

$allRequests = getRequests();
$counts = [
    'all'      => count($allRequests),
    'pending'  => count(array_filter($allRequests, fn($r) => $r['status'] === 'pending')),
    'accepted' => count(array_filter($allRequests, fn($r) => $r['status'] === 'accepted')),
    'rejected' => count(array_filter($allRequests, fn($r) => $r['status'] === 'rejected')),
];

$pageTitle = 'All Requests';
include __DIR__ . '/../includes/header.php';
?>

<h1 class="page-title">All Exchange Requests</h1>

<!-- Filter tabs -->
<div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
    <?php foreach (['all' => 'All', 'pending' => 'Pending', 'accepted' => 'Accepted', 'rejected' => 'Rejected'] as $key => $label): ?>
        <a href="?status=<?= $key ?>"
           class="btn btn-sm <?= $filterStatus === $key ? 'btn-primary' : 'btn-outline' ?>">
            <?= $label ?> (<?= $counts[$key] ?>)
        </a>
    <?php endforeach; ?>
</div>

<div class="card" style="padding:0;">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Offers (Teaches)</th>
                    <th>Wants (Learns)</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted" style="padding:2rem;">
                            No requests found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $r):
                        $sender       = findUserById($r['sender_id']);
                        $receiver     = findUserById($r['receiver_id']);
                        $offeredSkill = findSkillById($r['offered_skill_id']);
                        $wantedSkill  = findSkillById($r['requested_skill_id']);
                    ?>
                    <tr>
                        <td class="text-muted"><?= $r['id'] ?></td>
                        <td><strong><?= htmlspecialchars($sender['name']) ?></strong></td>
                        <td><strong><?= htmlspecialchars($receiver['name']) ?></strong></td>
                        <td><span class="skill-pill pill-teach"><?= htmlspecialchars($offeredSkill['name']) ?></span></td>
                        <td><span class="skill-pill pill-learn"><?= htmlspecialchars($wantedSkill['name']) ?></span></td>
                        <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                        <td class="text-muted"><?= htmlspecialchars($r['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
