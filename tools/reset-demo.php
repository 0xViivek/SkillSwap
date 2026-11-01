<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (($argv[1] ?? '') !== '--confirm') {
    fwrite(STDERR, "Replaces all local users, skills and requests with demo fixtures.\nRun php tools/reset-demo.php --confirm to continue.\n");
    exit(1);
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/../includes/functions.php';
$ok = mutateData(function () {
    foreach (['users.txt', 'skills.txt', 'user_skills.txt', 'requests.txt'] as $name) {
        $lines = file(SEED_DIR . $name, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false || !writeAllData(DATA_DIR . $name, $lines)) return false;
    }
    return true;
});
echo $ok ? "Demo records restored. Existing sessions should log out and back in.\n" : "Reset failed; previous records restored.\n";
exit($ok ? 0 : 1);
