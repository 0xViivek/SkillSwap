<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('DATA_DIR', $argv[1]);
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/../includes/functions.php';
$user = createUser('Concurrent Student', $argv[2], 'password', 'CSE', 2);
echo $user === false ? 'duplicate' : $user['id'];
