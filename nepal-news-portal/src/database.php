<?php
require_once __DIR__ . '/config.php';

// ── Connection ─────────────────────────────────────────────
function get_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    if (defined('DB_HOST') && DB_HOST !== '') {
        // MySQL / MariaDB
        $dsn = 'mysql:host=' . DB_HOST
             . ';port=' . DB_PORT
             . ';dbname=' . DB_NAME
             . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_FOUND_ROWS   => true,
        ]);
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
                    SET time_zone = '+05:45';
                    SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';");
    } else {
        // SQLite fallback (development / shared hosting without MySQL)
        if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
        $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $pdo->exec("PRAGMA journal_mode=WAL;
                    PRAGMA cache_size=10000;
                    PRAGMA foreign_keys=ON;
                    PRAGMA synchronous=NORMAL;
                    PRAGMA temp_store=MEMORY;");
    }
    return $pdo;
}

function db_driver(): string {
    return get_db()->getAttribute(PDO::ATTR_DRIVER_NAME); // 'mysql' | 'sqlite'
}

// ── Generic helpers ────────────────────────────────────────
function db_query(string $sql, array $params = []): PDOStatement {
    $stmt = get_db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
function db_fetch(string $sql, array $params = []): ?array {
    return db_query($sql, $params)->fetch() ?: null;
}
function db_fetchAll(string $sql, array $params = []): array {
    return db_query($sql, $params)->fetchAll();
}
function db_insert(string $sql, array $params = []): int {
    db_query($sql, $params);
    return (int) get_db()->lastInsertId();
}
function db_count(string $sql, array $params = []): int {
    return (int) db_query($sql, $params)->fetchColumn();
}
function db_exec(string $sql, array $params = []): int {
    return db_query($sql, $params)->rowCount();
}

// Insert-or-ignore helper (driver-aware)
function db_insert_ignore(string $table, array $cols, array $vals): int {
    $c  = implode(',', array_map(fn($x) => "`$x`", $cols));
    $ph = implode(',', array_fill(0, count($vals), '?'));
    if (db_driver() === 'mysql') {
        return db_insert("INSERT IGNORE INTO `$table` ($c) VALUES ($ph)", $vals);
    }
    return db_insert("INSERT OR IGNORE INTO `$table` ($c) VALUES ($ph)", $vals);
}

// ── Settings ───────────────────────────────────────────────
function get_all_settings(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        $rows  = db_fetchAll("SELECT `key`, value FROM settings");
        $cache = [];
        foreach ($rows as $r) $cache[$r['key']] = $r['value'];
    } catch (Exception $e) { $cache = []; }
    return $cache;
}
function setting(string $key, string $default = ''): string {
    return get_all_settings()[$key] ?? $default;
}
function save_setting(string $key, string $value): void {
    if (db_driver() === 'mysql') {
        db_query(
            "INSERT INTO settings (`key`, value, updated_at) VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()",
            [$key, $value]
        );
    } else {
        db_query(
            "INSERT INTO settings (`key`, value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)
             ON CONFLICT(`key`) DO UPDATE SET value = excluded.value, updated_at = CURRENT_TIMESTAMP",
            [$key, $value]
        );
    }
}

// ── Categories ─────────────────────────────────────────────
function get_categories(): array {
    return db_fetchAll(
        "SELECT c.*, COUNT(a.id) AS article_count
         FROM categories c
         LEFT JOIN articles a ON a.category_id = c.id AND a.status = 'published'
         GROUP BY c.id ORDER BY c.sort_order, c.name"
    );
}
function get_category_by_slug(string $slug): ?array {
    return db_fetch("SELECT * FROM categories WHERE slug = ?", [$slug]);
}
function save_category(array $data, ?int $id = null): int {
    if ($id) {
        db_query(
            "UPDATE categories SET name=?,name_np=?,slug=?,color=?,icon=?,sort_order=? WHERE id=?",
            [$data['name'],$data['name_np'],$data['slug'],$data['color'],$data['icon'],$data['sort_order'],$id]
        );
        return $id;
    }
    return db_insert(
        "INSERT INTO categories (name,name_np,slug,color,icon,sort_order) VALUES (?,?,?,?,?,?)",
        [$data['name'],$data['name_np'],$data['slug'],$data['color'],$data['icon'],$data['sort_order']]
    );
}
function delete_category(int $id): void {
    db_query("DELETE FROM categories WHERE id = ?", [$id]);
}

