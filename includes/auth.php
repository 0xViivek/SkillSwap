<?php
/**
 * SkillSwap — Session / Authentication helpers
 * File: includes/auth.php
 * Owner: Member 2 (Auth)
 */

require_once __DIR__ . '/functions.php';   // also defines BASE_URL

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Session helpers ──────────────────────────────────────────────────────────

function loginUser(string $email, string $plainPassword) {
    $user = findUserByEmail($email);
    if ($user === null) return false;
    if (!password_verify($plainPassword, $user['password'])) return false;

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];

    return $user;
}

function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

function currentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function currentUser(): ?array {
    $id = currentUserId();
    if ($id === null) return null;
    $user = findUserById($id);
    // If session has a stale user_id (user was deleted), clear it
    if ($user === null) {
        logoutUser();
    }
    return $user;
}

// ─── Redirect guards ──────────────────────────────────────────────────────────

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
    // Also verify user still exists (handles deleted accounts)
    if (findUserById((int)$_SESSION['user_id']) === null) {
        logoutUser();
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
}

function requireGuest(): void {
    if (isLoggedIn()) {
        $dest = isAdmin()
            ? BASE_URL . '/admin/dashboard.php'
            : BASE_URL . '/dashboard.php';
        header('Location: ' . $dest);
        exit;
    }
}
