<?php
/**
 * SkillSwap — Logout
 * File: logout.php
 * Owner: Member 2 (Auth)
 */

require_once __DIR__ . '/includes/auth.php';

// Only logout if actually logged in
if (isLoggedIn()) {
    logoutUser();
}

// Flash message shown on login page after logout
session_start();
$_SESSION['flash'] = ['type' => 'info', 'msg' => 'You have been logged out successfully.'];

header('Location: /SkillSwap/login.php');
exit;