// ── Authors ────────────────────────────────────────────────
function get_authors(): array {
    return db_fetchAll("SELECT * FROM authors ORDER BY name");
}
function get_author_by_slug(string $slug): ?array {
    return db_fetch("SELECT * FROM authors WHERE slug = ?", [$slug]);
}
function save_author(array $data, ?int $id = null): int {
    if ($id) {
        db_query(
            "UPDATE authors SET name=?,name_np=?,slug=?,bio=?,avatar_url=? WHERE id=?",
            [$data['name'],$data['name_np'],$data['slug'],$data['bio'],$data['avatar_url'],$id]
        );
        return $id;
    }
    return db_insert(
        "INSERT INTO authors (name,name_np,slug,bio,avatar_url) VALUES (?,?,?,?,?)",
        [$data['name'],$data['name_np'],$data['slug'],$data['bio'],$data['avatar_url']]
    );
}
function delete_author(int $id): void {
    db_query("DELETE FROM authors WHERE id = ?", [$id]);
}

// ── Tags ───────────────────────────────────────────────────
function get_tags(): array {
    return db_fetchAll(
        "SELECT t.*, COUNT(at.article_id) AS usage_count
         FROM tags t LEFT JOIN article_tags at ON at.tag_id = t.id
         GROUP BY t.id ORDER BY usage_count DESC, t.name"
    );
}
function save_tag(array $data, ?int $id = null): int {
    if ($id) {
        db_query("UPDATE tags SET name=?,slug=? WHERE id=?", [$data['name'],$data['slug'],$id]);
        return $id;
    }
    return db_insert_ignore('tags', ['name','slug'], [$data['name'],$data['slug']]);
}
function delete_tag(int $id): void {
    db_query("DELETE FROM tags WHERE id = ?", [$id]);
}

