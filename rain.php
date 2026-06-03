<?php
$apiKey = 'f243f958-a6cf-4f10-83f4-493b9ae08f21';
$apiUrl = 'https://api.rain.gg/v1/affiliates/races';

$cacheFile = sys_get_temp_dir() . '/teebee_rain_leaderboard_cache.json';
$cacheExpiry = 5; // 5 seconds for live updates

$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';
$isApiRequest = isset($_GET['api']) && $_GET['api'] === '1';

// Always force refresh for API requests to get latest data
if ($isApiRequest) {
    $forceRefresh = true;
}

function fetchRainLeaderboard($apiUrl, $apiKey) {
    $queryString = http_build_query([
        'participant_count' => 10
    ]);

    $fullUrl = $apiUrl . '?' . $queryString;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'x-api-key: ' . $apiKey
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

    return json_decode($response, true) ?: ['error' => 'Invalid JSON response'];
}

$leaderboardData = null;
if (!$forceRefresh && file_exists($cacheFile) && time() - filemtime($cacheFile) < $cacheExpiry) {
    $leaderboardData = json_decode(file_get_contents($cacheFile), true);
} else {
    $leaderboardData = fetchRainLeaderboard($apiUrl, $apiKey);

    if (!isset($leaderboardData['error'])) {
        file_put_contents($cacheFile, json_encode($leaderboardData));
    }
}

// Extract participants from the first race result
$entries = [];
if (isset($leaderboardData['results']) && is_array($leaderboardData['results']) && count($leaderboardData['results']) > 0) {
    $race = $leaderboardData['results'][0];
    $entries = isset($race['participants']) ? array_slice($race['participants'], 0, 10) : [];
}
$hasError = isset($leaderboardData['error']);

// Try to detect race end time from common API fields and expose as ISO for JS
$raceEndIso = '';
if (!empty($race) && is_array($race)) {
    $candidates = ['ends_at', 'end_at', 'end_time', 'endTime', 'end', 'ends_at_timestamp', 'end_timestamp', 'expires_at', 'expires_at_timestamp', 'scheduled_end'];
    foreach ($candidates as $key) {
        if (isset($race[$key]) && $race[$key]) {
            $val = $race[$key];
            // If numeric assume unix timestamp
            if (is_numeric($val)) {
                $raceEndIso = date(DATE_ATOM, (int)$val);
            } else {
                // try to normalize common formats; assume API returns ISO already
                $raceEndIso = $val;
            }
            break;
        }
    }
}

// If API request, return JSON
if ($isApiRequest) {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    if ($hasError) {
        echo json_encode(['error' => $leaderboardData['error']]);
    } else {
        echo json_encode([
            'entries' => $entries
        ]);
    }
    exit;
}

$home = '/';
$discord = 'https://discord.gg/W8Cs9tMPvQ';
$x = 'https://x.com/TEEBEEGAMBLES';
$kick = 'https://kick.com/teebee3016';
$yt = 'https://youtube.com/@teebee3016?si=WUtfHs0g0NQUOxxx';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Teebee.gg - Check leaderboards and giveaways from the teebee community here" />
    <title>Teebee.gg</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="/Teebee-site/assets/img/teebeepfp.png" />
    <link rel="stylesheet" href="/Teebee-site/assets/css/styles.css" />
</head>
  <body>
    <div class="ambient ambient-left" aria-hidden="true"></div>
    <div class="ambient ambient-right" aria-hidden="true"></div>

    <main class="page-shell">

          <aside class="leaderboard-banner-wrap">
            <img src="/Teebee-site/assets/img/rainbanner.png" alt="Rain.gg banner" class="leaderboard-banner"/>
            <div class="leaderboard-actions">
              <a class="button button-primary" href="https://rain.gg" target="_blank" rel="noreferrer">Open Rain.gg (official)</a>
              <a class="button button-secondary" href="/index.php">Back home</a>
            </div>
          </aside>
        </div>

      <?php if (!empty($raceEndIso)): ?>
        <div style="display:flex;justify-content:center;margin:18px 0 0;">
          <div id="leaderboard-countdown" class="leaderboard-countdown" data-ends-at="<?php echo htmlspecialchars($raceEndIso); ?>" aria-live="polite" aria-atomic="true">
            <span class="countdown-label">Ends in</span>
            <span class="countdown-time" aria-hidden="false"></span>
          </div>
        </div>
      <?php endif; ?>

      <section class="card tabs-section" style="margin-top: 24px;">
        <div class="section-heading">
          <p class="eyebrow">Rain.gg Affiliate Competition</p>
          <h2 class="animate-title">Hive Standings</h2>
          <p> Track the swarm’s top performers as they battle for glory, bragging rights, and the sweetest spot on the board. 🍯✨</p>
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
          <div id="leaderboard-cards" class="leaderboard-cards-container">
              <?php foreach ($entries as $index => $entry): $rank = $index + 1; $prize = isset($entry['prize']) ? $entry['prize'] / 100 : 0; ?>
              <div class="leaderboard-card">
                <div class="leaderboard-card-left">
                  <span class="rank-badge rank-<?php echo $rank; ?> leaderboard-card-rank">
                    <?php echo htmlspecialchars($rank); ?>
                  </span>
                  <div class="leaderboard-card-player">
                    <div class="player-avatar leaderboard-card-avatar">
                      <img src="<?php echo htmlspecialchars(isset($entry['avatar']['small']) ? $entry['avatar']['small'] : 'https://cdn.rain.gg/images/avatar/unknown_small.png'); ?>" alt="<?php echo htmlspecialchars(isset($entry['username']) ? $entry['username'] : 'Unknown'); ?>" />
                    </div>
                    <strong><?php echo htmlspecialchars(isset($entry['username']) ? $entry['username'] : 'N/A'); ?></strong>
                  </div>
                </div>

                <div class="leaderboard-card-right">
                  <div class="leaderboard-stat-block">
                    <p class="leaderboard-stat-label">Total Wagered</p>
                    <span class="stat-value">
                      <img class="currency-icon" src="/Teebee-site/assets/img/rain-coin.svg" alt="Rain Coin" loading="lazy" width="18" height="18" decoding="async" />
                      <?php echo number_format(isset($entry['wagered']) ? $entry['wagered'] : 0, 2); ?>
                    </span>
                  </div>

                  <div class="leaderboard-stat-block">
                    <p class="leaderboard-stat-label">Reward</p>
                    <?php if ($prize > 0): ?>
                      <div class="leaderboard-reward-badge">
                        <img src="/Teebee-site/assets/img/rain-coin.svg" alt="Coins" width="16" height="16" loading="lazy" decoding="async" />
                        <span><?php echo number_format($prize); ?></span>
                      </div>
                    <?php else: ?>
                      <span class="muted">—</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </main>
    <script src="/Teebee-site/assets/js/script.js"></script>
  </body>
</html>
