<?php
header('Content-Type: application/json; charset=utf-8');
try {
    require_once __DIR__ . '/includes/functions.php';
    initializeData();
    if (!is_writable(DATA_DIR)) throw new RuntimeException('Data directory is not writable.');
    echo json_encode(['status' => 'ok']);
} catch (Throwable $error) {
    error_log('SkillSwap health check failed: ' . $error->getMessage());
    http_response_code(503);
    echo json_encode(['status' => 'unavailable']);
}
