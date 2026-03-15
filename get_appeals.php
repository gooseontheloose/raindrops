<?php
// get_appeals.php - Retrieves logged ban appeals for the staff dashboard

// --- CONFIGURATION ---
$LOG_FILE = 'data/appeals_log.json';
$STAFF_CODE = 'RAINDROPS_STAFF'; // Must match staff.html

header('Content-Type: application/json');

// Check for auth header or session-like verification
$auth = $_GET['auth'] ?? '';

if ($auth !== $STAFF_CODE) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

if (!file_exists($LOG_FILE)) {
    echo json_encode([]);
    exit;
}

$content = file_get_contents($LOG_FILE);
echo $content;
?>
