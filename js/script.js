const tabButtons = Array.from(document.querySelectorAll('[data-tab]'));
const tabPanels = Array.from(document.querySelectorAll('[data-panel]'));
const leaderboardContainer = document.getElementById('leaderboard-cards');

function activateTab(tabName) {
  tabButtons.forEach((button) => {
    const isActive = button.dataset.tab === tabName;
    button.classList.toggle('is-active', isActive);
    button.setAttribute('aria-selected', String(isActive));
    button.tabIndex = isActive ? 0 : -1;
  });

  tabPanels.forEach((panel) => {
    const isActive = panel.dataset.panel === tabName;
    panel.classList.toggle('is-active', isActive);
    panel.hidden = !isActive;
  });
}

tabButtons.forEach((button) => {
  button.addEventListener('click', () => activateTab(button.dataset.tab));

  button.addEventListener('keydown', (event) => {
    const currentIndex = tabButtons.indexOf(button);
    let nextIndex = currentIndex;

    if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
      nextIndex = (currentIndex + 1) % tabButtons.length;
    }

    if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
      nextIndex = (currentIndex - 1 + tabButtons.length) % tabButtons.length;
    }

    if (nextIndex !== currentIndex) {
      event.preventDefault();
      tabButtons[nextIndex].focus();
      activateTab(tabButtons[nextIndex].dataset.tab);
    }
  });
});

activateTab('discord');

// Ensure the floating skin gallery is attached to the document body so transforms on
// parent containers don't affect the fixed positioning. This prevents the skins
// from moving with the page when the user scrolls.
const skinGallery = document.querySelector('.skin-gallery');
if (skinGallery && skinGallery.parentElement !== document.body) {
  document.body.appendChild(skinGallery);
  // make sure the gallery is fixed and covers the viewport
  skinGallery.style.position = 'fixed';
  skinGallery.style.inset = '0';
  skinGallery.style.pointerEvents = 'none';
}

const skinItems = Array.from(document.querySelectorAll('.skin-item'));

// Prepare items with independent positions and speeds. Each item wraps when it goes off-screen
const items = skinItems.map((el, index) => ({
  el,
  index,
  width: el.offsetWidth || 120,
  height: el.offsetHeight || 120,
  // start at random horizontal position
  x: Math.random() * window.innerWidth,
  // vertical band: spread items down the page
  baseY: window.innerHeight * (0.18 + (index * 0.12)),
  // alternate directions so some move left, some right
  dir: index % 2 === 0 ? -1 : 1,
  // slightly different speeds (increased base for faster motion)
  speed: 80 + (index * 18),
  offset: Math.random() * Math.PI * 2
}));

let lastTime = Date.now();

function updateSizes() {
  items.forEach(it => {
    it.width = it.el.offsetWidth || 120;
    it.height = it.el.offsetHeight || 120;
    // keep baseY within viewport on resize
    it.baseY = Math.max(60, Math.min(window.innerHeight - 60, window.innerHeight * (0.18 + (it.index * 0.12))));
  });
}

window.addEventListener('resize', updateSizes);

function moveSkins() {
  const now = Date.now();
  const dt = (now - lastTime) / 1000; // seconds since last frame
  lastTime = now;

  items.forEach((it) => {
    // update horizontal position
    it.x += it.dir * it.speed * dt * (1 + Math.sin((now / 1000) + it.offset) * 0.25);

    // wrap when off-screen: disappear at edge and reappear on opposite side
    if (it.x < -it.width) {
      it.x = window.innerWidth + it.width;
    }
    if (it.x > window.innerWidth + it.width) {
      it.x = -it.width;
    }

    // gentle vertical bobbing
    const bob = Math.sin((now / 800) + it.offset) * 18;
    const y = it.baseY + bob;

    it.el.style.position = 'fixed';
    it.el.style.left = it.x + 'px';
    it.el.style.top = y + 'px';
    it.el.style.zIndex = '5';
    it.el.style.transform = 'translate(-50%, -50%)';
  });

  requestAnimationFrame(moveSkins);
}

updateSizes();
moveSkins();


const titleElements = document.querySelectorAll('.animate-title');
titleElements.forEach((titleEl) => {
  const text = titleEl.textContent;
  titleEl.textContent = '';
  
  text.split('').forEach((letter, index) => {
    const span = document.createElement('span');
    // preserve spaces
    span.textContent = letter === ' ' ? '\u00A0' : letter;
    // append so initial CSS (opacity/transform) applies
    titleEl.appendChild(span);
    // apply animation on next frame so browser recognizes transition from initial state
    requestAnimationFrame(() => {
      span.style.animation = `letterFade 0.8s ease-in-out ${index * 0.08}s both`;
    });
  });
});

