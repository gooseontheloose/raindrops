<?php
// update_settings.php - Updates community settings

$SETTINGS_FILE = 'data/settings.json';
$STAFF_CODE = 'RAINDROPS_STAFF';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$auth = $data['auth'] ?? '';

if ($auth !== $STAFF_CODE) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($data['settings'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No settings provided']);
    exit;
}

if (file_put_contents($SETTINGS_FILE, json_encode($data['settings'], JSON_PRETTY_PRINT), LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save settings']);
    exit;
}

echo json_encode(['status' => 'success']);
?>
