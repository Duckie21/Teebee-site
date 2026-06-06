<?php
// Require SKINRAVE_API_URL via environment variable to avoid committing tokens
$apiUrl = getenv('SKINRAVE_API_URL') ?: null;
$cacheFile = sys_get_temp_dir() . '/teebee_skinrave_api_cache.json';
$cacheExpiry = (int) (getenv('SKINRAVE_CACHE_EXPIRY') ?: 60);
$corsOrigin = getenv('CORS_ALLOW_ORIGIN') ?: '*';

if (!$apiUrl) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: ' . $corsOrigin);
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(500);
    echo json_encode(['error' => 'Missing SKINRAVE_API_URL environment variable']);
    exit;
}

function skinraveFetchApplicants($apiUrl)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // Enable proper SSL verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
    ]);

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
    $payload = skinraveFetchApplicants($apiUrl);
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
header('Access-Control-Allow-Headers: Content-Type');
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (isset($payload['error'])) {
    http_response_code(500);
    echo json_encode(['error' => $payload['error']]);
    exit;
}

echo json_encode([
    'totalCount' => isset($payload['totalCount']) ? (int) $payload['totalCount'] : 0,
    'filteredCount' => isset($payload['filteredCount']) ? (int) $payload['filteredCount'] : 0,
    'list' => isset($payload['list']) && is_array($payload['list']) ? $payload['list'] : []
]);
