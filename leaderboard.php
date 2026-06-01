<?php
// Teebee Rain.gg Leaderboard

// API credentials and endpoint
$apiKey = 'f243f958-a6cf-4f10-83f4-493b9ae08f21';
$affiliateCode = 'TEEBEE';
$apiUrl = 'https://api.rain.gg/v1/affiliates/leaderboard';

// Date range (current UTC month)
$startDate = date('Y-m-d\T00:00:00.00\Z', strtotime('first day of this month'));
$endDate = date('Y-m-d\T23:59:59.00\Z', strtotime('last day of this month'));

// Prepare cache
$cacheFile = sys_get_temp_dir() . '/teebee_rain_leaderboard_cache.json';
$cacheExpiry = 300; // 5 minutes

// Function to fetch from Rain.gg API
function fetchRainLeaderboard($apiUrl, $apiKey, $affiliateCode, $startDate, $endDate) {
    $queryString = http_build_query([
        'start_date' => $startDate,
        'end_date' => $endDate,
        'type' => 'wagered',
        'code' => $affiliateCode
    ]);
    
    $fullUrl = $apiUrl . '?' . $queryString;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
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
    
    return json_decode($response, true) ?: ['error' => 'Invalid JSON response'];
}

// Try to load from cache first
$leaderboardData = null;
if (file_exists($cacheFile) && time() - filemtime($cacheFile) < $cacheExpiry) {
    $leaderboardData = json_decode(file_get_contents($cacheFile), true);
} else {
    // Fetch fresh data
    $leaderboardData = fetchRainLeaderboard($apiUrl, $apiKey, $affiliateCode, $startDate, $endDate);
    
    // Cache the result
    if (!isset($leaderboardData['error'])) {
        file_put_contents($cacheFile, json_encode($leaderboardData));
    }
}

// Extract entries
$entries = $leaderboardData['data'] ?? [];
$hasError = isset($leaderboardData['error']);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Teebee Rain.gg Affiliate Leaderboard — top affiliates ranked by wagered amount." />
    <title>Rain.gg Leaderboard — Teebee</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/assets/css/styles.css" />
    <style>
      .leaderboard-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 18px;
      }

      .leaderboard-table thead th {
        background: rgba(168, 85, 247, 0.12);
        border: 1px solid rgba(168, 85, 247, 0.25);
        padding: 12px 14px;
        text-align: left;
        font-weight: 700;
        color: #d8b4fe;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-size: 0.75rem;
      }

      .leaderboard-table tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        transition: background 180ms ease;
      }

      .leaderboard-table tbody tr:hover {
        background: rgba(168, 85, 247, 0.08);
      }

      .leaderboard-table tbody td {
        padding: 14px;
        color: var(--text);
      }

      .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d8b4fe 0%, #c4b5fd 100%);
        color: #171717;
        font-weight: 800;
        font-size: 0.9rem;
      }

      .rank-1 {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
      }

      .rank-2 {
        background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
      }

      .rank-3 {
        background: linear-gradient(135deg, #cd7f32 0%, #e6a45a 100%);
      }

      .stat-value {
        font-weight: 700;
        color: #d8b4fe;
      }

      .error-box {
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.35);
        color: #fca5a5;
        padding: 16px;
        border-radius: 12px;
        margin-top: 18px;
      }

      .empty-box {
        background: rgba(168, 85, 247, 0.08);
        border: 1px solid rgba(168, 85, 247, 0.2);
        color: var(--muted);
        padding: 24px;
        border-radius: 12px;
        text-align: center;
        margin-top: 18px;
      }
    </style>
  </head>
  <body>
    <div class="ambient ambient-left" aria-hidden="true"></div>
    <div class="ambient ambient-right" aria-hidden="true"></div>

    <main class="page-shell">
      <section class="card tabs-section">
        <div class="section-heading">
          <p class="eyebrow">Rain.gg Affiliate Competition</p>
          <h2>Leaderboard</h2>
          <p>Top affiliates ranked by wagered amount. These are the buzz-worthy players in the Teebee hive on Rain.gg this month.</p>
        </div>

        <?php if ($hasError): ?>
          <div class="error-box">
            <strong>Oops!</strong> Could not fetch leaderboard data. <?php echo htmlspecialchars($leaderboardData['error']); ?>
          </div>
        <?php elseif (empty($entries)): ?>
          <div class="empty-box">
            <p>No affiliate data available yet. Check back soon!</p>
          </div>
        <?php else: ?>
          <table class="leaderboard-table">
            <thead>
              <tr>
                <th>Rank</th>
                <th>Affiliate</th>
                <th>Wagered</th>
                <th>Commission</th>
                <th>Players</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($entries as $index => $entry): $rank = $index + 1; ?>
              <tr>
                <td>
                  <span class="rank-badge rank-<?php echo $rank; ?>">
                    <?php echo htmlspecialchars($rank); ?>
                  </span>
                </td>
                <td>
                  <strong><?php echo htmlspecialchars($entry['affiliateName'] ?? $entry['code'] ?? 'N/A'); ?></strong>
                </td>
                <td>
                  <span class="stat-value">
                    $<?php echo number_format($entry['totalWagered'] ?? 0, 2); ?>
                  </span>
                </td>
                <td>
                  <?php echo number_format($entry['commission'] ?? 0, 2); ?>
                </td>
                <td>
                  <?php echo htmlspecialchars($entry['playerCount'] ?? 0); ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

        <div style="margin-top: 24px;">
          <a class="button button-secondary" href="/" style="text-decoration: none;">← Back to hive</a>
        </div>
      </section>
    </main>
  </body>
</html>

