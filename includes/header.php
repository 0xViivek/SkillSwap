<?php
/**
 * SkillSwap — Shared HTML header
 * File: includes/header.php
 *
 * BASE_URL is defined in functions.php (via auth.php).
 * Do NOT redefine it here.
 */
if (!isset($pageTitle)) $pageTitle = 'SkillSwap';
$navAdmin = isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'admin';
$navItems = $navAdmin
    ? [['/admin/dashboard.php', 'Dashboard'], ['/admin/users.php', 'Students'], ['/admin/requests.php', 'Requests']]
    : [['/dashboard.php', 'Dashboard'], ['/skills.php', 'Skills'], ['/matches.php', 'Matches'], ['/requests.php', 'Requests'], ['/profile.php', 'Profile']];
$navCurrentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — SkillSwap</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
</head>
<body>
<?php if (getenv('SKILLSWAP_PUBLIC_DEMO') === '1'): ?>
<div style="text-align:center;padding:0.35rem 1rem;background:#eef2ff;color:#3730a3;font-size:0.8rem;">Public demo. Use sample details. Records may reset when the service restarts.</div>
<?php endif; ?>

<nav class="navbar" aria-label="Main navigation">
    <div class="navbar-inner">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php" aria-label="SkillSwap home">
            <span class="navbar-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8h14m-4-4 4 4-4 4M19 16H5m4-4-4 4 4 4"/></svg></span>
            <span>SkillSwap</span>
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <ul class="nav-links">
                <?php foreach ($navItems as [$path, $label]): $isActive = $navCurrentPath === BASE_URL . $path; ?>
                    <li><a href="<?= BASE_URL . $path ?>" <?= $isActive ? 'class="active" aria-current="page"' : '' ?>><?= $label ?></a></li>
                <?php endforeach; ?>
            </ul>
            <div class="navbar-account">
                <span class="nav-user" title="<?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES) ?>">
                    <span class="navbar-avatar" aria-hidden="true"><?= htmlspecialchars(mb_strtoupper(mb_substr($_SESSION['user_name'], 0, 1))) ?></span>
                    <span class="navbar-user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                </span>
                <form method="POST" action="<?= BASE_URL ?>/logout.php" class="navbar-logout">
                    <?= csrfField() ?>
                    <button type="submit" class="logout-button" aria-label="Logout" title="Logout"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 5H5v14h5m4-12 5 5-5 5m-5-5h10"/></svg></button>
                </form>
            </div>
        <?php else: ?>
            <div class="navbar-guest">
                <a href="<?= BASE_URL ?>/login.php" class="navbar-login">Login</a>
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-sm">Get started</a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<main class="container">
