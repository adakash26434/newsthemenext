<?php
/**
 * Live Data Database Tables Setup
 */

function install_live_data_tables(): void {
    $db = db();
    
    // API Cache Table
    $db->query("
        CREATE TABLE IF NOT EXISTS api_cache (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cache_key VARCHAR(100) NOT NULL,
            data LONGTEXT,
            fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            UNIQUE KEY unique_key (cache_key),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Weather Alerts Table
    $db->query("
        CREATE TABLE IF NOT EXISTS weather_alerts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            alert_type VARCHAR(50) NOT NULL,
            severity VARCHAR(20) DEFAULT 'moderate',
            title VARCHAR(255),
            description TEXT,
            source VARCHAR(100),
            start_time DATETIME,
            end_time DATETIME,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Earthquake Records Table
    $db->query("
        CREATE TABLE IF NOT EXISTS earthquake_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            external_id VARCHAR(50) UNIQUE,
            magnitude DECIMAL(4,2),
            place VARCHAR(255),
            latitude DECIMAL(10,6),
            longitude DECIMAL(10,6),
            depth DECIMAL(8,2),
            event_time DATETIME,
            tsunami TINYINT(1) DEFAULT 0,
            source VARCHAR(50) DEFAULT 'USGS',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_magnitude (magnitude),
            INDEX idx_time (event_time)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Government Notices Table
    $db->query("
        CREATE TABLE IF NOT EXISTS government_notices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            notice_type VARCHAR(50),
            source VARCHAR(100),
            notice_date DATE,
            expiry_date DATE,
            url VARCHAR(500),
            is_featured TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_date (notice_date),
            INDEX idx_featured (is_featured)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}
