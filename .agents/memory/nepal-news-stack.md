---
name: Nepal News Portal stack constraint
description: Hard constraint — PHP-only stack, no Node/React build step.
---

## Rule
This project is PHP 8.2 + SQLite (dev) / MySQL (prod) + Tailwind CDN + Alpine.js CDN + Lucide CDN.
No npm, no build step, no React, no TypeScript.

**Why:** User explicitly confirmed this stack. Must remain deployable as a cPanel zip.

**How to apply:** When adding features, always use PHP templates, CDN scripts, and vanilla CSS/JS patterns. Never introduce pnpm/npm packages into `nepal-news/`.

## Key file layout
- `index.php` — front controller / router
- `src/init.php` — DB schema bootstrap
- `src/helpers.php` — all utility functions (CSRF, icon(), auth, session, BS date, color helpers)
- `src/layout/header.php` — full HTML head + 3-zone header + sticky nav + breaking ticker
- `src/layout/footer.php` — newsletter bar, YouTube block, footer, mobile nav, JS bundle
- `src/pages/home.php` — homepage (hero grid, market bar, category sections, sidebar)
- `assets/style.css` — all CSS (3476+ lines); v2.3 additions at the bottom
- `assets/script.js` — lazy load, social share, bookmark, toast helpers
