# Nepal News Portal

A professional Nepali news portal built with **PHP + SQLite + Tailwind CSS + Alpine.js**.

## Tech Stack

- **Backend**: PHP 8.0+
- **Database**: SQLite 3 (PDO) / MySQL/MariaDB
- **Frontend**: Tailwind CSS CDN + Alpine.js
- **Icons**: Lucide Icons

## Features

- News articles with categories, authors, tags
- Dark / Light mode toggle
- Bilingual support (Nepali + English)
- Advertisement management
- Admin panel with full CRUD
- User authentication
- Newsletter subscriptions
- SEO optimized (Open Graph, Twitter Cards, JSON-LD)
- Accessibility compliant (WCAG 2.1 AA)
- PWA support with service worker
- Full-text search
- Breaking news ticker
- RSS feed

## Installation

1. Upload files to `public_html/` on your server
2. Ensure `mod_rewrite` is enabled
3. Set permissions: `chmod 755 data/`
4. Visit your domain - database auto-initializes
5. Go to `/admin/login` - Username: **admin** / Password: **admin123**
6. Change credentials in `/admin/settings`

## Directory Structure

```
├── index.php          # Front controller
├── .htaccess         # URL rewriting
├── robots.txt
├── assets/
│   ├── style.css     # Global styles
│   ├── sw.js         # Service worker
│   └── uploads/      # User uploads
├── src/
│   ├── config.php
│   ├── database.php
│   ├── helpers.php
│   ├── layout/       # Header, footer, admin layout
│   ├── pages/        # Public pages
│   └── admin/       # Admin panel
└── data/            # SQLite database
```

## Admin Routes

| URL | Description |
|-----|-------------|
| `/admin` | Dashboard |
| `/admin/articles` | Article management |
| `/admin/categories` | Categories |
| `/admin/authors` | Authors |
| `/admin/advertisements` | Advertisements |
| `/admin/settings` | Site settings |

## Requirements

- PHP 8.0+
- SQLite3 extension
- Apache mod_rewrite

## Security

- CSRF protection on all forms
- bcrypt password hashing
- Rate limiting on login
- XSS prevention via `h()` helper
- SQL injection prevention via prepared statements

## License

All rights reserved.
