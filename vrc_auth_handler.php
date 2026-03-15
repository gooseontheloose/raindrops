<?php
// vrc_auth_handler.php - Handles VRChat 2FA login flow
// Uses $_SESSION to hold temporary auth tokens during the 2FA step

session_start();
header('Content-Type: application/json');

$SETTINGS_FILE = 'data/settings.json';

// Simple API key auth check (must match STAFF_CODE)
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['auth']) || $input['auth'] !== 'RAINDROPS_STAFF') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing credentials']);
        exit;
    }

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
        CURLOPT_TIMEOUT => 15
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);

    // Extract cookies
    $cookies = [];
    preg_match_all('/Set-Cookie:\s*([^;]+)/i', $headers, $cookieMatches);
    foreach ($cookieMatches[1] as $cookie) {
        $cookies[] = $cookie;
    }
    $cookieString = implode('; ', $cookies);

    if ($httpCode === 200) {
        $data = json_decode($body, true);
        
        // Check if 2FA is required
        if (isset($data['requiresTwoFactorAuth']) && count($data['requiresTwoFactorAuth']) > 0) {
            // Save the half-cookie for the next step
            $_SESSION['vrc_half_cookie'] = $cookieString;
            
            // Prioritize TOTP (Authenticator App) over Email OTP if both exist
            $type = in_array('totp', $data['requiresTwoFactorAuth']) ? 'totp' : $data['requiresTwoFactorAuth'][0];
            
            echo json_encode([
                'status' => 'requires_2fa', 
                'type' => $type,
                'message' => "2FA Required. Please enter your $type code."
            ]);
            exit;
        }
        
        // If NO 2FA required (rare), just save this cookie as the final one
        saveFinalCookie($cookieString);
        echo json_encode(['status' => 'success', 'message' => 'Logged in without 2FA']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => "Login failed (HTTP $httpCode): " . $body]);
    exit;

} elseif ($action === 'verify') {
    $code = $input['code'] ?? '';
    $type = $input['type'] ?? 'totp'; // 'totp' or 'emailOtp'
    $halfCookie = $_SESSION['vrc_half_cookie'] ?? '';

    if (empty($code) || empty($halfCookie)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing code or session expired']);
        exit;
    }

    // VRChat endpoints:
    // /auth/twofactorauth/totp/verify
    // /auth/twofactorauth/emailotp/verify
    $endpoint = 'https://api.vrchat.cloud/api/1/auth/twofactorauth/' . strtolower($type) . '/verify';

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['code' => $code]),
        CURLOPT_HTTPHEADER => [
            'Cookie: ' . $halfCookie,
            'Content-Type: application/json',
            'User-Agent: RaindropsWebsite/1.0 (community site)'
        ],
        CURLOPT_HEADER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 15
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);

    if ($httpCode === 200) {
        // Find the new auth cookie (it replaces the old one)
        $cookies = [];
        preg_match_all('/Set-Cookie:\s*([^;]+)/i', $headers, $cookieMatches);
        foreach ($cookieMatches[1] as $cookie) {
            $cookies[] = $cookie;
        }
        $finalCookie = implode('; ', $cookies);

        // If for some reason new cookies weren't sent, fallback to the half cookie
        if (empty($finalCookie) || strpos($finalCookie, 'auth=') === false) {
            $finalCookie = $halfCookie;
        }

        saveFinalCookie($finalCookie);
        
        // Clear temp session
        unset($_SESSION['vrc_half_cookie']);

        echo json_encode(['status' => 'success', 'message' => '2FA Verified and account linked']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => "2FA verification failed (HTTP $httpCode)"]);
    exit;
}

function saveFinalCookie($cookieStr) {
    global $SETTINGS_FILE;
    $settings = file_exists($SETTINGS_FILE) ? json_decode(file_get_contents($SETTINGS_FILE), true) : [];
    
    // Save only the auth cookie, remove raw passwords if they exist
    $settings['vrc_auth_cookie'] = $cookieStr;
    unset($settings['vrc_username']);
    unset($settings['vrc_password']);
    
    file_put_contents($SETTINGS_FILE, json_encode($settings, JSON_PRETTY_PRINT));
}
?>
