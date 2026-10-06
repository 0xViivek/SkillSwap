<?php
/**
 * SkillSwap — Data Layer
 * File: includes/functions.php
 *
 * ALL .txt read/write operations go through this file.
 * Pages MUST NOT directly fopen/fwrite .txt files themselves.
 *
 * Schema versions (do NOT change without team discussion):
 *   users.txt      → user_id|name|email|password_hash|department|year|role
 *   skills.txt     → skill_id|skill_name|category
 *   user_skills.txt→ user_skill_id|user_id|skill_id|type(teach|learn)
 *   requests.txt   → request_id|sender_id|receiver_id|offered_skill_id|requested_skill_id|status|created_at
 */

// ─── BASE URL (auto-detects subfolder, works in any XAMPP setup) ─────────────
// e.g. localhost/SkillSwap/dashboard.php  → BASE_URL = /SkillSwap
// e.g. localhost/SkillSwap/admin/x.php   → BASE_URL = /SkillSwap
// e.g. localhost/dashboard.php           → BASE_URL = (empty string)
if (!defined('BASE_URL')) {
    $__dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $__dir = preg_replace('#/admin/?$#', '', $__dir);   // strip /admin suffix
    define('BASE_URL', rtrim($__dir, '/'));
}

// ─── Path constants ─────────────────────────────────────────────────────────
define('DATA_DIR',         __DIR__ . '/../data/');
define('USERS_FILE',       DATA_DIR . 'users.txt');
define('SKILLS_FILE',      DATA_DIR . 'skills.txt');
define('USER_SKILLS_FILE', DATA_DIR . 'user_skills.txt');
define('REQUESTS_FILE',    DATA_DIR . 'requests.txt');

// ─── Generic helpers ─────────────────────────────────────────────────────────

/**
 * Read a .txt file and return an array of trimmed non-empty lines.
 *
 * @param  string $file  Absolute path to the file.
 * @return string[]
 */
function readData(string $file): array {
    if (!file_exists($file)) return [];
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return $lines ?: [];
}

/**
 * Append a single pipe-delimited line to a file.
 *
 * @param  string $file  Absolute path to the file.
 * @param  string $line  The already-formatted line (NO newline needed).
 * @return bool
 */
function appendData(string $file, string $line): bool {
    return file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Overwrite the entire file with the provided array of lines.
 * Used for updates (accept/reject, profile edits, deletions).
 *
 * @param  string   $file   Absolute path to the file.
 * @param  string[] $lines  Lines WITHOUT trailing newlines.
 * @return bool
 */
function writeAllData(string $file, array $lines): bool {
    $content = implode(PHP_EOL, $lines);
    if (!empty($lines)) $content .= PHP_EOL;   // trailing newline
    return file_put_contents($file, $content, LOCK_EX) !== false;
}

/**
 * Get the next available numeric ID for a file.
 * Reads the first field of every line, returns max + 1.
 *
 * @param  string $file
 * @return int
 */
function getNextId(string $file): int {
    $lines = readData($file);
    if (empty($lines)) return 1;
    $max = 0;
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (isset($parts[0]) && (int)$parts[0] > $max) {
            $max = (int)$parts[0];
        }
    }
    return $max + 1;
}

// ─── USER functions ──────────────────────────────────────────────────────────

/**
 * Return all users as an array of associative arrays.
 *
 * @return array[]  Keys: id, name, email, password, department, year, role
 */
function getUsers(): array {
    $rows = readData(USERS_FILE);
    $users = [];
    foreach ($rows as $row) {
        $p = explode('|', $row);
        if (count($p) < 7) continue;   // skip malformed lines
        $users[] = [
            'id'         => (int)$p[0],
            'name'       => $p[1],
            'email'      => $p[2],
            'password'   => $p[3],
            'department' => $p[4],
            'year'       => (int)$p[5],
            'role'       => $p[6],
        ];
    }
    return $users;
}

/**
 * Find a single user by email (case-insensitive).
 *
 * @param  string $email
 * @return array|null  Associative user array, or null if not found.
 */
function findUserByEmail(string $email): ?array {
    foreach (getUsers() as $user) {
        if (strtolower($user['email']) === strtolower($email)) return $user;
    }
    return null;
}

/**
 * Find a single user by numeric ID.
 *
 * @param  int $id
 * @return array|null
 */
