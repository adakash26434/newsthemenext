# 🇳🇵 Nepal News Portal

A complete Nepali news portal built with **PHP + SQLite + Tailwind CSS + Alpine.js**.

## Features

- 📰 Full news portal (articles, categories, authors, tags)
- 🌙 Dark / Light mode toggle (persisted in localStorage)
- 📢 Advertisement management (header, sidebar, in-article placements)
- ⚙️ Site Settings (logo, name, colors, contact, social links)
- 🇳🇵 Bilingual (Nepali + English content per article)
- 🔐 Admin Panel with CSRF protection
- 📱 Fully responsive (mobile-first)
- 🏷️ Breaking news ticker
- 🔍 Full-text search

## Stack

| Layer      | Technology                  |
|------------|-----------------------------|
| Backend    | PHP 8.0+                    |
| Database   | SQLite 3 (via PDO)          |
| CSS        | Tailwind CDN + custom CSS   |
| JS         | Alpine.js v3 (CDN)          |
| Fonts      | Google Fonts (Mukta + Noto) |

## cPanel Deployment

1. Upload all files to `public_html/` (or a subdirectory)
2. Ensure `mod_rewrite` is enabled (most cPanel hosts have it on)
3. Give write permission to `data/` directory: `chmod 755 data/`
4. Visit your domain — DB auto-initializes on first load
5. Go to `/admin/login` → Username: **admin** / Password: **admin123**
6. **Change credentials immediately** at `/admin/settings`

## Directory Structure

```
public_html/          ← upload all this
├── index.php         ← front controller (all routes here)
├── .htaccess         ← Apache URL rewriting
├── robots.txt
├── assets/
│   └── style.css     ← global CSS (single source of truth)
├── src/
│   ├── config.php
│   ├── database.php
│   ├── helpers.php
│   ├── init.php      ← run once to seed DB (auto-runs on first visit)
│   ├── layout/
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── admin_layout.php
│   ├── pages/        ← public-facing pages
│   └── admin/        ← admin panel pages
└── data/
    └── news.db       ← auto-created SQLite database
```

## URL Structure

| URL                          | Page                    |
|------------------------------|-------------------------|
| `/`                          | Homepage                |
| `/article/{slug}`            | Article detail          |
| `/category/{slug}`           | Category listing        |
| `/author/{slug}`             | Author profile          |
| `/search?q=...`              | Search results          |
| `/admin`                     | Admin dashboard         |
| `/admin/articles`            | Article management      |
| `/admin/articles?action=new` | New article             |
| `/admin/categories`          | Category management     |
| `/admin/authors`             | Author management       |
| `/admin/tags`                | Tag management          |
| `/admin/advertisements`      | Ad management *(NEW)*   |
| `/admin/settings`            | Site settings *(NEW)*   |

## Advertisement Positions

| Position        | Where it shows                     |
|-----------------|------------------------------------|
| `header-banner` | Top of every page (above header)   |
| `sidebar-top`   | Top of sidebar                     |
| `sidebar-bottom`| Bottom of sidebar                  |
| `article-middle`| Inside article body                |
| `article-bottom`| Below article content              |

## Theme / Colors

All colors flow from `src/admin/settings.php` → DB → `assets/style.css` CSS variables.
To change colors: Admin → Settings → Theme Colors → Save.

## Requirements

- PHP 8.0+
- SQLite3 extension (enabled by default on most cPanel hosts)
- Apache mod_rewrite
- Write permission on `data/` directory

## Security Notes

- Change `admin` / `admin123` on first login
- `.htaccess` blocks direct access to `data/` and `*.db` files
- CSRF tokens on all forms
- All output HTML-escaped via `h()` helper
