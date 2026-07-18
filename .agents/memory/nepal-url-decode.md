---
name: Nepal News Portal URL decode fix
description: Nepali slugs in URLs require rawurldecode before route matching; JSON-LD must go in body not head.
---

## Rule 1: rawurldecode URI before routing
In `index.php`, the URI must be rawurldecoded before route matching:
```php
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$uri = rawurldecode($uri);   // ← required for Nepali slugs
$uri = rtrim($uri, '/') ?: '/';
```

**Why:** Browsers URL-encode Nepali characters when following links. Without decode, `get_article_by_slug()` gets the encoded string and returns 404.

## Rule 2: JSON-LD in body, not head
The `$json_ld` variable set in `article.php` before `require header.php` does NOT reliably appear in the `<head>` output via `!empty($json_ld)` check in header.php (scope/variable timing issue). 

**Fix:** Output JSON-LD immediately AFTER the `require header.php` call in article.php — schema.org JSON-LD is valid anywhere in the `<body>`.

```php
require SRC_DIR . '/layout/header.php';
?>
<?php if (!empty($json_ld)): ?>
<script type="application/ld+json"><?= $json_ld ?></script>
<?php endif; ?>
```

**How to apply:** Any new page needing JSON-LD should follow this pattern.