function findUserById(int $id): ?array {
    foreach (getUsers() as $user) {
        if ($user['id'] === $id) return $user;
    }
    return null;
}

/**
 * Create a new student account.
 * Password is stored as a bcrypt hash.
 *
 * @param  string $name
 * @param  string $email
 * @param  string $plainPassword
 * @param  string $department
 * @param  int    $year
 * @return array|false  The new user array, or false on failure.
 */
function createUser(string $name, string $email, string $plainPassword, string $department, int $year) {
    if (findUserByEmail($email) !== null) return false;   // duplicate e-mail

    $id   = getNextId(USERS_FILE);
    $hash = password_hash($plainPassword, PASSWORD_BCRYPT);
    $line = implode('|', [$id, $name, $email, $hash, $department, $year, 'student']);

    if (!appendData(USERS_FILE, $line)) return false;

    return [
        'id'         => $id,
        'name'       => $name,
        'email'      => $email,
        'password'   => $hash,
        'department' => $department,
        'year'       => $year,
        'role'       => 'student',
    ];
}

/**
 * Update an existing user's name, department, and year.
 * Only fields passed as non-null are changed.
 *
 * @param  int         $userId
 * @param  string|null $name
 * @param  string|null $department
 * @param  int|null    $year
 * @return bool
 */
function updateUser(int $userId, ?string $name, ?string $department, ?int $year): bool {
    $lines = readData(USERS_FILE);
    $updated = false;
    foreach ($lines as &$line) {
        $p = explode('|', $line);
        if ((int)$p[0] !== $userId) continue;
        if ($name       !== null) $p[1] = $name;
        if ($department !== null) $p[4] = $department;
        if ($year       !== null) $p[5] = (string)$year;
        $line    = implode('|', $p);
        $updated = true;
    }
    unset($line);
    return $updated && writeAllData(USERS_FILE, $lines);
}

/**
 * Delete a user and all related user_skills and requests.
 *
 * @param  int $userId
 * @return bool
 */
function deleteUser(int $userId): bool {
    // Remove from users.txt
    $lines = array_filter(readData(USERS_FILE), fn($l) => (int)explode('|', $l)[0] !== $userId);
    writeAllData(USERS_FILE, array_values($lines));

    // Remove user_skills
    $usLines = array_filter(readData(USER_SKILLS_FILE), fn($l) => (int)explode('|', $l)[1] !== $userId);
    writeAllData(USER_SKILLS_FILE, array_values($usLines));

    // Remove requests (sent or received)
    $rLines = array_filter(readData(REQUESTS_FILE), function($l) use ($userId) {
        $p = explode('|', $l);
        return (int)$p[1] !== $userId && (int)$p[2] !== $userId;
    });
    writeAllData(REQUESTS_FILE, array_values($rLines));

    return true;
}

// ─── SKILL functions ──────────────────────────────────────────────────────────

/**
 * Return all master skills.
 *
 * @return array[]  Keys: id, name, category
 */
function getSkills(): array {
    $rows   = readData(SKILLS_FILE);
    $skills = [];
    foreach ($rows as $row) {
        $p = explode('|', $row);
        if (count($p) < 3) continue;
        $skills[] = [
            'id'       => (int)$p[0],
            'name'     => $p[1],
            'category' => $p[2],
        ];
    }
    return $skills;
}

/**
 * Find a master skill by ID.
 *
 * @param  int $id
 * @return array|null
 */
function findSkillById(int $id): ?array {
    foreach (getSkills() as $skill) {
        if ($skill['id'] === $id) return $skill;
    }
    return null;
}

/**
 * Find a master skill by name (case-insensitive).
 *
 * @param  string $name
 * @return array|null
 */
function findSkillByName(string $name): ?array {
    foreach (getSkills() as $skill) {
        if (strtolower($skill['name']) === strtolower($name)) return $skill;
    }
    return null;
}

// ─── USER_SKILLS functions ────────────────────────────────────────────────────

/**
 * Return all user-skill records.
 *
 * @return array[]  Keys: id, user_id, skill_id, type
 */
