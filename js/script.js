const titleElements = document.querySelectorAll('.animate-title');
titleElements.forEach((titleEl) => {
  const text = titleEl.textContent;
  titleEl.textContent = '';
  text.split('').forEach((letter, index) => {
    const span = document.createElement('span');
    span.textContent = letter === ' ' ? '\u00A0' : letter;
    titleEl.appendChild(span);
    requestAnimationFrame(() => {
      span.style.animation = `letterFade 0.8s ease-in-out ${index * 0.08}s both`;
    });
  });
});

const tabButtons = Array.from(document.querySelectorAll('[data-tab]'));
const tabPanels = Array.from(document.querySelectorAll('[data-panel]'));

function activateHomeTab(tabName) {
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
  button.addEventListener('click', () => {
    if (button.id !== 'tab-rain' && button.id !== 'tab-skinrave') {
      activateHomeTab(button.dataset.tab);
    }
  });
  button.addEventListener('keydown', (event) => {
    if (button.id === 'tab-rain' || button.id === 'tab-skinrave') return;
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
      activateHomeTab(tabButtons[nextIndex].dataset.tab);
    }
  });
});

const skinGallery = document.querySelector('.skin-gallery');
const skinItems = Array.from(document.querySelectorAll('.skin-item'));
let items = [];
let lastTime = Date.now();

if (skinGallery && skinItems.length > 0) {
  if (skinGallery.parentElement !== document.body) {
    document.body.appendChild(skinGallery);
    skinGallery.style.position = 'fixed';
    skinGallery.style.inset = '0';
    skinGallery.style.pointerEvents = 'none';
  }
  items = skinItems.map((el, index) => ({
    el,
    index,
    width: el.offsetWidth || 120,
    height: el.offsetHeight || 120,
    x: Math.random() * window.innerWidth,
    baseY: window.innerHeight * (0.18 + (index * 0.12)),
    dir: index % 2 === 0 ? -1 : 1,
    speed: 80 + (index * 18),
    offset: Math.random() * Math.PI * 2
  }));
}

function updateSizes() {
  if (items.length === 0) return;
  items.forEach(it => {
    it.width = it.el.offsetWidth || 120;
    it.height = it.el.offsetHeight || 120;
    it.baseY = Math.max(60, Math.min(window.innerHeight - 60, window.innerHeight * (0.18 + (it.index * 0.12))));
  });
}

