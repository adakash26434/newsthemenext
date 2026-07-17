<?php
// ══════════════════════════════════════════════════════════
//  Simple IP-based Rate Limiter
//  Uses the rate_limits table (file-based fallback if DB unavailable).
//  Call rate_limit_check() on sensitive forms before processing.
// ══════════════════════════════════════════════════════════

/**
 * Check if the current IP has exceeded rate limit for a given action.
 * Returns true if allowed, false if limit exceeded.
 *
 * @param string $action   e.g. 'login', 'newsletter', 'event_register'
 * @param int    $max      max attempts in window
 * @param int    $window   window in seconds (default 300 = 5 min)
 */
function rate_limit_check(string $action, int $max = 5, int $window = 300): bool
{
    $ip = rate_limit_ip();

    try {
        $db  = get_db();
        $now = time();
        $key = $action . ':' . $ip;

        // Clean old entries (best-effort, not every request)
        if (rand(1, 20) === 1) {
            if (db_driver() === 'mysql') {
                $db->exec("DELETE FROM rate_limits WHERE expires_at < NOW()");
            } else {
                $db->exec("DELETE FROM rate_limits WHERE expires_at < CURRENT_TIMESTAMP");
            }
        }

        // Fetch current bucket
        $row = db_fetch("SELECT * FROM rate_limits WHERE action_key = ?", [$key]);

        if (!$row) {
            // First attempt — create bucket
            $exp = date('Y-m-d H:i:s', $now + $window);
            db_query(
                "INSERT INTO rate_limits (action_key, attempts, expires_at) VALUES (?, 1, ?)",
                [$key, $exp]
            );
            return true;
        }

        // Bucket expired — reset
        if (strtotime($row['expires_at']) < $now) {
            $exp = date('Y-m-d H:i:s', $now + $window);
            db_query(
                "UPDATE rate_limits SET attempts = 1, expires_at = ? WHERE action_key = ?",
                [$exp, $key]
            );
            return true;
        }

        // Over limit?
        if ((int)$row['attempts'] >= $max) {
            return false;
        }

        // Increment
        db_query(
            "UPDATE rate_limits SET attempts = attempts + 1 WHERE action_key = ?",
            [$key]
        );
        return true;

    } catch (\Exception $e) {
        // If rate_limits table doesn't exist yet, allow through
        return true;
    }
}

/** Best-effort client IP (behind proxy-aware) */
function rate_limit_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_REAL_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $k) {
        $v = $_SERVER[$k] ?? '';
        if ($v) {
            // X-Forwarded-For may be comma-list; take first
            $ip = trim(explode(',', $v)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

/**
 * Terminate the request with a 429 response if rate limit exceeded.
 * Convenience wrapper — call instead of checking return value manually.
 */
function rate_limit_or_die(string $action, int $max = 5, int $window = 300, string $msg = ''): void
{
    if (!rate_limit_check($action, $max, $window)) {
        http_response_code(429);
        $msg = $msg ?: 'धेरै प्रयास भयो। केही समयपछि फेरि प्रयास गर्नुहोस्।';
        if (!headers_sent()) {
            flash_set('error', $msg);
        }
        $back = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $back);
        exit;
    }
}

// Alias for convenience
function check_rate_limit(string $action, string $ip, int $max = 5, int $window = 300): bool {
    return rate_limit_check($action, $max, $window);
}
