<?php
// Development server router: php -S 127.0.0.1:8080 tools/router.php
if (PHP_SAPI !== 'cli-server') { http_response_code(404); exit; }
if (getenv('SKILLSWAP_TEST_DATA_DIR')) {
    define('DATA_DIR', getenv('SKILLSWAP_TEST_DATA_DIR'));
    session_save_path(DATA_DIR);
}
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$relative = preg_replace('#^/SkillSwap(?=/|$)#', '', $path);
if ($relative === '') $relative = '/';
$root = realpath(__DIR__ . '/..');
$target = realpath($root . ($relative === '/' ? '/index.php' : $relative));
if (!$target || !str_starts_with($target, $root . DIRECTORY_SEPARATOR) || !is_file($target) ||
    preg_match('#(^|/)(data|includes|tests|tools|\.git|\.github)(/|$)#i', $relative) ||
    !in_array(strtolower(pathinfo($target, PATHINFO_EXTENSION)), ['php', 'css', 'js'], true)) {
    http_response_code(404); exit;
}
if (pathinfo($target, PATHINFO_EXTENSION) === 'php') {
    $_SERVER['SCRIPT_NAME'] = ($path !== $relative ? '/SkillSwap' : '') . ($relative === '/' ? '/index.php' : $relative);
    require $target;
} else {
    header('Content-Type: ' . (str_ends_with($target, '.css') ? 'text/css' : 'application/javascript'));
    readfile($target);
}