function moveSkins() {
  if (items.length === 0) return;
  const now = Date.now();
  const dt = (now - lastTime) / 1000;
  lastTime = now;
  items.forEach((it) => {
    it.x += it.dir * it.speed * dt * (1 + Math.sin((now / 1000) + it.offset) * 0.25);
    if (it.x < -it.width) {
      it.x = window.innerWidth + it.width;
    }
    if (it.x > window.innerWidth + it.width) {
      it.x = -it.width;
    }
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

if (skinGallery && skinItems.length > 0) {
  window.addEventListener('resize', updateSizes);
  updateSizes();
  moveSkins();
}

let countdownTimer = null;
let currentActiveTab = 'rain';
let globalEndsAt = null;

const countdownEl = document.getElementById('leaderboard-countdown');
const progressBarEl = countdownEl ? countdownEl.querySelector('.progress-bar') : null;
const numberFormat = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function pad(v) { return String(v).padStart(2, '0'); }

function updateSegment(id, value) {
  const segEl = document.getElementById(id);
  if (!segEl) return;
  const valEl = segEl.querySelector('.value');
  if (!valEl) return;
  if (valEl.textContent !== value) {
    valEl.textContent = value;
  }
}

function startCountdown(endsAt) {
  if (!countdownEl || !endsAt) return;
  const endDate = new Date(endsAt);
  if (isNaN(endDate.getTime())) return;
  const totalMs = Math.max(1, endDate - new Date());
  function update() {
    const now = new Date();
    const diff = Math.max(0, endDate - now);
    const labelEl = countdownEl.querySelector('.countdown-label');
    if (labelEl) {
      labelEl.textContent = currentActiveTab === 'rain' ? 'LEADERBOARDS ENDS IN' : 'SKINRAVE ENDS IN';
    }
    if (diff <= 0) {
      if (labelEl) labelEl.textContent = 'Ended';
      updateSegment('cd-days', '00');
      updateSegment('cd-hours', '00');
      updateSegment('cd-minutes', '00');
      updateSegment('cd-seconds', '00');
      if (progressBarEl) progressBarEl.style.width = '0%';
      clearInterval(countdownTimer);
      return;
    }
    let tempDiff = diff;
    const days = Math.floor(tempDiff / 86400000); tempDiff %= 86400000;
    const hours = Math.floor(tempDiff / 3600000); tempDiff %= 3600000;
    const minutes = Math.floor(tempDiff / 60000);
    const seconds = Math.floor((tempDiff % 60000) / 1000);
    updateSegment('cd-days', pad(days));
    updateSegment('cd-hours', pad(hours));
    updateSegment('cd-minutes', pad(minutes));
    updateSegment('cd-seconds', pad(seconds));
    if (progressBarEl) {
      const percent = Math.max(0, Math.min(100, Math.round((1 - (diff / totalMs)) * 100)));
      progressBarEl.style.width = percent + '%';
    }
  }
  if (countdownTimer) clearInterval(countdownTimer);
  update();
  countdownTimer = setInterval(update, 1000);
}

function escapeHtml(value) {
  return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
}

async function fetchRainLeaderboard() {
  const cardsEl = document.getElementById('rain-cards');
  if (!cardsEl) return;
  try {
    const response = await fetch('api/rain.php?ts=' + Date.now(), { cache: 'no-store' });
    const data = await response.json();
    if (data.error) {
      cardsEl.innerHTML = `<div class="error-box"><strong>Error:</strong> ${escapeHtml(data.error)}</div>`;
      return;
    }
    const entries = Array.isArray(data.entries) ? data.entries : [];
    if (!entries.length) {
      cardsEl.innerHTML = '<div class="empty-box"><p>No affiliate data available yet.</p></div>';
      return;
    }
    cardsEl.innerHTML = entries.map((entry, index) => {
      const rank = index + 1;
      const prize = Number(entry.prize || 0) / 100;
      const avatar = (entry.avatar && entry.avatar.small) ? entry.avatar.small : 'https://cdn.rain.gg/images/avatar/unknown_small.png';
      const username = entry.username || 'N/A';
      const wagered = Number(entry.wagered || 0);
      return `
        <div class="leaderboard-card">
          <div class="leaderboard-card-left">
            <span class="rank-badge rank-${rank} leaderboard-card-rank">${rank}</span>
            <div class="leaderboard-card-player">
              <div class="player-avatar leaderboard-card-avatar">
                <img src="${escapeHtml(avatar)}" alt="${escapeHtml(username)}" />
              </div>
              <strong>${escapeHtml(username)}</strong>
            </div>
          </div>
          <div class="leaderboard-card-right">
            <div class="leaderboard-stat-block">
              <p class="leaderboard-stat-label">Total Wagered</p>
              <span class="stat-value">
                <img class="currency-icon" src="img/rain-coin.svg" alt="Coin" width="18" height="18" />
                ${numberFormat.format(wagered)}
              </span>
            </div>
            <div class="leaderboard-stat-block">
              <p class="leaderboard-stat-label">Reward</p>
              ${prize > 0 ? `
                <div class="leaderboard-reward-badge">
                  <img src="img/rain-coin.svg" alt="Coins" width="16" height="16" />
                  <span>${prize.toLocaleString('en-US')}</span>
                </div>
              ` : '<span class="muted">—</span>'}
            </div>
          </div>
        </div>`;
    }).join('');
    if (data.endsAt) {
      globalEndsAt = data.endsAt;
      startCountdown(globalEndsAt);
    }
  } catch (err) {
    cardsEl.innerHTML = '<div class="error-box"><strong>Oops!</strong> Could not fetch Rain data.</div>';
  }
}

async function fetchSkinraveLeaderboard() {
  const cardsEl = document.getElementById('skinrave-cards');
  const filteredEl = document.getElementById('skinrave-filtered');
  const totalEl = document.getElementById('skinrave-total');
  if (!cardsEl) return;
  try {
    const response = await fetch('api/skinrave.php?ts=' + Date.now(), { cache: 'no-store' });
    const data = await response.json();
    if (data.error) {
      cardsEl.innerHTML = `<div class="error-box"><strong>Error:</strong> ${escapeHtml(data.error)}</div>`;
      return;
    }
    const entries = Array.isArray(data.entries) ? data.entries : [];
    if (filteredEl) filteredEl.textContent = entries.length;
    if (totalEl) totalEl.textContent = data.total || entries.length;
    if (!entries.length) {
      cardsEl.innerHTML = '<div class="empty-box"><p>No applicants found.</p></div>';
      return;
    }
    cardsEl.innerHTML = entries.map((entry, index) => {
      const rank = index + 1;
      const username = entry.username || 'N/A';
      return `
        <div class="leaderboard-card">
          <div class="leaderboard-card-left">
            <span class="rank-badge rank-${rank} leaderboard-card-rank">${rank}</span>
            <div class="leaderboard-card-player">
              <strong>${escapeHtml(username)}</strong>
            </div>
          </div>
        </div>`;
    }).join('');
  } catch (err) {
    cardsEl.innerHTML = '<div class="error-box"><strong>Oops!</strong> Could not fetch Skinrave data.</div>';
  }
}

function updateAllData() {
  fetchRainLeaderboard();
  fetchSkinraveLeaderboard();
}

const tabRain = document.getElementById('tab-rain');
const tabSkin = document.getElementById('tab-skinrave');
const sectionRain = document.getElementById('section-rain');
const sectionSkin = document.getElementById('section-skinrave');
const linkExternal = document.getElementById('link-external');

function activateRain() {
  currentActiveTab = 'rain';
  if (tabRain) { tabRain.classList.add('active'); tabRain.setAttribute('aria-selected', 'true'); }
  if (tabSkin) { tabSkin.classList.remove('active'); tabSkin.setAttribute('aria-selected', 'false'); }
  if (sectionRain) sectionRain.style.display = 'block';
  if (sectionSkin) sectionSkin.style.display = 'none';
  if (linkExternal) {
    linkExternal.href = 'https://rain.gg';
    linkExternal.textContent = 'Open Rain.gg (official)';
  }
  if (globalEndsAt) startCountdown(globalEndsAt);
}

function activateSkin() {
  currentActiveTab = 'skinrave';
  if (tabSkin) { tabSkin.classList.add('active'); tabSkin.setAttribute('aria-selected', 'true'); }
  if (tabRain) { tabRain.classList.remove('active'); tabRain.setAttribute('aria-selected', 'false'); }
  if (sectionSkin) sectionSkin.style.display = 'block';
  if (sectionRain) sectionRain.style.display = 'none';
  if (linkExternal) {
    linkExternal.href = 'https://skinrave.gg';
    linkExternal.textContent = 'Open Skinrave';
  }
  if (globalEndsAt) startCountdown(globalEndsAt);
}

if (tabRain && tabSkin) {
  tabRain.addEventListener('click', activateRain);
  tabSkin.addEventListener('click', activateSkin);
}

const isLeaderboardPage = document.getElementById('rain-cards') || document.getElementById('skinrave-cards');
if (isLeaderboardPage) {
  updateAllData();
  setInterval(updateAllData, 10000);
}