// Render the Rain leaderboard from the local proxy API.
async function updateLeaderboardStatic() {
  if (!leaderboardContainer) {
    return;
  }

  try {
    const response = await fetch('/Teebee-site/api/rain.php?ts=' + Date.now(), { cache: 'no-store' });
    const data = await response.json();

    if (data.error) {
      console.error('API Error:', data.error);
      return;
    }

    if (!data.entries || data.entries.length === 0) {
      console.warn('No entries in response');
      return;
    }

    let html = '';
    data.entries.forEach((entry, index) => {
      const rank = index + 1;
      const prize = (entry.prize || 0) / 100;
      const avatar = (entry.avatar && entry.avatar.small) ? entry.avatar.small : 'https://cdn.rain.gg/images/avatar/unknown_small.png';
      const username = entry.username || 'N/A';
      const wagered = entry.wagered || 0;

      html += `
        <div class="leaderboard-card">
          <div class="leaderboard-card-left">
            <span class="rank-badge rank-${rank} leaderboard-card-rank">
              ${rank}
            </span>
            <div class="leaderboard-card-player">
              <div class="player-avatar leaderboard-card-avatar">
                <img src="${avatar}" alt="${username}" />
              </div>
              <strong>${username}</strong>
            </div>
          </div>

          <div class="leaderboard-card-right">
            <div class="leaderboard-stat-block">
              <p class="leaderboard-stat-label">Total Wagered</p>
              <span class="stat-value">
                <img class="currency-icon" src="/Teebee-site/assets/img/rain-coin.svg" alt="Rain Coin" loading="lazy" width="18" height="18" decoding="async" />
                ${wagered.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </span>
            </div>

            <div class="leaderboard-stat-block">
              <p class="leaderboard-stat-label">Reward</p>
              ${prize > 0 ? `
                <div class="leaderboard-reward-badge">
                  <img src="/Teebee-site/assets/img/rain-coin.svg" alt="Coins" width="16" height="16" loading="lazy" decoding="async" />
                  <span>${prize.toLocaleString('en-US')}</span>
                </div>
              ` : '<span class="muted">—</span>'}
            </div>
          </div>
        </div>
      `;
    });

    leaderboardContainer.innerHTML = html;
  } catch (err) {
    console.error('Failed to update leaderboard (static):', err);
  }
}

if (leaderboardContainer) {
  updateLeaderboardStatic();
  setInterval(updateLeaderboardStatic, 10000);
}

