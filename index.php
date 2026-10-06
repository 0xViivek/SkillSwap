<?php
/**
 * SkillSwap — Landing Page
 * File: index.php
 * Owner: Member 5 (UI + Admin)
 */

require_once __DIR__ . '/includes/auth.php';

// Already logged in? Redirect to dashboard
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . (isAdmin() ? '/admin/dashboard.php' : '/dashboard.php'));
    exit;
}

$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';
?>

<!-- Hero section -->
<div style="text-align:center; padding:4rem 1rem 3rem;">
    <div style="font-size:3.5rem; margin-bottom:1rem;">🔄</div>
    <h1 style="font-size:2.4rem; font-weight:800; color:var(--gray-900); margin-bottom:0.75rem; line-height:1.2;">
        Exchange Skills.<br>Grow Together.
    </h1>
    <p style="font-size:1.1rem; color:var(--gray-600); max-width:520px; margin:0 auto 2rem;">
        SkillSwap connects students based on what they can teach and what they want to learn — automatically finding the best mutual matches.
    </p>
    <div class="d-flex gap-2" style="justify-content:center; flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary" style="font-size:1rem; padding:0.65rem 2rem;">
            Get Started Free
        </a>
        <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline" style="font-size:1rem; padding:0.65rem 2rem;">
            Login
        </a>
    </div>
</div>

<!-- How it works -->
<div style="background:#fff; border-top:1px solid var(--gray-200); border-bottom:1px solid var(--gray-200); padding:3rem 1rem;">
    <h2 style="text-align:center; font-size:1.6rem; font-weight:700; margin-bottom:2.5rem;">How It Works</h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:2rem; max-width:900px; margin:0 auto;">

        <div style="text-align:center;">
            <div style="font-size:2.5rem; margin-bottom:0.75rem;">📝</div>
            <h3 style="font-weight:600; margin-bottom:0.4rem;">1. Create Profile</h3>
            <p class="text-muted" style="font-size:0.9rem;">Register and set up your student profile with your department and year.</p>
        </div>

        <div style="text-align:center;">
            <div style="font-size:2.5rem; margin-bottom:0.75rem;">🎯</div>
            <h3 style="font-weight:600; margin-bottom:0.4rem;">2. List Your Skills</h3>
            <p class="text-muted" style="font-size:0.9rem;">Add skills you can teach others and skills you're looking to learn.</p>
        </div>

        <div style="text-align:center;">
            <div style="font-size:2.5rem; margin-bottom:0.75rem;">🤖</div>
            <h3 style="font-weight:600; margin-bottom:0.4rem;">3. Get Matched</h3>
            <p class="text-muted" style="font-size:0.9rem;">Our two-way matching algorithm finds students with complementary skills.</p>
        </div>

        <div style="text-align:center;">
            <div style="font-size:2.5rem; margin-bottom:0.75rem;">🤝</div>
            <h3 style="font-weight:600; margin-bottom:0.4rem;">4. Start Exchanging</h3>
            <p class="text-muted" style="font-size:0.9rem;">Send a request, get accepted, and start your skill exchange journey.</p>
        </div>

    </div>
</div>

<!-- Match example -->
<div style="padding:3rem 1rem; max-width:760px; margin:0 auto;">
    <h2 style="text-align:center; font-size:1.5rem; font-weight:700; margin-bottom:2rem;">See It In Action</h2>

    <div style="display:grid; grid-template-columns:1fr auto 1fr; gap:1rem; align-items:center;">

        <div class="card" style="text-align:center;">
            <div style="width:52px; height:52px; border-radius:50%; background:var(--primary);
                        color:#fff; font-size:1.3rem; font-weight:700;
                        display:flex; align-items:center; justify-content:center; margin:0 auto 0.75rem;">V</div>
            <strong>Vivek</strong>
            <p style="font-size:0.78rem; color:var(--gray-600); margin:0.4rem 0 0.6rem;">CSE · Year 2</p>
            <p style="font-size:0.75rem; font-weight:600; color:var(--gray-600); margin-bottom:0.3rem;">CAN TEACH</p>
            <div class="skill-pills" style="justify-content:center; margin-bottom:0.5rem;">
                <span class="skill-pill pill-teach">C++</span>
                <span class="skill-pill pill-teach">HTML</span>
            </div>
            <p style="font-size:0.75rem; font-weight:600; color:var(--gray-600); margin-bottom:0.3rem;">WANTS TO LEARN</p>
            <div class="skill-pills" style="justify-content:center;">
                <span class="skill-pill pill-learn">Python</span>
                <span class="skill-pill pill-learn">Git</span>
            </div>
        </div>

        <div style="text-align:center;">
            <div style="font-size:2rem; color:var(--primary);">⇄</div>
            <div class="match-score-badge score-100" style="display:block; margin-top:0.5rem;">100% Match</div>
        </div>

        <div class="card" style="text-align:center;">
            <div style="width:52px; height:52px; border-radius:50%; background:#16a34a;
                        color:#fff; font-size:1.3rem; font-weight:700;
                        display:flex; align-items:center; justify-content:center; margin:0 auto 0.75rem;">R</div>
            <strong>Rahul</strong>
            <p style="font-size:0.78rem; color:var(--gray-600); margin:0.4rem 0 0.6rem;">ECE · Year 2</p>
            <p style="font-size:0.75rem; font-weight:600; color:var(--gray-600); margin-bottom:0.3rem;">CAN TEACH</p>
            <div class="skill-pills" style="justify-content:center; margin-bottom:0.5rem;">
                <span class="skill-pill pill-teach">Python</span>
                <span class="skill-pill pill-teach">Java</span>
            </div>
            <p style="font-size:0.75rem; font-weight:600; color:var(--gray-600); margin-bottom:0.3rem;">WANTS TO LEARN</p>
            <div class="skill-pills" style="justify-content:center;">
                <span class="skill-pill pill-learn">C++</span>
            </div>
        </div>

    </div>

    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--radius); padding:1rem; margin-top:1.5rem; text-align:center;">
        <p style="color:#166534; font-weight:600; margin-bottom:0.3rem;">✓ Mutual Skill Match Found!</p>
        <p style="color:#166534; font-size:0.85rem;">Vivek teaches C++ → Rahul learns C++ &nbsp;|&nbsp; Rahul teaches Python → Vivek learns Python</p>
    </div>
</div>

<!-- CTA -->
<div style="text-align:center; padding:2rem 1rem 4rem;">
    <h2 style="font-size:1.5rem; font-weight:700; margin-bottom:0.5rem;">Ready to swap skills?</h2>
    <p class="text-muted mb-2">Join your college's skill exchange network today.</p>
    <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary" style="font-size:1rem; padding:0.65rem 2.5rem;">
        Create Your Account →
    </a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
