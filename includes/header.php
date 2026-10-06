<?php
/**
 * SkillSwap — Shared HTML header
 * File: includes/header.php
 *
 * BASE_URL is defined in functions.php (via auth.php).
 * Do NOT redefine it here.
 */
if (!isset($pageTitle)) $pageTitle = 'SkillSwap';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — SkillSwap</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>

<nav class="navbar">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">🔄 SkillSwap</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <ul class="nav-links">
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li><a href="<?= BASE_URL ?>/admin/dashboard.php" <?= str_contains($_SERVER['REQUEST_URI'], 'admin/dashboard') ? 'class="active"' : '' ?>>Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/admin/users.php"     <?= str_contains($_SERVER['REQUEST_URI'], 'admin/users')    ? 'class="active"' : '' ?>>Students</a></li>
                <li><a href="<?= BASE_URL ?>/admin/requests.php"  <?= str_contains($_SERVER['REQUEST_URI'], 'admin/requests') ? 'class="active"' : '' ?>>Requests</a></li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>/dashboard.php" <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'class="active"' : '' ?>>Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/skills.php"    <?= str_contains($_SERVER['REQUEST_URI'], 'skills')    ? 'class="active"' : '' ?>>Skills</a></li>
                <li><a href="<?= BASE_URL ?>/matches.php"   <?= str_contains($_SERVER['REQUEST_URI'], 'matches')   ? 'class="active"' : '' ?>>Matches</a></li>
                <li><a href="<?= BASE_URL ?>/requests.php"  <?= str_contains($_SERVER['REQUEST_URI'], 'requests')  ? 'class="active"' : '' ?>>Requests</a></li>
                <li><a href="<?= BASE_URL ?>/profile.php"   <?= str_contains($_SERVER['REQUEST_URI'], 'profile')   ? 'class="active"' : '' ?>>Profile</a></li>
            <?php endif; ?>
            <li><form method="POST" action="<?= BASE_URL ?>/logout.php"><?= csrfField() ?><button type="submit" class="logout-button">Logout</button></form></li>
        </ul>
        <span class="nav-user">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
    <?php else: ?>
        <ul class="nav-links">
            <li><a href="<?= BASE_URL ?>/login.php">Login</a></li>
            <li><a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-sm">Register</a></li>
        </ul>
    <?php endif; ?>
</nav>

<main class="container">
