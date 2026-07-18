# Nepal News Portal

## Project Overview
A full-featured Nepali news portal with admin panel, built on PHP 8.2 + SQLite/MySQL. Deployed via the "Nepal News Portal" workflow on port 3000.

**UI/UX reference:** [karobardaily.com](https://www.karobardaily.com/) — solid red nav, 3-zone header, newspaper hero grid.

## Stack (hard constraint — do not change)
- **Backend:** PHP 8.2, SQLite (dev) / MySQL (prod)
- **Frontend:** Tailwind CSS CDN, Alpine.js CDN, Lucide Icons CDN, Noto Sans Devanagari (Google Fonts)
- **No build step** — cPanel zip-deployable; no npm/pnpm inside `nepal-news/`

## Running the Portal
```bash
cd /home/runner/workspace/nepal-news
php -S 0.0.0.0:3000 router.php
```
Or use the **"Nepal News Portal"** workflow in the Workflows panel.

## Key Directories
```
nepal-news/
├── index.php              # Front controller / router
├── router.php             # Dev-server router (PHP built-in server)
├── .htaccess              # Apache rules: security blocks, cache, compression
├── src/
│   ├── config.php         # Constants (colors, site defaults, admin creds)
│   ├── helpers.php        # All utility functions
│   ├── init.php           # DB schema bootstrap (SQLite + MySQL)
│   ├── layout/
│   │   ├── header.php     # 3-zone header: top-bar → logo-area → sticky nav+ticker
│   │   └── footer.php     # Newsletter, YouTube, footer grid, mobile nav, JS bundle
│   └── pages/             # home.php, article.php, category.php, search.php, ...
├── assets/
│   ├── style.css          # All CSS (3476+ lines; v2.3 additions at bottom)
│   └── script.js          # Lazy load, bookmarks, toast, social share
└── admin/                 # Full admin panel (articles, categories, media, settings…)
```

## Admin Panel
- URL: `/admin/login`
- Default login: `admin` / `admin123` — **change this in Settings → Admin Password**

## Features
- Multi-language (Nepali / English toggle)
- Dark mode (persisted to localStorage)
- Breaking news ticker with pause control
- Live market data (NEPSE, Forex, Gold)
- Weather + Air Quality + Earthquake widgets
- Horoscope, Events, ePaper
- Newsletter subscription
- RSS feed at `/rss`, Sitemap at `/sitemap.xml`
- JSON-LD structured data on articles
- CSRF protection, bcrypt passwords, rate limiting
- PWA manifest + service worker
- Bottom mobile nav bar (karobardaily-style)
- AJAX load-more for category/search pages
- Bookmarks (localStorage), Reactions, Comments

## Security Notes
- `debug.php` deleted (was a critical exposure)
- `.htaccess` blocks: `db.config.php`, `seed*.php`, `demo_data.sql`, `data/` directory
- Security headers: X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy
- Options `-Indexes` prevents directory listing

## Deployment
Zip the `nepal-news/` folder and upload to cPanel public_html. Requires PHP 8.0+ and either SQLite3 or MySQL extension.

## User preferences
- Always use the PHP-only stack (no Node/React build tools inside nepal-news/)
- karobardaily.com is the primary UI/UX reference
- Tailwind CDN classes are fine for layout; custom CSS goes in assets/style.css