// Countdown: if the page includes #leaderboard-countdown with data-ends-at, start a timer
function startLeaderboardCountdown() {
  const el = document.getElementById('leaderboard-countdown');
  if (!el) return;
  const endsAt = el.dataset.endsAt;
  if (!endsAt) return;

  // prefer a dedicated time element inside the container
  let timeEl = el.querySelector('.countdown-time');
  if (!timeEl) {
    timeEl = document.createElement('span');
    timeEl.className = 'countdown-time';
    el.appendChild(timeEl);
  }

  const endDate = new Date(endsAt);
  if (isNaN(endDate)) return;

  const pad = (n) => String(n).padStart(2, '0');

  let timer = null;
  function update() {
    const now = new Date();
    let diff = Math.max(0, endDate - now);
    if (diff <= 0) {
      el.classList.add('countdown-ended');
      // set label and time to show ended state
      const label = el.querySelector('.countdown-label');
      if (label) label.textContent = 'Ended';
      timeEl.textContent = '';
      if (timer) clearInterval(timer);
      return;
    }

    const days = Math.floor(diff / 86400000);
    diff = diff % 86400000;
    const hours = Math.floor(diff / 3600000);
    diff = diff % 3600000;
    const mins = Math.floor(diff / 60000);
    const secs = Math.floor((diff % 60000) / 1000);

    // Build segmented display
    const hh = pad(hours);
    const mm = pad(mins);
    const ss = pad(secs);

    // Helper to set segment value and animate on change
    function setSegment(segEl, value) {
      if (!segEl) return;
      const valEl = segEl.querySelector('.value');
      if (!valEl) return;
      if (valEl.textContent === value) return;

      // Smooth number-only animation: fade out, update, fade in on the number element only.
      try {
        if (valEl._anim && valEl._anim.cancel) valEl._anim.cancel();

        // Fade out (translate slightly for perception), no container changes
        const fadeOut = valEl.animate([
          { opacity: 1, transform: 'translateY(0)' },
          { opacity: 0, transform: 'translateY(-4px)' }
        ], { duration: 140, easing: 'cubic-bezier(.22,.9,.28,1)', fill: 'forwards' });

        fadeOut.onfinish = function () {
          valEl.textContent = value;
          // Fade in smoothly
          valEl._anim = valEl.animate([
            { opacity: 0, transform: 'translateY(4px)' },
            { opacity: 1, transform: 'translateY(0)' }
          ], { duration: 240, easing: 'cubic-bezier(.22,.9,.28,1)', fill: 'forwards' });
        };

        valEl._anim = fadeOut;
      } catch (e) {
        // Fallback: quick opacity swap with requestAnimationFrame
        valEl.style.transition = 'opacity 220ms cubic-bezier(.22,.9,.28,1), transform 220ms cubic-bezier(.22,.9,.28,1)';
        valEl.style.opacity = '0';
        valEl.style.transform = 'translateY(-4px)';
        setTimeout(() => {
          valEl.textContent = value;
          valEl.style.opacity = '1';
          valEl.style.transform = 'translateY(0)';
          setTimeout(() => {
            valEl.style.transition = '';
          }, 260);
        }, 140);
      }
    }

    // Create segments container if empty
    if (!timeEl.querySelector('.countdown-segments')) {
      timeEl.innerHTML = '';
      const container = document.createElement('span');
      container.className = 'countdown-segments';
      timeEl.appendChild(container);
    }

    const container = timeEl.querySelector('.countdown-segments');

    // If days present show Days + HH:MM:SS segments; otherwise show HH MM SS
    if (days > 0) {
      // ensure days segment exists
      let daysSeg = container.querySelector('[data-seg="days"]');
      if (!daysSeg) {
        daysSeg = document.createElement('span');
        daysSeg.className = 'countdown-segment';
        daysSeg.dataset.seg = 'days';
        daysSeg.innerHTML = '<span class="value"></span><span class="unit">Days</span>';
        container.appendChild(daysSeg);
        container.appendChild(Object.assign(document.createElement('span'), {className:'countdown-separator', textContent: ':'}));
      }
      setSegment(daysSeg, String(days));

      // hours segment
      let hSeg = container.querySelector('[data-seg="hours"]');
      if (!hSeg) {
        hSeg = document.createElement('span');
        hSeg.className = 'countdown-segment';
        hSeg.dataset.seg = 'hours';
        hSeg.innerHTML = '<span class="value"></span><span class="unit">HH</span>';
        container.appendChild(hSeg);
        container.appendChild(Object.assign(document.createElement('span'), {className:'countdown-separator', textContent: ':'}));
      }
      setSegment(hSeg, hh);

      let mSeg = container.querySelector('[data-seg="mins"]');
      if (!mSeg) {
        mSeg = document.createElement('span');
        mSeg.className = 'countdown-segment';
        mSeg.dataset.seg = 'mins';
        mSeg.innerHTML = '<span class="value"></span><span class="unit">MM</span>';
        container.appendChild(mSeg);
        container.appendChild(Object.assign(document.createElement('span'), {className:'countdown-separator', textContent: ':'}));
      }
      setSegment(mSeg, mm);

      let sSeg = container.querySelector('[data-seg="secs"]');
      if (!sSeg) {
        sSeg = document.createElement('span');
        sSeg.className = 'countdown-segment';
        sSeg.dataset.seg = 'secs';
        sSeg.innerHTML = '<span class="value"></span><span class="unit">SS</span>';
        container.appendChild(sSeg);
      }
      setSegment(sSeg, ss);

    } else {
      // remove any days segment if present
      const oldDays = container.querySelector('[data-seg="days"]');
      if (oldDays) {
        // remove separator after it too
        const sep = oldDays.nextElementSibling;
        if (sep && sep.classList.contains('countdown-separator')) sep.remove();
        oldDays.remove();
      }

      // Hours
      let hSeg = container.querySelector('[data-seg="hours"]');
      if (!hSeg) {
        hSeg = document.createElement('span');
        hSeg.className = 'countdown-segment';
        hSeg.dataset.seg = 'hours';
        hSeg.innerHTML = '<span class="value"></span><span class="unit">Hours</span>';
        container.appendChild(hSeg);
        container.appendChild(Object.assign(document.createElement('span'), {className:'countdown-separator', textContent: ':'}));
      }
      setSegment(hSeg, hh);

      let mSeg = container.querySelector('[data-seg="mins"]');
      if (!mSeg) {
        mSeg = document.createElement('span');
        mSeg.className = 'countdown-segment';
        mSeg.dataset.seg = 'mins';
        mSeg.innerHTML = '<span class="value"></span><span class="unit">Mins</span>';
        container.appendChild(mSeg);
        container.appendChild(Object.assign(document.createElement('span'), {className:'countdown-separator', textContent: ':'}));
      }
      setSegment(mSeg, mm);

      let sSeg = container.querySelector('[data-seg="secs"]');
      if (!sSeg) {
        sSeg = document.createElement('span');
        sSeg.className = 'countdown-segment';
        sSeg.dataset.seg = 'secs';
        sSeg.innerHTML = '<span class="value"></span><span class="unit">Secs</span>';
        container.appendChild(sSeg);
      }
      setSegment(sSeg, ss);
    }
  }

  update();
  timer = setInterval(() => {
    if (!document.hidden) update();
  }, 1000);
}

// Start countdown immediately (script is at end of page)
startLeaderboardCountdown();

