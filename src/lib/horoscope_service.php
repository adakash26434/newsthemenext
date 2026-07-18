<?php
/**
 * Horoscope & Astrology Service
 * Fetches real astrology data for Nepali news portal
 */

class HoroscopeService {
    private $db;
    
    // Zodiac signs with Nepali names
    const ZODIAC_SIGNS = [
        'mesh'      => ['name' => 'मेष', 'symbol' => '♈', 'dates' => 'मार्च 21 - अप्रिल 19'],
        'vrishabha' => ['name' => 'वृषभ', 'symbol' => '♉', 'dates' => 'अप्रिल 20 - मे 20'],
        'mithun'    => ['name' => 'मिथुन', 'symbol' => '♊', 'dates' => 'मे 21 - जुन 20'],
        'karkat'    => ['name' => 'कर्कट', 'symbol' => '♋', 'dates' => 'जुन 21 - जुलाई 22'],
        'sinh'      => ['name' => 'सिंह', 'symbol' => '♌', 'dates' => 'जुलाई 23 - अगस्ट 22'],
        'kanya'     => ['name' => 'कन्या', 'symbol' => '♍', 'dates' => 'अगस्ट 23 - सेप्टेम्बर 22'],
        'tula'      => ['name' => 'तुला', 'symbol' => '♎', 'dates' => 'सेप्टेम्बर 23 - अक्टोबर 22'],
        'vrishchik' => ['name' => 'वृश्चिक', 'symbol' => '♏', 'dates' => 'अक्टोबर 23 - नोभेम्बर 21'],
        'dhanu'     => ['name' => 'धनु', 'symbol' => '♐', 'dates' => 'नोभेम्बर 22 - डिसेम्बर 21'],
        'makar'     => ['name' => 'मकर', 'symbol' => '♑', 'dates' => 'डिसेम्बर 22 - जनुयरी 19'],
        'kumbha'    => ['name' => 'कुम्भ', 'symbol' => '♒', 'dates' => 'जनुयरी 20 - फेब्रुअरी 18'],
        'meen'      => ['name' => 'मीन', 'symbol' => '♓', 'dates' => 'फेब्रुअरी 19 - मार्च 20'],
    ];
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get all zodiac signs
     */
    public function getZodiacSigns(): array {
        return self::ZODIAC_SIGNS;
    }
    
    /**
     * Get sign by key
     */
    public function getSign(string $key): ?array {
        return self::ZODIAC_SIGNS[$key] ?? null;
    }
    
    /**
     * Get today's rashifal for a sign
     */
    public function getDailyRashifal(string $sign): ?array {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT * FROM horoscope_daily WHERE sign = ? AND date = ? LIMIT 1");
        $stmt->execute([$sign, $today]);
        $result = $stmt->fetch();
        if (!$result) {
            // Try to get the most recent one if today's is not available
            $stmt = $this->db->prepare("SELECT * FROM horoscope_daily WHERE sign = ? ORDER BY date DESC LIMIT 1");
            $stmt->execute([$sign]);
            $result = $stmt->fetch();
        }
        return $result ?: null;
    }
    
