<?php
/**
 * SkillSwap — Shared HTML header
 * File: includes/header.php
 *
 * Include at the top of every page AFTER session_start().
 * Pass $pageTitle before including:
 *   $pageTitle = 'Dashboard';
 *   include 'includes/header.php';
 */
if (!isset($pageTitle)) $pageTitle = 'SkillSwap';
$currentUser = function_exists('currentUser') ? currentUser() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — SkillSwap</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<nav class="navbar">
    <a class="navbar-brand" href="/index.php">🔄 SkillSwap</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <ul class="nav-links">
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li><a href="/admin/dashboard.php" <?= str_contains($_SERVER['REQUEST_URI'], 'admin/dashboard') ? 'class="active"' : '' ?>>Dashboard</a></li>
                <li><a href="/admin/users.php"     <?= str_contains($_SERVER['REQUEST_URI'], 'admin/users')    ? 'class="active"' : '' ?>>Students</a></li>
                <li><a href="/admin/requests.php"  <?= str_contains($_SERVER['REQUEST_URI'], 'admin/requests') ? 'class="active"' : '' ?>>Requests</a></li>
            <?php else: ?>
                <li><a href="/dashboard.php" <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'class="active"' : '' ?>>Dashboard</a></li>
                <li><a href="/skills.php"    <?= str_contains($_SERVER['REQUEST_URI'], 'skills')    ? 'class="active"' : '' ?>>Skills</a></li>
                <li><a href="/matches.php"   <?= str_contains($_SERVER['REQUEST_URI'], 'matches')   ? 'class="active"' : '' ?>>Matches</a></li>
                <li><a href="/requests.php"  <?= str_contains($_SERVER['REQUEST_URI'], 'requests')  ? 'class="active"' : '' ?>>Requests</a></li>
                <li><a href="/profile.php"   <?= str_contains($_SERVER['REQUEST_URI'], 'profile')   ? 'class="active"' : '' ?>>Profile</a></li>
            <?php endif; ?>
            <li><a href="/logout.php">Logout</a></li>
        </ul>
        <span class="nav-user">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
    <?php else: ?>
        <ul class="nav-links">
            <li><a href="/login.php">Login</a></li>
            <li><a href="/register.php" class="btn btn-primary btn-sm">Register</a></li>
        </ul>
    <?php endif; ?>
</nav>

<main class="container">