function getUserSkills(): array {
    $rows   = readData(USER_SKILLS_FILE);
    $result = [];
    foreach ($rows as $row) {
        $p = explode('|', $row);
        if (count($p) < 4) continue;
        $result[] = [
            'id'       => (int)$p[0],
            'user_id'  => (int)$p[1],
            'skill_id' => (int)$p[2],
            'type'     => $p[3],   // 'teach' or 'learn'
        ];
    }
    return $result;
}

/**
 * Get skills for a specific user, optionally filtered by type.
 *
 * @param  int         $userId
 * @param  string|null $type  'teach', 'learn', or null for both
 * @return array[]
 */
function getUserSkillsById(int $userId, ?string $type = null): array {
    $all = getUserSkills();
    return array_values(array_filter($all, function($us) use ($userId, $type) {
        if ($us['user_id'] !== $userId) return false;
        if ($type !== null && $us['type'] !== $type) return false;
        return true;
    }));
}

/**
 * Add a skill for a user (teach or learn).
 * Prevents duplicate entries.
 *
 * @param  int    $userId
 * @param  int    $skillId
 * @param  string $type    'teach' or 'learn'
 * @return bool   false if duplicate or invalid type
 */
function addUserSkill(int $userId, int $skillId, string $type): bool {
    if (!in_array($type, ['teach', 'learn'], true)) return false;

    // Check for duplicate
    foreach (getUserSkillsById($userId) as $us) {
        if ($us['skill_id'] === $skillId && $us['type'] === $type) return false;
    }

    $id   = getNextId(USER_SKILLS_FILE);
    $line = implode('|', [$id, $userId, $skillId, $type]);
    return appendData(USER_SKILLS_FILE, $line);
}

/**
 * Remove a user-skill record by its own ID.
 *
 * @param  int $userSkillId
 * @return bool
 */
function removeUserSkill(int $userSkillId): bool {
    $lines = array_filter(readData(USER_SKILLS_FILE), fn($l) => (int)explode('|', $l)[0] !== $userSkillId);
    return writeAllData(USER_SKILLS_FILE, array_values($lines));
}

// ─── REQUEST functions ────────────────────────────────────────────────────────

/**
 * Return all exchange requests.
 *
 * @return array[]  Keys: id, sender_id, receiver_id, offered_skill_id, requested_skill_id, status, created_at
 */
function getRequests(): array {
    $rows   = readData(REQUESTS_FILE);
    $result = [];
    foreach ($rows as $row) {
        $p = explode('|', $row);
        if (count($p) < 7) continue;
        $result[] = [
            'id'                => (int)$p[0],
            'sender_id'         => (int)$p[1],
            'receiver_id'       => (int)$p[2],
            'offered_skill_id'  => (int)$p[3],
            'requested_skill_id'=> (int)$p[4],
            'status'            => $p[5],   // pending | accepted | rejected
            'created_at'        => $p[6],
        ];
    }
    return $result;
}

/**
 * Get requests involving a specific user (as sender or receiver).
 *
 * @param  int         $userId
 * @param  string|null $role    'sender', 'receiver', or null for both
 * @return array[]
 */
function getRequestsForUser(int $userId, ?string $role = null): array {
    return array_values(array_filter(getRequests(), function($r) use ($userId, $role) {
        if ($role === 'sender')   return $r['sender_id']   === $userId;
        if ($role === 'receiver') return $r['receiver_id'] === $userId;
        return $r['sender_id'] === $userId || $r['receiver_id'] === $userId;
    }));
}

/**
 * Create a new exchange request.
 * Prevents duplicate pending requests between the same pair for the same skills.
 *
 * @param  int    $senderId
 * @param  int    $receiverId
 * @param  int    $offeredSkillId    Skill the sender offers to teach
 * @param  int    $requestedSkillId  Skill the sender wants to learn
 * @return array|false  The new request array, or false on failure/duplicate.
 */
function createRequest(int $senderId, int $receiverId, int $offeredSkillId, int $requestedSkillId) {
    // Check for existing pending request
    foreach (getRequests() as $r) {
        if (
            $r['sender_id']          === $senderId   &&
            $r['receiver_id']        === $receiverId &&
            $r['offered_skill_id']   === $offeredSkillId &&
            $r['requested_skill_id'] === $requestedSkillId &&
            $r['status']             === 'pending'
        ) return false;
    }

    $id   = getNextId(REQUESTS_FILE);
    $date = date('Y-m-d');
    $line = implode('|', [$id, $senderId, $receiverId, $offeredSkillId, $requestedSkillId, 'pending', $date]);

    if (!appendData(REQUESTS_FILE, $line)) return false;

    return [
        'id'                 => $id,
        'sender_id'          => $senderId,
        'receiver_id'        => $receiverId,
        'offered_skill_id'   => $offeredSkillId,
        'requested_skill_id' => $requestedSkillId,
        'status'             => 'pending',
        'created_at'         => $date,
    ];
}

