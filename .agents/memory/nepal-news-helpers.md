---
name: Nepal News Portal helper functions added in v2.3
description: New helper functions added to src/helpers.php that did not exist before.
---

## Functions added to src/helpers.php

### `bs_date_np(): string`
Returns today's date in full BS Nepali format using the existing `BsDate` class.
Wraps `\BsDate::formatFull(date('Y-m-d H:i:s'))`. Used in top-utility-bar in header.php.

### `lighten_color(string $hex): string`
Lightens a hex color by 20% toward white. Used for CSS `--c-primary-lt` token in inline style block.

### `darken_color(string $hex): string`  
Darkens a hex color to 60% brightness. Used for CSS `--c-primary-dk` token.

**Why:** These were called in the new header.php inline `<style>` block but were missing from helpers.php.
