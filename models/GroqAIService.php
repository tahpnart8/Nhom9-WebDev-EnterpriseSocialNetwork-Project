<?php
/**
 * GroqAIService — Service gọi Groq AI API (LLaMA).
 * Tách logic cURL lặp lại ở nhiều controller thành 1 service duy nhất.
 */
class GroqAIService {
    private string $apiKey;
    private string $model;
    private string $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

    /**
     * @param string $apiKey  Groq API key
     * @param string $model   Tên model AI (mặc định: llama-3.3-70b-versatile)
     */
    public function __construct(string $apiKey, string $model = 'llama-3.3-70b-versatile') {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    /**
     * Gửi prompt đến Groq AI và nhận kết quả.
     *
     * @param string $systemPrompt  Hướng dẫn hệ thống cho AI
     * @param string $userPrompt    Nội dung người dùng gửi
     * @param float  $temperature   Độ sáng tạo (0.0 - 1.0)
     * @param int    $maxTokens     Số token tối đa
     * @param int    $timeout       Timeout (giây)
     * @return array ['success' => bool, 'content' => string, 'error' => string]
     */
    public function generate(
        string $systemPrompt,
        string $userPrompt,
        float $temperature = 0.7,
        int $maxTokens = 1500,
        int $timeout = 60
    ): array {
        $postData = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        // Bypass SSL verification on Windows/XAMPP (no CA bundle)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'content' => '', 'error' => 'Lỗi kết nối Groq API: ' . $curlError];
        }

        if ($httpCode !== 200) {
            $errBody = json_decode($response, true);
            $errMsg = $errBody['error']['message'] ?? ('HTTP ' . $httpCode);
            return ['success' => false, 'content' => '', 'error' => 'Lỗi Groq API: ' . $errMsg];
        }

        $result = json_decode($response, true);
        $aiContent = $result['choices'][0]['message']['content'] ?? '';

        return ['success' => true, 'content' => $aiContent, 'error' => ''];
    }
}
?>
