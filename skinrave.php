<?php
$apiUrl = 'https://api.skinrave.gg/affiliates/public/applicants?token=3e179631-fbe3-4865-ac74-8213a718a3ac&skip=0&take=10&order=DESC&from=2026-05-29T10:29:47.028Z&to=2026-06-05T10:29:47.028Z';
$cacheFile = sys_get_temp_dir() . '/teebee_skinrave_applicants_cache.json';
$cacheExpiry = 15;

$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';
$isApiRequest = isset($_GET['api']) && $_GET['api'] === '1';

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

function skinraveNumber($value, $decimals = 2)
{
    return number_format((float) $value, $decimals, '.', ',');
}

function skinraveFormatDate($value)
{
    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $value;
    }

    return date('M j, Y g:i A', $timestamp);
}

if ($isApiRequest) {
    $payload = skinraveFetchApplicants($apiUrl);

    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo json_encode($payload);
    exit;
}

$payload = null;
if (!$forceRefresh && file_exists($cacheFile) && time() - filemtime($cacheFile) < $cacheExpiry) {
    $payload = json_decode(file_get_contents($cacheFile), true);
}

if (!is_array($payload)) {
    $payload = skinraveFetchApplicants($apiUrl);
    if (!isset($payload['error'])) {
        file_put_contents($cacheFile, json_encode($payload));
    }
}

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$entries = [];
$totalCount = 0;
$filteredCount = 0;

if (!isset($payload['error'])) {
    $totalCount = isset($payload['totalCount']) ? (int) $payload['totalCount'] : 0;
    $filteredCount = isset($payload['filteredCount']) ? (int) $payload['filteredCount'] : 0;
    if (isset($payload['list']) && is_array($payload['list'])) {
        $entries = $payload['list'];
    }
}

