<?php
// vrc_group_count.php - Cached proxy for VRChat group member count
// Returns JSON: {"count": 7400, "updated_at": "2026-03-14T23:00:00+00:00"}

$SETTINGS_FILE = 'data/settings.json';
$CACHE_FILE = 'data/vrc_cache.json';
$GROUP_ID = 'grp_77f8d8b8-dcfb-4b61-b065-d6d4d2bbbaa3';
$CACHE_TTL = 3600; // 1 hour in seconds

header('Content-Type: application/json');

// Check cache first
if (file_exists($CACHE_FILE)) {
    $cache = json_decode(file_get_contents($CACHE_FILE), true);
    if ($cache && isset($cache['updated_at'])) {
        $age = time() - strtotime($cache['updated_at']);
        if ($age < $CACHE_TTL) {
            echo json_encode($cache);
            exit;
        }
    }
}

// Cache is stale or missing — fetch fresh data from VRChat API
if (!file_exists($SETTINGS_FILE)) {
    echo json_encode(['count' => null, 'error' => 'No settings configured']);
    exit;
}

$settings = json_decode(file_get_contents($SETTINGS_FILE), true);
$username = $settings['vrc_username'] ?? '';
$password = $settings['vrc_password'] ?? '';

if (empty($username) || empty($password)) {
    // Return cached data even if stale, or null
    if (isset($cache['count'])) {
        echo json_encode($cache);
    } else {
        echo json_encode(['count' => null, 'error' => 'VRChat credentials not configured']);
    }
    exit;
}

// Step 1: Authenticate to VRChat API
$authHeader = 'Basic ' . base64_encode($username . ':' . $password);

$ch = curl_init('https://api.vrchat.cloud/api/1/auth/user');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: ' . $authHeader,
        'User-Agent: RaindropsWebsite/1.0 (community site)'
    ],
    CURLOPT_HEADER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 10
]);
$authResponse = curl_exec($ch);
$authHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$authHeaders = substr($authResponse, 0, $headerSize);
curl_close($ch);

if ($authHttpCode !== 200) {
    // Auth failed — return stale cache or error
    if (isset($cache['count'])) {
        echo json_encode($cache);
    } else {
        echo json_encode(['count' => null, 'error' => 'VRChat auth failed (HTTP ' . $authHttpCode . ')']);
    }
    exit;
}

// Extract auth cookie from response headers
$cookies = [];
preg_match_all('/Set-Cookie:\s*([^;]+)/i', $authHeaders, $cookieMatches);
foreach ($cookieMatches[1] as $cookie) {
    $cookies[] = $cookie;
}
$cookieString = implode('; ', $cookies);

// Step 2: Get group info
$ch = curl_init('https://api.vrchat.cloud/api/1/groups/' . $GROUP_ID);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Cookie: ' . $cookieString,
        'User-Agent: RaindropsWebsite/1.0 (community site)'
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 10
]);
$groupResponse = curl_exec($ch);
$groupHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($groupHttpCode !== 200) {
    if (isset($cache['count'])) {
        echo json_encode($cache);
    } else {
        echo json_encode(['count' => null, 'error' => 'Failed to fetch group (HTTP ' . $groupHttpCode . ')']);
    }
    exit;
}

$groupData = json_decode($groupResponse, true);
$memberCount = $groupData['memberCount'] ?? null;

if ($memberCount === null) {
    if (isset($cache['count'])) {
        echo json_encode($cache);
    } else {
        echo json_encode(['count' => null, 'error' => 'memberCount not found in response']);
    }
    exit;
}

// Save to cache
$cacheData = [
    'count' => $memberCount,
    'updated_at' => date('c')
];
file_put_contents($CACHE_FILE, json_encode($cacheData, JSON_PRETTY_PRINT), LOCK_EX);

echo json_encode($cacheData);
?>
