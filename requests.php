<?php
/**
 * SkillSwap — Exchange Requests
 * File: requests.php
 * Owner: Member 4 (Exchange Requests)
 */

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$userId = currentUserId();

// ── Handle Accept / Reject ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['request_id'])) {
    $requestId = (int)$_POST['request_id'];
    $action    = $_POST['action'];

    // Security: make sure this user is the receiver of this request
    $allReqs   = getRequests();
    $targetReq = null;
    foreach ($allReqs as $r) {
        if ($r['id'] === $requestId) { $targetReq = $r; break; }
    }

    if ($targetReq && $targetReq['receiver_id'] === $userId) {
        if ($action === 'accept') {
            updateRequestStatus($requestId, 'accepted');
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Request accepted! Skill exchange is now active.'];
        } elseif ($action === 'reject') {
            updateRequestStatus($requestId, 'rejected');
            $_SESSION['flash'] = ['type' => 'info', 'msg' => 'Request rejected.'];
        }
    }

    header('Location: /SkillSwap/requests.php');
    exit;
}

// ── Load data ─────────────────────────────────────────────────────
$incoming = getRequestsForUser($userId, 'receiver');
$outgoing = getRequestsForUser($userId, 'sender');

// Sort: pending first
usort($incoming, fn($a,$b) => ($a['status'] === 'pending' ? -1 : 1));
usort($outgoing, fn($a,$b) => ($a['status'] === 'pending' ? -1 : 1));

$pageTitle = 'Exchange Requests';
include __DIR__ . '/includes/header.php';
?>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<h1 class="page-title">Exchange Requests</h1>

<!-- Tab nav -->
<div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; border-bottom:2px solid var(--gray-200);">
    <button class="tab-btn active" onclick="showTab('incoming', this)"
            style="padding:0.5rem 1.2rem; border:none; background:none; cursor:pointer;
                   font-weight:600; color:var(--primary); border-bottom:2px solid var(--primary); margin-bottom:-2px;">
        📬 Incoming
        <?php $pendingIncoming = count(array_filter($incoming, fn($r) => $r['status'] === 'pending'));
              if ($pendingIncoming > 0): ?>
            <span style="background:var(--danger); color:#fff; border-radius:9999px;
                         padding:0.1rem 0.45rem; font-size:0.72rem; margin-left:0.3rem;">
                <?= $pendingIncoming ?>
            </span>
        <?php endif; ?>
    </button>
    <button class="tab-btn" onclick="showTab('outgoing', this)"
            style="padding:0.5rem 1.2rem; border:none; background:none; cursor:pointer;
                   font-weight:500; color:var(--gray-600); border-bottom:2px solid transparent; margin-bottom:-2px;">
        📤 Sent (<?= count($outgoing) ?>)
    </button>
</div>

<!-- Incoming requests -->
<div id="tab-incoming">
    <?php if (empty($incoming)): ?>
        <div class="card text-center" style="padding:2.5rem;">
            <p style="font-size:2.5rem;">📭</p>
            <p class="text-muted mt-1">No incoming requests yet.</p>
            <p class="text-muted" style="font-size:0.82rem;">When someone sends you an exchange request, it appears here.</p>
        </div>
    <?php else: ?>
        <?php foreach ($incoming as $req):
            $sender       = findUserById($req['sender_id']);
            $offeredSkill = findSkillById($req['offered_skill_id']);
            $wantedSkill  = findSkillById($req['requested_skill_id']);
        ?>
        <div class="card" style="margin-bottom:1rem;">
            <div class="d-flex justify-between align-center">
                <div>
                    <strong style="font-size:1rem;"><?= htmlspecialchars($sender['name']) ?></strong>
                    <span class="text-muted" style="font-size:0.82rem; margin-left:0.5rem;">
                        <?= htmlspecialchars($sender['department']) ?> &middot; Year <?= $sender['year'] ?>
                    </span>
                </div>
                <span class="badge badge-<?= $req['status'] ?>"><?= ucfirst($req['status']) ?></span>
            </div>

            <div style="margin:0.75rem 0; padding:0.75rem; background:var(--gray-50); border-radius:var(--radius);">
                <div class="d-flex gap-2" style="flex-wrap:wrap;">
                    <div>
                        <p style="font-size:0.75rem; color:var(--gray-600); margin-bottom:0.2rem;">THEY WILL TEACH YOU</p>
                        <span class="skill-pill pill-teach"><?= htmlspecialchars($offeredSkill['name']) ?></span>
                    </div>
                    <div style="align-self:center; font-size:1.2rem; color:var(--gray-300);">⇄</div>
                    <div>
                        <p style="font-size:0.75rem; color:var(--gray-600); margin-bottom:0.2rem;">THEY WANT TO LEARN</p>
                        <span class="skill-pill pill-learn"><?= htmlspecialchars($wantedSkill['name']) ?></span>
                    </div>
                </div>
            </div>

            <p class="text-muted" style="font-size:0.78rem; margin-bottom:0.75rem;">
                Received: <?= htmlspecialchars($req['created_at']) ?>
            </p>

            <?php if ($req['status'] === 'pending'): ?>
                <div class="d-flex gap-1">
                    <form method="POST" action="" style="margin:0;">
                        <input type="hidden" name="action"     value="accept">
                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                        <button type="submit" class="btn btn-success">✓ Accept</button>
                    </form>
                    <form method="POST" action="" style="margin:0;">
                        <input type="hidden" name="action"     value="reject">
                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                        <button type="submit" class="btn btn-danger"
                                data-confirm="Reject this exchange request from <?= htmlspecialchars($sender['name']) ?>?">
                            ✕ Reject
                        </button>
                    </form>
                </div>
            <?php elseif ($req['status'] === 'accepted'): ?>
                <div class="alert alert-success" style="margin:0; padding:0.5rem 0.85rem;">
                    🎉 Skill exchange is active! Connect with <?= htmlspecialchars($sender['name']) ?> to start learning.
                </div>
            <?php else: ?>
                <p class="text-muted" style="font-size:0.85rem;">You rejected this request.</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Outgoing requests -->