    /**
     * Get monthly rashifal
     */
    public function getMonthlyRashifal(string $sign, int $month = 0): ?array {
        if ($month == 0) $month = (int)date('n');
        $year = (int)date('Y');
        
        $stmt = $this->db->prepare("SELECT * FROM horoscope_monthly WHERE sign = ? AND month = ? AND year = ? LIMIT 1");
        $stmt->execute([$sign, $month, $year]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Get yearly rashifal
     */
    public function getYearlyRashifal(string $sign, int $year = 0): ?array {
        if ($year == 0) $year = (int)date('Y');
        
        $stmt = $this->db->prepare("SELECT * FROM horoscope_yearly WHERE sign = ? AND year = ? LIMIT 1");
        $stmt->execute([$sign, $year]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Get today's auspicious times (Subha Samaya)
     */
    public function getTodayAuspiciousTimes(): ?array {
        $nepali_date = $this->getCurrentNepaliDate();
        $stmt = $this->db->prepare("SELECT * FROM auspicious_times WHERE nepali_date = ? LIMIT 1");
        $stmt->execute([$nepali_date]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Get auspicious days for this month
     */
    public function getAuspiciousDays(int $month = 0, int $year = 0): array {
        if ($month == 0) $month = (int)date('n');
        if ($year == 0) $year = (int)date('Y');
        
        $stmt = $this->db->prepare("SELECT * FROM auspicious_days WHERE month = ? AND year = ? ORDER BY day ASC");
        $stmt->execute([$month, $year]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get Lagna (Ascendant) info for today
     */
    public function getTodayLagna(): ?array {
        $nepali_date = $this->getCurrentNepaliDate();
        $stmt = $this->db->prepare("SELECT * FROM lagna_info WHERE nepali_date = ? LIMIT 1");
        $stmt->execute([$nepali_date]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Get Gud Milan (Compatibility) for two signs
     */
    public function getGudMilan(string $boy_sign, string $girl_sign): ?array {
        $stmt = $this->db->prepare("SELECT * FROM gud_milan WHERE boy_sign = ? AND girl_sign = ? LIMIT 1");
        $stmt->execute([$boy_sign, $girl_sign]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Get Bastu recommendations
     */
    public function getBastuRecommendations(string $sign): ?array {
        $stmt = $this->db->prepare("SELECT * FROM bastu_recommendations WHERE sign = ? LIMIT 1");
        $stmt->execute([$sign]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Get current Nepali date
     */
    public function getCurrentNepaliDate(): string {
        $english_date = date('Y-m-d');
        return $this->convertToNepaliDate($english_date);
    }
    
    /**
     * Convert English date to Nepali (approximate)
     */
    private function convertToNepaliDate(string $eng_date): string {
        $nepali_months = ['बैशाख', 'जेठ', 'असार', 'श्रावण', 'भदौ', 'असोज', 'कार्तिक', 'मंसिर', 'पौष', 'माघ', 'फाल्गुन', 'चैत्र'];
        $month = (int)date('n', strtotime($eng_date)) - 1;
        if ($month < 0) $month = 11;
        $day = (int)date('j', strtotime($eng_date));
        $year = (int)date('Y', strtotime($eng_date));
        $bs_year = $year + 56;
        
        return $nepali_months[$month] . ' ' . $day . ', ' . $bs_year;
    }
    
    /**
     * Get today's rashifal for all signs (for dropdown/rss)
     */
    public function getAllDailyRashifal(): array {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT * FROM horoscope_daily WHERE date = ?");
        $stmt->execute([$today]);
        return $stmt->fetchAll();
    }
    
    /**
     * Calculate rating (1-5 stars) based on score
     */
    public function calculateRating(float $score): int {
        return min(5, max(1, (int)round($score)));
    }
    
    /**
     * Get lucky color for sign
     */
    public function getLuckyColor(string $sign): string {
        $colors = [
            'mesh'      => 'रातो (Red)',
            'vrishabha' => 'सेतो (White)',
            'mithun'    => 'हरियो (Green)',
            'karkat'    => 'पहेंलो (Yellow)',
            'sinh'      => 'सुनौलो (Gold)',
            'kanya'     => 'खैरो (Grey)',
            'tula'      => 'पार्दल (Pink)',
            'vrishchik' => 'रातो (Red)',
            'dhanu'     => 'हरियो (Green)',
            'makar'     => 'कालो (Black)',
            'kumbha'    => 'नीलो (Blue)',
            'meen'      => 'सुनौलो (Gold)',
        ];
        return $colors[$sign] ?? 'सेतो (White)';
    }
    
    /**
     * Get lucky number for sign
     */
    public function getLuckyNumber(string $sign): int {
        $numbers = [
            'mesh'      => 9,
            'vrishabha' => 6,
            'mithun'    => 5,
            'karkat'    => 2,
            'sinh'      => 1,
            'kanya'     => 5,
            'tula'      => 6,
            'vrishchik' => 8,
            'dhanu'     => 3,
            'makar'     => 9,
            'kumbha'    => 4,
            'meen'      => 7,
        ];
        return $numbers[$sign] ?? 3;
    }
    
    /**
     * Get lucky direction for sign
     */
    public function getLuckyDirection(string $sign): string {
        $directions = [
            'mesh'      => 'पूर्व (East)',
            'vrishabha' => 'दक्षिण (South)',
            'mithun'    => 'उत्तर (North)',
            'karkat'    => 'पश्चिम (West)',
            'sinh'      => 'उत्तर (North)',
            'kanya'     => 'दक्षिण (South)',
            'tula'      => 'पूर्व (East)',
            'vrishchik' => 'दक्षिण (South)',
            'dhanu'     => 'दक्षिण-पूर्व (South-East)',
            'makar'     => 'उत्तर-पूर्व (North-East)',
            'kumbha'    => 'उत्तर (North)',
            'meen'      => 'पश्चिम (West)',
        ];
        return $directions[$sign] ?? 'पूर्व (East)';
    }
}

// Global function for easy access
function horoscope_service(): HoroscopeService {
    static $service = null;
    if ($service === null) {
        $service = new HoroscopeService();
    }
    return $service;
}
