<?php
// get_public_content.php - Returns non-sensitive site content
$SETTINGS_FILE = 'data/settings.json';
$DEFAULT_FILE = 'default_settings.json';
header('Content-Type: application/json');

if (!file_exists($SETTINGS_FILE) && file_exists($DEFAULT_FILE)) {
    copy($DEFAULT_FILE, $SETTINGS_FILE);
}

if (!file_exists($SETTINGS_FILE)) {
    echo json_encode([]);
    exit;
}

$settings = json_decode(file_get_contents($SETTINGS_FILE), true);

// Remove sensitive data
unset($settings['discord_webhook']);

echo json_encode($settings);
?>
