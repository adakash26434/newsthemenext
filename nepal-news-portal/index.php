<?php
/**
 * Nepal News Portal — Front Controller
 * Place in document root (public_html/)
 */
define('APP_START', true);

require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/database.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/layout/admin_layout.php';

session_name(SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) session_start();

// Language switch
if (isset($_GET['lang']) && in_array($_GET['lang'], ['np','en'])) {
    set_lang($_GET['lang']);
    $back = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $back); exit;
}

// Auto-init DB
try {
    get_db()->query("SELECT 1 FROM settings LIMIT 1");
} catch (Exception $e) {
    require __DIR__ . '/src/init.php';
}

$uri  = strtok($_SERVER['REQUEST_URI'], '?');
$uri  = rtrim($uri, '/') ?: '/';
$meth = $_SERVER['REQUEST_METHOD'];

function route_match(string $pattern, string $uri): array|false {
    $regex = preg_replace('#\{[^}]+\}#', '([^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';
    if (preg_match($regex, $uri, $m)) { array_shift($m); return $m; }
    return false;
}

// ══════════════════════════════════════════════════════════
//  POST routes
// ══════════════════════════════════════════════════════════
if ($meth === 'POST') {

    // Admin login
    if ($uri === '/admin/login') {
        csrf_check();
        $u = trim($_POST['username'] ?? '');
        $p = $_POST['password'] ?? '';
        if (admin_login($u, $p)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user']      = $u;
            redirect('admin');
        } else {
            flash_set('error', 'गलत प्रयोगकर्ता नाम वा पासवर्ड।');
            redirect('admin/login');
        }
    }

    if ($uri === '/admin/logout') {
        csrf_check(); session_destroy(); redirect('admin/login');
    }

    // ── Articles ──────────────────────────────────────────
    if ($uri === '/admin/articles/save') {
        admin_check(); csrf_check();
        $id  = (int)($_POST['id'] ?? 0) ?: null;
        $pub = $_POST['published_at'] ?? null;
        if ($pub) $pub = date('Y-m-d H:i:s', strtotime($pub));
        // Handle image upload
        $image_url = trim($_POST['image_url'] ?? '');
        $uploaded  = handle_upload('image_file', 'articles');
        if ($uploaded) $image_url = $uploaded;
        $data = [
            'title'        => trim($_POST['title'] ?? ''),
            'title_np'     => trim($_POST['title_np'] ?? ''),
            'slug'         => slug_from_title(trim($_POST['slug'] ?? $_POST['title'] ?? '')),
            'content'      => $_POST['content'] ?? '',
            'content_np'   => $_POST['content_np'] ?? '',
            'summary'      => trim($_POST['summary'] ?? ''),
            'summary_np'   => trim($_POST['summary_np'] ?? ''),
            'language'     => in_array($_POST['language']??'',['np','en']) ? $_POST['language'] : 'np',
            'status'       => in_array($_POST['status']??'',['draft','published']) ? $_POST['status'] : 'draft',
            'featured'     => isset($_POST['featured']) ? 1 : 0,
            'is_breaking'  => isset($_POST['is_breaking']) ? 1 : 0,
            'image_url'    => $image_url,
            'category_id'  => (int)($_POST['category_id'] ?? 0),
            'author_id'    => (int)($_POST['author_id'] ?? 0),
            'published_at' => $pub ?: date('Y-m-d H:i:s'),
            'tag_ids'      => array_map('intval', $_POST['tag_ids'] ?? []),
        ];
        if (!$data['title'] || !$data['category_id'] || !$data['author_id']) {
            flash_set('error', 'शीर्षक, श्रेणी र लेखक अनिवार्य छ।');
            redirect($id ? "admin/articles?action=edit&id=$id" : 'admin/articles?action=new');
        }
        $base_slug = $data['slug'];
        $i = 1;
        while (true) {
            $conflict = db_fetch("SELECT id FROM articles WHERE slug=?", [$data['slug']]);
            if (!$conflict || ($id && $conflict['id'] == $id)) break;
            $data['slug'] = $base_slug . '-' . $i++;
        }
        $new_id = save_article($data, $id);
        flash_set('success', $id ? 'लेख सफलतापूर्वक अपडेट भयो।' : 'नयाँ लेख प्रकाशित भयो।');
        redirect("admin/articles?action=edit&id=$new_id");
    }

    if ($uri === '/admin/articles/delete') {
        admin_check(); csrf_check();
        delete_article((int)($_POST['id'] ?? 0));
        flash_set('success', 'लेख मेटाइयो।');
        redirect('admin/articles');
    }

    // ── Categories ────────────────────────────────────────
    if ($uri === '/admin/categories/save') {
        admin_check(); csrf_check();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $data = [
            'name'       => trim($_POST['name'] ?? ''),
            'name_np'    => trim($_POST['name_np'] ?? ''),
            'slug'       => slug_from_title(trim($_POST['slug'] ?? $_POST['name'] ?? '')),
            'color'      => trim($_POST['color'] ?? '#991B1B'),
            'icon'       => trim($_POST['icon'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];
        if (!$data['name']) { flash_set('error', 'नाम अनिवार्य छ।'); redirect('admin/categories'); }
        save_category($data, $id);
        flash_set('success', 'श्रेणी सेभ गरियो।');
        redirect('admin/categories');
    }
    if ($uri === '/admin/categories/delete') {
        admin_check(); csrf_check();
        delete_category((int)($_POST['id'] ?? 0));
        flash_set('success', 'श्रेणी मेटाइयो।');
        redirect('admin/categories');
    }

    // ── Authors ───────────────────────────────────────────
    if ($uri === '/admin/authors/save') {
        admin_check(); csrf_check();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $avatar = trim($_POST['avatar_url'] ?? '');
        $up = handle_upload('avatar_file', 'avatars');
        if ($up) $avatar = $up;
        $data = [
            'name'       => trim($_POST['name'] ?? ''),
            'name_np'    => trim($_POST['name_np'] ?? ''),
            'slug'       => slug_from_title(trim($_POST['slug'] ?? $_POST['name'] ?? '')),
            'bio'        => trim($_POST['bio'] ?? ''),
            'avatar_url' => $avatar,
        ];
        if (!$data['name']) { flash_set('error', 'नाम अनिवार्य छ।'); redirect('admin/authors'); }
        save_author($data, $id);
        flash_set('success', 'लेखक सेभ गरियो।');
        redirect('admin/authors');
    }
    if ($uri === '/admin/authors/delete') {
        admin_check(); csrf_check();
        delete_author((int)($_POST['id'] ?? 0));
        flash_set('success', 'लेखक मेटाइयो।');
        redirect('admin/authors');
    }

    // ── Tags ──────────────────────────────────────────────
    if ($uri === '/admin/tags/save') {
        admin_check(); csrf_check();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $name = trim($_POST['name'] ?? '');
        if (!$name) { flash_set('error', 'नाम अनिवार्य छ।'); redirect('admin/tags'); }
        save_tag(['name'=>$name,'slug'=>slug_from_title($name)], $id);
        flash_set('success', 'ट्याग सेभ गरियो।');
        redirect('admin/tags');
    }
    if ($uri === '/admin/tags/delete') {
        admin_check(); csrf_check();
        delete_tag((int)($_POST['id'] ?? 0));
        flash_set('success', 'ट्याग मेटाइयो।');
        redirect('admin/tags');
    }

    // ── Advertisements ────────────────────────────────────
    if ($uri === '/admin/advertisements/save') {
        admin_check(); csrf_check();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $img = trim($_POST['image_url'] ?? '');
        $up  = handle_upload('image_file', 'ads');
        if ($up) $img = $up;
        $data = [
            'title'      => trim($_POST['title'] ?? ''),
            'type'       => in_array($_POST['type']??'',['image','code']) ? $_POST['type'] : 'image',
            'image_url'  => $img,
            'code'       => $_POST['code'] ?? '',
            'link_url'   => trim($_POST['link_url'] ?? ''),
            'position'   => trim($_POST['position'] ?? 'sidebar-top'),
            'active'     => isset($_POST['active']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];
        if (!$data['title']) { flash_set('error', 'शीर्षक अनिवार्य छ।'); redirect('admin/advertisements'); }
        save_ad($data, $id);
        flash_set('success', 'विज्ञापन सेभ गरियो।');
        redirect('admin/advertisements');
    }
    if ($uri === '/admin/advertisements/delete') {
        admin_check(); csrf_check();
        delete_ad((int)($_POST['id'] ?? 0));
        flash_set('success', 'विज्ञापन मेटाइयो।');
        redirect('admin/advertisements');
    }

    // ── Settings ──────────────────────────────────────────
    if ($uri === '/admin/settings/save') {
        admin_check(); csrf_check();
        $safe_keys = ['site_name','site_name_en','site_tagline','site_logo_text','ticker_label',
            'primary_color','nav_color','accent_color',
            'social_facebook','social_twitter','social_youtube','social_instagram','social_tiktok',
            'contact_email','contact_phone','contact_address',
            'footer_about','footer_about_en','meta_keywords','google_analytics',
            'youtube_channel','youtube_embed','default_lang',
            'registration_no','founded_year','copyright_text'];
        foreach ($safe_keys as $k) {
            if (isset($_POST[$k])) save_setting($k, trim($_POST[$k]));
        }
        // Logo upload
        $logo_up = handle_upload('logo_file', 'logo');
        if ($logo_up) {
            save_setting('site_logo_url', $logo_up);
        } elseif (isset($_POST['site_logo_url'])) {
            save_setting('site_logo_url', trim($_POST['site_logo_url']));
        }
        // Favicon upload
        $fav_up = handle_upload('favicon_file', 'logo');
        if ($fav_up) save_setting('favicon_url', $fav_up);
        // Password change
        $new_pass = $_POST['admin_password_new'] ?? '';
        $confirm  = $_POST['admin_password_confirm'] ?? '';
        if ($new_pass && $new_pass === $confirm) {
            save_setting('admin_password', password_hash($new_pass, PASSWORD_DEFAULT));
        }
        if (isset($_POST['admin_username']) && trim($_POST['admin_username'])) {
            save_setting('admin_username', trim($_POST['admin_username']));
        }
        flash_set('success', 'सेटिङ्स सफलतापूर्वक सेभ भयो।');
        redirect('admin/settings');
    }

    // ── Events ────────────────────────────────────────────
    if ($uri === '/admin/events/save') {
        admin_check(); csrf_check();
        $id  = (int)($_POST['id'] ?? 0) ?: null;
        $img = trim($_POST['cover_image'] ?? '');
        $up  = handle_upload('cover_file', 'events');
        if ($up) $img = $up;
        $slug_base = slug_from_title(trim($_POST['slug'] ?? $_POST['title'] ?? ''));
        $slug = $slug_base; $i = 1;
        while (true) {
            $conflict = db_fetch("SELECT id FROM events WHERE slug=?", [$slug]);
            if (!$conflict || ($id && $conflict['id'] == $id)) break;
            $slug = $slug_base . '-' . $i++;
        }
        $data = [
            'title'                 => trim($_POST['title'] ?? ''),
            'title_en'              => trim($_POST['title_en'] ?? ''),
            'slug'                  => $slug,
            'description'           => $_POST['description'] ?? '',
            'description_en'        => $_POST['description_en'] ?? '',
            'cover_image'           => $img,
            'venue'                 => trim($_POST['venue'] ?? ''),
            'venue_en'              => trim($_POST['venue_en'] ?? ''),
            'start_datetime'        => trim($_POST['start_datetime'] ?? ''),
            'end_datetime'          => trim($_POST['end_datetime'] ?? ''),
            'registration_open'     => isset($_POST['registration_open']) ? 1 : 0,
            'registration_deadline' => trim($_POST['registration_deadline'] ?? '') ?: null,
            'capacity'              => (int)($_POST['capacity'] ?? 0) ?: null,
            'status'                => $_POST['status'] ?? 'upcoming',
            'show_in_menu'          => isset($_POST['show_in_menu']) ? 1 : 0,
        ];
        if (!$data['title']) { flash_set('error', 'शीर्षक अनिवार्य छ।'); redirect('admin/events'); }
        $eid = save_event($data, $id);
        flash_set('success', 'कार्यक्रम सेभ गरियो।');
        redirect("admin/events?action=edit&id=$eid");
    }
    if ($uri === '/admin/events/delete') {
        admin_check(); csrf_check();
        delete_event((int)($_POST['id'] ?? 0));
        flash_set('success', 'कार्यक्रम मेटाइयो।');
        redirect('admin/events');
    }
    if ($uri === '/admin/events/gallery/add') {
        admin_check(); csrf_check();
        $eid = (int)($_POST['event_id'] ?? 0);
        $fp  = handle_upload('photo_file', 'events/gallery');
        $data = [
            'event_id'   => $eid,
            'media_type' => trim($_POST['media_type'] ?? 'photo'),
            'file_path'  => $fp ?: '',
            'video_url'  => trim($_POST['video_url'] ?? ''),
            'caption'    => trim($_POST['caption'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];
        add_event_media($data);
        flash_set('success', 'मिडिया थपियो।');
        redirect("admin/events/gallery?event_id=$eid");
    }
    if ($uri === '/admin/events/gallery/delete') {
        admin_check(); csrf_check();
        $eid = (int)($_POST['event_id'] ?? 0);
        delete_event_media((int)($_POST['id'] ?? 0));
        flash_set('success', 'मिडिया मेटाइयो।');
        redirect("admin/events/gallery?event_id=$eid");
    }
    if ($uri === '/admin/events/registrations/update') {
        admin_check(); csrf_check();
        $eid = (int)($_POST['event_id'] ?? 0);
        update_registration_status((int)($_POST['id'] ?? 0), $_POST['status'] ?? 'pending');
        redirect("admin/events/registrations?event_id=$eid");
    }

    // ── Static pages ──────────────────────────────────────
    if ($uri === '/admin/pages/save') {
        admin_check(); csrf_check();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $data = [
            'slug'           => slug_from_title(trim($_POST['slug'] ?? '')),
            'title'          => trim($_POST['title'] ?? ''),
            'title_en'       => trim($_POST['title_en'] ?? ''),
            'body'           => $_POST['body'] ?? '',
            'body_en'        => $_POST['body_en'] ?? '',
            'show_in_footer' => isset($_POST['show_in_footer']) ? 1 : 0,
            'sort_order'     => (int)($_POST['sort_order'] ?? 0),
        ];
        if (!$data['title']) { flash_set('error', 'शीर्षक अनिवार्य छ।'); redirect('admin/pages'); }
        save_static_page($data, $id);
        flash_set('success', 'पृष्ठ सेभ गरियो।');
        redirect('admin/pages');
    }
    if ($uri === '/admin/pages/delete') {
        admin_check(); csrf_check();
        delete_static_page((int)($_POST['id'] ?? 0));
        flash_set('success', 'पृष्ठ मेटाइयो।');
        redirect('admin/pages');
    }

    // ── Newsletter subscribe ──────────────────────────────
    if ($uri === '/newsletter/subscribe') {
        csrf_check();
        $email = trim($_POST['email'] ?? '');
        $name  = trim($_POST['name'] ?? '');
        if (save_newsletter_email($email, $name)) {
            flash_set('success', 'न्यूजलेटर सदस्यता सफल भयो! धन्यवाद।');
        } else {
            flash_set('error', 'इमेल अवैध वा पहिले नै दर्ता छ।');
        }
        redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    // ── Ad click tracking ─────────────────────────────────
    http_response_code(404); exit;
}

// ══════════════════════════════════════════════════════════
//  GET routes
// ══════════════════════════════════════════════════════════

// Ad click redirect
if ($m = route_match('/ad/click/{id}', $uri)) {
    $ad = db_fetch("SELECT * FROM advertisements WHERE id=?", [(int)$m[0]]);
    if ($ad && $ad['link_url']) {
        track_ad_click((int)$m[0]);
        header('Location: ' . $ad['link_url']); exit;
    }
    redirect('/');
}

// ── Admin routes ──────────────────────────────────────────
if ($uri === '/admin' || $uri === '/admin/') {
    admin_check(); require SRC_DIR . '/admin/dashboard.php'; exit;
}
if ($uri === '/admin/login') {
    if (is_admin()) redirect('admin');
    require SRC_DIR . '/admin/login.php'; exit;
}
if ($uri === '/admin/articles') {
    admin_check();
    if (isset($_GET['action']) && in_array($_GET['action'],['new','edit'])) {
        require SRC_DIR . '/admin/article_form.php';
    } else {
        require SRC_DIR . '/admin/articles.php';
    }
    exit;
}
if ($uri === '/admin/categories') { admin_check(); require SRC_DIR . '/admin/categories.php'; exit; }
if ($uri === '/admin/authors')    { admin_check(); require SRC_DIR . '/admin/authors.php'; exit; }
if ($uri === '/admin/tags')       { admin_check(); require SRC_DIR . '/admin/tags.php'; exit; }
if ($uri === '/admin/advertisements') { admin_check(); require SRC_DIR . '/admin/advertisements.php'; exit; }
if ($uri === '/admin/settings')   { admin_check(); require SRC_DIR . '/admin/settings.php'; exit; }
if ($uri === '/admin/events')     { admin_check(); require SRC_DIR . '/admin/events.php'; exit; }
if ($uri === '/admin/events/gallery')       { admin_check(); require SRC_DIR . '/admin/event_gallery.php'; exit; }
if ($uri === '/admin/events/registrations') { admin_check(); require SRC_DIR . '/admin/event_registrations.php'; exit; }
if ($uri === '/admin/pages')      { admin_check(); require SRC_DIR . '/admin/static_pages.php'; exit; }
if ($uri === '/admin/subscribers'){ admin_check(); require SRC_DIR . '/admin/subscribers.php'; exit; }

// CSV export registrations
if ($uri === '/admin/events/registrations/export') {
    admin_check();
    $eid  = (int)($_GET['event_id'] ?? 0);
    $evt  = get_event_by_id($eid);
    $regs = get_event_registrations($eid, ['limit'=>9999]);
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="registrations-' . $eid . '.csv"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','नाम','Email','फोन','संस्था','सन्देश','स्थिति','मिति']);
    foreach ($regs as $r) {
        fputcsv($out, [$r['id'],$r['full_name'],$r['email'],$r['phone'],$r['organization'],$r['message'],$r['status'],$r['registered_at']]);
    }
    fclose($out);
    exit;
}

// Sitemap
if ($uri === '/sitemap.xml') {
    require __DIR__ . '/sitemap.php'; exit;
}

// ── Public routes ─────────────────────────────────────────
if ($uri === '/') {
    require SRC_DIR . '/pages/home.php'; exit;
}
if ($m = route_match('/article/{slug}', $uri)) {
    $_slug = $m[0];
    require SRC_DIR . '/pages/article.php'; exit;
}
if ($m = route_match('/category/{slug}', $uri)) {
    $_slug = $m[0];
    require SRC_DIR . '/pages/category.php'; exit;
}
if ($m = route_match('/author/{slug}', $uri)) {
    $_slug = $m[0];
    require SRC_DIR . '/pages/author.php'; exit;
}
if ($uri === '/search') {
    require SRC_DIR . '/pages/search.php'; exit;
}
if ($uri === '/events' || $uri === '/events/') {
    require SRC_DIR . '/pages/events.php'; exit;
}
if ($m = route_match('/event/{slug}', $uri)) {
    $_slug = $m[0];
    require SRC_DIR . '/pages/event_single.php'; exit;
}
if ($m = route_match('/event/{slug}/register', $uri)) {
    // AJAX registration endpoint
    $_event_slug = $m[0];
    require SRC_DIR . '/pages/event_register.php'; exit;
}
if ($m = route_match('/page/{slug}', $uri)) {
    $_slug = $m[0];
    require SRC_DIR . '/pages/static_page.php'; exit;
}

// 404
http_response_code(404);
require SRC_DIR . '/pages/404.php';