// ── Articles ───────────────────────────────────────────────
function get_articles(array $opts = []): array {
    $where = ['1=1']; $params = [];
    if (!empty($opts['status']))        { $where[] = 'a.status = ?';        $params[] = $opts['status']; }
    if (!empty($opts['featured']))      { $where[] = 'a.featured = 1'; }
    if (!empty($opts['is_breaking']))   { $where[] = 'a.is_breaking = 1'; }
    if (!empty($opts['category_id']))   { $where[] = 'a.category_id = ?';   $params[] = $opts['category_id']; }
    if (!empty($opts['category_slug'])) { $where[] = 'c.slug = ?';          $params[] = $opts['category_slug']; }
    if (!empty($opts['author_id']))     { $where[] = 'a.author_id = ?';     $params[] = $opts['author_id']; }
    if (!empty($opts['author_slug']))   { $where[] = 'au.slug = ?';         $params[] = $opts['author_slug']; }
    if (!empty($opts['language']))      { $where[] = 'a.language = ?';      $params[] = $opts['language']; }
    if (!empty($opts['exclude_id']))    { $where[] = 'a.id != ?';           $params[] = $opts['exclude_id']; }
    if (!empty($opts['search'])) {
        $where[] = '(a.title LIKE ? OR a.title_np LIKE ? OR a.summary LIKE ? OR a.content LIKE ?)';
        $s = '%' . $opts['search'] . '%';
        $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
    }
    $limit  = (int)($opts['limit']  ?? ARTICLES_PER_PAGE);
    $offset = (int)($opts['offset'] ?? 0);
    $order  = preg_replace('/[^a-zA-Z0-9_.() ,]/', '', $opts['order'] ?? 'a.published_at DESC, a.created_at DESC');
    $ws     = implode(' AND ', $where);
    return db_fetchAll(
        "SELECT a.*, c.name AS category_name, c.name_np AS category_name_np,
                c.slug AS category_slug, c.color AS category_color,
                au.name AS author_name, au.name_np AS author_name_np, au.slug AS author_slug,
                au.avatar_url AS author_avatar
         FROM articles a
         JOIN categories c  ON c.id = a.category_id
         JOIN authors    au ON au.id = a.author_id
         WHERE $ws ORDER BY $order LIMIT $limit OFFSET $offset",
        $params
    );
}
function count_articles(array $opts = []): int {
    $where = ['1=1']; $params = [];
    if (!empty($opts['status']))        { $where[] = 'a.status = ?';        $params[] = $opts['status']; }
    if (!empty($opts['category_slug'])) { $where[] = 'c.slug = ?';          $params[] = $opts['category_slug']; }
    if (!empty($opts['author_slug']))   { $where[] = 'au.slug = ?';         $params[] = $opts['author_slug']; }
    if (!empty($opts['language']))      { $where[] = 'a.language = ?';      $params[] = $opts['language']; }
    if (!empty($opts['search'])) {
        $where[] = '(a.title LIKE ? OR a.title_np LIKE ? OR a.summary LIKE ? OR a.content LIKE ?)';
        $s = '%' . $opts['search'] . '%';
        $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
    }
    $ws = implode(' AND ', $where);
    return db_count(
        "SELECT COUNT(*) FROM articles a
         JOIN categories c  ON c.id = a.category_id
         JOIN authors    au ON au.id = a.author_id
         WHERE $ws",
        $params
    );
}
function get_article_by_slug(string $slug): ?array {
    $a = db_fetch(
        "SELECT a.*, c.name AS category_name, c.name_np AS category_name_np,
                c.slug AS category_slug, c.color AS category_color,
                au.name AS author_name, au.name_np AS author_name_np,
                au.slug AS author_slug, au.bio AS author_bio, au.avatar_url AS author_avatar
         FROM articles a
         JOIN categories c  ON c.id = a.category_id
         JOIN authors    au ON au.id = a.author_id
         WHERE a.slug = ?",
        [$slug]
    );
    if (!$a) return null;
    $a['tags'] = db_fetchAll(
        "SELECT t.* FROM tags t JOIN article_tags at ON at.tag_id = t.id WHERE at.article_id = ?",
        [$a['id']]
    );
    return $a;
}
function get_article_by_id(int $id): ?array {
    $a = db_fetch(
        "SELECT a.*, c.name AS category_name, c.name_np AS category_name_np,
                c.slug AS category_slug, c.color AS category_color,
                au.name AS author_name, au.slug AS author_slug
         FROM articles a
         JOIN categories c  ON c.id = a.category_id
         JOIN authors    au ON au.id = a.author_id
         WHERE a.id = ?",
        [$id]
    );
    if (!$a) return null;
    $a['tags'] = db_fetchAll(
        "SELECT t.* FROM tags t JOIN article_tags at ON at.tag_id = t.id WHERE at.article_id = ?",
        [$id]
    );
    return $a;
}
function save_article(array $data, ?int $id = null): int {
    $fields = ['title','title_np','slug','content','content_np','summary','summary_np',
               'language','status','featured','is_breaking','image_url','category_id','author_id','published_at'];
    if ($id) {
        $sets = implode(',', array_map(fn($f) => "$f=?", $fields));
        $vals = array_map(fn($f) => $data[$f] ?? null, $fields);
        $vals[] = date('Y-m-d H:i:s');
        $vals[] = $id;
        db_query("UPDATE articles SET $sets, updated_at=? WHERE id=?", $vals);
        $new_id = $id;
    } else {
        $cols = implode(',', $fields);
        $phs  = implode(',', array_fill(0, count($fields), '?'));
        $vals = array_map(fn($f) => $data[$f] ?? null, $fields);
        $new_id = db_insert("INSERT INTO articles ($cols) VALUES ($phs)", $vals);
    }
    // Tags
    db_query("DELETE FROM article_tags WHERE article_id = ?", [$new_id]);
    if (!empty($data['tag_ids'])) {
        $ignore = db_driver() === 'mysql' ? 'INSERT IGNORE' : 'INSERT OR IGNORE';
        $st = get_db()->prepare("$ignore INTO article_tags (article_id, tag_id) VALUES (?, ?)");
        foreach ($data['tag_ids'] as $tid) $st->execute([$new_id, (int)$tid]);
    }
    return $new_id;
}
function delete_article(int $id): void {
    db_query("DELETE FROM articles WHERE id = ?", [$id]);
}
function increment_views(int $id): void {
    db_query("UPDATE articles SET views = views + 1 WHERE id = ?", [$id]);
}
function get_popular_articles(int $limit = 5): array {
    return get_articles(['status'=>'published', 'limit'=>$limit, 'order'=>'a.views DESC']);
}
function get_breaking_news(int $limit = 8): array {
    return get_articles(['status'=>'published', 'is_breaking'=>true, 'limit'=>$limit]);
}

