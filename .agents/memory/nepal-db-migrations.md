---
name: Nepal News Portal DB migrations
description: New DB columns on an existing database must be run manually via PHP CLI; init.php only fires on fresh installs.
---

## Problem
`src/init.php` only runs when the `settings` table doesn't exist. On an existing dev database, new `ALTER TABLE` and `CREATE TABLE` statements in init.php are never executed.

## Solution
Run migrations manually via PHP CLI:
```bash
cd /home/runner/workspace/nepal-news-portal && php -r "
require_once 'src/config.php';
require_once 'src/database.php';
\$db = get_db();
// ALTER TABLE ...
// CREATE TABLE IF NOT EXISTS ...
"
```

Use `try { \$db->exec(\$m); } catch (Exception \$e) {}` so existing-column errors are silently skipped.

**Why:** The portal is designed for cPanel zip-upload. init.php doubles as the migration runner for fresh installs. For incremental changes to an existing local DB, manual CLI migration is the only path.

**How to apply:** After adding new columns/tables to init.php, always run them manually in the dev environment.
