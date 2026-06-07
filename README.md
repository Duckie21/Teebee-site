# Teebee Site

This repository now serves a small landing page for **Teebee**. The site has a gambling/CS creator aesthetic and includes social tabs for Discord, X, Kick, YouTube, Rain.gg, and Skinrave.

Structure
- `index.html` — main homepage
- `leaderboards.html` — Combined Rain.gg + Skinrave leaderboards (use leaderboards.html)
- `leaderboards.html` — Combined Rain.gg + Skinrave leaderboards (use leaderboards.html)
- `api/rain.php` — local proxy for the Rain API
- `api/skinrave.php` — local proxy for the Skinrave API
- `rain.php` / `skinrave.php` — legacy redirects to the HTML pages
- `assets/css/styles.css` — styles
- `assets/js/script.js` — tab behavior + keyboard support
- `assets/img/IMG_6945.php` — serves the profile PNG stored at the project root (`IMG_6945.png`)

How to run (XAMPP / Apache + PHP)

1. Make sure Apache + PHP are running (e.g., XAMPP). The project is already in `htdocs`.
2. Open `http://localhost/Teebee-site/` in your browser — Apache will serve `index.html`.

Notes on images and assets

- The repo contains the original `IMG_6945.png` in the project root. To avoid binary duplication the image is served via `assets/img/IMG_6945.php` which proxys the file and adds cache headers.
- If you prefer the raw PNG moved into the `assets/img/` folder, move `IMG_6945.png` into `assets/img/` and delete (or update) `assets/img/IMG_6945.php` accordingly.

Customization

- Social links are hardcoded in `index.html` for the static build.
- Swap those URLs with real profiles or other destinations as needed.

Accessibility & behavior

- Tabs are keyboard-accessible (arrow navigation).
- The page is responsive and intended for a dark, high-contrast style suitable for a gambling/CS creator.