/**
 * Update the status of a request (accept or reject).
 *
 * @param  int    $requestId
 * @param  string $status     'accepted' or 'rejected'
 * @return bool
 */
function updateRequestStatus(int $requestId, string $status): bool {
    if (!in_array($status, ['accepted', 'rejected'], true)) return false;

    $lines   = readData(REQUESTS_FILE);
    $updated = false;
    foreach ($lines as &$line) {
        $p = explode('|', $line);
        if ((int)$p[0] !== $requestId) continue;
        $p[5]    = $status;
        $line    = implode('|', $p);
        $updated = true;
    }
    unset($line);
    return $updated && writeAllData(REQUESTS_FILE, $lines);
}

// ─── MATCHING ENGINE ─────────────────────────────────────────────────────────

/**
 * Calculate the match score between two users.
 *
 * Rules (as agreed by team):
 *   Both directions match → 100
 *   One direction matches → 50
 *   No match             → 0
 *
 * "Direction 1": currentUser LEARNS something that otherUser TEACHES
 * "Direction 2": otherUser  LEARNS something that currentUser TEACHES
 *
 * Returns detailed breakdown so the Match page can display "Why this is a match".
 *
 * @param  int $userAId
 * @param  int $userBId
 * @return array  Keys: score (int), dir1_matches (array), dir2_matches (array)
 */
function calculateMatchScore(int $userAId, int $userBId): array {
    $aTeach = array_column(getUserSkillsById($userAId, 'teach'), 'skill_id');
    $aLearn = array_column(getUserSkillsById($userAId, 'learn'), 'skill_id');
    $bTeach = array_column(getUserSkillsById($userBId, 'teach'), 'skill_id');
    $bLearn = array_column(getUserSkillsById($userBId, 'learn'), 'skill_id');

    // Direction 1: A learns ← B teaches
    $dir1 = array_values(array_intersect($aLearn, $bTeach));

    // Direction 2: B learns ← A teaches
    $dir2 = array_values(array_intersect($bLearn, $aTeach));

    $hasDir1 = !empty($dir1);
    $hasDir2 = !empty($dir2);

    if ($hasDir1 && $hasDir2) $score = 100;
    elseif ($hasDir1 || $hasDir2) $score = 50;
    else $score = 0;

    return [
        'score'        => $score,
        'dir1_matches' => $dir1,   // skill IDs A can learn from B
        'dir2_matches' => $dir2,   // skill IDs B can learn from A
    ];
}

/**
 * Get all match results for a user, sorted by score descending.
 * Only returns users with score > 0.
 *
 * @param  int $userId
 * @return array[]  Each item: user array + 'match' (score breakdown)
 */
function getMatchesForUser(int $userId): array {
    $results = [];
    foreach (getUsers() as $other) {
        if ($other['id'] === $userId) continue;
        if ($other['role'] !== 'student') continue;

        $match = calculateMatchScore($userId, $other['id']);
        if ($match['score'] === 0) continue;

        $results[] = array_merge($other, ['match' => $match]);
    }

    // Sort by score descending
    usort($results, fn($a, $b) => $b['match']['score'] <=> $a['match']['score']);
    return $results;
}

// ─── STATISTICS (for admin) ───────────────────────────────────────────────────

/**
 * Return quick statistics for the admin dashboard.
 *
 * @return array  Keys: total_students, total_skills, total_requests, total_accepted
 */
function getStats(): array {
    $users    = getUsers();
    $requests = getRequests();
    return [
        'total_students'  => count(array_filter($users, fn($u) => $u['role'] === 'student')),
        'total_skills'    => count(getSkills()),
        'total_requests'  => count($requests),
        'total_accepted'  => count(array_filter($requests, fn($r) => $r['status'] === 'accepted')),
    ];
}
