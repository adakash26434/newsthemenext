<?php
/**
 * User Authentication System
 * Simple session-based user authentication with bookmarks
 */

// Create users table if not exists
function init_users_table(): void {
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        is_admin INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    db_query($sql);
    
    // Create bookmarks table
    $sql2 = "CREATE TABLE IF NOT EXISTS bookmarks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        article_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
        UNIQUE(user_id, article_id)
    )";
    db_query($sql2);
    
    // Newsletter subscribers table
    $sql3 = "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        name VARCHAR(100),
        subscribed INTEGER DEFAULT 1,
        subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        unsubscribed_at DATETIME,
        ip_address VARCHAR(45)
    )";
    db_query($sql3);
}

function user_register(string $email, string $password, string $name): array {
    init_users_table();
    
    // Check if email exists
    $existing = db_fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing) {
        return ['success' => false, 'error' => 'Email already registered'];
    }
    
    // Hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $user_id = db_query(
        "INSERT INTO users (email, password_hash, name) VALUES (?, ?, ?)",
        [$email, $hash, $name]
    );
    
    return ['success' => true, 'user_id' => $user_id];
}

function user_login(string $email, string $password): array {
    init_users_table();
    
    $user = db_fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
    if (!$user) {
        return ['success' => false, 'error' => 'Invalid email or password'];
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Invalid email or password'];
    }
    
    // Set session
    configure_session();
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_is_admin'] = $user['is_admin'];
    
    return ['success' => true, 'user' => $user];
}

function user_logout(): void {
    configure_session();
    if (session_status() === PHP_SESSION_NONE) session_start();
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_is_admin']);
    session_destroy();
}

function is_logged_in(): bool {
    configure_session();
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    return [
        'id' => $_SESSION['user_id'] ?? 0,
        'email' => $_SESSION['user_email'] ?? '',
        'name' => $_SESSION['user_name'] ?? '',
        'is_admin' => $_SESSION['user_is_admin'] ?? 0
    ];
}

// Bookmarks
function user_bookmark(int $article_id): bool {
    if (!is_logged_in()) return false;
    try {
        db_query(
            "INSERT OR IGNORE INTO bookmarks (user_id, article_id) VALUES (?, ?)",
            [$_SESSION['user_id'], $article_id]
        );
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function user_unbookmark(int $article_id): bool {
    if (!is_logged_in()) return false;
    try {
        db_query(
            "DELETE FROM bookmarks WHERE user_id = ? AND article_id = ?",
            [$_SESSION['user_id'], $article_id]
        );
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function user_has_bookmarked(int $article_id): bool {
    if (!is_logged_in()) return false;
    $result = db_fetchOne(
        "SELECT id FROM bookmarks WHERE user_id = ? AND article_id = ?",
        [$_SESSION['user_id'], $article_id]
    );
    return !empty($result);
}

function user_bookmarks(int $limit = 50): array {
    if (!is_logged_in()) return [];
    try {
        return db_fetchAll("
            SELECT a.*, b.created_at as bookmarked_at 
            FROM bookmarks b 
            JOIN articles a ON b.article_id = a.id 
            WHERE b.user_id = ? 
            ORDER BY b.created_at DESC 
            LIMIT ?",
            [$_SESSION['user_id'], $limit]
        );
    } catch (Exception $e) {
        return [];
    }
}

// Newsletter
function newsletter_subscribe(string $email, string $name = ''): bool {
    try {
        db_query(
            "INSERT OR REPLACE INTO newsletter_subscribers (email, name, subscribed, subscribed_at, unsubscribed_at) VALUES (?, ?, 1, CURRENT_TIMESTAMP, NULL)",
            [$email, $name]
        );
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function newsletter_unsubscribe(string $email): bool {
    try {
        db_query(
            "UPDATE newsletter_subscribers SET subscribed = 0, unsubscribed_at = CURRENT_TIMESTAMP WHERE email = ?",
            [$email]
        );
        return true;
    } catch (Exception $e) {
        return false;
    }
}
