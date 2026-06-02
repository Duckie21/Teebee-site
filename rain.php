<?php
$apiKey = 'f243f958-a6cf-4f10-83f4-493b9ae08f21';
$affiliateCode = 'TEEBEE';
$apiUrl = 'https://api.rain.gg/v1/affiliates/leaderboard';

$startDate = date('Y-m-d\T00:00:00.00\Z', strtotime('first day of this month'));
$endDate = date('Y-m-d\T23:59:59.00\Z', strtotime('last day of this month'));

$cacheFile = sys_get_temp_dir() . '/teebee_rain_leaderboard_cache.json';
$cacheExpiry = 300; // 5 minutes

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
if (file_exists($cacheFile) && time() - filemtime($cacheFile) < $cacheExpiry) {
    $leaderboardData = json_decode(file_get_contents($cacheFile), true);
} else {
    $leaderboardData = fetchRainLeaderboard($apiUrl, $apiKey, $affiliateCode, $startDate, $endDate);

    if (!isset($leaderboardData['error'])) {
        file_put_contents($cacheFile, json_encode($leaderboardData));
    }
}

$entries = isset($leaderboardData['results']) ? array_slice($leaderboardData['results'], 0, 10) : [];
$hasError = isset($leaderboardData['error']);

// Reward tiers by rank (coins)
$rewards = [
    1 => 100,
    2 => 60,
    3 => 30,
    4 => 20,
    5 => 10,
    6 => 5,
];

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
    <meta name="description" content="Rain.gg — CS lootboxes, jackpots and where Teebee gambles. Learn how Rain.gg works and safety notes." />
    <title>Rain.gg — Teebee page</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="/Teebee-site/assets/css/styles.css" />
</head>
  <body>
    <div class="ambient ambient-left" aria-hidden="true"></div>
    <div class="ambient ambient-right" aria-hidden="true"></div>

    <main class="page-shell">
      <section class="card tabs-section">
        <div class="section-heading">
          <p class="eyebrow">Teebee on Rain.gg</p>
          <h2>Where the streams hit</h2>
          <p class="lead">This is the Rain.gg spot for Teebee: the page that ties the stream setup, the official link, and the current leaderboard together in one place.</p>
        </div>

        <div class="card" style="margin-top:18px;padding:18px;display:grid;grid-template-columns:1fr 320px;gap:18px;align-items:start;">
          <div>
            <h3>What you’ll find here</h3>
            <ul>
              <li>The official Rain.gg button for quick access.</li>
              <li>Teebee’s leaderboard with the current wagered rankings.</li>
              <li>Links back to the main site if you want to bounce home fast.</li>
            </ul>

            <h3>Teebee setup</h3>
            <p>Teebee runs Rain.gg during stream segments that match the purple bee theme and the CS/gambling vibe of the brand. It’s built to keep the focus on the live action and the current rankings.</p>

            <h3>Quick note</h3>
            <p>Use the official link if you’re heading there, and keep it responsible.</p>
          </div>

          <aside style="text-align:center;">
            <img src="/Teebee-site/assets/img/rainbanner.png" alt="Rain.gg banner" style="max-width:100%;height:auto;border-radius:12px;"/>
            <div style="margin-top:12px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
              <a class="button button-primary" href="https://rain.gg" target="_blank" rel="noreferrer">Open Rain.gg (official)</a>
              <a class="button button-secondary" href="/index.php" style="text-decoration:none;">Go to index</a>
              <a class="button button-secondary" href="/" style="text-decoration:none;">Back home</a>
            </div>
          </aside>
        </div>

        <div style="margin-top:18px;" class="card">
          <h3 style="margin:0 0 8px">Responsible play tips</h3>
          <ul style="margin:0 0 12px">
            <li>Set a strict budget before you join any drops or jackpots.</li>
            <li>Don't chase losses — stop if you feel uncomfortable.</li>
            <li>Use official, verified platforms and avoid sharing account credentials.</li>
          </ul>
        </div>
      </section>

      <section class="card tabs-section" style="margin-top: 24px;">
        <div class="section-heading">
          <p class="eyebrow">Rain.gg Affiliate Competition</p>
          <h2>This Week's Leaderboard</h2>
          <p>Top affiliates ranked by wagered amount. These are the buzz-worthy players in the Teebee hive on Rain.gg.</p>
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
                <th>Player</th>
                <th>Total Wagered</th>
                <th>Reward</th>
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
                  <div class="player-info">
                    <div class="player-avatar">
                      <img src="<?php echo htmlspecialchars(isset($entry['avatar']) ? $entry['avatar'] : 'https://cdn.rain.gg/images/avatar/unknown_small.png'); ?>" alt="<?php echo htmlspecialchars(isset($entry['username']) ? $entry['username'] : 'Unknown'); ?>" />
                    </div>
                    <strong><?php echo htmlspecialchars(isset($entry['username']) ? $entry['username'] : 'N/A'); ?></strong>
                  </div>
                </td>
                <td>
                  <span class="stat-value">
                    <img class="currency-icon" src="/Teebee-site/assets/img/rain-coin.svg" alt="Rain Coin" loading="lazy" width="20" height="20" decoding="async" />
                    <?php echo number_format(isset($entry['wagered']) ? $entry['wagered'] : 0, 2); ?>
                  </span>
                </td>
                <td class="reward-column">
                  <?php $reward = isset($rewards[$rank]) ? $rewards[$rank] : 0; ?>
                  <?php if ($reward > 0): ?>
                    <div class="reward-badge">
                      <img src="/Teebee-site/assets/img/rain-coin.svg" alt="Coins" width="16" height="16" loading="lazy" decoding="async" />
                      <span><?php echo $reward; ?> coins</span>
                    </div>
                  <?php else: ?>
                    <span class="muted">—</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>
    </main>
  </body>
</html>

