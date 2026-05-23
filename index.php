<?php
// Teebee landing (PHP front file)
$discord = 'https://discord.gg/W8Cs9tMPvQ';
$x = 'https://x.com/TEEBEEGAMBLES';
$kick = 'https://kick.com/teebee3016';
$yt = 'https://youtube.com/@teebee3016?si=WUtfHs0g0NQUOxxx';
$img = '/assets/img/teebeepfp.png';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Teebee — a bee-themed gambling and CS creator hub with Discord, X, Kick, and YouTube links." />
    <title>Teebee | Hive &amp; Hustle</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/assets/css/styles.css" />
  </head>
  <body>
    <div class="ambient ambient-left" aria-hidden="true"></div>
    <div class="ambient ambient-right" aria-hidden="true"></div>

    <main class="page-shell">
      <section class="hero card">
        <div class="hero-copy">
          <p class="eyebrow">CS drops • giveaways • live chaos</p>
          <h1>Teebee</h1>
          <p class="lead">A bee-branded creator with a gambling and CS-style identity built around giveaways, live streams, and clipped moments from the hive.</p>

          <div class="cta-row">
            <a class="button button-primary" href="#tabs">Jump to socials</a>
            <a class="button button-secondary" href="#about">About the hive</a>
          </div>

          <div class="stats" aria-label="Creator highlights">
            <div>
              <strong>Giveaways</strong>
              <span>Discord + X</span>
            </div>
            <div>
              <strong>Kick</strong>
              <span>Live streams</span>
            </div>
            <div>
              <strong>CS / Clips</strong>
              <span>YouTube highlights</span>
            </div>
          </div>
        </div>

        <div class="hero-art" aria-hidden="true">
          <div class="profile-wrap">
            <img class="profile-photo" src="<?php echo $img; ?>" alt="Teebee profile picture" />
            <div class="profile-glow"></div>
          </div>
          <div class="bee-fly">🐝</div>
          <div class="hive-ring hive-ring-one"></div>
          <div class="hive-ring hive-ring-two"></div>
          <div class="hive-card">
            <p>Honey-fueled creator identity</p>
            <h2>Buzz. Queue. Clip.</h2>
          </div>
        </div>
      </section>

      <section class="card tabs-section" id="tabs">
        <div class="section-heading">
          <p class="eyebrow">Social tabs</p>
          <h2>Pick the channel you want</h2>
          <p>Discord and X are for giveaways and updates, Kick is for streaming, and YouTube is for clips and highlights.</p>
        </div>

        <div class="tab-bar">
          <button class="tab-button is-active" id="tab-discord" data-tab="discord">Discord</button>
          <button class="tab-button" id="tab-x" data-tab="x">X</button>
          <button class="tab-button" id="tab-kick" data-tab="kick">Kick</button>
          <button class="tab-button" id="tab-yt" data-tab="yt">YT</button>
          <button class="tab-button" id="tab-rain" data-tab="rain">Rain.gg</button>
        </div>

        <div class="tab-panels">
          <article class="tab-panel is-active" id="panel-discord" data-panel="discord">
            <div>
              <p class="panel-label">Giveaways + community</p>
              <h3>Join the Discord hive</h3>
              <p>This is where the giveaways, alerts, and hive chatter happen. Join in if you want the fastest updates and the best chances at drops.</p>
            </div>
            <a class="button button-primary" href="<?php echo $discord; ?>" target="_blank" rel="noreferrer">Open Discord</a>
          </article>

          <article class="tab-panel" id="panel-x" data-panel="x" hidden>
            <div>
              <p class="panel-label">Giveaway alerts</p>
              <h3>Follow Teebee on X</h3>
              <p>Fast announcements, giveaway posts, and quick updates when the hive goes live or drops something new.</p>
            </div>
            <a class="button button-primary" href="<?php echo $x; ?>" target="_blank" rel="noreferrer">Open X</a>
          </article>

          <article class="tab-panel" id="panel-kick" data-panel="kick" hidden>
            <div>
              <p class="panel-label">Live streaming</p>
              <h3>Watch Teebee on Kick</h3>
              <p>This is the main live channel for streams, reactions, and the gambling/cs energy that powers the brand.</p>
            </div>
            <a class="button button-primary" href="<?php echo $kick; ?>" target="_blank" rel="noreferrer">Open Kick</a>
          </article>

          <article class="tab-panel" id="panel-yt" data-panel="yt" hidden>
            <div>
              <p class="panel-label">Clips & highlights</p>
              <h3>Subscribe on YouTube</h3>
              <p>The less-important channel, but still where you’ll find clips, highlights, and the best moments after streams.</p>
            </div>
            <a class="button button-primary" href="<?php echo $yt; ?>" target="_blank" rel="noreferrer">Open YouTube</a>
          </article>

          <article class="tab-panel" id="panel-rain" data-panel="rain" hidden>
            <div>
              <p class="panel-label">CS lootbox</p>
              <h3>Rain.gg</h3>
              <p>Visit the dedicated Rain.gg info page to learn how the platform works, find safety tips, and see how Teebee uses it during streams.</p>
            </div>
            <a class="button button-primary" href="/rain.php" target="_self" rel="noreferrer">Open Rain.gg page</a>
          </article>
        </div>
      </section>

      <section class="card about-section" id="about">
        <div>
          <p class="eyebrow">About Teebee</p>
          <h2>A bee-themed creator with CS energy</h2>
        </div>
        <p>Built to feel darker, cleaner, and more like a gambling/cs creator hub: bold contrast, gold accents, giveaway-forward socials, and a profile that stands out fast.</p>
        <p class="disclaimer">18+ only. If gambling is part of the content, keep it responsible and follow your local laws and platform rules.</p>
      </section>
    </main>

    <script src="/assets/js/script.js"></script>
  </body>
</html>

