<?php
// Skinrave affiliate applicants proxy — returns normalized entries and endsAt like api/rain.php
$token = getenv('SKINRAVE_TOKEN') ?: 'e1f9d079-ce81-43a8-b203-2b536a578ec3';
$apiUrl = getenv('SKINRAVE_API_URL') ?: 'https://api.skinrave.gg/affiliates/public/applicants?token=' . rawurlencode($token) . '&skip=0&take=10&order=DESC&from=2026-05-31T02:05:47.675Z&to=2026-06-07T02:05:47.675Z';
$cacheFile = sys_get_temp_dir() . '/teebee_skinrave_api_cache.json';
$cacheExpiry = (int) (getenv('SKINRAVE_CACHE_EXPIRY') ?: 60);
$corsOrigin = getenv('CORS_ALLOW_ORIGIN') ?: '*';


function skinraveFetch($apiUrl, $token = null)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // Match api/rain.php behaviour: relax SSL verification for environments where cert chain may fail
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $headers = [
        'Accept: application/json',
    ];
    if ($token) {
        $headers[] = 'x-api-key: ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => 'cURL error: ' . $error];
    }

    if ($httpCode !== 200) {
        return ['error' => 'API returned status ' . $httpCode];
    }

    $decoded = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        return ['error' => 'Invalid JSON response'];
    }

    return $decoded;
}

$payload = null;
if (file_exists($cacheFile) && time() - filemtime($cacheFile) < $cacheExpiry) {
    $cachedRaw = @file_get_contents($cacheFile);
    $cached = $cachedRaw ? json_decode($cachedRaw, true) : null;
    if ($cached && json_last_error() === JSON_ERROR_NONE && is_array($cached)) {
        $payload = $cached;
    }
}

if (!is_array($payload)) {
    $payload = skinraveFetch($apiUrl, $token);
    if (!isset($payload['error'])) {
        @file_put_contents($cacheFile, json_encode($payload), LOCK_EX);
    }
}

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Access-Control-Allow-Origin: ' . $corsOrigin);
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, x-api-key');
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (isset($payload['error'])) {
    http_response_code(500);
    echo json_encode(['error' => $payload['error']]);
    exit;
}

// Normalize payload to entries and endsAt (compatible with api/rain.php consumer)
$entries = [];
$endsAt = '';

// If the API returns races with participants
if (isset($payload['races']) && is_array($payload['races']) && count($payload['races']) > 0) {
    $first = $payload['races'][0];
    if (isset($first['participants']) && is_array($first['participants'])) {
        $entries = $first['participants'];
    } elseif (isset($first['entries']) && is_array($first['entries'])) {
        $entries = $first['entries'];
    }

    if (isset($first['endsAt'])) {
        $endsAt = $first['endsAt'];
    } elseif (isset($first['endAt'])) {
        $endsAt = $first['endAt'];
    } elseif (isset($first['end']) && is_numeric($first['end'])) {
        $dt = DateTime::createFromFormat('U', (string) $first['end']);
        if ($dt) {
            $endsAt = $dt->format(DateTime::ATOM);
        }
    }
}

// Fallback shapes
if (empty($entries) && isset($payload['list']) && is_array($payload['list'])) {
    $entries = $payload['list'];
}

if (empty($entries) && isset($payload['entries']) && is_array($payload['entries'])) {
    $entries = $payload['entries'];
}

// If payload itself is a numeric-indexed array, assume it's entries
if (empty($entries) && array_values($payload) === $payload) {
    $entries = $payload;
}

echo json_encode([
    'entries' => array_values($entries),
    'endsAt' => $endsAt,
]);
