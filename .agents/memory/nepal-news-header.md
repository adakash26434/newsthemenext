---
name: Nepal News Portal header architecture
description: 3-zone header layout; ownership rules for back-to-top and Lucide.
---

## Header zones (header.php)
1. **Top utility bar** (`.top-utility-bar`) — BS date, social icons, ePaper, TV, language toggle, dark mode
2. **Logo area** (`.logo-area`) — brand logo/name + tagline + search button (Ctrl+K shortcut)
3. **Sticky wrapper** (`.header-sticky-wrap`) — sticky nav (`.main-nav`) + breaking ticker

## Ownership rules
- `#read-prog` (reading progress bar) — **header.php only**. Do not add to footer.
- `#back-to-top-btn` — **header.php only**. CSS suppresses any `#back-top` from old script.js.
- `lucide.createIcons()` — called once in header.php on DOMContentLoaded. Footer re-calls only after `alpine:initialized` (for late-rendered icons). Load-more AJAX also calls it.

**Why:** Previously duplicated in both header and footer, causing double-rendering bugs.

## Breaking ticker
- Red "ब्रेकिङ" pill label (`.ticker-label`) + pause/play toggle button
- Scrolling items use `.ticker-track` CSS animation; `tickerToggle()` in header pauses it
- Data: `get_breaking_news(10)` — items repeated twice so ticker never goes blank

## Dark mode
- Toggled via Alpine `darkMode` watcher → `document.documentElement.setAttribute('data-theme', ...)`
- Persisted to `localStorage.getItem('theme')`
- CSS selectors use `[data-theme="dark"]`
