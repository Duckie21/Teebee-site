const tabButtons = Array.from(document.querySelectorAll('[data-tab]'));
const tabPanels = Array.from(document.querySelectorAll('[data-panel]'));

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


