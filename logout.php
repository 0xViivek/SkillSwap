<?php
/**
 * SkillSwap — Logout
 * File: logout.php
 * Owner: Member 2 (Auth)
 */

require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

if (isLoggedIn()) {
    logoutUser();
}

// Start fresh session just for the flash message
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['flash'] = ['type' => 'info', 'msg' => 'You have been logged out successfully.'];

header('Location: ' . BASE_URL . '/login.php');
exit;
