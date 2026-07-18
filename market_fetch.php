<?php
/**
 * Live Market Data Fetcher
 * Fetches NRB forex rates + gold/silver prices and updates market_widgets DB
 * Called automatically from home.php with 6-hour cache
 * Can also run standalone: php market_fetch.php
 */
if (!defined('BASE_DIR')) {
    define('BASE_DIR', __DIR__);
    define('SRC_DIR',  BASE_DIR . '/src');
    define('DATA_DIR', BASE_DIR . '/data');
    define('DB_PATH',  DATA_DIR . '/news.db');
    require_once SRC_DIR . '/config.php';
    require_once SRC_DIR . '/database.php';
    require_once SRC_DIR . '/helpers.php';
    require_once SRC_DIR . '/init.php';
}

function fetch_url(string $url, int $timeout = 8): ?string {
    $ctx = stream_context_create(['http' => [
        'timeout'        => $timeout,
        'ignore_errors'  => true,
        'user_agent'     => 'Mozilla/5.0 (compatible; NepalNewsPortal/1.0)',
        'header'         => "Accept: application/json\r\n",
    ]]);
    $res = @file_get_contents($url, false, $ctx);
    return $res !== false ? $res : null;
}

function update_market_widget(string $type, string $label, string $value, float $change_pct, int $sort = 99): void {
    $existing = db_fetch("SELECT id FROM market_widgets WHERE widget_type=? AND label=?", [$type, $label]);
    if ($existing) {
        db_query(
            "UPDATE market_widgets SET value=?, change_pct=?, updated_at=CURRENT_TIMESTAMP WHERE id=?",
            [$value, $change_pct, $existing['id']]
        );
    } else {
        db_query(
            "INSERT INTO market_widgets (widget_type, label, value, change_pct, sort_order) VALUES (?,?,?,?,?)",
            [$type, $label, $value, $change_pct, $sort]
        );
    }
}

function refresh_market_data(): bool {
    $updated = false;

    // ── 1. NRB Forex Rates ──────────────────────────────────
    $today = date('Y-m-d');
    $url   = "https://www.nrb.org.np/api/forex/v1/rates?page=1&per_page=5&from={$today}&to={$today}";
    $raw   = fetch_url($url);
    if ($raw) {
        $json = json_decode($raw, true);
        $rates = $json['data']['payload'][0]['rates'] ?? [];

        $want = ['USD'=>['USD/NPR',1],'EUR'=>['EUR/NPR',2],'GBP'=>['GBP/NPR',3],'INR'=>['INR/NPR',5],'AUD'=>['AUD/NPR',4],'SAR'=>['SAR/NPR',6],'AED'=>['AED/NPR',7],'QAR'=>['QAR/NPR',8]];
        foreach ($rates as $r) {
            $iso   = $r['currency']['iso3'];
            $unit  = (int)($r['currency']['unit'] ?? 1);
            $buy   = (float)$r['buy'];
            $sell  = (float)$r['sell'];
            $mid   = ($buy + $sell) / 2;
            if (!isset($want[$iso])) continue;
            [$label, $sort] = $want[$iso];
            // Calculate per-unit value
            $val_str = $unit > 1
                ? 'रू ' . number_format($buy, 2) . ' / ' . number_format($sell, 2) . ' (प्रति ' . $unit . ')'
                : 'रू ' . number_format($sell, 2);
            if ($iso === 'INR') {
                // INR is quoted per 100, display per 1
                $val_str = 'रू ' . number_format($sell / 100, 4);
            }
            // Change: approximate using buy-sell spread as indicator
            $chg = 0.0; // NRB doesn't provide daily change; keep 0 unless we store yesterday's
            update_market_widget('forex', $label, $val_str, $chg, $sort);
        }
        $updated = true;
    }

    // ── 2. Nepal Gold / Silver (from NRB reference or FENEGOSIDA) ──
    // Try to get international gold price and convert using USD/NPR rate
    $gold_url = fetch_url("https://query1.finance.yahoo.com/v8/finance/chart/GC=F?interval=1d&range=1d");
    if ($gold_url) {
        $gj = json_decode($gold_url, true);
        $gold_usd_per_oz = $gj['chart']['result'][0]['meta']['regularMarketPrice'] ?? null;
        if ($gold_usd_per_oz) {
            // Get current USD/NPR rate from DB
            $usd_row = db_fetch("SELECT value FROM market_widgets WHERE label='USD/NPR' LIMIT 1");
            $usd_npr = $usd_row ? (float)preg_replace('/[^0-9.]/', '', $usd_row['value']) : 154.0;
            if ($usd_npr < 100) $usd_npr = 154.0;

            $gram_per_oz  = 31.1035;
            $gold_npr_gram = $gold_usd_per_oz * $usd_npr / $gram_per_oz;
            $gold_10g_npr  = $gold_npr_gram * 10;
            $gold_tola_npr = $gold_npr_gram * 11.664; // 1 tola = 11.664g

            // Silver
            $silver_url = fetch_url("https://query1.finance.yahoo.com/v8/finance/chart/SI=F?interval=1d&range=1d");
            $silver_usd_per_oz = null;
            if ($silver_url) {
                $sj = json_decode($silver_url, true);
                $silver_usd_per_oz = $sj['chart']['result'][0]['meta']['regularMarketPrice'] ?? null;
            }

            update_market_widget('gold', 'सुन (१० ग्राम)', 'रू ' . number_format($gold_10g_npr, 0), 0.0, 1);
            update_market_widget('gold', 'सुन (१ तोला)',   'रू ' . number_format($gold_tola_npr, 0), 0.0, 2);

            if ($silver_usd_per_oz) {
                $silver_npr_gram = $silver_usd_per_oz * $usd_npr / $gram_per_oz;
                $silver_kg_npr   = $silver_npr_gram * 1000;
                update_market_widget('gold', 'चाँदी (१ किलो)', 'रू ' . number_format($silver_kg_npr, 0), 0.0, 3);
            }
            $updated = true;
        }
    }

    // ── 3. NEPSE Index (previously missing entirely — the homepage
    //      NEPSE widget checked market_widgets for widget_type='nepse'
    //      but nothing ever populated it, so it silently never showed) ──
    $nepse_url = fetch_url("https://www.nepalipaisa.com/api/GetMarketCap?type=main");
    if ($nepse_url) {
        $nj = json_decode($nepse_url, true);
        if (isset($nj[0]['nepseIndex'])) {
            $idx    = (float)$nj[0]['nepseIndex'];
            $chg    = (float)($nj[0]['percentChange'] ?? 0);
            update_market_widget('nepse', 'नेप्से परिसूचक', number_format($idx, 2), $chg, 1);
            $updated = true;
        }
    }

    // ── 4. Store last fetch time ─────────────────────────────
    save_setting('market_last_fetch', date('Y-m-d H:i:s'));

    return $updated;
}

// Auto-check cache: only refresh if last fetch was > 6 hours ago (or never)
function maybe_refresh_market(int $ttl_hours = 6): bool {
    $last = setting('market_last_fetch', '');
    if (!$last || (time() - strtotime($last)) > ($ttl_hours * 3600)) {
        return refresh_market_data();
    }
    return false;
}

// If run directly via CLI / cron
if (php_sapi_name() === 'cli' || (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__)) {
    $result = refresh_market_data();
    echo $result ? "✓ Market data updated.\n" : "✗ Fetch failed.\n";
}