// ── Advertisements ─────────────────────────────────────────
function get_active_ads(string $position): array {
    $now = date('Y-m-d H:i:s');
    return db_fetchAll(
        "SELECT * FROM advertisements
         WHERE position = ? AND active = 1
           AND (start_date IS NULL OR start_date <= ?)
           AND (end_date   IS NULL OR end_date   >= ?)
         ORDER BY sort_order",
        [$position, $now, $now]
    );
}
function get_all_ads(): array {
    return db_fetchAll("SELECT * FROM advertisements ORDER BY position, sort_order");
}
function save_ad(array $data, ?int $id = null): int {
    $fields = ['title','type','image_url','code','link_url','position','active','sort_order',
               'device','start_date','end_date'];
    if ($id) {
        $sets = implode(',', array_map(fn($f) => "$f=?", $fields));
        $vals = array_map(fn($f) => $data[$f] !== '' ? $data[$f] : null, $fields);
        $vals[] = date('Y-m-d H:i:s');
        $vals[] = $id;
        db_query("UPDATE advertisements SET $sets, updated_at=? WHERE id=?", $vals);
        return $id;
    }
    $cols = implode(',', $fields);
    $phs  = implode(',', array_fill(0, count($fields), '?'));
    return db_insert(
        "INSERT INTO advertisements ($cols) VALUES ($phs)",
        array_map(fn($f) => $data[$f] !== '' ? $data[$f] : null, $fields)
    );
}
function delete_ad(int $id): void {
    db_query("DELETE FROM advertisements WHERE id = ?", [$id]);
}
function track_ad_click(int $id): void {
    db_query("UPDATE advertisements SET clicks = clicks + 1 WHERE id = ?", [$id]);
}
function track_ad_impression(int $id): void {
    db_query("UPDATE advertisements SET impressions = impressions + 1 WHERE id = ?", [$id]);
}

// ── Events ─────────────────────────────────────────────────
function get_events(array $opts = []): array {
    $where = ['1=1']; $params = [];
    if (!empty($opts['status'])) { $where[] = 'status = ?'; $params[] = $opts['status']; }
    $ws     = implode(' AND ', $where);
    $limit  = (int)($opts['limit']  ?? 20);
    $offset = (int)($opts['offset'] ?? 0);
    $order  = $opts['order'] ?? 'start_datetime DESC';
    return db_fetchAll(
        "SELECT * FROM events WHERE $ws ORDER BY $order LIMIT $limit OFFSET $offset",
        $params
    );
}
function count_events(array $opts = []): int {
    $where = ['1=1']; $params = [];
    if (!empty($opts['status'])) { $where[] = 'status = ?'; $params[] = $opts['status']; }
    return db_count("SELECT COUNT(*) FROM events WHERE " . implode(' AND ', $where), $params);
}
function get_upcoming_events(int $limit = 5): array {
    return db_fetchAll(
        "SELECT * FROM events WHERE status IN ('upcoming','ongoing') ORDER BY start_datetime ASC LIMIT ?",
        [$limit]
    );
}
function get_event_by_slug(string $slug): ?array {
    $e = db_fetch("SELECT * FROM events WHERE slug = ?", [$slug]);
    if (!$e) return null;
    $e['registrations_count'] = db_count("SELECT COUNT(*) FROM event_registrations WHERE event_id = ?", [$e['id']]);
    $e['gallery'] = db_fetchAll("SELECT * FROM event_media WHERE event_id = ? ORDER BY sort_order", [$e['id']]);
    return $e;
}
function get_event_by_id(int $id): ?array {
    $e = db_fetch("SELECT * FROM events WHERE id = ?", [$id]);
    if (!$e) return null;
    $e['registrations_count'] = db_count("SELECT COUNT(*) FROM event_registrations WHERE event_id = ?", [$id]);
    $e['gallery'] = db_fetchAll("SELECT * FROM event_media WHERE event_id = ? ORDER BY sort_order", [$id]);
    return $e;
}
function save_event(array $data, ?int $id = null): int {
    $fields = ['title','title_en','slug','description','description_en','cover_image','venue','venue_en',
               'start_datetime','end_datetime','registration_open','registration_deadline',
               'capacity','status','show_in_menu'];
    if ($id) {
        $sets = implode(',', array_map(fn($f) => "$f=?", $fields));
        $vals = array_map(fn($f) => $data[$f] ?? null, $fields);
        $vals[] = $id;
        db_query("UPDATE events SET $sets WHERE id=?", $vals);
        return $id;
    }
    $cols = implode(',', $fields);
    $phs  = implode(',', array_fill(0, count($fields), '?'));
    return db_insert(
        "INSERT INTO events ($cols) VALUES ($phs)",
        array_map(fn($f) => $data[$f] ?? null, $fields)
    );
}
function delete_event(int $id): void {
    db_query("DELETE FROM events WHERE id = ?", [$id]);
}
function save_event_registration(array $data): int {
    return db_insert(
        "INSERT INTO event_registrations (event_id,full_name,email,phone,organization,message) VALUES (?,?,?,?,?,?)",
        [$data['event_id'],$data['full_name'],$data['email'],$data['phone']??'',$data['organization']??'',$data['message']??'']
    );
}
function get_event_registrations(int $event_id, array $opts = []): array {
    $limit  = (int)($opts['limit']  ?? 100);
    $offset = (int)($opts['offset'] ?? 0);
    return db_fetchAll(
        "SELECT * FROM event_registrations WHERE event_id = ? ORDER BY registered_at DESC LIMIT ? OFFSET ?",
        [$event_id, $limit, $offset]
    );
}
function update_registration_status(int $id, string $status): void {
    db_query("UPDATE event_registrations SET status = ? WHERE id = ?", [$status, $id]);
}

