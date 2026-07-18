<?php
/**
 * Live Data Service
 * Fetches real-time data from various APIs for Nepal
 * Earthquake, Weather, Alerts, Government Notices
 */

class LiveDataService {
    private $db;
    
    // API Endpoints
    const USGS_EARTHQUAKE_API = 'https://earthquake.usgs.gov/fdsnws/event/1/query';
    const WEATHER_API_BASE = 'https://api.open-meteo.com/v1/forecast';
    const ALERT_API_BASE = 'https://api.reliefweb.int/v1/reports';
    
    // Cache duration in seconds
    const CACHE_DURATION = 300; // 5 minutes
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get recent earthquakes in Nepal region
     */
    public function getEarthquakes(int $limit = 10): array {
        $cache_key = 'earthquakes_nepal';
        $cached = $this->getCached($cache_key);
        if ($cached) return $cached;
        
        try {
            // Nepal bounding box: lat 26-31, lon 80-89
            $url = self::USGS_EARTHQUAKE_API . '?' . http_build_query([
                'format' => 'geojson',
                'starttime' => date('Y-m-d', strtotime('-7 days')),
                'minlatitude' => 26,
                'maxlatitude' => 31,
                'minlongitude' => 80,
                'maxlongitude' => 89,
                'orderby' => 'time',
                'limit' => $limit
            ]);
            
            $response = $this->fetchUrl($url);
            if (!$response) return [];
            
            $data = json_decode($response, true);
            if (!isset($data['features'])) return [];
            
            $earthquakes = [];
            foreach ($data['features'] as $quake) {
                $props = $quake['properties'];
                $coords = $quake['geometry']['coordinates'];
                
                $earthquakes[] = [
                    'id' => $quake['id'],
                    'magnitude' => $props['mag'] ?? 0,
                    'place' => $props['place'] ?? 'Nepal',
                    'time' => date('Y-m-d H:i:s', (int)($props['time'] ?? time()) / 1000),
                    'latitude' => $coords[1],
                    'longitude' => $coords[0],
                    'depth' => $coords[2],
                    'url' => $props['url'] ?? '',
                    'tsunami' => $props['tsunami'] ?? 0,
                    'mag_type' => $props['magType'] ?? 'ml',
                ];
            }
            
            $this->setCached($cache_key, $earthquakes);
            return $earthquakes;
            
        } catch (Exception $e) {
            error_log('Earthquake API Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get weather data for Kathmandu
     */
    public function getWeather(string $city = 'Kathmandu'): array {
        $cache_key = 'weather_' . strtolower($city);
        $cached = $this->getCached($cache_key);
        if ($cached) return $cached;
        
        try {
            // Kathmandu coordinates: 27.7172, 85.3241
            $url = self::WEATHER_API_BASE . '?' . http_build_query([
                'latitude' => 27.7172,
                'longitude' => 85.3241,
                'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,cloud_cover,wind_speed_10m,wind_direction_10m',
                'hourly' => 'temperature_2m,precipitation_probability,weather_code',
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,weather_code',
                'timezone' => 'Asia/Kathmandu',
                'forecast_days' => 7
            ]);
            
            $response = $this->fetchUrl($url);
            if (!$response) return [];
            
            $data = json_decode($response, true);
            if (!isset($data['current'])) return [];
            
            $weather = [
                'city' => $city,
                'current' => [
                    'temperature' => $data['current']['temperature_2m'] ?? 0,
                    'feels_like' => $data['current']['apparent_temperature'] ?? 0,
                    'humidity' => $data['current']['relative_humidity_2m'] ?? 0,
                    'precipitation' => $data['current']['precipitation'] ?? 0,
                    'weather_code' => $data['current']['weather_code'] ?? 0,
                    'weather_text' => $this->getWeatherText($data['current']['weather_code'] ?? 0),
                    'cloud_cover' => $data['current']['cloud_cover'] ?? 0,
                    'wind_speed' => $data['current']['wind_speed_10m'] ?? 0,
                    'wind_direction' => $data['current']['wind_direction_10m'] ?? 0,
                    'wind_direction_text' => $this->getWindDirection($data['current']['wind_direction_10m'] ?? 0),
                ],
                'hourly' => [],
                'daily' => [],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Process hourly data (next 24 hours)
            if (isset($data['hourly'])) {
                for ($i = 0; $i < min(24, count($data['hourly']['time'])); $i++) {
                    $weather['hourly'][] = [
                        'time' => $data['hourly']['time'][$i],
                        'temp' => $data['hourly']['temperature_2m'][$i],
                        'precip_prob' => $data['hourly']['precipitation_probability'][$i],
                        'weather_text' => $this->getWeatherText($data['hourly']['weather_code'][$i])
                    ];
                }
            }
            
            // Process daily data (next 7 days)
            if (isset($data['daily'])) {
                for ($i = 0; $i < min(7, count($data['daily']['time'])); $i++) {
                    $weather['daily'][] = [
                        'date' => $data['daily']['time'][$i],
                        'temp_max' => $data['daily']['temperature_2m_max'][$i],
                        'temp_min' => $data['daily']['temperature_2m_min'][$i],
                        'precipitation' => $data['daily']['precipitation_sum'][$i],
                        'weather_text' => $this->getWeatherText($data['daily']['weather_code'][$i])
                    ];
                }
            }
            
            $this->setCached($cache_key, $weather);
            return $weather;
            
        } catch (Exception $e) {
            error_log('Weather API Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get weather alerts/warnings
     */
    public function getWeatherAlerts(): array {
        $cache_key = 'weather_alerts';
        $cached = $this->getCached($cache_key);
        if ($cached) return $cached;
        
        try {
            // Use Open-Meteo weather codes to determine alerts
            // Check for severe weather conditions
            $weather = $this->getWeather();
            $alerts = [];
            
            if (!empty($weather) && isset($weather['current'])) {
                $code = $weather['current']['weather_code'];
                
                // Check for severe conditions
                $severe_codes = [
                    95 => ['type' => 'Thunderstorm', 'severity' => 'severe', 'text' => 'आँधीबेरी हुन सक्छ। सावधान रहनुहोस्।'],
                    96 => ['type' => 'Thunderstorm', 'severity' => 'severe', 'text' => 'गम्भीर आँधीबेरी हुन सक्छ।'],
                    99 => ['type' => 'Thunderstorm', 'severity' => 'extreme', 'text' => 'अत्यन्त गम्भीर आँधीबेरी। खतरनाक।'],
                    61 => ['type' => 'Rain', 'severity' => 'moderate', 'text' => 'हल्का वर्षाको सम्भावना।'],
                    63 => ['type' => 'Rain', 'severity' => 'moderate', 'text' => 'मध्यम वर्षा हुन सक्छ।'],
                    65 => ['type' => 'Rain', 'severity' => 'severe', 'text' => 'भारी वर्षाको चेतावनी।'],
                    71 => ['type' => 'Snow', 'severity' => 'moderate', 'text' => 'हिमपातको सम्भावना।'],
                    73 => ['type' => 'Snow', 'severity' => 'severe', 'text' => 'भारी हिमपात हुन सक्छ।'],
                    75 => ['type' => 'Snow', 'severity' => 'extreme', 'text' => 'अत्यन्त भारी हिमपात।'],
                    80 => ['type' => 'Rain', 'severity' => 'moderate', 'text' => 'स्थानीय वर्षा।'],
                    81 => ['type' => 'Rain', 'severity' => 'moderate', 'text' => 'मध्यम वर्षा।'],
                    82 => ['type' => 'Rain', 'severity' => 'severe', 'text' => 'भारी वर्षाको सम्भावना।'],
                ];
                
                if (isset($severe_codes[$code])) {
                    $alerts[] = [
                        'type' => $severe_codes[$code]['type'],
                        'severity' => $severe_codes[$code]['severity'],
                        'text' => $severe_codes[$code]['text'],
                        'time' => date('Y-m-d H:i:s'),
                        'source' => 'Weather Service'
                    ];
                }
            }
            
            $this->setCached($cache_key, $alerts, 600);
            return $alerts;
            
        } catch (Exception $e) {
            error_log('Weather Alerts Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get disaster alerts from ReliefWeb
     */
    public function getDisasterAlerts(): array {
        $cache_key = 'disaster_alerts';
        $cached = $this->getCached($cache_key);
        if ($cached) return $cached;
        
        try {
            // ReliefWeb API for Nepal disasters
            $url = self::ALERT_API_BASE . '?' . http_build_query([
                'app_name' => 'nepal-news-portal',
                'filter' => [
                    'field' => 'country',
                    'value' => 'Nepal'
                ],
                'limit' => 10,
                'sort' => ['field' => 'date.created', 'order' => 'desc']
            ]);
            
            // Note: ReliefWeb requires authentication for some endpoints
            // This is a simplified version
            $alerts = [];
            
            // Check for any cached earthquake-generated alerts
            $earthquakes = $this->getEarthquakes(5);
            foreach ($earthquakes as $eq) {
                if ($eq['magnitude'] >= 4.0) {
                    $alerts[] = [
                        'type' => 'Earthquake',
                        'severity' => $eq['magnitude'] >= 5.5 ? 'severe' : 'moderate',
                        'text' => "{$eq['magnitude']} magnitude earthquake reported near {$eq['place']}",
                        'magnitude' => $eq['magnitude'],
                        'time' => $eq['time'],
                        'source' => 'USGS',
                        'url' => $eq['url']
                    ];
                }
            }
            
            $this->setCached($cache_key, $alerts, 600);
            return $alerts;
            
        } catch (Exception $e) {
            error_log('Disaster Alerts Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get upcoming government notices/events
     */
    public function getGovernmentNotices(): array {
        $cache_key = 'gov_notices';
        $cached = $this->getCached($cache_key);
        if ($cached) return $cached;
        
        // Static notices that would typically come from government sources
        // In production, these would come from actual government APIs
        $notices = [
            [
                'title' => 'राष्ट्रिय तथ्यांक कार्यालय - जनगणना २०७८',
                'description' => 'राष्ट्रिय जनगणना २०७८ को प्रारम्भिक नतिजा सार्वजनिक',
                'date' => date('Y-m-d'),
                'source' => 'CBS Nepal',
                'url' => 'https://cbs.gov.np'
            ],
            [
                'title' => 'मौसम पूर्वानुमान - मनसुन',
                'description' => 'नेपालमा मनसुन सक्रिय, स्थानीय वर्षाको सम्भावना',
                'date' => date('Y-m-d'),
                'source' => 'Department of Hydrology and Meteorology',
                'url' => 'https://dhm.gov.np'
            ],
            [
                'title' => 'भूकम्प सुरक्षा दिवस',
                'description' => 'प्रत्येक माघ २ गते भूकम्प सुरक्षा दिवस मनाइन्छ',
                'date' => date('Y-m-d'),
                'source' => 'NDRRMA',
                'url' => 'https://ndrma.gov.np'
            ]
        ];
        
        $this->setCached($cache_key, $notices, 3600);
        return $notices;
    }
    
    /**
     * Get air quality data for Kathmandu
     */
    public function getAirQuality(): array {
        $cache_key = 'air_quality';
        $cached = $this->getCached($cache_key);
        if ($cached) return $cached;
        
        try {
            // Using Open-Meteo Air Quality API
            $url = 'https://air-quality-api.open-meteo.com/v1/air-quality?' . http_build_query([
                'latitude' => 27.7172,
                'longitude' => 85.3241,
                'current' => 'us_aqi,pm2_5,pm10,ozone,nitrogen_dioxide,sulphur_dioxide,carbon_monoxide',
                'timezone' => 'Asia/Kathmandu'
            ]);
            
            $response = $this->fetchUrl($url);
            if (!$response) return [];
            
            $data = json_decode($response, true);
            if (!isset($data['current'])) return [];
            
            $aqi = $data['current']['us_aqi'] ?? 0;
            $quality = $this->getAQIText($aqi);
            
            $air = [
                'aqi' => $aqi,
                'status' => $quality['status'],
                'status_np' => $quality['status_np'],
                'pm25' => $data['current']['pm2_5'] ?? 0,
                'pm10' => $data['current']['pm10'] ?? 0,
                'ozone' => $data['current']['ozone'] ?? 0,
                'no2' => $data['current']['nitrogen_dioxide'] ?? 0,
                'so2' => $data['current']['sulphur_dioxide'] ?? 0,
                'co' => $data['current']['carbon_monoxide'] ?? 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->setCached($cache_key, $air, 600);
            return $air;
            
        } catch (Exception $e) {
            error_log('Air Quality API Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get sunrise/sunset times for Kathmandu
     */
    public function getSunTimes(): array {
        $cache_key = 'sun_times';
        $cached = $this->getCached($cache_key);
        if ($cached) return $cached;
        
        try {
            $url = 'https://api.open-meteo.com/v1/forecast?' . http_build_query([
                'latitude' => 27.7172,
                'longitude' => 85.3241,
                'daily' => 'sunrise,sunset',
                'timezone' => 'Asia/Kathmandu',
                'forecast_days' => 1
            ]);
            
            $response = $this->fetchUrl($url);
            if (!$response) return [];
            
            $data = json_decode($response, true);
            if (!isset($data['daily'])) return [];
            
            $sun = [
                'sunrise' => date('H:i', strtotime($data['daily']['sunrise'][0])),
                'sunset' => date('H:i', strtotime($data['daily']['sunset'][0])),
                'date' => $data['daily']['time'][0]
            ];
            
            $this->setCached($cache_key, $sun, 3600);
            return $sun;
            
        } catch (Exception $e) {
            error_log('Sun Times API Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get Nepal Stock Exchange data
     */
    public function getStockData(): array {
        $cache_key = 'stock_data';
        $cached = $this->getCached($cache_key);
        if ($cached) return $cached;
        
        try {
            // NEPSE API (unofficial)
            $url = 'https://www.nepalipaisa.com/api/GetMarketCap?type=main';
            $response = $this->fetchUrl($url);
            
            $stock = [];
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data[0])) {
                    $stock = [
                        'nepse_index' => $data[0]['nepseIndex'] ?? 0,
                        'index_change' => $data[0]['indexChange'] ?? 0,
                        'change_percent' => $data[0]['percentChange'] ?? 0,
                        'market_cap' => $data[0]['marketCap'] ?? 0,
                        'total_volume' => $data[0]['totalVolume'] ?? 0,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
            }
            
            $this->setCached($cache_key, $stock, 300);
            return $stock;
            
        } catch (Exception $e) {
            error_log('Stock API Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get gold and fuel prices (static data - would need real API)
     */
    public function getCommodityPrices(): array {
        $cache_key = 'commodity_prices';
        $cached = $this->getCached($cache_key);
        if ($cached) return $cached;
        
        // Static commodity prices - in production would fetch from real APIs
        $prices = [
            'gold_22k' => [
                'name' => 'सुन २२ क्यारेट',
                'price' => '₹ ७,२५,०००',
                'unit' => 'प्रति तोला',
                'change' => '+₹ १,५००',
                'trend' => 'up'
            ],
            'gold_24k' => [
                'name' => 'सुन २४ क्यारेट',
                'price' => '₹ ७,४५,०००',
                'unit' => 'प्रति तोला',
                'change' => '+₹ १,५००',
                'trend' => 'up'
            ],
            'petrol' => [
                'name' => 'पेट्रोल',
                'price' => '₹ १७३',
                'unit' => 'प्रति लिटर',
                'change' => '₹ ०',
                'trend' => 'stable'
            ],
            'diesel' => [
                'name' => 'डिजेल',
                'price' => '₹ १६८',
                'unit' => 'प्रति लिटर',
                'change' => '₹ ०',
                'trend' => 'stable'
            ]
        ];
        
        $this->setCached($cache_key, $prices, 3600);
        return $prices;
    }
    
    /**
     * Get all live dashboard data
     */
    public function getDashboard(): array {
        return [
            'earthquakes' => $this->getEarthquakes(5),
            'weather' => $this->getWeather(),
            'air_quality' => $this->getAirQuality(),
            'sun_times' => $this->getSunTimes(),
            'alerts' => array_merge($this->getWeatherAlerts(), $this->getDisasterAlerts()),
            'notices' => $this->getGovernmentNotices(),
            'commodities' => $this->getCommodityPrices(),
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    // Helper methods
    
    private function fetchUrl(string $url): ?string {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        return $response ?: null;
    }
    
    private function getCached(string $key): ?array {
        try {
            $expires = date('Y-m-d H:i:s');
            $stmt = $this->db->prepare("SELECT data, expires_at FROM api_cache WHERE cache_key = ? AND expires_at > ?");
            $stmt->execute([$key, $expires]);
            $row = $stmt->fetch();
            if ($row) {
                return json_decode($row['data'], true);
            }
        } catch (Exception $e) {
            // Table might not exist
        }
        return null;
    }
    
    private function setCached(string $key, array $data, int $duration = self::CACHE_DURATION): void {
        try {
            $expires = date('Y-m-d H:i:s', time() + $duration);
            $stmt = $this->db->prepare("
                INSERT INTO api_cache (cache_key, data, expires_at)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE data = VALUES(data), expires_at = VALUES(expires_at)
            ");
            $stmt->execute([$key, json_encode($data), $expires]);
        } catch (Exception $e) {
            // Table might not exist
        }
    }
    }
    
    private function getWeatherText(int $code): string {
        $codes = [
            0 => 'Clear sky',
            1 => 'Mainly clear',
            2 => 'Partly cloudy',
            3 => 'Overcast',
            45 => 'Foggy',
            48 => 'Depositing rime fog',
            51 => 'Light drizzle',
            53 => 'Moderate drizzle',
            55 => 'Dense drizzle',
            61 => 'Slight rain',
            63 => 'Moderate rain',
            65 => 'Heavy rain',
            71 => 'Slight snow',
            73 => 'Moderate snow',
            75 => 'Heavy snow',
            77 => 'Snow grains',
            80 => 'Slight rain showers',
            81 => 'Moderate rain showers',
            82 => 'Violent rain showers',
            85 => 'Slight snow showers',
            86 => 'Heavy snow showers',
            95 => 'Thunderstorm',
            96 => 'Thunderstorm with slight hail',
            99 => 'Thunderstorm with heavy hail'
        ];
        
        $nepali = [
            0 => 'सफा', 1 => 'मुख्यतः सफा', 2 => 'आंशिक बादल', 3 => 'बादल',
            45 => ' हु mist', 48 => 'हिउँ जमेको कु mist',
            51 => 'हल्का पानी', 53 => 'मध्यम पानी', 55 => 'घना पानी',
            61 => 'हल्का वर्षा', 63 => 'मध्यम वर्षा', 65 => 'भारी वर्षा',
            71 => 'हल्का हिउँ', 73 => 'मध्यम हिउँ', 75 => 'भारी हिउँ',
            77 => 'हिउँका दाना', 80 => 'हल्का पानी', 81 => 'मध्यम पानी', 82 => 'भारी पानी',
            85 => 'हल्का हिउँ', 86 => 'भारी हिउँ',
            95 => 'आँधीबेरी', 96 => 'आँधीबेरी र अल्ली', 99 => 'गम्भीर आँधीबेरी'
        ];
        
        return $nepali[$code] ?? $codes[$code] ?? 'Unknown';
    }
    
    private function getWindDirection(int $deg): string {
        $dirs = ['उ', 'उ-पू', 'पू', 'द-पू', 'द', 'द-प', 'प', 'उ-प'];
        return $dirs[(int)round($deg / 45) % 8] ?? 'N/A';
    }
    
    private function getAQIText(int $aqi): array {
        if ($aqi <= 50) return ['status' => 'Good', 'status_np' => 'राम्रो', 'color' => 'green'];
        if ($aqi <= 100) return ['status' => 'Moderate', 'status_np' => 'ठीक', 'color' => 'yellow'];
        if ($aqi <= 150) return ['status' => 'Unhealthy for Sensitive', 'status_np' => 'संवेदनशीलको लागि अस्वस्थ', 'color' => 'orange'];
        if ($aqi <= 200) return ['status' => 'Unhealthy', 'status_np' => 'अस्वस्थ', 'color' => 'red'];
        if ($aqi <= 300) return ['status' => 'Very Unhealthy', 'status_np' => 'धेरै अस्वस्थ', 'color' => 'purple'];
        return ['status' => 'Hazardous', 'status_np' => 'खतरनाक', 'color' => 'maroon'];
    }
}

// Global function
function live_data(): LiveDataService {
    static $service = null;
    if ($service === null) {
        $service = new LiveDataService();
    }
    return $service;
}
