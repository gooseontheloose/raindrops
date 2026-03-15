<?php
// submit_appeal.php - Handles ban appeal submissions for Raindrops

$LOG_FILE = 'data/appeals_log.json';
$SETTINGS_FILE = 'data/settings.json';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    exit;
}

$data['timestamp'] = date('c');

// Save to log
$current_data = file_exists($LOG_FILE) ? json_decode(file_get_contents($LOG_FILE), true) : [];
$current_data[] = $data;
file_put_contents($LOG_FILE, json_encode($current_data, JSON_PRETTY_PRINT), LOCK_EX);

// Get Webhook from dynamic settings
$webhook = '';
if (file_exists($SETTINGS_FILE)) {
    $settings = json_decode(file_get_contents($SETTINGS_FILE), true);
    $webhook = $settings['discord_webhook'] ?? '';
}

// Send Webhook if configured
if (!empty($webhook)) {
    $payload = json_encode([
        "embeds" => [[
            "title" => "🚨 New Ban Appeal: " . $data['ticket'],
            "color" => 0x00BFBF,
            "fields" => [
                ["name" => "VRChat Profile", "value" => $data['vrc'], "inline" => false],
                ["name" => "Discord", "value" => $data['discord'], "inline" => true],
                ["name" => "Ban Reason", "value" => $data['reason'], "inline" => true],
                ["name" => "Appeal Message", "value" => $data['message']]
            ],
            "footer" => ["text" => "Raindrops System"],
            "timestamp" => $data['timestamp']
        ]]
    ]);

    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
}

echo json_encode(['status' => 'success', 'ticket' => $data['ticket']]);
?>
