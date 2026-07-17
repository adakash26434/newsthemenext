# Changelog

All notable changes to this project will be documented in this file.

## [2.1.0] - 2026-07-17

### Added
- **Accessibility**: Skip to main content link for keyboard navigation
- **Accessibility**: `focus-visible` styles for all interactive elements
- **Accessibility**: Reduced motion support via `prefers-reduced-motion` media query
- **Accessibility**: High contrast mode support via `prefers-contrast` media query
- **Accessibility**: ARIA labels on buttons and navigation elements
- **Performance**: Settings cache with 60-second TTL for reduced database queries
- **Performance**: New `lazy_img()` helper function for lazy-loaded images
- **Performance**: New `img_with_srcset()` helper for responsive images
- **Performance**: New `og_image()` helper for Open Graph images
- **Performance**: Skeleton loading animation for images
- **SEO**: Enhanced Open Graph meta tags with image dimensions
- **SEO**: Twitter Card meta tags for social sharing
- **SEO**: Article published time meta tag
- **SEO**: Author meta tag
- **SEO**: Enhanced robots directive with `max-image-preview:large`
- **Design**: Enhanced card system with hover effects and smooth transitions
- **Design**: Improved section headings with gradient backgrounds
- **Design**: Better newsletter widget styling
- **Design**: Enhanced tag cloud with hover effects
- **Design**: Improved event widget layout
- **Design**: Comprehensive button variants (primary, secondary, danger)
- **Design**: Badge variants (green, gray, red)
- **Design**: Form enhancements with focus states

### Fixed
- **Bug**: `db_fetchOne()` undefined function → changed to `db_fetch()` in `user_auth.php`
- **Bug**: `user_register()` now properly uses `db_insert()` for correct lastInsertId return
- **Bug**: Settings cache now properly times out after 60 seconds

### Security
- Enhanced rate limiting documentation
- bcrypt password hashing enforced
- CSRF protection on all forms

## [2.0.0] - Previous

### Added
- Initial professional redesign
- Dark/Light mode toggle
- Bilingual support (Nepali/English)
- Admin panel with full CRUD
- Advertisement management
- User authentication system
- Newsletter subscriptions
- Scheduled publishing
- PWA manifest and service worker
- Quill WYSIWYG editor for articles
- Auto-save drafts
- Social share bar
- Author bio box
- Related articles grid
- Reading progress bar
- Breaking news ticker
- Search overlay with live suggestions
- Market widgets (forex, gold, NEPSE)
- Event management system
- ePaper support
- Redirect management
- Media library
- Comment moderation
- Tag management
- Category management with icons and colors
- Author management
- Comprehensive SEO (JSON-LD, meta tags, sitemap)
- RSS feed
- Google News sitemap
- robots.txt
- Sitemap.xml

---

## Installation

```bash
# Clone the repository
git clone https://github.com/adakash26434/newsthemenext.git
cd newsthemenext/nepal-news-portal

# Set permissions
chmod 755 data/

# Access the site
# Admin: /admin (admin / admin123)
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

This project is proprietary software. All rights reserved.
