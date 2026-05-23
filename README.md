# Teebee Site (PHP)

This repository now serves a small PHP landing page for **Teebee**. The site has a gambling/CS creator aesthetic and includes social tabs for Discord, X, Kick, YouTube and a Rain.gg link.

Structure
- `index.php` — main PHP page (uses asset paths below)
- `assets/css/styles.css` — styles
- `assets/js/script.js` — tab behavior + keyboard support
- `assets/img/IMG_6945.php` — serves the profile PNG stored at the project root (`IMG_6945.png`)

How to run (XAMPP / Apache + PHP)

1. Make sure Apache + PHP are running (e.g., XAMPP). The project is already in `htdocs`.
2. Open `http://localhost/Teebee-site/` in your browser — Apache will serve `index.php`.

Notes on images and assets

- The repo contains the original `IMG_6945.png` in the project root. To avoid binary duplication the image is served via `assets/img/IMG_6945.php` which proxys the file and adds cache headers.
- If you prefer the raw PNG moved into the `assets/img/` folder, move `IMG_6945.png` into `assets/img/` and delete (or update) `assets/img/IMG_6945.php` accordingly.

Customization

- Social links are defined as PHP variables at the top of `index.php` for easy editing.
- Swap those URLs with real profiles or other destinations as needed.

Accessibility & behavior

- Tabs are keyboard-accessible (arrow navigation).
- The page is responsive and intended for a dark, high-contrast style suitable for a gambling/CS creator.