$hasError = isset($payload['error']);
$home = '/Teebee-site/index.php';
$payouts = [
    1 => 100,
    2 => 65,
    3 => 50,
    4 => 35,
    5 => 25,
    6 => 15,
    7 => 10,
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Teebee.gg Skinrave applicants and free daily rewards." />
    <title>Teebee.gg | Skinrave</title>
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
            <img src="/Teebee-site/assets/img/Teebeeskinrave.png" alt="Skinrave free daily rewards" class="leaderboard-banner skinrave-banner" />
            <div class="leaderboard-actions">
                <a class="button button-primary" href="https://skinrave.gg" target="_blank" rel="noreferrer">Open Skinrave</a>
                <a class="button button-secondary" href="<?php echo $home; ?>">Back home</a>
            </div>
        </aside>

        <section class="card tabs-section" style="margin-top: 24px;">
            <div class="section-heading">
                <p class="eyebrow">Skinrave affiliate applicants</p>
                <h2 class="animate-title">Free Daily Rewards</h2>
                <p>Latest applicants pulled from the Skinrave public affiliates endpoint.</p>
                <?php if (!$hasError): ?>
                    <p class="lead" style="margin-top: 14px;">Showing <span id="skinrave-filtered"><?php echo htmlspecialchars((string) $filteredCount); ?></span> of <span id="skinrave-total"><?php echo htmlspecialchars((string) $totalCount); ?></span> applicants in the requested range.</p>
                <?php endif; ?>
            </div>

            <div class="leaderboard-cards-container" id="skinrave-cards">
                <?php if ($hasError): ?>
                    <div class="error-box" style="margin-top: 18px;">
                        <strong>Oops!</strong> Could not fetch Skinrave applicant data. <?php echo htmlspecialchars($payload['error']); ?>
                    </div>
                <?php elseif (empty($entries)): ?>
                    <div class="empty-box" id="skinrave-empty">
                        <p>No Skinrave applicants were returned for this time window.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($entries as $index => $entry): ?>
                        <?php
                        $user = isset($entry['user']) && is_array($entry['user']) ? $entry['user'] : [];
                        $username = isset($user['username']) && $user['username'] !== '' ? $user['username'] : 'Unknown';
                        $avatar = isset($user['avatarUrl']) && $user['avatarUrl'] !== '' ? $user['avatarUrl'] : 'https://avatars.steamstatic.com/ee6f3e2b5cc32b7c5f8f3cb9a0f0f1f2e8f3f5a0_full.jpg';
                        $level = isset($user['level']) ? (int) $user['level'] : 0;
                        $tier = isset($user['levelTier']) && $user['levelTier'] !== '' ? $user['levelTier'] : 'UNKNOWN';
                        $rank = $index + 1;
                        $wagered = isset($entry['wagered']) ? $entry['wagered'] : 0;
                        $prize = isset($payouts[$rank]) ? $payouts[$rank] : null;
                        ?>
                        <article class="leaderboard-card">
                            <div class="leaderboard-card-left">
                                <span class="rank-badge <?php echo $rank <= 3 ? 'rank-' . $rank : ''; ?> leaderboard-card-rank">
                                    <?php echo htmlspecialchars((string) $rank); ?>
                                </span>
                                <div class="leaderboard-card-player">
                                    <div class="player-avatar leaderboard-card-avatar">
                                        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="<?php echo htmlspecialchars($username); ?>" loading="lazy" />
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($username); ?></strong>
                                        <div style="margin-top: 4px; color: var(--muted); font-size: 0.92rem;">
                                            Level <?php echo htmlspecialchars((string) $level); ?> &middot; <?php echo htmlspecialchars($tier); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="leaderboard-card-right">
                                <div class="leaderboard-stat-block">
                                    <p class="leaderboard-stat-label">Wagered</p>
                                    <span class="stat-value"><?php echo htmlspecialchars(skinraveNumber($wagered)); ?></span>
                                </div>

                                <?php if ($prize !== null): ?>
                                    <div class="leaderboard-stat-block">
                                        <p class="leaderboard-stat-label">Prize</p>
                                        <span class="stat-value"><?php echo htmlspecialchars((string) $prize); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <script>
    (function () {
        var titleElements = document.querySelectorAll('.animate-title');

        titleElements.forEach(function (titleEl) {
            var text = titleEl.textContent;
            titleEl.textContent = '';

            text.split('').forEach(function (letter, index) {
                var span = document.createElement('span');
                span.textContent = letter === ' ' ? '\u00A0' : letter;
                titleEl.appendChild(span);

                requestAnimationFrame(function () {
                    span.style.animation = 'letterFade 0.8s ease-in-out ' + (index * 0.08) + 's both';
                });
            });
        });
    }());
    </script>
    <script>
    (function () {
        var apiUrl = '/Teebee-site/skinrave.php?api=1';
        var payouts = {1: 100, 2: 65, 3: 50, 4: 35, 5: 25, 6: 15, 7: 10};
        var cardsEl = document.getElementById('skinrave-cards');
        var filteredEl = document.getElementById('skinrave-filtered');
        var totalEl = document.getElementById('skinrave-total');
        var numberFormat = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderCards(entries) {
            if (!cardsEl) {
                return;
            }

            if (!entries.length) {
                cardsEl.innerHTML = '<div class="empty-box"><p>No Skinrave applicants were returned for this time window.</p></div>';
                return;
            }

            cardsEl.innerHTML = entries.map(function (entry, index) {
                var user = entry.user || {};
                var username = user.username || 'Unknown';
                var avatar = user.avatarUrl || 'https://avatars.steamstatic.com/ee6f3e2b5cc32b7c5f8f3cb9a0f0f1f2e8f3f5a0_full.jpg';
                var level = user.level || 0;
                var tier = user.levelTier || 'UNKNOWN';
                var wagered = Number(entry.wagered || 0);
                var rank = index + 1;
                var prize = Object.prototype.hasOwnProperty.call(payouts, rank) ? payouts[rank] : null;

                return '' +
                    '<article class="leaderboard-card">' +
                        '<div class="leaderboard-card-left">' +
                            '<span class="rank-badge ' + (rank <= 3 ? 'rank-' + rank : '') + ' leaderboard-card-rank">' + rank + '</span>' +
                            '<div class="leaderboard-card-player">' +
                                '<div class="player-avatar leaderboard-card-avatar">' +
                                    '<img src="' + escapeHtml(avatar) + '" alt="' + escapeHtml(username) + '" loading="lazy" />' +
                                '</div>' +
                                '<div>' +
                                    '<strong>' + escapeHtml(username) + '</strong>' +
                                    '<div style="margin-top: 4px; color: var(--muted); font-size: 0.92rem;">Level ' + escapeHtml(level) + ' &middot; ' + escapeHtml(tier) + '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="leaderboard-card-right">' +
                            '<div class="leaderboard-stat-block">' +
                                '<p class="leaderboard-stat-label">Wagered</p>' +
                                '<span class="stat-value">' + numberFormat.format(wagered) + '</span>' +
                            '</div>' +
                            (prize !== null ? (
                                '<div class="leaderboard-stat-block">' +
                                    '<p class="leaderboard-stat-label">Prize</p>' +
                                    '<span class="stat-value">' + prize + '</span>' +
                                '</div>'
                            ) : '') +
                        '</div>' +
                    '</article>';
            }).join('');
        }

        async function refreshSkinrave() {
            try {
                var response = await fetch(apiUrl + '&ts=' + Date.now(), { cache: 'no-store' });
                var data = await response.json();

                if (data.error) {
                    if (cardsEl) {
                        cardsEl.innerHTML = '<div class="error-box"><strong>Oops!</strong> Could not fetch Skinrave applicant data. ' + escapeHtml(data.error) + '</div>';
                    }
                    return;
                }

                if (filteredEl) {
                    filteredEl.textContent = data.filteredCount || 0;
                }

                if (totalEl) {
                    totalEl.textContent = data.totalCount || 0;
                }

                renderCards(Array.isArray(data.list) ? data.list : []);
            } catch (error) {
                if (cardsEl) {
                    cardsEl.innerHTML = '<div class="error-box"><strong>Oops!</strong> Could not refresh Skinrave data.</div>';
                }
            }
        }

        if (cardsEl) {
            refreshSkinrave();
            setInterval(refreshSkinrave, 10000);
        }
    }());
    </script>
</body>
</html>
