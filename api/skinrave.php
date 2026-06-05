<?php
$apiUrl = 'https://api.skinrave.gg/affiliates/public/applicants?token=3e179631-fbe3-4865-ac74-8213a718a3ac&skip=0&take=10&order=DESC&from=2026-05-29T10:29:47.028Z&to=2026-06-05T10:29:47.028Z';
$cacheFile = sys_get_temp_dir() . '/teebee_skinrave_api_cache.json';
$cacheExpiry = 15;

function skinraveFetchApplicants($apiUrl)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    if ($error) {
        return ['error' => 'cURL error: ' . $error];
    }

    if ($httpCode !== 200) {
        return ['error' => 'API returned status ' . $httpCode];
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        return ['error' => 'Invalid JSON response'];
    }

    return $decoded;
}

$payload = null;
if (file_exists($cacheFile) && time() - filemtime($cacheFile) < $cacheExpiry) {
    $payload = json_decode(file_get_contents($cacheFile), true);
}

if (!is_array($payload)) {
    $payload = skinraveFetchApplicants($apiUrl);
    if (!isset($payload['error'])) {
        file_put_contents($cacheFile, json_encode($payload));
    }
}

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (isset($payload['error'])) {
    echo json_encode(['error' => $payload['error']]);
    exit;
}

echo json_encode([
    'totalCount' => isset($payload['totalCount']) ? (int) $payload['totalCount'] : 0,
    'filteredCount' => isset($payload['filteredCount']) ? (int) $payload['filteredCount'] : 0,
    'list' => isset($payload['list']) && is_array($payload['list']) ? $payload['list'] : []
]);
