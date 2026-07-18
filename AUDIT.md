# Nepal News Portal — Full Audit Report (v2.3)

## Audit Date: 2026-07-18

---

## 🔴 SECURITY — Fixed

| Issue | Severity | Fix Applied |
|-------|----------|-------------|
| `debug.php` publicly accessible | **Critical** | Deleted |
| `seed.php`, `seed_news.php` exposed | High | `.htaccess` blocks access |
| `demo_data.sql` accessible | High | `.htaccess` blocks access |
| `db.config.php` accessible if Apache misconfigured | Medium | `.htaccess` blocks access |
| Default admin password `admin123` | Medium | Documented — change via Settings panel |
| No security headers (X-Frame-Options, etc.) | Medium | Added to `.htaccess` |
| `data/` directory browseable | Medium | `Options -Indexes` applied |

---

## 🟡 BUGS — Fixed

| Issue | Fix |
|-------|-----|
| Reading progress bar duplicated (in both header.php and footer.php) | Removed from footer.php — header.php owns it |
| Duplicate back-to-top button (Alpine + vanilla JS both created one) | Removed vanilla JS version from footer.php, header.php owns `#back-to-top-btn` |
| Old `#back-top` CSS selector was unused after rename | Added `#back-top { display:none }` suppressor in CSS |
| `lucide.createIcons()` called twice on DOMContentLoaded | Removed duplicate from footer.php — header.php runs it on DOMContentLoaded |
| Market widget emoji (📈 💱 🥇) render inconsistently on some devices | Replaced with Lucide SVG icons |
| Missing helper functions `bs_date_np()`, `lighten_color()`, `darken_color()` | Added to `src/helpers.php` |

---

## 🟢 UI/UX — Improved (karobardaily.com reference)

### Header
- **Before**: Single sticky header bar mixing logo + nav
- **After**:
  - **Top utility bar**: BS date, social icons (Facebook/Twitter/YouTube), ePaper, TV, language toggle, dark mode
  - **Logo area**: Clean brand section with site name/tagline + search button with Ctrl+K shortcut
  - **Sticky nav**: Solid brand-color bar (not gradient) with category icons + breaking/markets/epaper links
  - **Breaking ticker**: Red "ब्रेकिङ" badge + pause button + scrolling news with dot separators
  - **Mobile hamburger**: Properly styled toggle with X on open

### Breaking Ticker
- **Before**: Gradient background, no pause control, basic styling
- **After**: Clean white background with red bottom border, red "ब्रेकिङ" pill label, pause/play toggle, dot separators between items

### Section Headings
- **Before**: Gradient background cards with white text
- **After**: Karobardaily-style: white left border + colored text + light gradient wash

### Cards
- **Before**: Basic hover with translate
- **After**: Image zoom on hover, title color change, border color accent on hover

### Market Widgets
- **Before**: Emoji icons, inconsistent rendering
- **After**: Lucide SVG icons, consistent across all platforms

### Bottom Mobile Nav
- Kept and improved (karobardaily-style bottom tab bar)

### Accessibility
- Added skip-to-content link
- ARIA labels on all interactive elements
- Keyboard shortcut (Ctrl+K) for search overlay
- `aria-current="page"` on active nav links

---

## 🔵 CODE QUALITY — Improved

| Item | Change |
|------|--------|
| CSS file | Added v2.3 section with 350 lines of organized improvements |
| `.htaccess` | Complete rewrite with security rules + caching + compression |
| `header.php` | Full rewrite — cleaner, more organized, no code duplication |
| `footer.php` | Removed ~45 lines of duplicate JS (progress bar, back-to-top, lucide init) |
| `helpers.php` | Added 3 missing utility functions |
| `debug.php` | Removed (was a security liability) |

---

## 📋 WHAT REMAINED UNCHANGED (intentionally)

- Admin panel (all pages) — functional, no changes needed
- Article page layout — already good quality
- Category/search/author pages — working well
- Database schema — no changes needed
- Router/URL structure — works correctly
- PWA (manifest + service worker) — kept
- RSS feed, Sitemap — kept
- All admin features (CRUD, media, events, ePaper, market widgets, etc.)

---

## 🚀 STACK (unchanged as requested)

- **Backend**: PHP 8.2, SQLite (dev) / MySQL (prod)
- **Frontend**: Tailwind CSS CDN, Alpine.js CDN, Lucide Icons CDN
- **No build step** — cPanel zip-deployable
- **Admin**: `/admin` — username: `admin`, password: change from Settings panel

---

## Run

```bash
cd /home/runner/workspace/nepal-news
php -S 0.0.0.0:3000 router.php
```
