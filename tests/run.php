<?php
// CLI-only regression checks, using disposable copies of the demo data.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$temporary = sys_get_temp_dir() . '/skillswap-test-' . bin2hex(random_bytes(6)) . '/';
define('DATA_DIR', $temporary);
mkdir($temporary, 0700, true);
session_save_path($temporary);
$_SERVER['SCRIPT_NAME'] = '/SkillSwap/dashboard.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
require __DIR__ . '/../includes/auth.php';
$checks = 0;
function check(bool $condition, string $message): void {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}
try {
    initializeData();
    check(BASE_URL === '/SkillSwap', 'Subfolder base URL');
    $oldSession = session_id();
    check(loginUser('vivek@gmail.com', 'wrong') === false, 'Wrong password rejected');
    check(loginUser('vivek@gmail.com', 'password')['id'] === 2, 'Demo login');
    check(session_id() !== $oldSession, 'Session ID rotates at login');
    $token = csrfToken();
    check(validCsrfToken($token), 'Valid CSRF token');
    check(!validCsrfToken('bad') && !validCsrfToken([]), 'Forged CSRF token rejected');
    loginUser('rahul@gmail.com', 'password');
    check(!validCsrfToken($token), 'Old login CSRF token invalidated');
    check(calculateMatchScore(2, 3)['score'] === 100, 'Mutual match');
    check(!removeUserSkill(1, 3), 'Cannot delete another user skill');
    check(!addUserSkill(2, 999, 'teach'), 'Unknown skill rejected');
    check(!addUserSkill(999, 1, 'teach'), 'Unknown user rejected');
    check(!addUserSkill(2, 1, 'teach'), 'Duplicate skill rejected');
    check(!createRequest(2, 2, 1, 2), 'Self request rejected');
    check(!createRequest(2, 999, 1, 2), 'Unknown receiver rejected');
    check(!createRequest(2, 1, 1, 2), 'Admin receiver rejected');
    check(!createRequest(2, 3, 3, 2), 'Unoffered skill rejected');
    check(!createRequest(2, 3, 1, 999), 'Unknown requested skill rejected');
    $request = createRequest(2, 3, 1, 2);
    check(is_array($request), 'Valid exchange created');
    check(!createRequest(2, 3, 1, 2), 'Duplicate pending exchange rejected');
    check(!updateRequestStatus($request['id'], 'accepted', 2), 'Sender cannot accept');
    check(updateRequestStatus($request['id'], 'accepted', 3), 'Receiver can accept');
    check(!updateRequestStatus($request['id'], 'rejected', 3), 'Answered exchange cannot change');
    check(getStats()['total_accepted'] === 1, 'Accepted count');
    check(!createUser('Bad|Name', 'new@example.com', 'password', 'CSE', 2), 'Pipe injection rejected');
    check(!createUser("Bad\nName", 'new@example.com', 'password', 'CSE', 2), 'Newline injection rejected');
    check(!updateUser(2, 'Vivek', "CSE\nadmin", 2), 'Profile newline injection rejected');
    check(!createUser('New Student', 'VIVEK@gmail.com', 'password', 'CSE', 2), 'Case-insensitive duplicate email');
    $new = createUser('New Student', 'new@example.com', 'password', 'CSE', 2);
    check(is_array($new) && password_verify('password', $new['password']), 'Registration stores hash');
    check(updateUser($new['id'], 'Edited Student', 'ECE', 3), 'Profile update');
    check(findUserById($new['id'])['name'] === 'Edited Student', 'Profile saved');
    check(calculateMatchScore(2, $new['id'])['score'] === 0, 'No match');
    check(removeUserSkill(7, 3), 'Owner can remove skill');
    check(calculateMatchScore(2, 3)['score'] === 50, 'One-way match');
    check(!createRequest(2, 3, 1, 2), 'Removed matching skill invalidates new requests');
    check(!deleteUser(1), 'Admin deletion rejected');
    $before = readData(USERS_FILE);
    check(mutateData(function () { writeAllData(USERS_FILE, []); return false; }) === false, 'Failure returned');
    check(readData(USERS_FILE) === $before, 'Failed transaction rolls back');
    check(deleteUser(3), 'Student deletion');
    check(findUserById(3) === null && getUserSkillsById(3) === [] && getRequestsForUser(3) === [], 'Deletion cascades');
    check(deleteUser($new['id']), 'Delete highest student ID');
    $replacement = createUser('Replacement Student', 'replacement@example.com', 'password', 'CSE', 2);
    check($replacement['id'] > $new['id'], 'Deleted IDs never reused by new accounts');
} finally {
    logoutUser();
    if (is_dir($temporary)) {
        foreach (glob($temporary . '*') as $file) unlink($file);
        foreach (glob($temporary . '.*') as $file) if (is_file($file)) unlink($file);
        rmdir($temporary);
    }
}
echo "PASS: $checks checks\n";
