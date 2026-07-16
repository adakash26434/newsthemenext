<?php
// ── Security helpers ───────────────────────────────────────
function h(mixed $v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}
function csrf_check(): void {
    if (!hash_equals(csrf_token(), $_POST['csrf_token'] ?? '')) {
        http_response_code(403); die('CSRF check failed.');
    }
}

// ── Admin auth ─────────────────────────────────────────────
function admin_login(string $user, string $pass): bool {
    $stored_user = setting('admin_username', DEFAULT_ADMIN_USERNAME);
    $stored_hash = setting('admin_password', '');
    if ($user !== $stored_user) return false;
    // Support legacy plain-text passwords and hashed
    if ($stored_hash && str_starts_with($stored_hash, '$2y$')) {
        return password_verify($pass, $stored_hash);
    }
    // Fallback plain
    $plain = setting('admin_password_plain', DEFAULT_ADMIN_PASSWORD);
    return $pass === $plain || $pass === $stored_hash;
}
function is_admin(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['admin_logged_in']);
}
function admin_check(): void {
    if (!is_admin()) { redirect('admin/login'); exit; }
}

// ── Flash messages ─────────────────────────────────────────
function flash_set(string $key, string $msg): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'][$key] = $msg;
}
function flash_get(string $key): ?string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

// ── Redirect ───────────────────────────────────────────────
function redirect(string $path): void {
    header('Location: /' . ltrim($path, '/'));
    exit;
}

// ── URL helpers ────────────────────────────────────────────
function current_url(): string {
    return $_SERVER['REQUEST_URI'] ?? '/';
}

// ── String helpers ─────────────────────────────────────────
function excerpt(string $text, int $words = 20): string {
    $text  = strip_tags($text);
    $parts = preg_split('/\s+/u', trim($text));
    if (count($parts) <= $words) return $text;
    return implode(' ', array_slice($parts, 0, $words)) . '…';
}
function slug_from_title(string $title): string {
    $slug = mb_strtolower(trim($title));
    $slug = preg_replace('/[^\w\s-]/u', '', $slug);
    $slug = preg_replace('/[\s_]+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = $slug ?: 'article-' . time();
    return substr($slug, 0, 200);
}
function np_number(int $n): string {
    return str_replace(
        ['0','1','2','3','4','5','6','7','8','9'],
        ['०','१','२','३','४','५','६','७','८','९'],
        (string)$n);
}

// ── Date helpers ───────────────────────────────────────────
function format_date(string $date, bool $time = false): string {
    $ts   = strtotime($date);
    if (!$ts) return $date;
    $months_np = [
        1=>'जनवरी',2=>'फेब्रुअरी',3=>'मार्च',4=>'अप्रिल',
        5=>'मे',6=>'जुन',7=>'जुलाई',8=>'अगस्ट',
        9=>'सेप्टेम्बर',10=>'अक्टोबर',11=>'नोभेम्बर',12=>'डिसेम्बर',
    ];
    $d  = date('j', $ts);
    $m  = (int)date('n', $ts);
    $y  = date('Y', $ts);
    $str = np_number($d) . ' ' . $months_np[$m] . ' ' . np_number((int)$y);
    if ($time) $str .= ', ' . date('g:i A', $ts);
    return $str;
}
function time_ago(string $date): string {
    $ts   = strtotime($date);
    if (!$ts) return '';
    $diff = time() - $ts;
    if ($diff < 60)     return 'भर्खर';
    if ($diff < 3600)   return np_number((int)round($diff/60)) . ' मिनेट अघि';
    if ($diff < 86400)  return np_number((int)round($diff/3600)) . ' घण्टा अघि';
    if ($diff < 604800) return np_number((int)round($diff/86400)) . ' दिन अघि';
    return format_date($date);
}
function bs_date_today(): string {
    $y   = (int)date('Y') + 57;
    $m   = (int)date('n');
    $d   = (int)date('j');
    $days_np = ['आइतबार','सोमबार','मंगलबार','बुधबार','बिहीबार','शुक्रबार','शनिबार'];
    $dow  = $days_np[(int)date('w')];
    $months = NP_MONTHS;
    return np_number($y) . ' ' . $months[$m] . ' ' . np_number($d) . ' गते ' . $dow;
}

// ── Language helpers ───────────────────────────────────────
function current_lang(): string {
    return $_SESSION['lang'] ?? setting('default_lang', 'np');
}
function set_lang(string $lang): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['lang'] = in_array($lang, ['np','en']) ? $lang : 'np';
}
function lang_label(string $np_text, string $en_text): string {
    return current_lang() === 'en' ? $en_text : $np_text;
}

