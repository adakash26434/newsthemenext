---
name: Nepal News Portal stack
description: Core stack decisions and conventions for the Nepal News Portal PHP project.
---

## Stack (locked)
- PHP 8.2 via Nix, SQLite for dev, MySQL for prod
- Tailwind CSS / Alpine.js / Lucide icons — all CDN (no Composer, no Node)
- PHP built-in server: `cd /home/runner/workspace/nepal-news-portal && php -S 0.0.0.0:3000 router.php`
- Admin login: `admin` / `admin123`

## Key conventions
- All DB queries: PDO prepared statements, dual MySQL/SQLite support via `db_driver()`
- Migration pattern: `try { $db->exec($m); } catch (Exception $e) {}` — silently skips existing columns
- Admin pages: `admin_check()` at top → `admin_html_start(title)` → `admin_sidebar(active_key)` → `<div class="admin-content">` → `admin_topbar(title)` → `<div class="p-6">` → content → `admin_html_end()`
- `admin_html_end()` defined in admin_layout.php — outputs `</div></div></body></html>`
- New article columns: `seo_title`, `seo_desc`, `trending_score`, `type`, `image_credit` — all have DEFAULT values

## New admin pages (Phase 1)
- `/admin/epaper` → src/admin/epaper.php
- `/admin/market` → src/admin/market_widgets.php
- `/admin/redirects` → src/admin/redirects.php

**Why:** cPanel zip-deployable, no build step. All admin pages must include admin_check() or will expose to unauthenticated users.