<div id="tab-outgoing" style="display:none;">
    <?php if (empty($outgoing)): ?>
        <div class="card text-center" style="padding:2.5rem;">
            <p style="font-size:2.5rem;">📤</p>
            <p class="text-muted mt-1">You haven't sent any requests yet.</p>
            <a href="/SkillSwap/matches.php" class="btn btn-primary mt-2">Find Matches →</a>
        </div>
    <?php else: ?>
        <?php foreach ($outgoing as $req):
            $receiver     = findUserById($req['receiver_id']);
            $offeredSkill = findSkillById($req['offered_skill_id']);
            $wantedSkill  = findSkillById($req['requested_skill_id']);
        ?>
        <div class="card" style="margin-bottom:1rem;">
            <div class="d-flex justify-between align-center">
                <div>
                    <strong style="font-size:1rem;">To: <?= htmlspecialchars($receiver['name']) ?></strong>
                    <span class="text-muted" style="font-size:0.82rem; margin-left:0.5rem;">
                        <?= htmlspecialchars($receiver['department']) ?> &middot; Year <?= $receiver['year'] ?>
                    </span>
                </div>
                <span class="badge badge-<?= $req['status'] ?>"><?= ucfirst($req['status']) ?></span>
            </div>

            <div style="margin:0.75rem 0; padding:0.75rem; background:var(--gray-50); border-radius:var(--radius);">
                <div class="d-flex gap-2" style="flex-wrap:wrap;">
                    <div>
                        <p style="font-size:0.75rem; color:var(--gray-600); margin-bottom:0.2rem;">YOU WILL TEACH</p>
                        <span class="skill-pill pill-teach"><?= htmlspecialchars($offeredSkill['name']) ?></span>
                    </div>
                    <div style="align-self:center; font-size:1.2rem; color:var(--gray-300);">⇄</div>
                    <div>
                        <p style="font-size:0.75rem; color:var(--gray-600); margin-bottom:0.2rem;">YOU WANT TO LEARN</p>
                        <span class="skill-pill pill-learn"><?= htmlspecialchars($wantedSkill['name']) ?></span>
                    </div>
                </div>
            </div>

            <p class="text-muted" style="font-size:0.78rem;">Sent: <?= htmlspecialchars($req['created_at']) ?></p>

            <?php if ($req['status'] === 'accepted'): ?>
                <div class="alert alert-success" style="margin:0.5rem 0 0; padding:0.5rem 0.85rem;">
                    🎉 <?= htmlspecialchars($receiver['name']) ?> accepted! Skill exchange is active.
                </div>
            <?php elseif ($req['status'] === 'rejected'): ?>
                <p class="text-muted" style="font-size:0.85rem; margin-top:0.25rem;">
                    <?= htmlspecialchars($receiver['name']) ?> rejected this request.
                    <a href="/SkillSwap/matches.php">Send another →</a>
                </p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function showTab(tab, btn) {
    document.getElementById('tab-incoming').style.display = tab === 'incoming' ? '' : 'none';
    document.getElementById('tab-outgoing').style.display = tab === 'outgoing' ? '' : 'none';
    document.querySelectorAll('.tab-btn').forEach(function(b) {
        b.style.color       = 'var(--gray-600)';
        b.style.fontWeight  = '500';
        b.style.borderBottom= '2px solid transparent';
    });
    btn.style.color       = 'var(--primary)';
    btn.style.fontWeight  = '600';
    btn.style.borderBottom= '2px solid var(--primary)';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