// Event gallery
function add_event_media(array $data): int {
    return db_insert(
        "INSERT INTO event_media (event_id,media_type,file_path,video_url,caption,sort_order) VALUES (?,?,?,?,?,?)",
        [$data['event_id'],$data['media_type']??'photo',$data['file_path']??'',$data['video_url']??'',$data['caption']??'',$data['sort_order']??0]
    );
}
function delete_event_media(int $id): void {
    db_query("DELETE FROM event_media WHERE id = ?", [$id]);
}

// ── Static pages ───────────────────────────────────────────
function get_static_pages(bool $footer_only = false): array {
    $where = $footer_only ? 'WHERE show_in_footer = 1' : '';
    return db_fetchAll("SELECT * FROM static_pages $where ORDER BY sort_order");
}
function get_static_page(string $slug): ?array {
    return db_fetch("SELECT * FROM static_pages WHERE slug = ?", [$slug]);
}
function save_static_page(array $data, ?int $id = null): int {
    $fields = ['slug','title','title_en','body','body_en','show_in_footer','sort_order'];
    if ($id) {
        $sets = implode(',', array_map(fn($f) => "$f=?", $fields));
        $vals = array_map(fn($f) => $data[$f] ?? null, $fields);
        $vals[] = date('Y-m-d H:i:s');
        $vals[] = $id;
        db_query("UPDATE static_pages SET $sets, updated_at=? WHERE id=?", $vals);
        return $id;
    }
    $cols = implode(',', $fields);
    $phs  = implode(',', array_fill(0, count($fields), '?'));
    return db_insert(
        "INSERT INTO static_pages ($cols) VALUES ($phs)",
        array_map(fn($f) => $data[$f] ?? null, $fields)
    );
}
function delete_static_page(int $id): void {
    db_query("DELETE FROM static_pages WHERE id = ?", [$id]);
}

// ── Newsletter ─────────────────────────────────────────────
function save_newsletter_email(string $email, string $name = ''): bool {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    try {
        db_insert_ignore('newsletter_subscribers', ['email','name'], [$email, $name]);
        return true;
    } catch (Exception $e) { return false; }
}
function get_subscribers(int $limit = 100, int $offset = 0): array {
    return db_fetchAll(
        "SELECT * FROM newsletter_subscribers ORDER BY created_at DESC LIMIT ? OFFSET ?",
        [$limit, $offset]
    );
}
function count_subscribers(): int {
    return db_count("SELECT COUNT(*) FROM newsletter_subscribers");
}

// ── Dashboard stats ────────────────────────────────────────
function get_dashboard_stats(): array {
    $total       = db_count("SELECT COUNT(*) FROM articles");
    $published   = db_count("SELECT COUNT(*) FROM articles WHERE status='published'");
    $draft       = db_count("SELECT COUNT(*) FROM articles WHERE status='draft'");
    $views       = db_count("SELECT COALESCE(SUM(views),0) FROM articles");
    $cats        = db_count("SELECT COUNT(*) FROM categories");
    $auths       = db_count("SELECT COUNT(*) FROM authors");
    $ads_total   = db_count("SELECT COUNT(*) FROM advertisements");
    $ads_active  = db_count("SELECT COUNT(*) FROM advertisements WHERE active=1");
    $subscribers = db_count("SELECT COUNT(*) FROM newsletter_subscribers");
    $events_total= db_count("SELECT COUNT(*) FROM events");
    $events_reg  = db_count("SELECT COUNT(*) FROM event_registrations");
    $bycat  = db_fetchAll(
        "SELECT c.name, c.name_np, c.color, COUNT(a.id) AS cnt
         FROM categories c
         LEFT JOIN articles a ON a.category_id = c.id AND a.status = 'published'
         GROUP BY c.id ORDER BY cnt DESC LIMIT 10"
    );
    $recent = get_articles(['status'=>'published','limit'=>6]);
    return compact('total','published','draft','views','cats','auths','ads_total','ads_active',
                   'subscribers','events_total','events_reg','bycat','recent');
}
