<?php
// Require API key via environment to avoid committing secrets
$apiKey = getenv('RAIN_API_KEY') ?: null;
$apiUrl = getenv('RAIN_API_URL') ?: 'https://api.rain.gg/v1/affiliates/races';
$cacheFile = sys_get_temp_dir() . '/teebee_rain_api_cache.json';
$cacheExpiry = (int) (getenv('RAIN_CACHE_EXPIRY') ?: 60);
$corsOrigin = getenv('CORS_ALLOW_ORIGIN') ?: '*';

function fetchRainLeaderboard($apiUrl, $apiKey)
{
    if (!$apiKey) {
        return ['error' => 'Missing RAIN API key'];
    }

    $fullUrl = $apiUrl . '?' . http_build_query([
        'participant_count' => 10
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // enable SSL verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'x-api-key: ' . $apiKey
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

function detectRaceEndIso(array $race)
{
    $candidates = ['ends_at', 'end_at', 'end_time', 'endTime', 'end', 'ends_at_timestamp', 'end_timestamp', 'expires_at', 'expires_at_timestamp', 'scheduled_end'];
    foreach ($candidates as $key) {
        if (isset($race[$key]) && $race[$key]) {
            $val = $race[$key];
            if (is_numeric($val)) {
                return date(DATE_ATOM, (int) $val);
            }
            return $val;
        }
    }

    return '';
}

$leaderboardData = null;
if (file_exists($cacheFile) && time() - filemtime($cacheFile) < $cacheExpiry) {
    $cachedRaw = @file_get_contents($cacheFile);
    $cached = $cachedRaw ? json_decode($cachedRaw, true) : null;
    if ($cached && json_last_error() === JSON_ERROR_NONE && is_array($cached)) {
        $leaderboardData = $cached;
    }
}

if (!is_array($leaderboardData)) {
    $leaderboardData = fetchRainLeaderboard($apiUrl, $apiKey);
    if (!isset($leaderboardData['error'])) {
        @file_put_contents($cacheFile, json_encode($leaderboardData), LOCK_EX);
    }
}

// Response headers
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

if (isset($leaderboardData['error'])) {
    http_response_code(500);
    echo json_encode(['error' => $leaderboardData['error']]);
    exit;
}

$entries = [];
$raceEndIso = '';
if (isset($leaderboardData['results']) && is_array($leaderboardData['results']) && count($leaderboardData['results']) > 0) {
    $race = $leaderboardData['results'][0];
    $entries = isset($race['participants']) && is_array($race['participants']) ? array_slice($race['participants'], 0, 10) : [];
    $raceEndIso = detectRaceEndIso($race);
}

echo json_encode([
    'entries' => $entries,
    'endsAt' => $raceEndIso
]);
