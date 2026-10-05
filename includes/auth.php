<?php
/**
 * SkillSwap — Session / Authentication helpers
 * File: includes/auth.php
 *
 * Member 2 (Auth) owns this file.
 * All session logic lives here — pages just call these functions.
 */

require_once __DIR__ . '/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Session helpers ──────────────────────────────────────────────────────────

/**
 * Log a user in: verify credentials and write session.
 *
 * @param  string $email
 * @param  string $plainPassword
 * @return array|false  User array on success, false on failure.
 */
function loginUser(string $email, string $plainPassword) {
    $user = findUserByEmail($email);
    if ($user === null) return false;
    if (!password_verify($plainPassword, $user['password'])) return false;

    // Persist in session (never store password hash in session)
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];

    return $user;
}

/**
 * Destroy the current session (logout).
 */
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

/**
 * Check whether any user is currently logged in.
 *
 * @return bool
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Check whether the logged-in user is an admin.
 *
 * @return bool
 */
function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

/**
 * Return the logged-in user's ID, or null.
 *
 * @return int|null
 */
function currentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Return the logged-in user's full record, or null.
 *
 * @return array|null
 */
function currentUser(): ?array {
    $id = currentUserId();
    return $id !== null ? findUserById($id) : null;
}

// ─── Redirect guards (call at top of protected pages) ────────────────────────

/**
 * Redirect to login if not logged in.
 * Usage: requireLogin();
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Redirect to student dashboard if not an admin.
 * Usage: requireAdmin();
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: /dashboard.php');
        exit;
    }
}

/**
 * Redirect already-logged-in users away from login/register pages.
 * Usage: requireGuest();
 */
function requireGuest(): void {
    if (isLoggedIn()) {
        $dest = isAdmin() ? '/admin/dashboard.php' : '/dashboard.php';
        header('Location: ' . $dest);
        exit;
    }
}