// ── Theme helpers ──────────────────────────────────────────
function primary_color(): string  { return setting('primary_color', DEFAULT_COLOR_PRIMARY); }
function nav_color(): string      { return setting('nav_color',     DEFAULT_COLOR_NAV); }
function accent_color(): string   { return setting('accent_color',  DEFAULT_COLOR_ACCENT); }
function site_name(): string      { return current_lang()==='en' ? setting('site_name_en', DEFAULT_SITE_NAME_EN) : setting('site_name', DEFAULT_SITE_NAME); }
function site_name_np(): string   { return setting('site_name', DEFAULT_SITE_NAME); }
function site_name_en(): string   { return setting('site_name_en', DEFAULT_SITE_NAME_EN); }
function site_tagline(): string   { return setting('site_tagline', DEFAULT_SITE_TAGLINE); }
function site_logo_url(): string  { return setting('site_logo_url', ''); }
function site_logo_text(): string { return setting('site_logo_text', site_name_np()); }
function category_color(string $color = ''): string {
    return $color ?: accent_color();
}

// ── Ads helpers ────────────────────────────────────────────
function render_ad(array $ad): string {
    $out = '<div class="ad-slot" data-pos="' . h($ad['position']) . '">';
    if ($ad['link_url']) {
        $out .= '<a href="/ad/click/' . (int)$ad['id'] . '" target="_blank" rel="noopener sponsored">';
    }
    if ($ad['type'] === 'image' && $ad['image_url']) {
        $out .= '<img src="' . h($ad['image_url']) . '" alt="' . h($ad['title']) . '" loading="lazy" style="max-width:100%;display:block;margin:0 auto">';
    } elseif ($ad['type'] === 'code' && $ad['code']) {
        $out .= $ad['code'];
    } else {
        $out .= '<div class="ad-placeholder"><span>विज्ञापन</span><br><small>' . h($ad['title']) . '</small></div>';
    }
    if ($ad['link_url']) $out .= '</a>';
    $out .= '</div>';
    return $out;
}
function render_ads(string $position): void {
    $ads = get_active_ads($position);
    if (empty($ads)) return;
    echo '<div class="ads-container ads-' . h($position) . '">';
    echo '<span class="ad-label">विज्ञापन</span>';
    foreach ($ads as $ad) echo render_ad($ad);
    echo '</div>';
}

// ── Pagination ─────────────────────────────────────────────
function paginate(int $total, int $per_page, int $current_page, string $url_pattern): array {
    $total_pages  = max(1, (int)ceil($total / $per_page));
    $current_page = max(1, min($current_page, $total_pages));
    return [
        'total'       => $total,
        'per_page'    => $per_page,
        'current'     => $current_page,
        'total_pages' => $total_pages,
        'offset'      => ($current_page - 1) * $per_page,
        'url_pattern' => $url_pattern,
        'has_prev'    => $current_page > 1,
        'has_next'    => $current_page < $total_pages,
        'prev_page'   => $current_page - 1,
        'next_page'   => $current_page + 1,
    ];
}
function render_pagination(array $pag): void {
    if ($pag['total_pages'] <= 1) return;
    echo '<nav class="pagination" aria-label="Pagination">';
    if ($pag['has_prev']) {
        echo '<a href="' . str_replace('{page}', $pag['prev_page'], $pag['url_pattern']) . '" class="page-link">‹ अघिल्लो</a>';
    }
    $start = max(1, $pag['current'] - 2);
    $end   = min($pag['total_pages'], $pag['current'] + 2);
    if ($start > 1) { echo '<a href="' . str_replace('{page}', 1, $pag['url_pattern']) . '" class="page-link">1</a>'; if ($start > 2) echo '<span class="page-dots">…</span>'; }
    for ($i = $start; $i <= $end; $i++) {
        $cls = $i === $pag['current'] ? 'page-link active' : 'page-link';
        echo '<a href="' . str_replace('{page}', $i, $pag['url_pattern']) . '" class="' . $cls . '">' . np_number($i) . '</a>';
    }
    if ($end < $pag['total_pages']) {
        if ($end < $pag['total_pages'] - 1) echo '<span class="page-dots">…</span>';
        echo '<a href="' . str_replace('{page}', $pag['total_pages'], $pag['url_pattern']) . '" class="page-link">' . np_number($pag['total_pages']) . '</a>';
    }
    if ($pag['has_next']) {
        echo '<a href="' . str_replace('{page}', $pag['next_page'], $pag['url_pattern']) . '" class="page-link">पछिल्लो ›</a>';
    }
    echo '</nav>';
}

// ── File upload ────────────────────────────────────────────
function handle_upload(string $field, string $sub = 'general'): string {
    if (empty($_FILES[$field]['name'])) return '';
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) return '';
    $allowed = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml'];
    $mime    = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed)) return '';
    if ($file['size'] > 5 * 1024 * 1024) return ''; // 5MB max
    $ext  = match($mime) {
        'image/jpeg' => 'jpg', 'image/png' => 'png',
        'image/gif'  => 'gif', 'image/webp' => 'webp',
        'image/svg+xml' => 'svg', default => 'jpg'
    };
    $dir  = BASE_DIR . '/assets/uploads/' . $sub;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = uniqid('img_', true) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return '';
    return '/assets/uploads/' . $sub . '/' . $name;
}
