<?php
/**
 * AI Service - Multi-provider AI integration
 * Supports: OpenAI (ChatGPT), Google Gemini, DeepSeek
 */

class AIService {
    private $provider;
    private $model;
    private $api_key;
    private $temperature;
    private $max_tokens;
    
    public function __construct() {
        $this->provider = setting('ai_provider', 'openai');
        $this->model = setting('ai_model', 'gpt-4o-mini');
        $this->temperature = (float) setting('ai_temperature', 0.7);
        $this->max_tokens = (int) setting('ai_max_tokens', 1000);
        
        switch ($this->provider) {
            case 'gemini':
                $this->api_key = setting('gemini_key', '');
                break;
            case 'deepseek':
                $this->api_key = setting('deepseek_key', '');
                break;
            default:
                $this->api_key = setting('openai_key', '');
        }
    }
    
    public function isEnabled(): bool {
        return setting('ai_enabled', '0') === '1' && !empty($this->api_key);
    }
    
    public function getProvider(): string {
        return $this->provider;
    }
    
    /**
     * Send chat message and get response
     */
    public function chat(string $message, array $context = []): string {
        if (!$this->isEnabled()) {
            return 'AI is not configured. Please contact the administrator.';
        }
        
        // Build system prompt with context
        $system_prompt = $this->buildSystemPrompt($context);
        
        switch ($this->provider) {
            case 'gemini':
                return $this->chatGemini($message, $system_prompt);
            case 'deepseek':
                return $this->chatDeepSeek($message, $system_prompt);
            default:
                return $this->chatOpenAI($message, $system_prompt);
        }
    }
    
    /**
     * Summarize article content
     */
    public function summarize(string $content, string $title = ''): string {
        if (!$this->isEnabled()) {
            return 'AI is not configured.';
        }
        
        $prompt = "You are a professional news summarizer. Summarize the following article in 2-3 concise sentences in Nepali/English mixed language. Focus on the key points and main facts.\n\n";
        if ($title) {
            $prompt .= "Title: $title\n\n";
        }
        $prompt .= "Content:\n" . substr($content, 0, 4000);
        
        return $this->chat($prompt);
    }
    
    /**
     * Build system prompt with site context
     */
    private function buildSystemPrompt(array $context = []): string {
        $site_name = site_name();
        $site_name_np = site_name_np();
        $site_url = rtrim(setting('site_url', 'https://example.com'), '/');
        
        $prompt = "You are an AI assistant for {$site_name} ({$site_name_np}), a Nepali news portal. ";
        $prompt .= "Your role is to help users find news, summarize articles, and answer questions about the site.\n\n";
        
        $prompt .= "Guidelines:\n";
        $prompt .= "- Respond in Nepali when user writes in Nepali, otherwise in English\n";
        $prompt .= "- Be helpful, friendly, and concise\n";
        $prompt .= "- For news queries, suggest relevant categories and recent articles\n";
        $prompt .= "- Do not make up news or information not present on the site\n";
        $prompt .= "- If you don't know something, say you don't know\n";
        $prompt .= "- Do not share internal admin information\n";
        
        if (!empty($context['categories'])) {
            $prompt .= "\nAvailable categories: " . implode(', ', $context['categories']) . "\n";
        }
        
        if (!empty($context['recent_articles'])) {
            $prompt .= "\nRecent articles:\n";
            foreach (array_slice($context['recent_articles'], 0, 5) as $article) {
                $prompt .= "- {$article['title']} ({$article['category']})\n";
            }
        }
        
        return $prompt;
    }
    
    /**
     * OpenAI (ChatGPT) API
     */
    private function chatOpenAI(string $message, string $system_prompt): string {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $message]
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->max_tokens,
        ];
        
        return $this->makeRequest($url, $data, [
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json'
        ]);
    }
    
    /**
     * Google Gemini API
     */
    private function chatGemini(string $message, string $system_prompt): string {
        $model_map = [
            'gemini-2.0-flash' => 'gemini-2.0-flash-exp',
            'gemini-1.5-pro' => 'gemini-1.5-pro',
            'gemini-1.5-flash' => 'gemini-1.5-flash',
        ];
        
        $model = $model_map[$this->model] ?? 'gemini-1.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $this->api_key;
        
        $contents = [
            ['role' => 'user', 'parts' => [['text' => $system_prompt . "\n\nUser: " . $message]]]
        ];
        
        $data = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $this->temperature,
                'maxOutputTokens' => $this->max_tokens,
            ]
        ];
        
        return $this->makeRequest($url, $data, [
            'Content-Type: application/json'
        ], 'gemini');
    }
    
    /**
     * DeepSeek API
     */
    private function chatDeepSeek(string $message, string $system_prompt): string {
        $url = 'https://api.deepseek.com/v1/chat/completions';
        
        $model = $this->model === 'deepseek-coder' ? 'deepseek-coder' : 'deepseek-chat';
        
        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $message]
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->max_tokens,
        ];
        
        return $this->makeRequest($url, $data, [
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json'
        ]);
    }
    
    /**
     * Make HTTP request to AI API
     */
    private function makeRequest(string $url, array $data, array $headers = [], string $provider = 'openai'): string {
        $ch = curl_init($url);
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return 'Error: ' . $error;
        }
        
        if ($http_code !== 200) {
            return 'Error: API request failed (HTTP ' . $http_code . ')';
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Error: Invalid JSON response';
        }
        
        // Parse response based on provider
        return $this->parseResponse($decoded, $provider);
    }
    
    /**
     * Parse API response
     */
    private function parseResponse(array $data, string $provider): string {
        switch ($provider) {
            case 'gemini':
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return trim($data['candidates'][0]['content']['parts'][0]['text']);
                }
                if (isset($data['error'])) {
                    return 'Error: ' . ($data['error']['message'] ?? 'Unknown error');
                }
                break;
                
            default: // OpenAI and DeepSeek
                if (isset($data['choices'][0]['message']['content'])) {
                    return trim($data['choices'][0]['message']['content']);
                }
                if (isset($data['error'])) {
                    return 'Error: ' . ($data['error']['message'] ?? 'Unknown error');
                }
        }
        
        return 'Error: Unable to parse response';
    }
    
    /**
     * Search news using AI understanding
     */
    public function searchNews(string $query, int $limit = 5): array {
        $db = get_db();
        
        // Simple text search in articles
        $stmt = $db->prepare("
            SELECT a.id, a.title, a.title_np, a.summary, a.slug, 
                   c.name as category, c.slug as category_slug,
                   a.published_at
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.status = 'published' 
            AND (a.title LIKE :q OR a.title_np LIKE :q OR a.summary LIKE :q OR a.content LIKE :q)
            ORDER BY a.published_at DESC
            LIMIT :limit
        ");
        
        $search_term = '%' . $query . '%';
        $stmt->bindValue(':q', $search_term, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * Get AI service instance
 */
function get_ai_service(): AIService {
    static $instance = null;
    if ($instance === null) {
        $instance = new AIService();
    }
    return $instance;
}

/**
 * Check if AI is enabled
 */
function ai_is_enabled(): bool {
    return get_ai_service()->isEnabled();
}
