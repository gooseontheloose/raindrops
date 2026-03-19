<?php
// get_settings.php - Retrieves community settings for authorized staff

$SETTINGS_FILE = '../../data/settings.json';
$DEFAULT_FILE = 'default_settings.json';
$STAFF_CODE = 'RAINDROPS_STAFF';

header('Content-Type: application/json');

if (!file_exists($SETTINGS_FILE) && file_exists($DEFAULT_FILE)) {
    copy($DEFAULT_FILE, $SETTINGS_FILE);
}

$auth = $_GET['auth'] ?? '';
if ($auth !== $STAFF_CODE) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!file_exists($SETTINGS_FILE)) {
    echo json_encode(['discord_webhook' => '']);
    exit;
}

echo file_get_contents($SETTINGS_FILE);
?>
