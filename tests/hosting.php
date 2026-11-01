<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$temporary = sys_get_temp_dir() . '/skillswap-hosting-' . bin2hex(random_bytes(6)) . '/';
mkdir($temporary, 0700, true);
session_save_path($temporary);
putenv('SKILLSWAP_DATA_DIR=' . $temporary);
putenv('SKILLSWAP_PUBLIC_DEMO=1');
putenv('SKILLSWAP_TRUST_PROXY=1');
$_SERVER['SCRIPT_NAME'] = '/dashboard.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
require __DIR__ . '/../includes/auth.php';
$checks = 0;
function hostingCheck(bool $condition, string $message): void {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}
try {
    hostingCheck(realpath(DATA_DIR) === realpath($temporary), 'Configured external data directory');
    hostingCheck(BASE_URL === '', 'Root-hosted base URL');
    hostingCheck(isHttpsRequest() && session_get_cookie_params()['secure'], 'Trusted HTTPS proxy uses secure cookies');
    putenv('SKILLSWAP_TRUST_PROXY=0');
    hostingCheck(!isHttpsRequest(), 'Untrusted forwarded header ignored');
    $_SERVER['HTTPS'] = 'on';
    hostingCheck(isHttpsRequest(), 'Direct HTTPS detected');
    unset($_SERVER['HTTPS']);
    putenv('SKILLSWAP_TRUST_PROXY=1');
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';
    hostingCheck(!isHttpsRequest(), 'Forwarded HTTP stays HTTP');
    initializeData();
    hostingCheck(count(getUsers()) === 3, 'Demo seed initialization');
    hostingCheck(findUserByEmail('vivek@gmail.com') === null, 'Original seed email not published in demo');
    hostingCheck(findUserByEmail('student@skillswap.test')['name'] === 'Demo Student', 'Sanitized demo student');
    hostingCheck(loginUser('student@skillswap.test', 'password')['id'] === 2, 'Hosted student login');
    hostingCheck(calculateMatchScore(2, 3)['score'] === 100, 'Hosted mutual match');
    hostingCheck(loginUser('admin@skillswap.test', 'password')['role'] === 'admin', 'Hosted admin login');
} finally {
    logoutUser();
    foreach (glob($temporary . '*') as $file) unlink($file);
    foreach (glob($temporary . '.*') as $file) if (is_file($file)) unlink($file);
    rmdir($temporary);
}
echo "PASS: $checks hosting checks\n";
